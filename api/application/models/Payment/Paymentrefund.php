<?php

/**
 * 退款model
 * @version v0.01
 * @author lqt
 * @time 2018-08-1
 */
namespace Payment;

use Custom\YDLib;
use Order\OrderModel;
use Order\OrderChildModel;
use Services\Finance\FinanceService;
use User\UserSupplierThridModel;
use Seckill\SeckillModel;
use Seckill\SeckillProductModel;
use Seckill\SeckillLogModel;
use Pay\PayFactory;
use Seckill\SeckillOrderModel;
use Grade\GradeModel;
use Product\ProductModel;
use Order\OrderChildProductModel;
use Payment\BizpaymentModel;
use ErrnoStatus;
use Common\SerialNumber;

class PaymentrefundModel extends \Common\CommonBase {
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
	 * 获得对应的支付流水
	 * 
	 * @param interger $payment_company_id        	
	 * @param string $biz_no        	
	 * @param string $biz_type        	
	 * @return array
	 */
	public static function getTransactionByCompanyId($payment_company_id, $biz_no, $biz_type = 'order') {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['biz_type'] = $biz_type;
		$where ['biz_no'] = $biz_no;
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['pay_type'] = self::PAYMENT_TYPE [$payment_company_id];
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	/**
	 * 获得对应的支付流水
	 * 
	 * @param interger $payment_id        	
	 * @return array
	 */
	public static function getTransactionByPaymentId($payment_id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['payment_id'] = $payment_id;
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}

    /**
     * 创建退款
     * @param $detail
     * @return bool
     */
	public static function create($detail)
    {
		// 验证是否已退款
		$paymentInfo = self::getTransactionByPaymentId ( $detail ['id'] );
		if (is_array ( $paymentInfo ) && count ( $paymentInfo ) > 0) {
			$payment_no = $paymentInfo ['payment_no'];
			$payment_id = $paymentInfo ['id'];
		} else {
			$payment_no = SerialNumber::createSN ( SerialNumber::SN_PAYMENT_NO ); // 退款订单号
			$paymentData = [ 
					'supplier_id' => $detail ['supplier_id'],
					'user_id' => $detail ['user_id'],
					'user_name' => $detail ['user_name'],
					'biz_type' => $detail ['biz_type'],
					'biz_no' => $detail ['biz_no'],
					'pay_type' => $detail ['pay_type'],
					'pay_amount' => $detail ['pay_amount'],
					'status' => '1',
					'return_payment_no' => '',
					'return_payment_data' => '',
					'ip' => (new \Publicb ())->GetIP (),
					'payment_no' => $payment_no,
					'pay_time' => date ( 'Y-m-d H:i:s' ),
					'type' => '2',
					'is_return' => '1',
					'payment_id' => $detail ['id'] 
			];
			$payment_id = self::addData ( $paymentData );
	    }

        if ($detail['biz_type'] == 'order') {
            $pay = [
                    'transaction_id' => $detail ['return_payment_no'], // 设置微信订单号
                    'payment_no' => $payment_no, // 商户系统内部的退款单号
                    'pay_amount' => $detail ['pay_amount'], // 订单总金额
                    'refund_desc' => $detail ['refund_desc'], // 退款原因
                    'notify_url' => '' // 退款回调地址
            ];
            $payment = PayFactory::factory ( $detail ['pay_type'] );
            $res = $payment->returnPay ( $pay );
            if ($res) {
                // 更新回调数据
                self::returnUpdate ( $payment_id, $res );
                YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
            } else {
                YDLib::output ( ErrnoStatus::STATUS_60587 );
            }
        } else if ($detail['biz_type'] == 'margin'){
            $pay = [
                    'transaction_id' => $detail ['return_payment_no'], // 设置微信订单号
                    'payment_no' => $payment_no, // 商户系统内部的退款单号
                    'pay_amount' => $detail ['pay_amount'], // 订单总金额
                    'refund_desc' => '未获拍退款', // 退款原因
                    'notify_url' => ''// 退款回调地址
            ];
            $payment = PayFactory::factory ( $detail ['pay_type'] );
            $res = $payment->returnPay ( $pay );
            if ($res) {
                // 更新回调数据
                self::returnTpyeUpdate ( $payment_id, $res );
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 手动退款接口
     */
    public static function returnSelfPay()
    {
        $payment_no = SerialNumber::createSN ( SerialNumber::SN_PAYMENT_NO ); // 退款订单号
        $pay = [
            'transaction_id' => '4200000213201812253207739494', // 设置微信订单号
            'payment_no' => $payment_no, // 商户系统内部的退款单号
            'pay_amount' => 440, // 订单总金额
            'refund_desc' => '下单失败退款', // 退款原因
            'notify_url' => '' // 退款回调地址
        ];
        YDLib::testLog($pay);
        $pay_type = 'weixin';
        $payment = PayFactory::factory ( $pay_type );
        $res = $payment->returnPay ( $pay );
        YDLib::testLog($res);
    }
	
	/**
	 * 退款回调更新数据
	 * 
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function returnUpdate($id, $res)
    {
        YDLib::testLog($res);
		// 更新退款单为已退款
		$resNum = self::updateByID ( [ 
				'return_payment_data' => json_encode ( $res ),
				'status' => self::PAY_YES_PAY,
				'return_payment_no' => '',
				'pay_time' => date ( "Y-m-d H:i:s" ) 
		], $id );
		
		$detail = self::getInfoByID ( $id );

		// 更新原单为退款单
		$resNum = self::updateByID ( [ 
				'is_return' => '2',
				'payment_id' => $id 
		]
		, $detail ['payment_id'] );
		
		$info = SeckillLogModel::getGroupInfoByOrderNo ( $detail ['biz_no'] );
		if ($info) {
			$resNum = SeckillLogModel::updateByID ( [ 
					'order_status' => '3' 
			], $info ['id'] );
		}

        //收支处理
        $mem = YDLib::getMem('memcache');
        $supplier = $mem->get('supplier_'.SUPPLIER_DOMAIN);
        //商户支出//需要结算
        $finance = new FinanceService();
        $finance->setObjType(FinanceService::USER_ORDER_RETURN);
        $finance->setObjId($detail['biz_no']);
        $finance->setAmount($detail['pay_amount']);
        $finance->setPayType($detail['pay_type']);
        $finance->setPaymentNo($detail ['return_payment_no']);
        $finance->setPaymentId($detail['id']);
        $finance->setRoleType(FinanceService::ROLE_SUPPLIER);
        $finance->setRoleObjId(SUPPLIER_ID);
        $finance->setRoleObjName($supplier['company']);
        $finance->setSupplierId(SUPPLIER_ID);
        $finance->out();

        //会员收入//无需结算
        $finance = new FinanceService();
        $finance->setObjType(FinanceService::USER_ORDER_RETURN);
        $finance->setObjId($detail['biz_no']);
        $finance->setAmount($detail['pay_amount']);
        $finance->setPayType($detail['pay_type']);
        $finance->setPaymentNo($detail ['return_payment_no']);
        $finance->setPaymentId($detail['id']);
        $finance->setRoleType(FinanceService::ROLE_USER);
        $finance->setRoleObjId($detail['user_id']);
        $finance->setRoleObjName($detail['user_name']);
        $finance->setSupplierId(SUPPLIER_ID);
        $finance->setSettleType(FinanceService::SETTLEMENT_IGNORE);
        $finance->in();

	}
	
	/**
	 * 回调通知
	 * 
	 * @param interger $type
	 *        	支付公司ID
	 * @return array
	 */
	public static function notify($data) {
		
		// 判断是否是支付方式
		if (! isset ( self::PAYMENT_TYPE [$data ['type']] )) {
			YDLib::testlog ( "call back: ORDER_NOT_PAYMENT" );
			return self::ORDER_NOT_PAYMENT;
		}
		
		// 判断订单是否存在
		$orderInfo = OrderModel::getInfoByID ( $data ['orderId'] );
		if (empty ( $orderInfo ) || count ( $orderInfo ) < 1) {
			YDLib::testlog ( "call back: ORDER_NOT_PAYMENT" );
			return self::ORDER_NOT_PAYMENT;
		}
		
		// 验证订单是否为线上订单
		if ($orderInfo ['pay_type'] != OrderModel::ORDER_PAY_TYPE_ONLINE) {
			YDLib::testlog ( "call back: ORDER_PAY_NOTFOUNT_ONLINE" );
			return self::ORDER_PAY_NOTFOUNT_ONLINE;
		}
		
		// 验证是否为付款订单
		if ($orderInfo ['status'] != OrderModel::STATUS_PENDING_PAYMENT) {
			YDLib::testlog ( "call back: ORDER_PENDING_PAYMENT" );
			return self::ORDER_PENDING_PAYMENT;
		}
		
		// 判断是否已支付
		$paymentInfo = self::getTransactionByCompanyId ( $data ['type'], $orderInfo ['order_no'] );
		if (is_array ( $paymentInfo ) && count ( $paymentInfo ) > 0) {
			// 存在支付流水处理逻辑
			if ($paymentInfo ['status'] == self::PAY_YES_PAY) {
				YDLib::testlog ( "call back: ORDER_PAY_YES_PAY" );
				return self::ORDER_PAY_YES_PAY;
			}
		} else {
			YDLib::testlog ( "call back: ORDER_PAY_YES_PAY" );
			return self::ORDER_PAY_YES_PAY;
		}
		YDLib::testlog ( "call back: PayFactory" );
		$payment = PayFactory::factory ( self::PAYMENT_TYPE [$data ['type']] );
		$payment->notifyPay (); // PaymentWeixin->notify();
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
	public static function addData($data) {
		$data ['supplier_id'] = SUPPLIER_ID;
		$data ['is_del'] = self::DELETE_SUCCESS;
		$data ['created_at'] = date ( "Y-m-d H:i:s" );
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->insert ( self::$_tableName, $data );
	}
	
	/**
	 * 保证金退款回调更新数据
	 *
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function returnTpyeUpdate($id, $res)
    {
        YDLib::testLog($res);
		// 更新退款单为已退款
		$resNum = self::updateByID ( [ 
            'return_payment_data' => json_encode ( $res ),
            'status' => self::PAY_YES_PAY,
            'return_payment_no' => '',
            'pay_time' => date ( "Y-m-d H:i:s" )
        ], $id );
				
        $detail = self::getInfoByID ( $id );
        // 更新原单为退款单
        $resNum = self::updateByID ( [
                'is_return' => '2',
                'payment_id' => $id
        ]
        , $detail ['payment_id'] );
				
        //更新保证金订单状态
        $info = SeckillOrderModel::getSeckillOrderByID( $detail ['biz_no']);
        
        $skInfo = SeckillModel ::getInfoByID($info['seckill_id']);
        if($skInfo['status'] != '3'){
        	$data['status'] = 3 ;
 	    }else{
 	    	$data['status'] = 5 ;
 	    }
 	    
 	    if($info){
 	    	$data['order_status'] = '98';
 	    	$data['is_margin'] = 3;
 	    	SeckillOrderModel::updateByID($data,$info['id']);
 	    		
 	    	$skData['spey'] = 5;
 	    	SeckillModel::updateByID ($skData,$info['seckill_id']);
 	    }
		
		
        //收支处理
        $mem = YDLib::getMem('memcache');
        $supplier = $mem->get('supplier_'.SUPPLIER_DOMAIN);
        //商户支出//需要结算
        $finance = new FinanceService();
        $finance->setObjType(FinanceService::USER_MARGIN_RETURN);
        $finance->setObjId($detail['biz_no']);
        $finance->setAmount($detail['pay_amount']);
        $finance->setPayType($detail['pay_type']);
        $finance->setPaymentNo($detail ['return_payment_no']);
        $finance->setPaymentId($detail['id']);
        $finance->setRoleType(FinanceService::ROLE_SUPPLIER);
        $finance->setRoleObjId(SUPPLIER_ID);
        $finance->setRoleObjName($supplier['company']);
        $finance->setSupplierId(SUPPLIER_ID);
        $finance->out();

        //会员收入//无需结算
        $finance = new FinanceService();
        $finance->setObjType(FinanceService::USER_MARGIN_RETURN);
        $finance->setObjId($detail['biz_no']);
        $finance->setAmount($detail['pay_amount']);
        $finance->setPayType($detail['pay_type']);
        $finance->setPaymentNo($detail ['return_payment_no']);
        $finance->setPaymentId($detail['id']);
        $finance->setRoleType(FinanceService::ROLE_USER);
        $finance->setRoleObjId($detail['user_id']);
        $finance->setRoleObjName($detail['user_name']);
        $finance->setSupplierId(SUPPLIER_ID);
        $finance->setSettleType(FinanceService::SETTLEMENT_IGNORE);
        $finance->in();
		
	}
	
	
	
	/**
	 * 保证金机器人退款回调更新数据
	 *
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function returnMachineUpdate($order_no)
	{
		YDLib::testLog($order_no);
		
		//更新保证金订单状态
		$info = SeckillOrderModel::getSeckillOrderByID( $order_no);
	
		$skInfo = SeckillModel ::getInfoByID($info['seckill_id']);
		if($skInfo['status'] != '3'){
			$data['status'] = 3 ;
		}else{
			$data['status'] = 5 ;
		}
	
		if($info){
			$data['order_status'] = '98';
			$data['is_margin'] = 3;
			SeckillOrderModel::updateByID($data,$info['id']);
	
			$skData['spey'] = 5;
			SeckillModel::updateByID ($skData,$info['seckill_id']);
		}
	
	}
	
	
}