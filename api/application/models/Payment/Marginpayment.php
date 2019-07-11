<?php

/**
 * 支付model
 * @version v0.01
 * @time 2018-05-23
 */
namespace Payment;

use Custom\YDLib;
use Seckill\SeckillOrderModel;
use Seckill\SeckillModel;
use Services\Finance\FinanceService;

class MarginpaymentModel extends \Common\CommonBase {
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
	 * 支付保证金成功回执
	 * 
	 * @param array $payInfo        	
	 */
	public static function paySuccess($payInfo) {
		YDLib::testlog ( $payInfo );
		$pdo = self::_pdo ( 'db_w' );
		$pdo->beginTransaction ();
		try {
			
			// 更新三方支付流水状态
			$paymentInfo = self::getTransactionByMarginID ( $payInfo ['paymentId'], $payInfo ['out_trade_no'] );
			YDLib::testlog ( "paymentInfo  celect  success: data:" . $paymentInfo);
			if (is_array ( $paymentInfo ) && count ( $paymentInfo ) > 0) {
				
				$resNum = self::updateByID ( [ 
						'return_payment_data' => $payInfo ['return_payment_data'],
						'status' => self::PAY_YES_PAY,
						'return_payment_no' => $payInfo ['return_payment_no'],
						'pay_time' => date ( "Y-m-d H:i:s" ) 
				], $paymentInfo ['id'] );
				if ($resNum === FALSE) {
					YDLib::testlog ( "call payment_data err ,return_payment_data:" . json_encode ( $payInfo ['return_payment_data'] ) . " , return_payment_no: " . $payInfo ['return_payment_no'] . " ,id : " . $paymentInfo ['id'] );
					return;
				}

                //收支处理
                $mem = YDLib::getMem('memcache');
                $supplier = $mem->get('supplier_'.SUPPLIER_DOMAIN);
                //商户收入//需要结算
                $finance = new FinanceService();
                $finance->setObjType(FinanceService::USER_MARGIN_PAYMENT);
                $finance->setObjId($paymentInfo['biz_no']);
                $finance->setAmount($paymentInfo['pay_amount']);
                $finance->setPayType($paymentInfo['pay_type']);
                $finance->setPaymentNo($payInfo ['return_payment_no']);
                $finance->setPaymentId($paymentInfo['id']);
                $finance->setRoleType(FinanceService::ROLE_SUPPLIER);
                $finance->setRoleObjId(SUPPLIER_ID);
                $finance->setRoleObjName($supplier['company']);
                $finance->setSupplierId(SUPPLIER_ID);
                $finance->in();

                //会员支出//无需结算
                $finance = new FinanceService();
                $finance->setObjType(FinanceService::USER_MARGIN_PAYMENT);
                $finance->setObjId($paymentInfo['biz_no']);
                $finance->setAmount($paymentInfo['pay_amount']);
                $finance->setPayType($paymentInfo['pay_type']);
                $finance->setPaymentNo($payInfo ['return_payment_no']);
                $finance->setPaymentId($paymentInfo['id']);
                $finance->setRoleType(FinanceService::ROLE_USER);
                $finance->setRoleObjId($paymentInfo['user_id']);
                $finance->setRoleObjName($paymentInfo['user_name']);
                $finance->setSupplierId(SUPPLIER_ID);
                $finance->setSettleType(FinanceService::SETTLEMENT_IGNORE);
                $finance->out();

			} else {
				$pdo->rollback ();
				YDLib::testlog ( "select getTransactionByCompanyId err , paymentId:" . $payInfo ['paymentId'] . " , out_trade_no:" . $payInfo ['out_trade_no'] );
				return;
			}
			
			// 更新保证金支付状态
			$seckillorder = SeckillOrderModel::getSeckillOrderByID ( $payInfo ['out_trade_no'] );
//			print_r($seckillorder);
//			exit;
			YDLib::testlog ( "seckillorder  celect  success: data:" . $seckillorder);
			if (! empty ( $seckillorder )) {
				if ($seckillorder ['is_margin'] == 1) {
					$data ['is_margin'] = self::PAY_YES_PAY;
					$data ['order_status'] = self::STATUS_SUCCESSFUL_TRADE;
					$payYes = SeckillOrderModel::updateByID ( $data, $seckillorder ['id'] );
					if ($payYes === FALSE) {
						$pdo->rollback ();
						YDLib::testlog ( "order update faild: id: " . $payInfo ['orderid'] . ",supplier_id: " . SUPPLIER_ID . " , paymentId:" . $payInfo ['paymentId'] );
						return;
					} else {
						$seckillInfo = SeckillModel::getInfoByID($seckillorder['seckill_id']);
						$activityData ['apply_num'] = intval ( $seckillInfo ['apply_num'] + 1 );
						SeckillModel::updateByID ( $activityData, $seckillorder ['seckill_id'] );
					}
				}
			}
			$pdo->commit ();
		} catch ( Exception $e ) {
			$pdo->rollback ();
			YDLib::testlog ( "call Exception: " . $e->getMessage () . " , paymentId:" . $payInfo ['pt_id'] );
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
	 * 获得对应的支付流水
	 * 
	 * @param interger $payment_company_id        	
	 * @param string $biz_no        	
	 * @param string $biz_type        	
	 * @return array
	 */
	public static function getTransactionByMarginID($payment_company_id, $biz_no, $biz_type = 'margin') {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['biz_type'] = $biz_type;
		$where ['biz_no'] = $biz_no;
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['pay_type'] = self::PAYMENT_TYPE [$payment_company_id];
		$pdo = self::_pdo ( 'db_r' );
		
		$info = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		return $info;
	}
	
	/**
	 * 支付保证金失败回执
	 * 
	 * @param array $payInfo        	
	 *
	 */
	public static function payFailure($payInfo) {
		YDLib::testlog ($payInfo );
		$pdo = self::_pdo ( 'db_w' );
		$pdo->beginTransaction ();
		try {
			
			// 更新三方支付流水状态
			$paymentInfo = self::getTransactionByMarginID ( $payInfo ['paymentId'], $payInfo ['out_trade_no'] );
			
			if (is_array ( $paymentInfo ) && count ( $paymentInfo ) > 0) {
				
				$resNum = self::updateByID ( [ 
						'is_del' => self::PAY_NONE_PAY 
				], $paymentInfo ['id'] );
				
				if ($resNum === FALSE) {
					YDLib::testlog ( "call payment_data err ,return_payment_data:" . json_encode ( $payInfo ['return_payment_data'] ) . " , return_payment_no: " . $payInfo ['return_payment_no'] . " ,id : " . $paymentInfo ['id'] );
					return;
				}
			} else {
				$pdo->rollback ();
				YDLib::testlog ( "select getTransactionByCompanyId err , paymentId:" . $payInfo ['paymentId'] . " , out_trade_no:" . $payInfo ['out_trade_no'] );
				return;
			}
			// 更新保证金支付状态
			$seckillorder = SeckillOrderModel::getSeckillOrderByID ( $payInfo ['out_trade_no'] );
			if (! empty ( $seckillorder )) {
				if ($seckillorder ['is_margin'] == 1) {
					SeckillOrderModel::updateByID ( [ 
							'is_del' => self::PAY_NONE_PAY 
					], $seckillorder ['id'] );
				}
			}
			$pdo->commit ();
		} catch ( Exception $e ) {
			$pdo->rollback ();
			YDLib::testlog ( "call Exception: " . $e->getMessage () . " , paymentId:" . $payInfo ['pt_id'] );
		}
	}
}