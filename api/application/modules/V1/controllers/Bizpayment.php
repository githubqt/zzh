<?php
use Custom\YDLib;
use Payment\BizpaymentModel;
use Common\CommonBase;
use Pay\PayFactory;
use Pushmsg\PushmsgRechargeModel;
use Payment\PaymentTransactionModel;
/**
 * URL支付接口
 * 
 * @version v0.01
 * @author lqt
 *         @time 2018-06-20
 */
class BizpaymentController extends BaseController {
	
	/**
	 * 短信支付生成二维码支付url
	 * <pre>
	 * 本地测试：
	 * https://api.qudiandang.com/v1/payment/bizpayurl
	 *
	 * 线上地址：
	 * https://testapi.qudiandang.com/v1/payment/bizpayurl
	 * </pre>
	 * post参数
	 * companyId　： 【必填】支付类型 {1：微信，2：支付宝} 默认1
	 * rechargeId ： 【必填】短信支付id
	 * <pre>
	 * get参数
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         </pre>
	 */
	public function bizpayurlAction() {
		$companyId = $this->_request->get ( "companyId" );
		if (! isset ( $companyId ) || ! is_numeric ( $companyId ) || ! in_array ( $companyId, [ 
				1,
				2 
		] )) {
			$companyId = 1;
		}
		$recharge_id = $this->_request->get ( "rechargeId" );
		if (! isset ( $recharge_id ) || ! is_numeric ( $recharge_id ) || $recharge_id <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40102 );
		}
		
		// 是否待支付
		$orderInfo = PushmsgRechargeModel::getInfoByID ( $recharge_id );
		if (! $orderInfo) {
			YDLib::output ( ErrnoStatus::STATUS_60299 );
		}
		
		if ($orderInfo ['status'] != '1') {
			YDLib::output ( ErrnoStatus::STATUS_50019 );
		}
		
		define ( 'W_WEIXIN_APPID', WEIXIN_APPID );
		define ( 'W_WEIXIN_APPSECRET', WEIXIN_APPSECRET );
		
		$res = BizpaymentModel::bizpayurl ( $companyId, $orderInfo );
		
		if ($res) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $res );
		} else {
			YDLib::output ( ErrnoStatus::STATUS_60521 );
		}
	}
	
	/**
	 * 异步通知回调
	 */
	public function notifyAction($id, $identif) {
		define ( 'W_WEIXIN_APPID', WEIXIN_APPID );
		define ( 'W_WEIXIN_APPSECRET', WEIXIN_APPSECRET );
		
		// 是否待支付
		$orderInfo = PushmsgRechargeModel::getInfoByID ( $id );
		if (! $orderInfo) {
			YDLib::output ( ErrnoStatus::STATUS_60299 );
		}
		
		if ($orderInfo ['status'] != '1') {
			YDLib::output ( ErrnoStatus::STATUS_50019 );
		}
		
		// 查询交易流水状态
		$info = PaymentTransactionModel::getInfo ( $orderInfo ['supplier_id'], 'sms', $orderInfo ['recharge_no'] );
		
		$data = [ ];
		$data ['type'] = $info ['pay_type'];
		$data ['orderId'] = $info ['payment_no'];
		$data ['paymentId'] = $info ['id'];
		
		$_GET ['orderid'] = $id;
		$_GET ['type'] = $info ['pay_type'];
		$_GET ['paymentid'] = $info ['id'];
		$_GET ['biz_type'] = 'sms';
		
		// 处理支付状态
		$res = BizpaymentModel::notify ( $data );
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $res );
	}
}