<?php

namespace Pay\Weixin;

use Custom\YDLib;
use Pay\PayFactory;
use Pay\PayAbstract;
use Pay\Weixin\WxPay\WxPayNotify;
use Pay\Weixin\WxPay\JsApiPay;
use Pay\Weixin\WxPay\WxPayUnifiedOrder;
use Pay\Weixin\WxPay\WxPayRefund;
use Pay\Weixin\WxPay\WxPayConfig;
use Pay\Weixin\WxPay\WxPayApi;
use Pay\Weixin\WxPay\WxPayOrderQuery;
use Pay\Weixin\WxPay\NativePay;
use Payment\PaymentModel;
use Payment\MarginpaymentModel;

/**
 * 微信支付基类
 * 
 * @version v0.1
 * @author zhaoyu
 *         @time 2018-5-23
 */
class PayInfo extends WxPayNotify implements PayAbstract {
	protected $_pay = NULL;
	
	/**
	 * 同步消息返回
	 * 
	 * @param int $id
	 *        	表自增ID
	 */
	public function callbackPay() {
	}
	
	/**
	 * 退款操作
	 * 
	 * @return boolean 更新结果
	 */
	public function returnPay($payinfo) {
		// ②、统一下单
		$input = new WxPayRefund ();
		$input->SetTransaction_id ( $payinfo ['transaction_id'] ); // 设置微信订单号
		$input->SetOut_refund_no ( $payinfo ['payment_no'] ); // 商户系统内部的退款单号
		$input->SetTotal_fee ( $payinfo ['pay_amount'] * 100 ); // 订单总金额
		$input->SetRefund_fee ( $payinfo ['pay_amount'] * 100 ); // 退款总金额
		$input->SetRefund_desc ( $payinfo ['refund_desc'] ); // 退款原因
		if ($payinfo ['notify_url']) {
			$input->SetNotify_url ( $payinfo ['notify_url'] ); // 退款回调地址
		}
		YDLib::testlog ( $input );
		try {
			$order = WxPayApi::refund ( $input );
			YDLib::testlog ( $order );
			return $order;
			//$tools = new JsApiPay ();
			//$jsApiParameters = $tools->GetJsApiParameters ( $order );
			//YDLib::testlog ( $jsApiParameters );
		} catch ( Exception $e ) {
			YDLib::testlog ( '66666666666' );
			return FALSE;
		}
		
		//return json_decode ( $jsApiParameters, TRUE );
	}
	
	/**
	 * 创建支付操作
	 * 
	 * @param array $payinfo
	 *        	['title' => '微信支付','body' => '某某某微信支付','money' =>0.01]
	 * @return
	 *
	 */
	public function createPay($payinfo) {
		// ①、获取用户openid
		$tools = new JsApiPay ();
		$openId = $payinfo ['openId']; // $tools->GetOpenid();
		if (empty ( $openId )) {
			return FALSE;
		}
		
		$totalFree = $payinfo ['paymoney'] * 100; // 换算支付金额
		                                         // ②、统一下单
		$input = new WxPayUnifiedOrder ();
		$input->SetBody ( $payinfo ['body'] );
		$input->SetAttach ( $payinfo ['title'] );
		$input->SetOut_trade_no ( WxPayConfig::MCHID . date ( "YmdHis" ) );
		$input->SetTotal_fee ( $totalFree );
		$input->SetTime_start ( date ( "YmdHis" ) );
		$input->SetTime_expire ( date ( "YmdHis", time () + 600 ) );
		$input->SetGoods_tag ( "扎呵呵" );
		
		$input->SetNotify_url ( "https://" . $_SERVER ['HTTP_HOST'] . "/v1/payment/notify/mini/" . intval ( $_REQUEST ['mini'] ) . "/payId/" . $payinfo ['payid'] . "/orderId/" . $payinfo ['orderid'] . "/paymentId/" . $payinfo ['paymentid'] . "/identif/" . $_REQUEST ['identif'] );
		$input->SetTrade_type ( "JSAPI" );
		$input->SetOpenid ( $openId );
		$input->SetAppid ( W_WEIXIN_APPID ); // 支付商户
		$input->SetMch_id ( WEIXIN_MCHID ); // 支付商户ID号
		$order = WxPayApi::unifiedOrder ( $input );
		try {
			$jsApiParameters = $tools->GetJsApiParameters ( $order );
		} catch ( Exception $e ) {
			return FALSE;
		}
		
		// 获取共享收货地址js函数参数
		// $editAddress = $tools->GetEditAddressParameters();
		
		// ③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
		/**
		 * 注意：
		 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
		 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
		 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
		 */
		return json_decode ( $jsApiParameters, TRUE );
	}
	/**
	 * 创建支付操作
	 * 
	 * @param array $payinfo
	 *        	['title' => '微信支付','body' => '某某某微信支付','money' =>0.01]
	 * @return
	 *
	 */
	public function createMarginPay($payinfo) {
		// ①、获取用户openid
		$tools = new JsApiPay ();
		$openId = $payinfo ['openId']; // $tools->GetOpenid();
		if (empty ( $openId )) {
			return FALSE;
		}
		
		$totalFree = $payinfo ['paymoney'] * 100; // 换算支付金额
		                                         // ②、统一下单
		$input = new WxPayUnifiedOrder ();
		$input->SetBody ( $payinfo ['body'] );
		$input->SetAttach ( $payinfo ['title'] );
		$input->SetOut_trade_no ( WxPayConfig::MCHID . date ( "YmdHis" ) );
		$input->SetTotal_fee ( $totalFree );
		$input->SetTime_start ( date ( "YmdHis" ) );
		$input->SetTime_expire ( date ( "YmdHis", time () + 600 ) );
		$input->SetGoods_tag ( "扎呵呵" );
		
		$input->SetNotify_url ( "https://".$_SERVER['HTTP_HOST']."/v1/payment/notifyMargin/mini/0/payId/".$payinfo['payid']."/orderId/".$payinfo['orderid']."/paymentId/".$payinfo['paymentid']."/identif/".$_REQUEST['identif'] );
		$input->SetTrade_type ( "JSAPI" );
		$input->SetOpenid ( $openId );
		$input->SetAppid ( W_WEIXIN_APPID ); // 支付商户
		$input->SetMch_id ( WEIXIN_MCHID ); // 支付商户ID号
		$order = WxPayApi::unifiedOrder ( $input );
		try {
			$jsApiParameters = $tools->GetJsApiParameters ( $order );
		} catch ( Exception $e ) {
			return FALSE;
		}
		
		// 获取共享收货地址js函数参数
		// $editAddress = $tools->GetEditAddressParameters();
		
		// ③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
		/**
		 * 注意：
		 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
		 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
		 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
		 */
		return json_decode ( $jsApiParameters, TRUE );
	}
	
	/**
	 * 获得对应的openId
	 * 
	 * @return array
	 */
	public function getOpenId() {
		$tools = new JsApiPay ();
		return $tools->GetOpenid ();
	}

	/**
	 * 查询订单
	 * 
	 * @param string $transaction_id        	
	 * @return bool
	 */
	public function Queryorder($transaction_id) {
		$input = new WxPayOrderQuery ();
		$input->SetTransaction_id ( $transaction_id );
		$result = WxPayApi::orderQuery ( $input );
		
		YDLib::testlog ( "call back:" . json_encode ( $result ) );
		
		if (array_key_exists ( "return_code", $result ) && array_key_exists ( "result_code", $result ) && $result ["return_code"] == "SUCCESS" && $result ["result_code"] == "SUCCESS") {
			YDLib::testlog ( "call success:" . json_encode ( $result ) );
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 返回消息通知回调
	 * 
	 * @param array $data
	 *        	回调信息
	 * @param string $msg
	 *        	回调消息
	 * @return bool
	 */
	public function NotifyProcess($data, &$msg) {
		// 打印支付回调通知
		YDLib::testlog ( "call back: NotifyProcess start" );
		YDLib::testlog ( "call back:" . json_encode ( $data ) );
		
		$notfiyOutput = array ();
		
		if (! array_key_exists ( "transaction_id", $data )) {
			$msg = "输入参数不正确";
			YDLib::testlog ( "call back: place input params!" );
			return FALSE;
		}
		// 查询订单，判断订单真实性
		if (! $this->Queryorder ( $data ["transaction_id"] )) {
			$msg = "订单查询失败";
			YDLib::testlog ( "call back: order query err!" );
			return FALSE;
		}
		
		try {

		    $params = [
                'orderid' => $_GET ['orderid'], // 充值订单id
                'return_payment_data' => json_encode ( $data ),
                'return_payment_no' => $data ['transaction_id'],
                'paymentId' => $_GET ['type'], // 支付类型
                'biz_type' => $_GET ['biz_type'], // 订单类型
                'pt_id' => $_GET ['paymentid'], // qdd_payment_transaction id,流水ID
                'out_trade_no' => $data ['attach']
            ];

		    if ($_GET ['biz_type'] == 'margin') {
                // 处理调用逻辑
                MarginpaymentModel::paySuccess ($params);
            } else {
                // 处理调用逻辑
                PaymentModel::paySuccess ($params);
            }
		} catch ( Exception $e ) {
			YDLib::testlog ( "call back: " . $e->getMessage () );
		}
		YDLib::testlog ( "call back: NotifyProcess end" );
		YDLib::testlog ( "call back: ok" );
		return TRUE;
	}

    /**
     * 消息通知
     */
    public function notifyMarginPay() {
        YDLib::testlog ( "call: notifyPay" );
        $notify = new self ();
        $notify->Handle ( false );
    }

	/**
	 * 消息通知
	 */
	public function notifyPay() {
		YDLib::testlog ( "call: notifyPay" );
		$notify = new self ();
		$notify->Handle ( false );
	}
	
	/**
	 * 返回二维码支付url
	 * 
	 * @param array $payinfo
	 *        	['title' => '微信支付','body' => '某某某微信支付','money' =>0.01]
	 * @return
	 *
	 */
	public function bizpayurl($payinfo) {
		$totalFree = $payinfo ['paymoney'] * 100; // 换算支付金额
		                                         // ②、统一下单
		$input = new WxPayUnifiedOrder ();
		$input->SetBody ( $payinfo ['body'] );
		$input->SetAttach ( $payinfo ['title'] );
		$input->SetOut_trade_no ( $payinfo ['order_no'] );
		$input->SetTotal_fee ( $totalFree );
		$input->SetTime_start ( date ( "YmdHis" ) );
		$input->SetTime_expire ( date ( "YmdHis", time () + 600 ) );
		$input->SetGoods_tag ( "扎呵呵" );
		$input->SetNotify_url ( $payinfo ['callback'] );
		$input->SetTrade_type ( "NATIVE" );
		$input->SetProduct_id ( $payinfo ['paymentid'] );
		$input->SetAppid ( W_WEIXIN_APPID ); // 支付商户
		$input->SetMch_id ( WEIXIN_MCHID ); // 支付商户ID号
		YDLib::testlog ( $input );
		$notify = new NativePay ();
		$result = $notify->GetPayUrl ( $input );
		YDLib::testlog ( $result );
		return $result ["code_url"];
	}
}