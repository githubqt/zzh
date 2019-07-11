<?php
use Custom\YDLib;
use Payment\PaymentModel;
use Weixin\Wechat;
use Order\OrderModel;
use Common\CommonBase;
use Pay\PayFactory;
use Supplier\SupplierModel;
/**
 * 支付接口
 *
 * @version v0.01
 * @author zhaoyu
 *         @time 2018-05-23
 */
class PaymentController extends BaseController {
	const ORDER_PAY_NOTFOUNT_ONLINE = - 1; // 非线上支付单
	const ORDER_PENDING_PAYMENT = - 2; // 非支付订单
	const ORDER_NOT_PAYMENT = - 3; // 非支付订单
	const USER_NOT_OPENID = - 4;
	const ORDER_PAY_YES_PAY = - 5;
	const PAY_NONE_PAY = 1; // 未支付
	const PAY_YES_PAY = 2; // 已支付

	const WECHATOPENID = "MMGFTSYERYHDGA_PAY_" . SUPPLIER_ID; // 微信openID

	/**
	 * 发起支付接口
	 * <pre>
	 * 本地测试：
	 * http://api.qudiandang.com/v1/payment/create
	 *
	 * 线上地址：
	 * http://testapi.qudiandang.com/v1/payment/create
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * orderId　： 【必填】订单ID
	 * payCompanyId　：【必填】 支付公司ID: 1 微信 2 支付宝 3银联支付
	 * mini : 【微信特有】 是否小程序支付 , 填写则 openId必填 {空：不是小程序 ，1：是微信小程序}
	 * openId ： 微信openID
	 * callback　： 【非必填】订单支付成功后跳转地址（全路径，需要url编码），默认跳转后台支付成功页面
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
	 *         "appId": "wx51628c95d861ebd6",
	 *         "nonceStr": "9z7bdbnwtus5glwnojgboyslc9sut3qx",
	 *         "package": "prepay_id=wx20170320163756a8e72c88050974798341",
	 *         "signType": "MD5",
	 *         "timeStamp": "1489999076",
	 *         "paySign": "99AF4755B996D874C7749B8FD37195E8"
	 *         }
	 *         }
	 *
	 *         失败：
	 *         {
	 *         "errno": "60151",
	 *         "errmsg": "已支付不能重复支付",
	 *         "data": ""
	 *         }
	 *         </pre>
	 */
	public function createAction() {
		$data = [ ];
		$data ['orderId'] = $this->_request->getPost ( "orderId" );
		$data ['payCompanyId'] = $this->_request->getPost ( "payCompanyId" );
		$data ['callback'] = $this->_request->getPost ( "callback" );
		if (isset ( $data ['callback'] ) && ! empty ( $data ['callback'] )) {
			$data ['callback'] = urldecode ( $data ['callback'] );
		} else {
			$data ['callback'] = SHOM_URL . $_REQUEST ['identif'] . "/success";
		}
		$mini = $this->_request->getPost ( 'mini' );
		if (! isset ( $mini ) || empty ( $mini ) || ! is_numeric ( $mini ) || $mini != 1) {
			$mini = 0;
		}

		if (! isset ( $data ['orderId'] ) || ! is_numeric ( $data ['orderId'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40088 );
		}

		if (! isset ( $data ['payCompanyId'] ) || ! is_numeric ( $data ['payCompanyId'] ) || ! in_array ( $data ['payCompanyId'], [
				1,
				2,
				3
		] )) {
			YDLib::output ( ErrnoStatus::STATUS_40089 );
		}
		if ($mini == 1) {

			define ( 'W_WEIXIN_APPID', MINI_WEIXIN_APPID );
			define ( 'W_WEIXIN_APPSECRET', MINI_WEIXIN_APPSECRET );

			if ($this->_request->getPost ( 'openId' )) {
				$res = PaymentModel::create ( $data ['orderId'], $data ['payCompanyId'], $data ['callback'], [
						'openId' => $this->_request->getPost ( 'openId' )
				] );
			} else {
				YDLib::output ( ErrnoStatus::STATUS_40094 );
			}
		} else {
			define ( 'W_WEIXIN_APPID', WEIXIN_APPID );

			define ( 'W_WEIXIN_APPSECRET', WEIXIN_APPSECRET );
			if ($this->_request->getPost ( 'openId' )) {
				$res = PaymentModel::create ( $data ['orderId'], $data ['payCompanyId'], $data ['callback'], [
						'openId' => $this->_request->getPost ( 'openId' )
				] );
			} else {
                YDLib::testlog ($data);
				$res = PaymentModel::create ( $data ['orderId'], $data ['payCompanyId'], $data ['callback'] );
			}
		}

        YDLib::testlog ($res);
		
		if (PaymentModel::ORDER_PAY_NOTFOUNT_ONLINE === $res) {
			YDLib::output ( ErrnoStatus::STATUS_60088 );
		}

		if (PaymentModel::ORDER_PENDING_PAYMENT === $res) {
			YDLib::output ( ErrnoStatus::STATUS_60089 );
		}

		if (PaymentModel::ORDER_NOT_PAYMENT === $res) {
			YDLib::output ( ErrnoStatus::STATUS_60090 );
		}

		if (PaymentModel::USER_NOT_OPENID === $res) {
			YDLib::output ( ErrnoStatus::STATUS_60150 );
		}

		if (PaymentModel::ORDER_PAY_YES_PAY === $res) {
			YDLib::output ( ErrnoStatus::STATUS_60151 );
		}

		if (PaymentModel::TUAN_CLOSE === $res) {//拼团已成团，不能支付参与拼团了
			YDLib::output ( ErrnoStatus::STATUS_50026 );
		}

		if (FALSE === $res) {
			YDLib::output ( ErrnoStatus::STATUS_60249 );
		}
		if($res == 200){
		$info ['single']  = '200';
		$info ['callback'] = SHOM_URL_MINI . $_REQUEST ['identif'] . "/success";
		$info ['failback'] = SHOM_URL_MINI . $_REQUEST ['identif'] . "/user";
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $info);
		}
		//小程序专用返回值
		$res ['callback'] = SHOM_URL_MINI . $_REQUEST ['identif'] . "/success";
		$res ['failback'] = SHOM_URL_MINI . $_REQUEST ['identif'] . "/user";
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $res );
	}

	/**
	 * 支付页面
	 * <pre>
	 * 本地测试：
	 * https://api.qudiandang.com/v1/payment/pay
	 *
	 * 线上地址：
	 * https://testapi.qudiandang.com/v1/payment/pay
	 * </pre>
	 *
	 * <pre>
	 * get参数
	 * orderId　： 【必填】订单ID
	 * mini : 【非必填】 是否微信小程序支付 {空：不是小程序 ，1：是微信小程序}
	 * lang : 【非必填】 是否长链接 {空：是，1：不是}
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *
	 *         <pre>
	 *         </pre>
	 */
	public function payAction() {
		Yaf_Dispatcher::getInstance ()->enableView ();

		$orderId = $this->_request->get ( 'orderId' );

		$mini = $this->_request->get ( 'mini' );
		if (! isset ( $mini ) || empty ( $mini ) || ! is_numeric ( $mini ) || $mini != 1) {
			$mini = 0;
		}

		$lang = $this->_request->get ( 'lang' );
		if (! isset ( $lang ) || empty ( $lang ) || ! is_numeric ( $lang ) || $lang != 1) {
			$lang = 0;
		}

		if (! isset ( $orderId ) || ! is_numeric ( $orderId ) || $orderId <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40058 );
		}

		// 订单是否存在
		$orderInfo = OrderModel::getInfoByID ( $orderId );
		if (! $orderInfo) {
			YDLib::output ( ErrnoStatus::STATUS_60299 );
		}

		// 获取当前浏览器信息：是否是微信浏览器
		if (strpos ( $_SERVER ['HTTP_USER_AGENT'], 'MicroMessenger' ) !== FALSE && $mini == 0) {

			$wxinfo = [ ];
			if (! empty ( $_COOKIE [self::WECHATOPENID] )) {
				$wxinfo ['openid'] = $_COOKIE [self::WECHATOPENID];
			} else {
				// 获取微信授权
				if (! isset ( $_GET ['code'] )) {
					$redirectUrl = "/v1/payment/pay/?identif=" . $_REQUEST ['identif'] . "&orderId=" . $orderId;
					$url = "/v1/weixin/oauthPay/?redirectUrl=" . urlencode ( $redirectUrl ) . "&identif=" . $_REQUEST ['identif'];
					header ( "Location: " . $url );
					exit ();
				}

				$options = array (
						'token' => WEIXIN_TOKEN, // 填写你设定的key
						'encodingaeskey' => WEIXIN_AES, // 填写加密用的EncodingAESKey，如接口为明文模式可忽略
						'appid' => WEIXIN_APPID, // 填写高级调用功能的app id
						'appsecret' => WEIXIN_APPSECRET
				); // 填写高级调用功能的密钥

				$weObj = new Wechat ( $options );
				$wxinfo = $weObj->getOauthAccessToken ();
				setcookie ( self::WECHATOPENID, $wxinfo ['openid'], 0, "/", COOKIE_DOMAIN );
			}
			$this->getView ()->assign ( "wxinfo", $wxinfo );
		}

		if ($lang == 0) {
			$callback = M_URL_HTTP . "success"; // 长连接
		} else {
			$callback = SHOM_URL_HTTP . $_REQUEST ['identif'] . "/success"; // 短链接
		}

		$this->getView ()->assign ( "callback", $callback );
		$this->getView ()->assign ( "mini", $mini );

		// 获取秒杀信息
		$skillInfo = OrderModel::getSeckillInfoByOrderID ( $orderId );
		if ($skillInfo ['discount_type'] == 1) {
			$second = $skillInfo ['order_del'] * 60 - time () + strtotime ( $orderInfo ['created_at'] );
			$time ['time_t'] = $skillInfo ['order_del'];
		} else {
			$second = 40 * 60 - time () + strtotime ( $orderInfo ['created_at'] );
			$time ['time_t'] = 40;
		}

		$time ['time_m'] = 0;
		$time ['time_i'] = "00";
		$time ['time_s'] = "00";
		if ($second > 0) {
			$time ['time_m'] = $second - 1;
			$time ['time_i'] = floor ( $second / 60 );
			if ($time ['time_i'] < 10)
				$time ['time_i'] = "0" . $time ['time_i'];
			$time ['time_s'] = $second % 60;
			if ($time ['time_s'] < 10)
				$time ['time_s'] = "0" . $time ['time_s'];
		}
		$this->getView ()->assign ( "time", $time );

		$this->getView ()->assign ( "orderInfo", $orderInfo );
	}

	/**
	 * 支付成功
	 * <pre>
	 * 本地测试：
	 * https://api.qudiandang.com/v1/payment/success
	 *
	 * 线上地址：
	 * https://testapi.qudiandang.com/v1/payment/success
	 * </pre>
	 */
	public function successAction() {
		// 测试支付成功回调事件处理
		// YDLib::testlog("测试支付成功回调事件处理" );
		// Array
		// (
		// [childid] => 80
		// [orderid] => 97
		// [title] => 101807261000000209
		// [payid] => 2
		// [body] => 101807261000000209
		// [order_no] => 101807261000000209
		// [paymoney] => 101.00
		// [company] => alipay
		// [paymentid] => 34
		// [callback] => http://testm.qudiandang.com/test/success
		// )

//		 $payInfo = [
//		 'orderid'=>'537',//$payinfo['orderid']
//		 'return_payment_data'=>json_encode($_POST),
//		 'return_payment_no' => '101811011000001827',
//		 'paymentId'=>'2',//$payinfo['payid']
//		 'biz_type'=>'order',
//		 'pt_id'=>'285',//$payinfo['paymentid']
//		 'out_trade_no'=>'101811011000001827'
//		 ];
//         print_r($payInfo);//\Payment\MarginpaymentModel//PaymentModel
//		 $res = PaymentModel::paySuccess($payInfo);
//		 //$res = \Payment\MarginpaymentModel::paySuccess($payInfo);
//		 print_r($res);
//		 exit;
		Yaf_Dispatcher::getInstance ()->enableView ();
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
		YDLib::testlog ($_GET);

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

	/**
	 * 保证金支付页面
	 * <pre>
	 * 本地测试：
	 * https://api.qudiandang.com/v1/payment/marginpay
	 *
	 * 线上地址：
	 * https://testapi.qudiandang.com/v1/payment/marginpay
	 * </pre>
	 *
	 * <pre>
	 * get参数
	 * id　： 【必填】活动关联ID
	 * margin： 【必填】保证金
	 * user_id ：【必填】用户ID
	 * address_id ：【必填】收货地址
	 * product_id ： 【必填】商品ID
	 * mini : 【非必填】 是否微信小程序支付 {空：不是小程序 ，1：是微信小程序}
	 * lang : 【非必填】 是否长链接 {空：是，1：不是}
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *
	 *         <pre>
	 *         </pre>
	 */
	public function marginpayAction() {
		Yaf_Dispatcher::getInstance ()->enableView ();
		$info = [ ];
		$info ['id'] = $this->_request->get ( 'id' );
		$info ['margin'] = $this->_request->get ( 'margin' );
		$info ['user_id'] = $this->_request->get ( 'user_id' );
		$info ['address_id'] = $this->_request->get ( 'address_id' );
		$info ['product_id'] = $this->_request->get ( 'product_id' );
		$info ['delivery_type'] = $this->_request->get ( 'delivery_type' );

		$mini = $this->_request->get ( 'mini' );
		if (! isset ( $mini ) || empty ( $mini ) || ! is_numeric ( $mini ) || $mini != 1) {
			$mini = 0;
		}

		$lang = $this->_request->get ( 'lang' );
		if (! isset ( $lang ) || empty ( $lang ) || ! is_numeric ( $lang ) || $lang != 1) {
			$lang = 0;
		}

		if (! isset ( $info ['id'] ) || ! is_numeric ( $info ['id'] ) || $info ['id'] <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40058 );
		}

		if (! isset ( $info ['user_id'] ) || ! is_numeric ( $info ['user_id'] ) || $info ['user_id'] <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}

		if (! isset ( $info ['product_id'] ) || ! is_numeric ( $info ['product_id'] ) || $info ['product_id'] <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40009 );
		}

        if (! isset ( $info ['delivery_type'] ) || ! is_numeric ( $info ['delivery_type'] ) || $info ['delivery_type'] != 1) {
            $info ['delivery_type'] = 0;
        }

        if ($info ['delivery_type'] == 0) {
            if (! isset ( $info ['address_id'] ) || ! is_numeric ( $info ['address_id'] ) || $info ['address_id'] <= 0) {
                YDLib::output ( ErrnoStatus::STATUS_40098 );
            }
        }

		if (! isset ( $info ['margin'] ) || ! is_numeric ( $info ['margin'] ) || $info ['margin'] <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_60589 );
		}

		// 获取当前浏览器信息：是否是微信浏览器
		if (strpos ( $_SERVER ['HTTP_USER_AGENT'], 'MicroMessenger' ) !== FALSE && $mini == 0) {

			$wxinfo = [ ];
			if (! empty ( $_COOKIE [self::WECHATOPENID] )) {
				$wxinfo ['openid'] = $_COOKIE [self::WECHATOPENID];
			} else {
								// 获取微信授权
				if (! isset ( $_GET ['code'] )) {
					$redirectUrl = "/v1/payment/marginpay/?identif=" . $_REQUEST ['identif'] ."&lang=" . $lang. "&id=" . $info ['id'] . "&margin=" . $info ['margin'] .
					"&user_id=" . $info ['user_id'] . "&address_id=" . $info ['address_id'] . "&product_id=" . $info ['product_id'] . "&delivery_type=" . $info ['delivery_type'] ;
					$url = "/v1/weixin/oauthPay/?redirectUrl=" . urlencode ( $redirectUrl ) . "&identif=" . $_REQUEST ['identif'];
					header ( "Location: " . $url );
					exit ();
				}

				$options = array (
						'token' => WEIXIN_TOKEN, // 填写你设定的key
						'encodingaeskey' => WEIXIN_AES, // 填写加密用的EncodingAESKey，如接口为明文模式可忽略
						'appid' => WEIXIN_APPID, // 填写高级调用功能的app id
						'appsecret' => WEIXIN_APPSECRET
				); // 填写高级调用功能的密钥

				$weObj = new Wechat ( $options );
				$wxinfo = $weObj->getOauthAccessToken ();
				setcookie ( self::WECHATOPENID, $wxinfo ['openid'], 0, "/", COOKIE_DOMAIN);
			}
			$this->getView ()->assign ( "wxinfo", $wxinfo );
		}

		if ($lang == 0) {
			$callback = M_URL_HTTP . "product?id=".$info ['id']."&product_id=".$info ['product_id']; // 长连接
		} else {
			$callback = SHOM_URL_HTTP . $_REQUEST ['identif'] . "/product?id=".$info ['id']."&product_id=".$info ['product_id']; // 短链接
		}

		$this->getView ()->assign ( "callback", $callback );
		$this->getView ()->assign ( "mini", $mini );

		$this->getView ()->assign ( "info", $info );
	}

	/*
	 * 发起支付保证金接口
	 *
	 * * <pre>
	 * 本地测试：
	 * https://api.qudiandang.com/v1/payment/createmargin
	 *
	 * 线上地址：
	 * https://testapi.qudiandang.com/v1/payment/createmargin
	 *
	 * <pre>
	 * POST参数
	 * orderId　： 【必填】订单ID
	 * payCompanyId　：【必填】 支付公司ID: 1 微信 2 支付宝 3银联支付
	 * mini : 【微信特有】 是否小程序支付 , 填写则 openId必填 {空：不是小程序 ，1：是微信小程序}
	 * openId ： 微信openID
	 * callback　： 【非必填】订单支付成功后跳转地址（全路径，需要url编码），默认跳转后台支付成功页面
	 * </pre>
	 *
	 *
	 */
	public function createmarginAction() {
		$data = [ ];
		$data ['orderId'] = $this->_request->getPost ( "orderId" );
		$data ['payCompanyId'] = $this->_request->getPost ( "payCompanyId" );
		$data ['callback'] = $this->_request->getPost ( "callback" );
		if (isset ( $data ['callback'] ) && ! empty ( $data ['callback'] )) {
			$data ['callback'] = urldecode ( $data ['callback'] );
		} else {
			$data ['callback'] = SHOM_URL . $_REQUEST ['identif'] . "/success";
		}
		$mini = $this->_request->getPost ( 'mini' );
		if (! isset ( $mini ) || empty ( $mini ) || ! is_numeric ( $mini ) || $mini != 1) {
			$mini = 0;
		}


		if (! isset ( $data ['orderId'] ) || ! is_numeric ( $data ['orderId'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40088 );
		}

		if (! isset ( $data ['payCompanyId'] ) || ! is_numeric ( $data ['payCompanyId'] ) || ! in_array ( $data ['payCompanyId'], [
				1,
				2,
				3
		] )) {
			YDLib::output ( ErrnoStatus::STATUS_40089 );
		}


		if ($mini == 1) {

			define ( 'W_WEIXIN_APPID', MINI_WEIXIN_APPID );
			define ( 'W_WEIXIN_APPSECRET', MINI_WEIXIN_APPSECRET );

			if ($this->_request->getPost ( 'openId' )) {

				$res = PaymentModel::createMargin ( $data ['orderId'], $data ['payCompanyId'], $data ['callback'], [
						'openId' => $this->_request->getPost ( 'openId' )
				] );

			} else {
				YDLib::output ( ErrnoStatus::STATUS_40094 );
			}
		} else {
			define ( 'W_WEIXIN_APPID', WEIXIN_APPID );
			define ( 'W_WEIXIN_APPSECRET', WEIXIN_APPSECRET );
			if ($this->_request->getPost ( 'openId' )) {

				$res = PaymentModel::createMargin ( $data ['orderId'], $data ['payCompanyId'], $data ['callback'], [
						'openId' => $this->_request->getPost ( 'openId' )
				] );

			} else {
				$res = PaymentModel::createMargin ( $data ['orderId'], $data ['payCompanyId'], $data ['callback'] );

			}
		}
		if (PaymentModel::ORDER_PAY_NOTFOUNT_ONLINE === $res) {
			YDLib::output ( ErrnoStatus::STATUS_60088 );
		}

		if (PaymentModel::ORDER_PENDING_PAYMENT === $res) {
			YDLib::output ( ErrnoStatus::STATUS_60089 );
		}

		if (PaymentModel::ORDER_NOT_PAYMENT === $res) {
			YDLib::output ( ErrnoStatus::STATUS_60090 );
		}

		if (PaymentModel::USER_NOT_OPENID === $res) {
			YDLib::output ( ErrnoStatus::STATUS_60150 );
		}

		if (PaymentModel::ORDER_PAY_YES_PAY === $res) {
			YDLib::output ( ErrnoStatus::STATUS_60151 );
		}

		if (FALSE === $res) {
			YDLib::output ( ErrnoStatus::STATUS_60249 );
		}

		$res ['callback'] = SHOM_URL_MINI . $_REQUEST ['identif'] . "/success";
		$res ['failback'] = SHOM_URL_MINI . $_REQUEST ['identif'] . "/user";

		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $res );
	}

	/**
	 * 保证金异步通知回调
	 * <pre>
	 * 本地测试：
	 * https://api.qudiandang.com/v1/payment/notifymargin
	 *
	 * 线上地址：
	 * https://testapi.qudiandang.com/v1/payment/notifymargin
	 *
	 *
	 * <pre>
	 * POST参数
	 * orderId　： 【必填】订单ID
	 * payCompanyId　：【必填】 支付公司ID: 1 微信 2 支付宝 3银联支付
	 * payId ： 【必填】 支付渠道 来自weixin,alipay,unionpay
	 * identif：【必填】 test
	 * mini : 【微信特有】 是否小程序支付 , 填写则 openId必填 {空：不是小程序 ，1：是微信小程序}
	 * </pre>
	 */
	public function notifymarginAction($mini, $payId, $orderId, $paymentId, $identif) {
		$data = [ ];
		$data ['type'] = $payId;
		$data ['orderId'] = $orderId;
		$data ['paymentId'] = $paymentId;
		$_GET ['orderid'] = $orderId;
		$_GET ['type'] = $payId;
		$_GET ['paymentid'] = $paymentId;
		$_GET ['biz_type'] = 'margin';
		if ($mini == 1) {
			define ( 'W_WEIXIN_APPID', MINI_WEIXIN_APPID );
			define ( 'W_WEIXIN_APPSECRET', MINI_WEIXIN_APPSECRET );
		} else {
			define ( 'W_WEIXIN_APPID', WEIXIN_APPID );
			define ( 'W_WEIXIN_APPSECRET', WEIXIN_APPSECRET );
		}

		$res = PaymentModel::notifyMargin ( $data );

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



	/**
	 *
	 * 保证金支付文案页
	 *  http://api.qudiandang.com/v1/bidding/copywriting
	 */
	public function copywritingAction() {
		Yaf_Dispatcher::getInstance ()->enableView ();

	}

}