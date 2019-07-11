<?php
use Custom\YDLib;
use Common\CommonBase;
use Order\OrderModel;
// use Publicb;
use Weixin\Wechat;
/**
 * 支付接口
 * 
 * @version v0.01
 * @author lqt
 *         @time 2018-06-06
 */
class HfbController extends BaseController {
	
	/**
	 * 扫码（服务码）支付
	 * <pre>
	 * 本地测试：
	 * https://api.qudiandang.com/v1/hfb/scanpay
	 *
	 * 线上地址：
	 * https://testapi.qudiandang.com/v1/hfb/scanpay
	 * </pre>
	 *
	 * <pre>
	 * get参数
	 * orderId　： 【必填】订单ID
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         </pre>
	 */
	public function scanpayAction() {
		Yaf_Dispatcher::getInstance ()->enableView ();
		
		$orderId = $this->_request->get ( 'orderId' );
		
		if (! isset ( $orderId ) || ! is_numeric ( $orderId ) || $orderId <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40058 );
		}
		
		// 订单是否存在
		$orderInfo = OrderModel::getInfoByID ( $orderId );
		if (! $orderInfo) {
			YDLib::output ( ErrnoStatus::STATUS_60299 );
		}
		YDLib::testLogHfb ( $orderInfo );
		$this->getView ()->assign ( "orderId", $orderId );
		$this->getView ()->assign ( "money", $orderInfo ['order_actual_amount'] );
	}
	public function scanpayhandlerAction() {
		$orderId = $this->_request->get ( 'orderId' );
		if (! isset ( $orderId ) || ! is_numeric ( $orderId ) || $orderId <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40058 );
		}
		
		// 订单是否存在
		$orderInfo = OrderModel::getInfoByID ( $orderId );
		if (! $orderInfo) {
			YDLib::output ( ErrnoStatus::STATUS_60299 );
		}
		
		// $wxinfo = [];
		// //获取当前浏览器信息：是否是微信浏览器
		// if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== FALSE) {
		//
		// //获取微信授权
		// if (!isset($_GET['code'])){
		// $redirectUrl = "/v1/hfb/scanpayhandler/?identif=".$_REQUEST['identif']."&orderId=".$orderId;
		// $url = "/v1/weixin/oauth/?redirectUrl=".urlencode($redirectUrl)."&identif=".$_REQUEST['identif'];
		// header("Location: ".$url);
		// exit;
		// }
		//
		// $options = array(
		// 'token' => WEIXIN_TOKEN, //填写你设定的key
		// 'encodingaeskey' => WEIXIN_AES, //填写加密用的EncodingAESKey，如接口为明文模式可忽略
		// 'appid' => WEIXIN_APPID, //填写高级调用功能的app id
		// 'appsecret' => WEIXIN_APPSECRET //填写高级调用功能的密钥
		// );
		//
		// $weObj = new Wechat($options);
		// $wxinfo = $weObj->getOauthAccessToken();
		// //$this->getView()->assign("wxinfo", $wxinfo);
		//
		// }
		
		include_once APPLICATION_PATH . "/application/library/Pay/Hfb/Hfb/func/secureUtil.php";
		include_once APPLICATION_PATH . "/application/library/Pay/Hfb/Hfb/func/HFBConfig.php";
		
		$params ['amount'] = 1; // 交易金额
		$params ['payType'] = 3; // 交易类型
		                         // 代码 描述
		                         // 1 微信扫码支付
		                         // 2 支付宝扫码支付
		                         // 3 微信公众号支付
		                         // 4 支付宝服务窗支付
		                         // 5 微信条码支付
		                         // 6 支付宝条码支付
		                         // 7 QQ 钱包扫码支付
		                         // 8 微信 H5/WAP 支付第 23 页 23 页
		                         // 9 支付宝 H5/WAP 支付
		                         // 10 微信 APP 支付
		                         // 11 QQ 钱包条码支付
		                         // 12 支付宝 APP 支付
		                         // 13 QQ 钱包 WAP 支付
		                         // 14 QQ 钱包公众号支付
		                         // 15 银联钱包支付
		                         // 16 京东扫码支付
		                         // 17 京东 WAP 支付
		                         // 18 翼支付 JS 支付
		                         // 19 百度钱包扫码支付
		                         // 20 百度钱包条码支付
		                         // 21 银联钱包条码支付
		                         // 22 一码多付
		                         // 23 刷卡支付
		$params ['notifyUrl'] = NOTIFY_URL; // 结果通知地址
		$params ['bindId'] = bindId_3; // 入住ID:微信公众号支付
		$params ['remark'] = '摘要'; // 摘要不能为空
		$params ['bizType'] = '05'; // 业务代码
		$params ['goodsName'] = '1'; // 商品名称不能为空
		$params ['goodsInfo'] = ''; // 商品信息
		$params ['goodsNum'] = '1'; // 商品数量
		$params ['buyerName'] = encryptData ( 'qdd' ); // 买家姓名
		$params ['contact'] = ''; // 买家联系方式
		$params ['buyerId'] = '1'; // 用户ID不能为空
		$params ['ext1'] = 'ohOlU0pjn3P9hUEWLvJmlfGyQrN8'; // $wxinfo['openid'];
		                                                   // 去微信第三方绑定表查询数据
		                                                   // 如果没有，重新授权
		                                                   // 当 payType 取 3 或者 4 时，微信公众号
		                                                   // 支付时传入，接入方下单前需要调用微
		                                                   // 信【网页授权获取用户信息】接口获取
		                                                   // 微信用户在该公众号的 openid。第 8 页 23 页
		                                                   // 支付宝服务窗支付时传入，接入方下单
		                                                   // 前需要调用支付宝【用户信息授权】接
		                                                   // 口获取支付宝用户的 userId,则该字段
		                                                   // 为必传项
		
		$params ['ext2'] = '';
		$params ['YUL1'] = NOTIFY_URL;
		// 当 payType 取 3 或者 4， 8， 13
		// 时，该字段必输，平台会发前
		// 台通知到此地址上，报文同异
		// 步通知
		$params ['YUL2'] = '';
		// 当 payType 取 8、 10 或者 12
		// 时，该字段必输。 固定值： IOS
		// 或 ANDROID 或 WAP
		// 注释：IOS 移动应用 ：IOS
		// 安卓移动应用：ANDROID
		// WAP 网站应用：WAP
		
		$public = new Publicb ();
		$params ['YUL3'] = $public->GetIP ();
		// 用户设备外网 IP
		
		$params ['tranCode'] = 'YS1003'; // 交易码YS1003
		$params ['merchantNo'] = MERCHANTNO; // 商户编号
		$params ['version'] = VERSION; // 接口版本号v1
		$params ['channelNo'] = CHANNELNO; // 渠道编号05
		                                  // list($s1, $s2) = explode(' ', microtime());
		                                  // $millisecond = (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
		                                  // $data=time();
		$params ['tranFlow'] = $orderInfo ['order_no']; // 订单号
		$params ['tranDate'] = date ( "Ymd" );
		$params ['tranTime'] = date ( "His" );
		
		// 签名
		sign ( $params );
		
		print_r ( $params );
		YDLib::testLogHfb ( $params );
		print_r ( HFB_PAY_URL );
		YDLib::testLogHfb ( $params );
		$result = post ( $params, HFB_PAY_URL, $errMsg );
		YDLib::testLogHfb ( $result );
		var_dump ( $result );
		$resultData = convertStringToArray ( $result );
		YDLib::testLogHfb ( $resultData );
		print_r ( $resultData );
		$flag = verify ( $resultData );
		YDLib::testLogHfb ( $flag );
		print_r ( $flag );
	}
	
	/**
	 * 支付成功
	 */
	public function successAction() {
		Yaf_Dispatcher::getInstance ()->enableView ();
	}
	
	/**
	 * 异步通知回调
	 */
	public function notifyAction() {
		header ( 'Content-type:text/html;charset=utf-8' );
		include_once APPLICATION_PATH . "/application/library/Pay/Hfb/Hfb/func/secureUtil.php";
		include_once APPLICATION_PATH . "/application/library/Pay/Hfb/Hfb/func/HFBCommon.php";
		YDLib::testLogHfb ( $_POST );
		print_r ( $_POST );
		$paramStr = createLinkString ( $_POST, '' );
		YDLib::testLogHfb ( $paramStr );
		print_r ( $paramStr );
		// 验签
		$flag = verify ( $_POST );
		YDLib::testLogHfb ( $flag );
		print_r ( $flag );
	}
	public function refundpayAction() {
		include_once APPLICATION_PATH . "/application/library/Pay/Hfb/Hfb/func/secureUtil.php";
		
		$orderId = $this->_request->get ( 'orderId' );
		if (! isset ( $orderId ) || ! is_numeric ( $orderId ) || $orderId <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40058 );
		}
		
		// 订单是否存在
		$orderInfo = OrderModel::getInfoByID ( $orderId );
		if (! $orderInfo) {
			YDLib::output ( ErrnoStatus::STATUS_60299 );
		}
		
		$params ['amount'] = 500; // 交易金额
		$params ['refundReason'] = '测试'; // 交易金额
		$params ['oldTranSerialNum'] = $orderInfo ['order_no']; // 原支付交易流水号
		$params ['currency'] = 'CNY'; // 交易币种
		$params ['remark'] = 'remark'; // 备注
		$params ['ext1'] = ''; // 备注
		$params ['ext2'] = ''; // 备注
		$params ['YUL1'] = ''; // 备注
		$params ['YUL2'] = ''; // 备注
		$params ['YUL3'] = ''; // 备注
		
		$params ['tranCode'] = 'YS9001';
		$params ['merchantNo'] = MERCHANTNO;
		$params ['version'] = VERSION;
		$params ['channelNo'] = CHANNELNO;
		// list($s1, $s2) = explode(' ', microtime());
		// $millisecond = (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
		// $data=time();
		$params ['tranSerialNum'] = $orderInfo ['order_no'];
		$params ['tranDate'] = date ( "Ymd" );
		$params ['tranTime'] = date ( "His" );
		
		// 签名
		sign ( $params );
		YDLib::testLogHfb ( '退款-----------------------------' );
		YDLib::testLogHfb ( $params );
		print_r ( $params );
		$result = post ( $params, HFB_PAY_URL, $errMsg );
		$resultData = convertStringToArray ( $result );
		YDLib::testLogHfb ( $resultData );
		print_r ( $resultData );
		// 验签
		$flag = verify ( $resultData );
		YDLib::testLogHfb ( $flag );
		print_r ( $flag );
	}
	public function dpayAction() {
		include_once APPLICATION_PATH . "/application/library/Pay/Hfb/Hfb/func/secureUtil.php";
		include_once APPLICATION_PATH . "/application/library/Pay/Hfb/Hfb/func/HFBCommon.php";
		
		YDLib::testLogHfb ( "===========处理实时代付请求开始============" );
		print_r ( "===========处理实时代付请求开始============" );
		
		$params ['accNo'] = '6222021715003135656'; // 收款账号
		$params ['accName'] = '赖清涛'; // 收款账户名
		$params ['bankAgentId'] = '102100099996'; // 账户联行号
		$params ['bankName'] = '中国工商银行'; // 收款行名称
		$params ['amount'] = 500; // 交易金额（分）
		$params ['remark'] = '测试代付'; // 摘要
		                             
		// 加密敏感数据
		if (! empty ( $params ['accNo'] )) {
			$buyerName = $params ['accNo']; // 买家姓名
			$buyerName = encryptData ( $buyerName );
			$params ['accNo'] = $buyerName;
			
			if (! empty ( $params ['accName'] )) {
				$contact = $params ['accName']; // 买家联系方式
				$contact = encryptData ( $contact );
				$params ['accName'] = $contact;
			}
		}
		$params ['tranCode'] = '1001';
		$params ['merchantNo'] = MERCHANTNO;
		$params ['version'] = VERSION;
		$params ['channelNo'] = CHANNELNO;
		list ( $s1, $s2 ) = explode ( ' ', microtime () );
		$millisecond = ( float ) sprintf ( '%.0f', (floatval ( $s1 ) + floatval ( $s2 )) * 1000 );
		$data = time ();
		$params ['tranFlow'] = $millisecond;
		$params ['tranDate'] = date ( "Ymd" );
		$params ['tranTime'] = date ( "His" );
		$params ['currency'] = 'RMB';
		$params ['ext1'] = '';
		$params ['ext2'] = '';
		$params ['YUL1'] = '';
		$params ['YUL2'] = '';
		$params ['YUL3'] = '';
		// 签名
		sign ( $params );
		YDLib::testLogHfb ( $params );
		print_r ( $params );
		$result = post ( $params, HFB_PAY_URL, $errMsg );
		YDLib::testLogHfb ( $result );
		print_r ( $result );
		$resultData = convertStringToArray ( $result );
		YDLib::testLogHfb ( $resultData );
		print_r ( $resultData );
		$flag = verify ( $resultData );
		YDLib::testLogHfb ( $flag );
		print_r ( $flag );
	}
}
?>