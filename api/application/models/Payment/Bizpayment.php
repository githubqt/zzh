<?php

/**
 * 支付model
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-23
 */
namespace Payment;

use Custom\YDLib;
use Order\OrderModel;
use Order\OrderChildModel;
use Services\Finance\FinanceService;
use User\UserSupplierModel;
use User\UserModel;
use User\UserSupplierThridModel;
use Seckill\SeckillModel;
use Pay\PayFactory;
use Product\ProductModel;
use Order\OrderChildProductModel;
use Pushmsg\PushmsgModel;
use Pushmsg\PushmsgContentModel;
use Pushmsg\PushmsgRechargeModel;

class BizpaymentModel extends \Common\CommonBase {
	const ORDER_PAY_NOTFOUNT_ONLINE = - 1; // 非线上支付单
	const ORDER_PENDING_PAYMENT = - 2; // 非支付订单
	const ORDER_NOT_PAYMENT = - 3; // 非支付订单
	const USER_NOT_OPENID = - 4;
	const ORDER_PAY_YES_PAY = - 5;
	const PAY_NONE_PAY = 1; // 未支付
	const PAY_YES_PAY = 2; // 已支付
	const PAYMENT_TYPE_WEIXIN = 1;
	const PAYMENT_TYPE_ALIPY = 2;
	const DELIVERY_TYPE_EXPRESS = 0; // 快递
	const DELIVERY_TYPE_SHOP = 1; // 门店自提
	const PAYMENT_TYPE = [ 
			self::PAYMENT_TYPE_WEIXIN => 'weixin',
			self::PAYMENT_TYPE_ALIPY => 'alipay' 
	];
	/**
	 * 定义表名后缀
	 */
	protected static $_tableName = 'payment_transaction';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
	}
	
	/**
	 * 根据表自增ID获取该条记录信息
	 * 
	 * @param int $id
	 *        	表自增ID
	 */
	public static function getInfoByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		$where ['supplier_id'] = SUPPLIER_ID;
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	/**
	 * 回调通知
	 * 
	 * @param interger $type
	 *        	支付公司ID
	 * @return array
	 */
	public static function notify($data) {
		YDLib::testlog ( "call back: PayFactory" );
		$payment = PayFactory::factory ( $data ['type'] );
		$payment->notifyPay ();
	}
	
	/**
	 * 购买成功回执
	 * 
	 * @param array $payInfo        	
	 */
	public static function paySuccess($payInfo) {
		YDLib::testlog ( $payInfo );
		$pdo = self::_pdo ( 'db_w' );
		$pdo->beginTransaction ();
		try {
			
			// 查询充值条数
			$orderInfo = PushmsgRechargeModel::getInfoByID ( $payInfo ['orderid'] );
			// 查询商户短信信息
			$supplier_sms = PushmsgModel::getContent ();
			
			// 更新短信统计
			$upsms = [ ];
			$upsms ['remain_num'] = $supplier_sms ['remain_num'] + $orderInfo ['recharge_num'];
			$upsms ['total_num'] = $supplier_sms ['total_num'] + $orderInfo ['recharge_num'];
			$res = PushmsgModel::updateByID ( $upsms, $supplier_sms ['id'] );
			if ($res === FALSE) {
				$pdo->rollback ();
				YDLib::testlog ( "call payment_data err ,更新短信统计失败:" . json_encode ( $payInfo ['return_payment_data'] ) . " , return_payment_no: " . $payInfo ['return_payment_no'] . " ,id : " . $payInfo ['paymentId'] );
				return FALSE;
			}
			
			// 更新充值状态
			$upsmsrecharge = [ ];
			$upsmsrecharge ['recharge_at'] = date ( "Y-m-d H:i:s" );
			$upsmsrecharge ['old_num'] = $supplier_sms ['remain_num'];
			$upsmsrecharge ['new_num'] = $upsms ['remain_num'];
			$upsmsrecharge ['status'] = self::PAY_YES_PAY;
			$res = PushmsgRechargeModel::updateByID ( $upsmsrecharge, $payInfo ['orderid'] );
			if ($res === FALSE) {
				$pdo->rollback ();
				YDLib::testlog ( "call payment_data err ,更新充值状态失败:" . json_encode ( $payInfo ['return_payment_data'] ) . " , return_payment_no: " . $payInfo ['return_payment_no'] . " ,id : " . $payInfo ['paymentId'] );
				return FALSE;
			}
			
			// 更新三方支付流水状态
			$updata = [ ];
			$updata ['return_payment_data'] = $payInfo ['return_payment_data'];
			$updata ['return_payment_no'] = $payInfo ['return_payment_no'];
			$updata ['status'] = self::PAY_YES_PAY;
			$updata ['pay_time'] = date ( "Y-m-d H:i:s" );
			$resNum = self::updateByID ( $updata, $payInfo ['pt_id'] );
			if ($resNum === FALSE) {
				$pdo->rollback ();
				YDLib::testlog ( "call payment_data err ,更新三方支付流水状态失败:" . json_encode ( $payInfo ['return_payment_data'] ) . " , return_payment_no: " . $payInfo ['return_payment_no'] . " ,id : " . $payInfo ['pt_id'] );
				return FALSE;
			}

            $paymentInfo = self::getInfoByID($payInfo ['pt_id']);
            //收支处理
            $mem = YDLib::getMem('memcache');
            $supplier = $mem->get('supplier_'.SUPPLIER_DOMAIN);
            //商户支出//无需结算
            $finance = new FinanceService();
            $finance->setObjType(FinanceService::SMS_RECHARGE);
            $finance->setObjId($paymentInfo['biz_no']);
            $finance->setAmount($paymentInfo['pay_amount']);
            $finance->setPayType($paymentInfo['pay_type']);
            $finance->setPaymentNo($payInfo ['return_payment_no']);
            $finance->setPaymentId($paymentInfo['id']);
            $finance->setRoleType(FinanceService::ROLE_USER);
            $finance->setRoleObjId(SUPPLIER_ID);
            $finance->setRoleObjName($supplier['company']);
            $finance->setSupplierId(SUPPLIER_ID);
            $finance->setSettleType(FinanceService::SETTLEMENT_IGNORE);
            $finance->out();
			
			$pdo->commit ();
			return TRUE;
		} catch ( Exception $e ) {
			$pdo->rollback ();
			YDLib::testlog ( "call Exception: " . $e->getMessage () . " , paymentId:" . $payInfo ['pt_id'] );
			return FALSE;
		}
	}
	
	/**
	 * 根据一条自增ID更新表记录
	 * 
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateByID($data, $id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$pdo = self::_pdo ( 'db_w' );
		$update = $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ),
				'supplier_id' => SUPPLIER_ID 
		) );
		if ($update) {
			return $update;
		}
		return false;
	}
	/**
	 * 记录入库
	 * 
	 * @param array $data
	 *        	表字段名作为key的数组
	 * @return int 入库成功则返回入库记录的自增ID，否则返回FALSE
	 */
	public static function addData($data, $pdo = null) {
		$data ['supplier_id'] = SUPPLIER_ID;
		$data ['is_del'] = self::DELETE_SUCCESS;
		$data ['created_at'] = date ( "Y-m-d H:i:s" );
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = $pdo ? $pdo : self::_pdo ( 'db_w' );
		return $pdo->insert ( self::$_tableName, $data );
	}
	
	/**
	 * 返回二维码支付url
	 * 
	 * @return array
	 */
	public static function bizpayurl($companyId, $detail) {
		$pay = [ 
				'paymoney' => $detail ['recharge_ament'],
				'title' => '短信充值',
				'body' => '短信充值',
				'payid' => $companyId,
				'order_no' => $detail ['recharge_no'],
				'company' => self::PAYMENT_TYPE [$companyId],
				'paymentid' => $detail ['id'],
				'callback' => "https://" . $_SERVER ['HTTP_HOST'] . "/v1/Bizpayment/notify/id/" . $detail ['id'] . "/identif/" . $_REQUEST ['identif'] 
		];
		$payment = PayFactory::factory ( self::PAYMENT_TYPE [$companyId] );
		return $payment->bizpayurl ( $pay );
	}
}