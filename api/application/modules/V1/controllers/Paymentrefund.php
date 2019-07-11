<?php
use Custom\YDLib;
use Payment\PaymentModel;
use Payment\PaymentrefundModel;
use Payment\PaymentTransactionModel;
use Weixin\Wechat;
use Order\OrderModel;
use Common\CommonBase;
use Pay\PayFactory;
/**
 * 退款接口
 * 
 * @version v0.01
 * @author lqt
 *         @time 2018-08-1
 */
class PaymentrefundController extends BaseController {
	
	/**
	 * 发起退款接口
	 * <pre>
	 * 本地测试：
	 * http://api.qudiandang.com/v1/Paymentrefund/refund
	 *
	 * 线上地址：
	 * http://testapi.qudiandang.com/v1/Paymentrefund/refund
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * payment_id　： 【必填】支付流水ID
 	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功",
	 *         "data": {
	 *        
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "60151",
	 *         "errmsg": "退款失败",
	 *         "data": ""
	 *         }
	 *         </pre>
	 */
	public function refundAction() {
		$payment_id = $this->_request->getPost ( "payment_id" );
		$payment_money = $this->_request->getPost ( "payment_money" );
		$biz_no = $this->_request->getPost ( "biz_no" );
		$refund_desc = $this->_request->getPost ( "refund_desc" );

		if (empty ( $payment_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40110 );
		}
		
		$detail = PaymentTransactionModel::getInfoByID ( $payment_id );
		if (! $detail) {
			YDLib::output ( ErrnoStatus::STATUS_40111 );
		}
		if ($detail ['type'] == '2' || $detail ['is_return'] == '2' || $detail ['status'] == '1' || $detail ['biz_type'] !== 'order') {
			YDLib::output ( ErrnoStatus::STATUS_40112 );
		}

		if (isset($payment_money)) {
            $detail['pay_amount'] = $payment_money;
        }

        if (isset($biz_no)) {
            $detail['biz_no'] = $biz_no;
        }

        $detail ['refund_desc'] = '未成团退款';
        if (isset($refund_desc)) {
            $detail ['refund_desc'] = $refund_desc;
        }

		define ( 'W_WEIXIN_APPID', WEIXIN_APPID );
		define ( 'W_WEIXIN_APPSECRET', WEIXIN_APPSECRET );
		PaymentrefundModel::create ( $detail );
	}

    public function returnSelfPayAction()
    {
        define ( 'W_WEIXIN_APPID', WEIXIN_APPID );
        define ( 'W_WEIXIN_APPSECRET', WEIXIN_APPSECRET );
        PaymentrefundModel::returnSelfPay();
    }
	
	/**
	 * 异步通知回调
	 */
	public function notifyAction($mini, $payId, $orderId, $paymentId, $identif) {
		$data = [ ];
		$data ['type'] = $payId;
		$data ['orderId'] = $orderId;
		$data ['paymentId'] = $paymentId;
		$_GET ['orderid'] = $orderId;
		$_GET ['type'] = $payId;
		$_GET ['paymentid'] = $paymentId;
		$_GET ['biz_type'] = 'order';
		if ($mini == 1) {
			define ( 'W_WEIXIN_APPID', MINI_WEIXIN_APPID );
			define ( 'W_WEIXIN_APPSECRET', MINI_WEIXIN_APPSECRET );
		} else {
			define ( 'W_WEIXIN_APPID', WEIXIN_APPID );
			define ( 'W_WEIXIN_APPSECRET', WEIXIN_APPSECRET );
		}
		
		$res = PaymentModel::notify ( $data );
		
		if (PaymentModel::ORDER_PAY_NOTFOUNT_ONLINE === $res) {
			YDLib::testlog ( "call back: " . ErrnoStatus::STATUS_60088 );
			YDLib::output ( ErrnoStatus::STATUS_60088 );
		}
		
		if (PaymentModel::ORDER_PENDING_PAYMENT === $res) {
			YDLib::testlog ( "call back: " . ErrnoStatus::STATUS_60089 );
			YDLib::output ( ErrnoStatus::STATUS_60089 );
		}
		
		if (PaymentModel::ORDER_NOT_PAYMENT === $res) {
			YDLib::testlog ( "call back: " . ErrnoStatus::STATUS_60090 );
			YDLib::output ( ErrnoStatus::STATUS_60090 );
		}
		
		if (PaymentModel::USER_NOT_OPENID === $res) {
			YDLib::testlog ( "call back: " . ErrnoStatus::STATUS_60150 );
			YDLib::output ( ErrnoStatus::STATUS_60150 );
		}
		
		if (PaymentModel::ORDER_PAY_YES_PAY === $res) {
			YDLib::testlog ( "call back: " . ErrnoStatus::STATUS_60151 );
			YDLib::output ( ErrnoStatus::STATUS_60151 );
		}
	}
	
	
	
}