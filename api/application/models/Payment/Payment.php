<?php

/**
 * 支付model
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-23
 */
namespace Payment;

use Common\CommonBase;
use Common\SerialNumber;
use Custom\YDLib;
use Order\OrderModel;
use Order\OrderChildModel;
use Product\ProductChannelModel;
use Services\Finance\FinanceBaseService;
use Services\Finance\FinanceService;
use Services\Recovery\RecoveryService;
use User\UserSupplierModel;
use User\UserModel;
use User\UserSupplierThridModel;
use Seckill\SeckillModel;
use Seckill\SeckillProductModel;
use Seckill\SeckillLogModel;
use Seckill\SeckillOrderModel;
use Pay\PayFactory;
use Grade\GradeModel;
use Product\ProductModel;
use Order\OrderChildProductModel;
use Services\Purchase\PurchaseOrderService;
use Services\Purchase\PlatformPurchaseService;
use Purchase\PurchaseModel;
use CommonController;
use Payment\BizpaymentModel;
use Sms\SmsModel;
use Supplier\SupplierModel;
use Services\Msg\MsgService;


class PaymentModel extends \Common\CommonBase
{
	const ORDER_PAY_NOTFOUNT_ONLINE =  -1;  //非线上支付单
	const ORDER_PENDING_PAYMENT 	=  -2;	//非支付订单
	const ORDER_NOT_PAYMENT 		=  -3;	//非支付订单
	const USER_NOT_OPENID			=  -4;
	const ORDER_PAY_YES_PAY			=  -5;
	const TUAN_CLOSE				=  -6; // 拼团已经成团，不能支付参团了

	const PAY_NONE_PAY				=  1;   //未支付
	const PAY_YES_PAY				=  2;	//已支付

	const PAYMENT_TYPE_WEIXIN 		=  1;
	const PAYMENT_TYPE_ALIPY		=  2;

	const DELIVERY_TYPE_EXPRESS		= 0; // 快递
    const DELIVERY_TYPE_SHOP 		= 1; // 门店自提

	const PAYMENT_TYPE 				= [
		self::PAYMENT_TYPE_WEIXIN  => 'weixin',
		self::PAYMENT_TYPE_ALIPY   => 'alipay'
	];
    /**
     * 定义表名后缀
     */
    protected static $_tableName = 'payment_transaction';

    /**
     * 获取表名
     */
    public static function getTb()
    {
        return self::$_tablePrefix . self::$_tableName;
    }

	 /**
     * 根据表自增ID获取该条记录信息
     * @param int $id 表自增ID
     */
    public static function getInfoByID($id)
    {
        $where['is_del'] = self::DELETE_SUCCESS;
		$where['id'] = intval($id);
		$where['supplier_id'] = SUPPLIER_ID;

		$pdo = self::_pdo('db_r');
		return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
    }

	/**
	 * 获得对应的支付流水
	 * @param interger $payment_company_id
	 * @param string   $biz_no
	 * @param string   $biz_type
	 * @return array
	 */
	public static function getTransactionByCompanyId($payment_company_id,$biz_no,$biz_type = 'order')
	{
		$where['is_del'] = self::DELETE_SUCCESS;
		$where['biz_type'] = $biz_type;
	    $where['biz_no'] = 	 $biz_no;
		$where['supplier_id'] = SUPPLIER_ID;
		$where['pay_type'] = self::PAYMENT_TYPE[$payment_company_id];
	    $pdo = self::_pdo('db_r');

		return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();

	}

	/**
	 * 获得保证金对应的支付流水
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
		$user = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		return $user;
	}

	/**
	 * 创建支付
	 * @param interger  $orderId   订单ID
	 * @param interger  $companyId 支付公司ID
	 * @param interger  $callback 成功返回页面
     * @param array     $payload 小程序支付 统一下单有效载荷
	 * @return array
	 */
	public static function create($orderId,$companyId,$callback,$payload = '')
	{
		$orderInfo = OrderModel::getInfoByID($orderId);
	
		if (is_array($orderInfo) && count($orderInfo) > 0) {

			//验证订单是否为线上订单
			if ($orderInfo['pay_type'] != OrderModel::ORDER_PAY_TYPE_ONLINE) {
				return self::ORDER_PAY_NOTFOUNT_ONLINE;
			}

			//验证是否为付款订单
			if ($orderInfo['status'] != OrderModel::STATUS_PENDING_PAYMENT) {
				return self::ORDER_PENDING_PAYMENT;
			}

		} else {
			return FALSE;
		}

		//获得子订单
		$orderChildInfo = OrderChildModel::getInfoByOrderId($orderId);
		if (empty($orderChildInfo) || count($orderChildInfo) < 1) {
			return FALSE;
		}

		//订单商品
		$product = OrderChildProductModel::getInfoByChildID ( $orderChildInfo ['id'] );
		// 订单类型
		$discount_type = '0';
		$discount_id = '0';
		$discount_product_id = '0';
		$tuan_id = '0';
		if (count ($product) == 1) {
			$discount_type = $product [0] ['discount_type'];
			$discount_id = $product [0] ['discount_id'];
			$discount_product_id = $product [0] ['discount_product_id'];
		}
		// 拼团
		if ($discount_type == 4) {
			$tuan_info = SeckillLogModel::getInfos ( $discount_product_id, $orderId );
			if ($tuan_info) {
				if ($tuan_info['status'] != '1') {
					return self::TUAN_CLOSE;//拼团已经成团，不能支付参团了
				}
			}
		}

		if (!isset(self::PAYMENT_TYPE[$companyId])) {
			return self::ORDER_NOT_PAYMENT;
		}

		$company = self::PAYMENT_TYPE[$companyId];
		//支付总金额
        $pay_amount = $orderInfo['order_actual_amount'];

		//验证是否有支付流水信息
		$paymentInfo = self::getTransactionByCompanyId($companyId,$orderInfo['order_no']);

		if (is_array($paymentInfo) && count($paymentInfo) > 0) {
			//存在支付流水处理逻辑
			if ($paymentInfo['status'] == self::PAY_YES_PAY) {
				return self::ORDER_PAY_YES_PAY;
			}
			self::updateByID(
				[
					'biz_type'=>'order',
					'pay_amount'=>$pay_amount,
					'biz_no'=>$orderInfo['order_no'],
					'pay_type'=>self::PAYMENT_TYPE[$companyId],
					'ip'=>(new \Publicb()) -> GetIP()
				],
				$paymentInfo['id']);
		} else {

			//不存在支付流水处理逻辑
			$userInfo = UserSupplierModel::getAdminInfo($orderInfo['user_id']);
			$userInfo = UserModel::getAdminInfo($userInfo['user_id']);
			$paymentInfo['id'] = self::addData(
			[
				'biz_type'=>'order',
				'pay_amount'=>$pay_amount,
				'user_name'=> empty($userInfo['name']) ? ' ' : $userInfo['name'] ,
				'biz_no'=>$orderInfo['order_no'],
				'user_id'=>$orderInfo['user_id'],
				'pay_type'=>self::PAYMENT_TYPE[$companyId],
				'ip'=>(new \Publicb()) -> GetIP()
			]);

		}

		$pay = [
		    'childid'=>$orderChildInfo['id'],
            'orderid'=>$orderInfo['id'],
            'title'=>$orderInfo['order_no'],
            'payid'=>$companyId,
            'body' => $orderInfo['order_no'],
            'order_no'=>$orderInfo['order_no'],
            'paymoney'=>$pay_amount,
            'company' => $company,
            'paymentid' => $paymentInfo['id'],
            'callback' => $callback
        ];

		if (self::PAYMENT_TYPE[$companyId] == 'weixin') {  //处理微信逻辑
			if (empty($payload['openId'])) {
				$tridInfo = UserSupplierThridModel::getInfoByUserId($orderInfo['user_id']);
				if (is_array($tridInfo) && count($tridInfo) > 0) {
					$pay['openId'] = $tridInfo['openid'];
				} else {
					return self::USER_NOT_OPENID;  //没有授权
				}
			} else {
				$pay['openId'] = $payload['openId'];
			}
		}
		if (self::PAYMENT_TYPE[$companyId] == 'mini') {
		    if (!empty($payload)) {
		        $pay['openId'] = $payload['openId'];
            }
        }

        //调试支付成功回调
//        $payInfo = [
//             'orderid'=>$pay['orderid'],
//             'return_payment_data'=>json_encode($_POST),
//             'return_payment_no' => $pay['order_no'],
//             'paymentId'=>$pay['payid'],
//             'biz_type'=>'order',
//             'pt_id'=>$pay['paymentid'],
//             'out_trade_no'=>$pay['order_no'],
//        ];
     
       //		$res = PaymentModel::paySuccess($payInfo);//正常订单支付成功
       //        //$res = \Payment\MarginpaymentModel::paySuccess($payInfo);//竞价拍支付成功
       //        print_r($res);
       //        exit;
       
       
		// 0元单处理
        if($orderInfo['order_actual_amount']==='0.00'){
        	$payInfo = [
        			'orderid'=>$pay['orderid'],
        			'return_payment_data'=>json_encode($_POST),
        			'return_payment_no' => $pay['order_no'],
        			'paymentId'=>$pay['payid'],
        			'biz_type'=>'order',
        			'pt_id'=>$pay['paymentid'],
        			'out_trade_no'=>$pay['order_no'],
        	];
        	$res = PaymentModel::paySuccess($payInfo);
        	return $data['single'] = '200';
        }else{
        	$payment = PayFactory::factory(self::PAYMENT_TYPE[$companyId]);
        	return $payment->createPay($pay);
        }
	}

	/**
	 * 回调通知
	 * @param interger  $type   支付公司ID
	 * @return array
	 */
	public static function notify($data)
	{


		//判断是否是支付方式
		if (!isset(self::PAYMENT_TYPE[$data['type']])) {
			YDLib::testlog("call back: ORDER_NOT_PAYMENT" );
			return self::ORDER_NOT_PAYMENT;
		}

		//判断订单是否存在
		$orderInfo = OrderModel::getInfoByID($data['orderId']);

		if (empty($orderInfo) || count($orderInfo) < 1) {

			YDLib::testlog("call back: ORDER_NOT_PAYMENT" );
			return self::ORDER_NOT_PAYMENT;
		}

		//验证订单是否为线上订单
		if ($orderInfo['pay_type'] != OrderModel::ORDER_PAY_TYPE_ONLINE) {

			YDLib::testlog("call back: ORDER_PAY_NOTFOUNT_ONLINE" );
			return self::ORDER_PAY_NOTFOUNT_ONLINE;
		}

		//验证是否为付款订单
		if ($orderInfo['status'] != OrderModel::STATUS_PENDING_PAYMENT) {

			YDLib::testlog("call back: ORDER_PENDING_PAYMENT" );
			return self::ORDER_PENDING_PAYMENT;
		}

		//判断是否已支付
		$paymentInfo = self::getTransactionByCompanyId($data['type'],$orderInfo['order_no']);

		if (is_array($paymentInfo) && count($paymentInfo) > 0) {
			//存在支付流水处理逻辑
			if ($paymentInfo['status'] == self::PAY_YES_PAY) {
				YDLib::testlog("call back: ORDER_PAY_YES_PAY" );
				return self::ORDER_PAY_YES_PAY;
			}
		} else {
			YDLib::testlog("call back: ORDER_PAY_YES_PAY" );
			return self::ORDER_PAY_YES_PAY;
		}
		YDLib::testlog("call back: PayFactory" );
		$payment = PayFactory::factory(self::PAYMENT_TYPE[$data['type']]);
		$payment->notifyPay(); //PaymentWeixin->notify();
	}


	/**
	 * 购买成功回执
	 * @param array  $payInfo
	 */
	public static function paySuccess($payInfo)
	{
		//跳转处理sms回调
		if ($payInfo['biz_type'] == 'sms') {
			BizpaymentModel::paySuccess($payInfo);
			return TRUE;
		}

		
		//短信类型
		$user_sms_model_id = '8';//普通下单成功

		$pdo = self::_pdo('db_w');
		$pdo->beginTransaction();
		try {
			
			//更新订单状态
			$res = OrderModel::updateByID(['status'=>self::STATUS_ALREADY_PAID],$payInfo['orderid']);
			if ($res === FALSE) {
				$pdo->rollback();
				YDLib::testlog("order update faild: id: " . $payInfo['orderid'] .",supplier_id: " . SUPPLIER_ID . " , paymentId:" . $payInfo['paymentId']);
				return;
			}

            $paymentInfo = self::getTransactionByCompanyId ( $payInfo ['paymentId'], $payInfo ['out_trade_no'] );

			//支付成功进行拆单
            //订单主单
            $orderInfo = OrderModel::getInfoByID($payInfo['orderid']);
            //YDlib::testLog($orderInfo);

            //订单子单
            $childOrderInfo = OrderChildModel::getInfoByOrderId($payInfo['orderid']);
            //YDlib::testLog($childOrderInfo);

            //商品详情
            $orderChildProducts = OrderChildProductModel::getInfoByOrderID($payInfo['orderid']);
            //YDlib::testLog($orderChildProducts);

            //活动类型
            $discount_type = $orderChildProducts[0]['discount_type'];
            $products = [];
            //商品拆单
            foreach ($orderChildProducts as $key => $value) {
                //获取商品信息
                $productInfo = ProductModel::getSingleInfoByID($value['product_id']);
                $products[$productInfo['supplier_id']][] = $value;
                //回收商品下单更新回收状态
                if ($productInfo['is_purchase'] == '3') {
                    RecoveryService::addOrderCallBack($productInfo['id']);
                }
            }
            $child_order_num = count($products);

            //YDlib::testLog($child_order_num);
            //YDlib::testLog($products);
            //判断是都是供应订单
            $is_channel = false;
            if ($child_order_num == 1) {
                $res = OrderChildModel::updateByOrderID(['child_status'=>self::STATUS_ALREADY_PAID],$payInfo['orderid']);
                if ($res === FALSE) {
                    $pdo->rollback();
                    YDLib::testlog("orderChild update faild: id: " . $payInfo['orderid'] .",supplier_id: ".SUPPLIER_ID . " , paymentId:" . $payInfo['paymentId']);
                    return;
                }
                //生成采购单
                foreach ( $products as $key => $value) {
                    if ($key != SUPPLIER_ID && $discount_type != '4' ) {
                        $is_channel = true;
                        //组装采购商品数据
                        $purchase_products = [];
                        foreach ($value as $k => $v) {
                            $purchase_product = [];
                            $purchase_product['id'] = $v['product_id'];
                            $purchase_product['num'] = $v['sale_num'];
                            $purchase_product['order_child_product_id'] =  $v['id'];
                            $purchase_products[] = $purchase_product;
                        }
                        $res = self::createPurchase($purchase_products, $childOrderInfo['id'], $paymentInfo['pay_type']);
                        if (!$res) {
                            $pdo->rollback();
                            YDLib::testlog("生成采购单失败 , paymentId:" . $payInfo ['paymentId']);
                            return;
                        }
                    }
                }
            } else {
                //运费平摊
                $freight_average = 0;
                $freight_last = 0;
                if ($orderInfo['freight_charge_original_amount'] > 0) {
                    $freight_average = bcdiv($orderInfo['freight_charge_original_amount'], $child_order_num, 2);
                    $freight_last = bcsub(bcadd($orderInfo['freight_charge_original_amount'],$freight_average,2),bcmul($freight_average,$child_order_num,2),2);
                }

                $n = 1;
                foreach ( $products as $key => $value) {
                    if ($n < $child_order_num) {
                        //生成子单
                        $orderChildData = [];
                        $orderChildData['supplier_id'] = SUPPLIER_ID;
                        $orderChildData['user_id'] = $childOrderInfo['user_id'];
                        $orderChildData['order_id'] = $childOrderInfo['order_id'];
                        $orderChildData['order_no'] = $childOrderInfo['order_no'];
                        $orderChildData['child_order_no'] = SerialNumber::createSN(SerialNumber::SN_ORDER_CHILD);

                        $orderChildData['child_product_original_amount'] = 0;
                        $orderChildData['child_product_actual_amount'] = 0;
                        $orderChildData['child_product_discount_amount'] = 0;
                        $orderChildData['sale_num'] = 0;
                        $orderChildData['coupan_discount_amount'] = 0;
                        foreach ($value as $k => $v) {
                            $orderChildData['child_product_original_amount'] = bcadd($orderChildData['child_product_original_amount'],bcmul($v['sale_price'],$v['sale_num'],2),2);
                            $orderChildData['child_product_actual_amount'] = bcadd($orderChildData['child_product_actual_amount'],$v['actual_amount'],2);
                            $orderChildData['child_product_discount_amount'] = bcadd($orderChildData['child_product_discount_amount'],$v['discount_amount'],2);
                            $orderChildData['sale_num'] = bcadd($orderChildData['sale_num'],$v['sale_num']);
                            $orderChildData['coupan_discount_amount'] = bcadd($orderChildData['coupan_discount_amount'],$v['coupan_discount_amount'],2);
                        }

                        $orderChildData['child_freight_charge_original_amount'] = $freight_average;
                        $orderChildData['child_freight_charge_actual_amount'] = $freight_average;
                        $orderChildData['child_freight_charge_discount_amount'] = 0;

                        $orderChildData['child_order_original_amount'] = bcadd($orderChildData['child_product_original_amount'],$orderChildData['child_freight_charge_original_amount'],2);
                        $orderChildData['child_order_actual_amount'] = bcadd($orderChildData['child_product_actual_amount'],$orderChildData['child_freight_charge_actual_amount'],2);
                        $orderChildData['child_order_discount_amount'] = bcadd(bcadd($orderChildData['child_product_discount_amount'],$orderChildData['child_freight_charge_discount_amount'],2),$orderChildData['coupan_discount_amount'],2);

                        $orderChildData['child_pay_type'] = $childOrderInfo['child_pay_type'];
                        $orderChildData['child_status'] = self::STATUS_ALREADY_PAID;
                        $orderChildData['is_comment'] = $childOrderInfo['is_comment'];
                        $orderChildData['ip'] = $childOrderInfo['ip'];
                        $orderChildData['province_id'] = $childOrderInfo['province_id'];
                        $orderChildData['province_name'] = $childOrderInfo['province_name'];
                        $orderChildData['city_id'] = $childOrderInfo['city_id'];
                        $orderChildData['city_name'] = $childOrderInfo['city_name'];
                        $orderChildData['area_id'] = $childOrderInfo['area_id'];
                        $orderChildData['area_name'] = $childOrderInfo['area_name'];
                        $orderChildData['street_id'] = $childOrderInfo['street_id'];
                        $orderChildData['street_name'] = $childOrderInfo['street_name'];
                        $orderChildData['address'] = $childOrderInfo['address'];
                        $orderChildData['accept_name'] = $childOrderInfo['accept_name'];
                        $orderChildData['accept_mobile'] = $childOrderInfo['accept_mobile'];
                        $orderChildData['order_from'] = $childOrderInfo['order_from'];
                        $orderChildData['express_id'] = $childOrderInfo['express_id'];
                        $orderChildData['express_name'] = $childOrderInfo['express_name'];
                        $orderChildData['express_no'] = $childOrderInfo['express_no'];
                        $orderChildData['delivery_type'] = $childOrderInfo['delivery_type'];
                        $orderChildData['note'] = '';
                        $orderChildData['is_after_sales'] = CommonBase::SERVICE_NONE;
                        //YDlib::testLog($orderChildData);
                        $order_child_id = OrderChildModel::addData($orderChildData);
                        if (!$order_child_id) {
                            $pdo->rollback();
                            YDLib::testlog("orderChild 生成子单 faild: id: " . $payInfo['orderid'] .",supplier_id: ".SUPPLIER_ID . " , paymentId:" . $payInfo['paymentId']);
                            return;
                        }

                        //更新商品详情
                        foreach ($value as $k => $v) {
                            $orderChildProductData = [];
                            $orderChildProductData['child_order_id'] = $order_child_id;
                            $orderChildProductData['child_order_no'] = $orderChildData['child_order_no'];
                            //YDlib::testLog($orderChildProductData);
                            $res = OrderChildProductModel::updateByID($orderChildProductData,$v['id']);
                            if ($res === FALSE) {
                                $pdo->rollback();
                                YDLib::testlog("orderChild 更新商品详情 faild: id: " . $payInfo['orderid'] .",supplier_id: ".SUPPLIER_ID . " , paymentId:" . $payInfo['paymentId']);
                                return;
                            }
                        }
                        //生成采购单
                        if ($key != SUPPLIER_ID && $discount_type != '4') {
                            $is_channel = true;
                            //组装采购商品数据
                            $purchase_products = [];
                            foreach ($value as $k => $v) {
                                $purchase_product = [];
                                $purchase_product['id'] =  $v['product_id'];
                                $purchase_product['num'] =  $v['sale_num'];
                                $purchase_product['order_child_product_id'] =  $v['id'];
                                $purchase_products[] = $purchase_product;
                            }
                            $res = self::createPurchase($purchase_products,$order_child_id,$paymentInfo['pay_type']);
                            if (!$res) {
                                $pdo->rollback();
                                YDLib::testlog ( "生成采购单失败 , paymentId:" . $payInfo ['paymentId'] );
                                return;
                            }
                        }
                    } else {
                        //更新子单数据
                        $orderChildData = [];
                        $orderChildData['child_product_original_amount'] = 0;
                        $orderChildData['child_product_actual_amount'] = 0;
                        $orderChildData['child_product_discount_amount'] = 0;
                        $orderChildData['sale_num'] = 0;
                        $orderChildData['coupan_discount_amount'] = 0;
                        foreach ($value as $k => $v) {
                            $orderChildData['child_product_original_amount'] = bcadd($orderChildData['child_product_original_amount'],bcmul($v['sale_price'],$v['sale_num'],2),2);
                            $orderChildData['child_product_actual_amount'] = bcadd($orderChildData['child_product_actual_amount'],$v['actual_amount'],2);
                            $orderChildData['child_product_discount_amount'] = bcadd($orderChildData['child_product_discount_amount'],$v['discount_amount'],2);
                            $orderChildData['sale_num'] = bcadd($orderChildData['sale_num'],$v['sale_num']);
                            $orderChildData['coupan_discount_amount'] = bcadd($orderChildData['coupan_discount_amount'],$v['coupan_discount_amount'],2);
                        }

                        $orderChildData['child_freight_charge_original_amount'] = $freight_last;
                        $orderChildData['child_freight_charge_actual_amount'] = $freight_last;
                        $orderChildData['child_freight_charge_discount_amount'] = 0;

                        $orderChildData['child_order_original_amount'] = bcadd($orderChildData['child_product_original_amount'],$orderChildData['child_freight_charge_original_amount'],2);
                        $orderChildData['child_order_actual_amount'] = bcadd($orderChildData['child_product_actual_amount'],$orderChildData['child_freight_charge_actual_amount'],2);
                        $orderChildData['child_order_discount_amount'] = bcadd(bcadd($orderChildData['child_product_discount_amount'],$orderChildData['child_freight_charge_discount_amount'],2),$orderChildData['coupan_discount_amount'],2);
                        $orderChildData['child_status'] = self::STATUS_ALREADY_PAID;
                        //YDlib::testLog($orderChildData);
                        $res = OrderChildModel::updateByID($orderChildData,$childOrderInfo['id']);
                        if ($res === FALSE) {
                            $pdo->rollback();
                            YDLib::testlog("orderChild update faild: id: " . $payInfo['orderid'] .",supplier_id: ".SUPPLIER_ID . " , paymentId:" . $payInfo['paymentId']);
                            return;
                        }
                        //生成采购单
                        if ($key != SUPPLIER_ID && $discount_type != '4') {
                            $is_channel = true;
                            //组装采购商品数据
                            $purchase_products = [];
                            foreach ($value as $k => $v) {
                                $purchase_product = [];
                                $purchase_product['id'] =  $v['product_id'];
                                $purchase_product['num'] =  $v['sale_num'];
                                $purchase_product['order_child_product_id'] =  $v['id'];
                                $purchase_products[] = $purchase_product;
                            }
                            $res = self::createPurchase($purchase_products,$childOrderInfo['id'],$paymentInfo['pay_type']);
                            if (!$res) {
                                $pdo->rollback();
                                YDLib::testlog ( "生成采购单失败 , paymentId:" . $payInfo ['paymentId'] );
                                return;
                            }
                        }
                    }
                    $n++;
                }
            }

            //更新购买数量 购买统计
	        if (is_array($orderChildProducts) && count($orderChildProducts) > 0) {
				$isDiscount = [];
				$isGroup = [];
				foreach ($orderChildProducts as $key => $value) {
		           	$productId = $value['product_id'];
					$resNum = ProductModel::addSaleNumByID($productId,$value['sale_num']);
					if ($resNum === FALSE) {
						$pdo->rollback ();
						YDLib::testlog ( "seckill addProductNum by null , paymentId:" . $payInfo ['paymentId'] );
						return;
					}
					//0库存订单更新渠道商品销售数量
                    if ($value['is_channel'] == OrderModel::IS_CHANNEL_2) {
                        $resNum = ProductChannelModel::autoUpdateByID(['sale_num'=>$value['sale_num']],$value['channel_id']);
                        if ($resNum === FALSE) {
                            $pdo->rollback ();
                            YDLib::testlog ( "供应订单更新渠道商品销售数量失败 , paymentId:" . $payInfo ['paymentId'] );
                            return;
                        }
                    }

					// 秒杀活动更新数量
					if ($value ['discount_type'] == 1) {
						$discount = [ ];
						$discount ['seckill_id'] = $value ['discount_id'];
						$discount ['sale_num'] = $value ['sale_num'];
						$discount ['actual_amount'] = $value ['actual_amount'];
						$isDiscount [] = $discount;
					}
					// 拼团活动
					if ($value ['discount_type'] == 4) {
						$group = [ ];
						$group ['seckill_id'] = $value ['discount_id'];
						$group ['seckill_product_id'] = $value ['discount_product_id'];
						$group ['sale_num'] = $value ['sale_num'];
						$group ['actual_amount'] = $value ['actual_amount'];
						$isGroup [] = $group;
					}
				}
				// 更新活动数量
				if (is_array ( $isDiscount ) && count ( $isDiscount ) > 0) {
					foreach ( $isDiscount as $key => $value ) {
						$num  = SeckillModel::getInfoByID($value ['seckill_id']);
						$upInfo = [ ];
						$upInfo ['order_num'] = intval($num ['order_num'] +1);
						$upInfo ['order_people_num'] = intval($num ['order_people_num'] +1);
						$upInfo ['order_sale_price'] = $value ['actual_amount'];
						$upInfo ['oredr_product_num'] = $value ['sale_num'];
						$res = SeckillModel::autoUpdateByID ( $upInfo, $value ['seckill_id'] );
						if ($res === FALSE) {
							$pdo->rollback ();
							YDLib::testlog ( "更新活动数量失败 , paymentId:" . $payInfo ['paymentId'] );
							return;
						}
					}
				}
				// 更新团购数据
				if (is_array ( $isGroup ) && count ( $isGroup ) > 0) {
					foreach ( $isGroup as $key => $value ) {
						$num  = SeckillModel::getInfoByID($value ['seckill_id']);
						$upInfo = [ ];
						$upInfo ['order_num'] = intval($num ['order_num'] +1);
						$upInfo ['order_people_num'] = intval($num ['order_people_num'] +1);
						$upInfo ['order_sale_price'] = $value ['actual_amount'];
						$upInfo ['oredr_product_num'] = $value ['sale_num'];
						$res = SeckillProductModel::autoUpdateByID ( $upInfo, $value ['seckill_product_id'] );
						if ($res === FALSE) {
							$pdo->rollback ();
							YDLib::testlog ( "更新团购商品数据失败 , paymentId:" . $payInfo ['paymentId'] );
							return;
						}
						$res = SeckillModel::autoUpdateByID ( $upInfo, $value ['seckill_id'] );
						if ($res === FALSE) {
							$pdo->rollback ();
							YDLib::testlog ( "更新活动数量失败 , paymentId:" . $payInfo ['paymentId'] );
							return;
						}
						// 成团判断(团购商品id,订单id)
						$groupres = SeckillLogModel::groupPrve ( $value ['seckill_product_id'], $payInfo ['orderid'] );

						if (!$groupres) {
							$pdo->rollback ();
							YDLib::testlog ( "成团判断失败 user_id:" . $orderInfo ['user_id'] . " , order_actual_amount:" . $orderInfo ['order_actual_amount'] . ' order_id:' . $orderInfo ['id'] );
							return;
						}
					}
					
				}
				
			} else {
				$pdo->rollback ();
				YDLib::testlog ( "call addProductNum by null , paymentId:" . $payInfo ['paymentId'] );
				return;
		    }

			// 更新三方支付流水状态

			if (is_array ( $paymentInfo ) && count ( $paymentInfo ) > 0) {
				$resNum = self::updateByID ( [
						'return_payment_data' => $payInfo ['return_payment_data'],
						'status' => self::PAY_YES_PAY,
						'return_payment_no' => $payInfo ['return_payment_no'],
						'pay_time' => date ( "Y-m-d H:i:s" )
				], $paymentInfo ['id'] );
				if ($resNum === FALSE) {
					$pdo->rollback ();
					YDLib::testlog ( "call payment_data err ,return_payment_data:" . json_encode ( $payInfo ['return_payment_data'] ) . " , return_payment_no: " . $payInfo ['return_payment_no'] . " ,id : " . $paymentInfo ['id'] );
					return;
				}
				//收支处理
                $mem = YDLib::getMem('memcache');
                $supplier = $mem->get('supplier_'.SUPPLIER_DOMAIN);
                //商户收入//需要结算
                $finance = new FinanceService();
                if ($is_channel) {
                    $finance->setObjSummary('供应订单支付');
                }
                $finance->setObjType(FinanceService::USER_ORDER_PAYMENT);
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
                if ($is_channel) {
                    $finance->setObjSummary('供应订单支付');
                }
                $finance->setObjType(FinanceService::USER_ORDER_PAYMENT);
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
				YDLib::testlog ( "call payment_data err ,return_payment_data:" . json_encode ( $payInfo ['return_payment_data'] ) . " , return_payment_no: " . $payInfo ['return_payment_no'] . " ,id : " . $paymentInfo ['id'] );
				return;
			}

			// 累积成长值与升级(会员ID,消费金额,类型,订单id)
 			$judgeInfo = GradeModel::growthJudge ( $orderInfo ['user_id'], $orderInfo ['order_actual_amount'], '1', $orderInfo ['id'] );
			
			if (! $judgeInfo) {
				$pdo->rollback ();
				YDLib::testlog ( "growthJudge err , user_id:" . $orderInfo ['user_id'] . " , order_actual_amount:" . $orderInfo ['order_actual_amount'] . ' order_id:' . $orderInfo ['id'] );
				return;
			}

			$pdo->commit ();

            // 发送微信通知 @todo
            // if ($userThird == UserSupplierThridModel::getInfoByUserId($orderInfo['user_id'])) {
            //
            // }

            //发送短信提醒
            $user_info = UserModel::getAdminInfo($orderInfo['user_id']);
            $suppplier_detail = SupplierModel::getInfoByID(SUPPLIER_ID);
            if ($user_info) {
                //下单短信
//                 $smsdata = [ ];
//                 $smsdata ['mobile'] = $user_info ['mobile'];
//                 $smsdata ['model_id'] = $user_sms_model_id;
//                 $params = array (
//                     $orderInfo['order_no'],
//                 );
//                 $smsdata ['params'] = $params;
//                 $smsdata ['sms_type'] = '4';
                // 用户下单发送短信
//               SmsModel::SendSmsJustFire ( $smsdata );
                
            	
            	$weichat_url = sprintf(M_URL, $suppplier_detail['domain']).'mobile/user';
            	$msgData = [
            			'params' => [
            					'0' => $orderInfo['order_no'],
            			],
            			'weixin_params' => [
            					'url' => $weichat_url,
            					'pagepath' => [
            							'appid' => MINI_APPID,
            							'pagepath' => 'pages/index?domain=${'.$suppplier_detail['domain'].'}&share_url=${'.urlencode(SHOM_URL.$suppplier_detail['domain'].'mobile/user').'}'
            					],
            					'data' => [
            							'first' => [
            									'value' => '尊敬的用户，您的订单'.$orderInfo['order_no'].'已支付成功'
            							],
            							'orderMoneySum' => [
            									'value' => $orderInfo ['order_actual_amount']
            							],
            							'orderProductName' => [
            									'value' => $orderChildProducts[0]['product_name']
            							]
            					]
            			]
            	];
            	 
            	 
            	/* 发送短信 */
            	MsgService::fireMsg('2', $user_info ['mobile'], $orderInfo['user_id'],$msgData);
            	
            }

            // 通知商户
//             $supplier = SupplierModel::getInfoByID(SUPPLIER_ID);
            $smsdata ['model_id'] = '9';
            $smsdata ['mobile'] = $suppplier_detail ['mobile'];
            $smsdata ['params'] = '';
            SmsModel::SendSmsJustFire ( $smsdata );

		} catch ( Exception $e ) {
			$pdo->rollback ();
			YDLib::testlog ( "call Exception: " . $e->getMessage () . " , paymentId:" . $payInfo ['paymentId'] );
			return;
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
	 * 回调保证金通知
	 *
	 * @param interger $type
	 *        	支付公司ID
	 * @return array
	 */
	public static function notifyMargin($data) {
		// 判断是否是支付方式

		if (! isset ( self::PAYMENT_TYPE [$data ['type']] )) {
			YDLib::testlog ( "call back: ORDER_NOT_PAYMENT" );
			return self::·;
		}


		// 判断订单是否存在
		$orderInfo = SeckillOrderModel::getInfoByID ( $data ['orderId'] );
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
		if ($orderInfo ['order_status'] != OrderModel::STATUS_PENDING_PAYMENT) {
			YDLib::testlog ( "call back: ORDER_PENDING_PAYMENT" );
			return self::ORDER_PENDING_PAYMENT;
		}

		// 判断是否已支付
		$paymentInfo = self::getTransactionByMarginID ( $data ['type'], $orderInfo ['order_no'] );
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
		$payment->notifyMarginPay (); // PaymentWeixin->notify();
	}



	/**
	 * 创建保证金支付
	 *
	 * @param interger $orderId
	 *        	订单ID
	 * @param interger $companyId
	 *        	支付公司ID
	 * @param interger $callback
	 *        	成功返回页面
	 * @param array $payload
	 *        	小程序支付 统一下单有效载荷
	 * @return array
	 */
	public static function createMargin($orderId, $companyId, $callback, $payload = '') {
		$orderInfo = SeckillOrderModel::getInfoByID ( $orderId );

		if (is_array ( $orderInfo ) && count ( $orderInfo ) > 0) {

			// 验证订单是否为线上订单
			if ($orderInfo ['pay_type'] != OrderModel::ORDER_PAY_TYPE_ONLINE) {
				return self::ORDER_PAY_NOTFOUNT_ONLINE;
			}

			// 验证是否为付款订单
			if ($orderInfo ['order_status'] != OrderModel::STATUS_PENDING_PAYMENT) {
				return self::ORDER_PENDING_PAYMENT;
			}
		} else {
			return FALSE;
		}

		if (! isset ( self::PAYMENT_TYPE [$companyId] )) {
			return self::ORDER_NOT_PAYMENT;
		}

		$company = self::PAYMENT_TYPE [$companyId];
		// 支付总金额
		$pay_amount = $orderInfo ['margin'];
		// $pay_amount = 0.01;

		// 验证是否有支付流水信息
		$paymentInfo = self::getTransactionByMarginID ( $companyId, $orderInfo ['order_no'] );

		if (is_array ( $paymentInfo ) && count ( $paymentInfo ) > 0) {
			// 存在支付流水处理逻辑
			if ($paymentInfo ['status'] == self::PAY_YES_PAY) {
				return self::ORDER_PAY_YES_PAY;
			}
			self::updateByID ( [
					'biz_type' => 'margin',
					'pay_amount' => $pay_amount,
					'biz_no' => $orderInfo ['order_no'],
					'pay_type' => self::PAYMENT_TYPE [$companyId],
					'ip' => (new \Publicb ())->GetIP ()
			], $paymentInfo ['id'] );
		} else {

			// 不存在支付流水处理逻辑
			$userInfo = UserSupplierModel::getAdminInfo ( $orderInfo ['user_id'] );
			$userInfo = UserModel::getAdminInfo ( $userInfo ['user_id'] );
			$paymentInfo ['id'] = self::addData ( [
					'biz_type' => 'margin',
					'pay_amount' => $pay_amount,
					'user_name' => empty ( $userInfo ['name'] ) ? ' ' : $userInfo ['name'],
					'biz_no' => $orderInfo ['order_no'],
					'user_id' => $orderInfo ['user_id'],
					'pay_type' => self::PAYMENT_TYPE [$companyId],
					'ip' => (new \Publicb ())->GetIP ()
			] );
		}

		$pay = [
				'orderid' => $orderInfo ['id'],
				'title' => $orderInfo ['order_no'],
				'payid' => $companyId,
				'body' => $orderInfo ['order_no'],
				'order_no' => $orderInfo ['order_no'],
				'paymoney' => $pay_amount,
				'company' => $company,
				'paymentid' => $paymentInfo ['id'],
				'callback' => $callback
		];

		if (self::PAYMENT_TYPE [$companyId] == 'weixin') { // 处理微信逻辑
			if (empty ( $payload ['openId'] )) {
				$tridInfo = UserSupplierThridModel::getInfoByUserId ( $orderInfo ['user_id'] );
				if (is_array ( $tridInfo ) && count ( $tridInfo ) > 0) {
					$pay ['openId'] = $tridInfo ['openid'];
				} else {
					return self::USER_NOT_OPENID; // 没有授权
				}
			} else {
				$pay ['openId'] = $payload ['openId'];
			}
		}
		if (self::PAYMENT_TYPE [$companyId] == 'mini') {
			if (! empty ( $payload )) {
				$pay ['openId'] = $payload ['openId'];
			}
		}
//        print_r($pay);
//		exit;
		$payment = PayFactory::factory ( self::PAYMENT_TYPE [$companyId] );

		return $payment->createMarginPay ( $pay );
	}

    /**
     * 生成采购订单
     */
    public static function createPurchase($purchase_products,$order_child_id,$pay_type)
    {
        //获取商户收货地址
        $mem = YDLib::getMem ( 'memcache' );
        $supplier_data = $mem->get ( 'supplier_' . SUPPLIER_DOMAIN );
        //组装采购参数
        $params = Array
        (
            'title' => '供应采购单'.date("ymd"),
            'name' => $supplier_data['contact'],
            'mobile' => $supplier_data['mobile'],
            'province_id' => $supplier_data['province_id'],
            'city_id' => $supplier_data['city_id'],
            'area_id' => $supplier_data['area_id'],
            'address' => $supplier_data['address'],
            'pay_type' => $pay_type,
            'source_id' => $order_child_id,
            'product' => $purchase_products
        );
        YDLib::testlog('采购参数');
        YDLib::testlog($params);
        $PurchaseOrderService = new PurchaseOrderService();
        //设置采购参数
        $PurchaseOrderService->setRequest($params);
        //设置供应采购
        $PurchaseOrderService->setPurchaseType(PurchaseOrderService::PURCHASE_TYPE_SALE);

        //创建采购单
        $result = $PurchaseOrderService->createPurchase();
        if (!$result) {
            YDLib::testLog($PurchaseOrderService->getError());
            return false;
        }

        return true;

    }

}