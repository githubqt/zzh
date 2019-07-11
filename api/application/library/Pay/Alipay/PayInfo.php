<?php

namespace Pay\Alipay;

use Pay\PayAbstract;
use Custom\YDLib;
use Payment\PaymentModel;
use Pay\Alipay\Alipay\AlipaySubmit;
use Pay\Alipay\Alipay\AlipayNotify;
use Pay\Alipay\Aop\AopClient;
use Pay\Alipay\Aop\AlipayTradePrecreateRequest;
use Pay\Alipay\Aop\AlipayTradeRefundRequest;
use Pay\Alipay\Aop\AopService;
use Payment\MarginpaymentModel;

/** 
 * @desc 支付宝支付基类
 * @version v0.1
 * @author zhaoyu 
 * @time  2018-5-23
 */
class PayInfo implements PayAbstract
{
 
	 /**
     * 异步消息返回
     * @param array $data 表字段名作为key的数组
     * @return bool
     */
    public function notifyPay()
    {
    	/**
		 * 功能：支付宝服务器异步通知页面
		 * 版本：3.3
		 * 日期：2012-07-23
		 * 说明：
		 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
		 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
		
		
		 *************************页面功能说明*************************
		 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
		 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
		 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
		 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
		 */
		 	require_once ("Alipay/alipay.config.php");

		 	
			//计算得出通知验证结果
			$alipayNotify = new AlipayNotify($alipay_config);
			$verify_result = $alipayNotify->verifyNotify();
			
			if($verify_result) {//验证成功
				/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				//请在这里加上商户的业务逻辑程序代
			
				
				//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
				
			    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
				
				//商户订单号
			
				$out_trade_no = $_POST['out_trade_no'];
			
				//支付宝交易号
			
				$trade_no = $_POST['trade_no'];
			
				//交易状态
				$trade_status = $_POST['trade_status'];
				YDLib::testlog(" call back:".json_encode($_POST));
			
			    if($_POST['trade_status'] == 'TRADE_FINISHED') {
					//判断该笔订单是否在商户网站中已经做过处理
						//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
						//如果有做过处理，不执行商户的业务程序
			    	
			    	
					//注意：
					//该种交易状态只在两种情况下出现
					//1、开通了普通即时到账，买家付款成功后。
					//2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
			
			        //调试用，写文本函数记录程序运行情况是否正常
			        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
			    } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
					//判断该笔订单是否在商户网站中已经做过处理
						//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
						//如果有做过处理，不执行商户的业务程序
					YDLib::testlog(" paySuccess: ".json_encode([
									'orderid'=>$_GET['orderid'],
									'return_payment_data'=>json_encode($_POST),
									'return_payment_no' => $trade_no,
									'paymentId'=>$_GET['type'],
									'biz_type'=>$_GET['biz_type'],
									'out_trade_no'=>$out_trade_no
								]));
					PaymentModel::paySuccess([
									'orderid'=>$_GET['orderid'],
									'return_payment_data'=>json_encode($_POST),
									'return_payment_no' => $trade_no,
									'paymentId'=>$_GET['type'],
									'biz_type'=>$_GET['biz_type'],
									'pt_id'=>$_GET['paymentid'],//qdd_payment_transaction id,流水ID
									'out_trade_no'=>$out_trade_no
								]);
					//注意：
					//该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
			
			        //调试用，写文本函数记录程序运行情况是否正常
			        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
			    }
			
				//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
			        
				echo "success";		//请不要修改或删除
				YDLib::testlog(" success: ".$_GET['orderid']);
				/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			} else {
			    //验证失败
			   // echo "fail";
			    
			 PaymentModel::paySuccess([
			    		'orderid'=>$_GET['orderid'],
			    		'return_payment_data'=>json_encode($_POST),
			    		'return_payment_no' => $trade_no,
			    		'paymentId'=>$_GET['type'],
			    		'biz_type'=>$_GET['biz_type'],
			    		'pt_id'=>$_GET['paymentid'],//qdd_payment_transaction id,流水ID
			    		'out_trade_no'=>$out_trade_no
			    ]);
			    
				YDLib::testlog(" fail: ".$_GET['orderid']);
			    //调试用，写文本函数记录程序运行情况是否正常
			    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
				
			}
    }
    
    /**
     * 同步消息返回
     * @param int $id 表自增ID
     */
    public function callbackPay()
    {
    	
    }
	
        
    /**
     * 退款操作
     * @return boolean 更新结果
     */
    public function returnPay($payinfo)
	{
		$aop = new AopClient ();
		$aop->appId = ALIPAY_OPENID;
		$aop->rsaPrivateKeyFilePath = ALIPAY_OPENKEY_PATH;
		$aop->alipayPublicKey = ALIPAY_OPENERT_PATH;	
		$request = new AlipayTradeRefundRequest ();
		$params = [];
		$params['trade_no'] = $payinfo['transaction_id'];
		$params['refund_amount'] = $payinfo['pay_amount'];
		$params['refund_reason'] = $payinfo['refund_desc'];
		$params['out_request_no'] = $payinfo['payment_no'];//部分退款必填
		$request->setBizContent(json_encode($params));
		YDLib::testlog($request);
		$result = $aop->execute ( $request); 
		YDLib::testlog($result);	
		
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
			return $result;
		} else {
			return FALSE;
		}		
		
				
	}
	
	/**
	 * 创建支付操作
	 */
	public function createPay($payinfo)
	{
		require_once ("Alipay/alipay.config.php");
		/**************************请求参数**************************/
        //支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        $notify_url = "https://".$_SERVER['HTTP_HOST']."/v1/payment/notify/mini/0/payId/".$payinfo['payid']."/orderId/".$payinfo['orderid']."/paymentId/".$payinfo['paymentid']."/identif/".$_REQUEST['identif'];
        
        //需http://格式的完整路径，不能加?id=123这类自定义参数

        //页面跳转同步通知页面路径
       	 //$return_url = "http://".$_SERVER['SERVER_NAME']."/index.php?c=V1Payment&a=callback&type=".$payinfo['payid']."&orderid=".$payinfo['orderid'];
        //$return_url =  "https://".$_SERVER['HTTP_HOST']."/user/order/done?payment_platform=alipay&type=".$payinfo['payid']."&orderid=".$payinfo['orderid']."&childid=".$payinfo['childid']."&identif=".$_REQUEST['identif'];
		$return_url = $payinfo['callback'];
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //商户订单号
        $out_trade_no = $payinfo['order_no'];
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = $payinfo['title'];
        //必填

        //付款金额
        $total_fee = $payinfo['paymoney'];
        //必填

        //商品展示地址
        $show_url = SHOM_URL.$_REQUEST['identif']."/user";
        //必填，需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html

        //订单描述
        $body = $payinfo['body'];
        //选填

        //超时时间
        $it_b_pay = '';
        //选填

        //钱包token
        $extern_token = '';
        //选填
		
		
		/************************************************************/
		
		//构造要请求的参数数组，无需改动
		/* $parameter = [
		    // 接口名称 (required)
		    'service' => 'create_direct_pay_by_user',
		    // 签约的支付宝账号对应的支付宝唯一用户号 (required)
		    'partner' => $alipay_config['partner'],
		    // 卖家支付宝用户号 (required)
		    'seller_id' => $alipay_config['partner'],
		    // 卖家支付宝账号 (optional)
		    'seller_email' => $alipay_config['seller_email'],
		    // 支付类型 (required)
		    'payment_type' => '1',
		    // 服务器异步通知页面路径 (optional)
		    'notify_url' => $notify_url,
		    // 请求出错时的通知页面路径 (optional)
		    'error_notify_url' => "http://{$_SERVER['HTTP_HOST']}/v1/Order/payErr/PayType/3",
		    // 商户订单号 (required)
		    'out_trade_no' => $out_trade_no,
		    // 订单名称 (required)
		    'subject' => $subject,
		    // {{{ 注: total_fee不能与price、quantity同时存在；存在price、quantity，就不能存在total_fee, 防止出现支付金额错误
		    // 付款金额, 总价和单价不可以同时出现, 如果存在总价，则应该输入商品数量，如果未输入商品数量, 系统不报错, 默认为1 (required)
		    'total_fee' => $total_fee,
		    // 商品描述 (optional)
		    'body' => 'tete',
		    // 参数编码字符集 (required)
		    '_input_charset' => $this->_alipay_config['input_charset'],
		]; */
		//构造要请求的参数数组，无需改动
		$parameter = array(
				// 接口名称 (required)
				"service" => "alipay.wap.create.direct.pay.by.user",
				// 签约的支付宝账号对应的支付宝唯一用户号 (required)
				"partner" => trim(ALIPAY_APPID),
				// 卖家支付宝用户号 (required)
				"seller_id" => trim(ALIPAY_APPID),
				// 支付类型 (required)
				"payment_type"	=> $payment_type,
				
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"show_url"	=> $show_url,
				"body"	=> $body,
				"it_b_pay"	=> $it_b_pay,
				"extern_token"	=> $extern_token,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
		$data['from'] = ($html_text);
		return $data;
	}


	/**
	 * 返回二维码支付url
	 * @param array $payinfo ['title' => '微信支付','body' => '某某某微信支付','money' =>0.01]
	 * @return 
	 */
	public function bizpayurl($payinfo)
	{
		$aop = new AopClient ();
		$aop->appId = ALIPAY_OPENID;
		$aop->rsaPrivateKeyFilePath = ALIPAY_OPENKEY_PATH;
		$aop->alipayPublicKey = ALIPAY_OPENERT_PATH;				
		$request = new AlipayTradePrecreateRequest();
		$request->setNotifyUrl($payinfo['callback']);//回调地址
		
		$params = [];
		$params['out_trade_no'] = $payinfo['order_no'];
		$params['total_amount'] = $payinfo['paymoney'];
		$params['subject'] = '短信充值';
		
		$request->setBizContent(json_encode($params));
		$result = $aop->execute($request); 
		
		YDLib::testlog($result);
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		if(!empty($resultCode)&&$resultCode == 10000){
			return $result->$responseNode->qr_code;
		} else {
			return FALSE;
		}			
	}	
	
	
	
	/**
	 * 创建保证金支付操作
	 */
	public function createMarginPay($payinfo)
	{
		require_once ("Alipay/alipay.config.php");
		/**************************请求参数**************************/
		//支付类型
		$payment_type = "1";
		//必填，不能修改'orderid'=>$orderInfo['id'],
		//服务器异步通知页面路径
		$notify_url = "https://".$_SERVER['HTTP_HOST']."/v1/payment/notifymargin/mini/0/payId/".$payinfo['payid']."/orderId/".$payinfo['orderid']."/paymentId/".$payinfo['paymentid']."/identif/".$_REQUEST['identif'];
	
		//需http://格式的完整路径，不能加?id=123这类自定义参数
	
		//页面跳转同步通知页面路径
		//$return_url = "http://".$_SERVER['SERVER_NAME']."/index.php?c=V1Payment&a=callback&type=".$payinfo['payid']."&orderid=".$payinfo['orderid'];
		//$return_url =  "https://".$_SERVER['HTTP_HOST']."/user/order/done?payment_platform=alipay&type=".$payinfo['payid']."&orderid=".$payinfo['orderid']."&childid=".$payinfo['childid']."&identif=".$_REQUEST['identif'];
		$return_url = $payinfo['callback'];
		//需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
	
		//商户订单号
		$out_trade_no = $payinfo['order_no'];
		//商户网站订单系统中唯一订单号，必填
	
		//订单名称
		$subject = $payinfo['title'];
		//必填
	
		//付款金额
		$total_fee = $payinfo['paymoney'];
		//必填
	
		//商品展示地址
		$show_url = SHOM_URL_HTTP;
		//必填，需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html
	
		//订单描述
		$body = $payinfo['body'];
		//选填
	
		//超时时间
		$it_b_pay = '';
		//选填
	
		//钱包token
		$extern_token = '';
		//选填
	
	
		/************************************************************/
	
		//构造要请求的参数数组，无需改动
		/* $parameter = [
		 // 接口名称 (required)
		 'service' => 'create_direct_pay_by_user',
		 // 签约的支付宝账号对应的支付宝唯一用户号 (required)
		 'partner' => $alipay_config['partner'],
		 // 卖家支付宝用户号 (required)
		 'seller_id' => $alipay_config['partner'],
		 // 卖家支付宝账号 (optional)
		 'seller_email' => $alipay_config['seller_email'],
		 // 支付类型 (required)
		 'payment_type' => '1',
		 // 服务器异步通知页面路径 (optional)
		 'notify_url' => $notify_url,
		 // 请求出错时的通知页面路径 (optional)
		 'error_notify_url' => "http://{$_SERVER['HTTP_HOST']}/v1/Order/payErr/PayType/3",
		 // 商户订单号 (required)
		 'out_trade_no' => $out_trade_no,
		 // 订单名称 (required)
		 'subject' => $subject,
		 // {{{ 注: total_fee不能与price、quantity同时存在；存在price、quantity，就不能存在total_fee, 防止出现支付金额错误
		 // 付款金额, 总价和单价不可以同时出现, 如果存在总价，则应该输入商品数量，如果未输入商品数量, 系统不报错, 默认为1 (required)
		 'total_fee' => $total_fee,
		 // 商品描述 (optional)
		 'body' => 'tete',
		 // 参数编码字符集 (required)
		 '_input_charset' => $this->_alipay_config['input_charset'],
			]; */
		//构造要请求的参数数组，无需改动
		$parameter = array(
				// 接口名称 (required)
				"service" => "alipay.wap.create.direct.pay.by.user",
				// 签约的支付宝账号对应的支付宝唯一用户号 (required)
				"partner" => trim(ALIPAY_APPID),
				// 卖家支付宝用户号 (required)
				"seller_id" => trim(ALIPAY_APPID),
				// 支付类型 (required)
				"payment_type"	=> $payment_type,
	
				"notify_url"	=> $notify_url,
				"return_url"	=> $return_url,
				"out_trade_no"	=> $out_trade_no,
				"subject"	=> $subject,
				"total_fee"	=> $total_fee,
				"show_url"	=> $show_url,
				"body"	=> $body,
				"it_b_pay"	=> $it_b_pay,
				"extern_token"	=> $extern_token,
				"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		//建立请求
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
		$data['from'] = ($html_text);
		return $data;
	}
	
	
	
	/**
	 * 异步保证金消息返回
	 * @param array $data 表字段名作为key的数组
	 * @return bool
	 */
	public function notifyMarginPay()
	{
		/**
		 * 功能：支付宝服务器异步通知页面
		 * 版本：3.3
		 * 日期：2012-07-23
		 * 说明：
		 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
		 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
	
	  
		 *************************页面功能说明*************************
		 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
		 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
		 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
		 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
		 */
		require_once ("Alipay/alipay.config.php");
	
		//计算得出通知验证结果
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result) {//验证成功
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//请在这里加上商户的业务逻辑程序代
				
	
			//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
	
			//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
	
			//商户订单号
				
			$out_trade_no = $_POST['out_trade_no'];
				
			//支付宝交易号
				
			$trade_no = $_POST['trade_no'];
				
			//交易状态
			$trade_status = $_POST['trade_status'];
			YDLib::testlog(" call back:".json_encode($_POST));
				
			if($_POST['trade_status'] == 'TRADE_FINISHED') {
				//判断该笔订单是否在商户网站中已经做过处理
				//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
				//如果有做过处理，不执行商户的业务程序
				YDLib::testlog(" fail: ".json_encode([
						'orderid'=>$_GET['orderid'],
						'return_payment_data'=>json_encode($_POST),
						'return_payment_no' => $trade_no,
						'paymentId'=>$_GET['type'],
						'biz_type'=>$_GET['biz_type'],
						'out_trade_no'=>$out_trade_no
				]));
				
				
				MarginpaymentModel::payFailure([
						'orderid'=>$_GET['orderid'],
						'return_payment_data'=>json_encode($_POST),
						'return_payment_no' => $trade_no,
						'paymentId'=>$_GET['type'],
						'biz_type'=>$_GET['biz_type'],
						'pt_id'=>$_GET['paymentid'],//qdd_payment_transaction id,流水ID
						'out_trade_no'=>$out_trade_no
				]);
				//注意：
				//该种交易状态只在两种情况下出现
				//1、开通了普通即时到账，买家付款成功后。
				//2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
					
				//调试用，写文本函数记录程序运行情况是否正常
				//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
			} else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
				//判断该笔订单是否在商户网站中已经做过处理
				//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
				//如果有做过处理，不执行商户的业务程序
				YDLib::testlog(" success: ".json_encode([
						'orderid'=>$_GET['orderid'],
						'return_payment_data'=>json_encode($_POST),
						'return_payment_no' => $trade_no,
						'paymentId'=>$_GET['type'],
						'biz_type'=>$_GET['biz_type'],
						'out_trade_no'=>$out_trade_no
				]));
				YDLib::testlog ("支付成功回调".$_GET);
				MarginpaymentModel::paySuccess([
						'orderid'=>$_GET['orderid'],
						'return_payment_data'=>json_encode($_POST),
						'return_payment_no' => $trade_no,
						'paymentId'=>$_GET['type'],
						'biz_type'=>$_GET['biz_type'],
						'pt_id'=>$_GET['paymentid'],//qdd_payment_transaction id,流水ID
						'out_trade_no'=>$out_trade_no
				]);
				
				
				//注意：
				//该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
					
				//调试用，写文本函数记录程序运行情况是否正常
				//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
			}
				
			//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
			 
			echo "success";		//请不要修改或删除
			YDLib::testlog(" success: ".$_GET['orderid']);
			/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		} else {
			//验证失败
			echo "fail";
			//logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
			
			YDLib::testlog(" fail: ".json_encode([
					'orderid'=>$_GET['orderid'],
					'return_payment_data'=>json_encode($_POST),
					'return_payment_no' => $trade_no,
					'paymentId'=>$_GET['type'],
					'biz_type'=>$_GET['biz_type'],
					'out_trade_no'=>$out_trade_no
			]));
			
			 MarginpaymentModel::payFailure([
					'orderid'=>$_GET['orderid'],
					'return_payment_data'=>json_encode($_POST),
					'return_payment_no' => $trade_no,
					'paymentId'=>$_GET['type'],
					'biz_type'=>$_GET['biz_type'],
					'pt_id'=>$_GET['paymentid'],//qdd_payment_transaction id,流水ID
					'out_trade_no'=>$out_trade_no
			]);
		}
	}
	
	
}