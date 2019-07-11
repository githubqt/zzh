<?php
use Custom\YDLib;
use Core\Logger;
use Supplier\SupplierModel;
use User\UserModel;
use User\UserSupplierModel;
use Common\Crypt3Des;

/* plugin class should be placed under ./application/plugins/ */
class PretreatmentPlugin extends Yaf_Plugin_Abstract {
	public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		/* 在路由之前执行,这个钩子里，你可以做url重写等功能 */
		// var_dump("routerStartup");
	}
	public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		/* 路由完成后，在这个钩子里，你可以做登陆检测等功能 */
		// var_dump("routerShutdown");
	}
	public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		// var_dump("dispatchLoopStartup");
	}
	public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		$mem = YDLib::getMem ( 'memcache' );
		if (__ENV__ != 'ONLINE') {
			
			$temp ['Url'] = $_SERVER ['HTTP_HOST'] . $request->getRequestUri ();
			$temp ['UsrAgent'] = isset ( $_SERVER ['HTTP_USER_AGENT'] ) ? $_SERVER ['HTTP_USER_AGENT'] : '';
			$temp ['Prama'] = $_REQUEST;
			YDLib::testLog ( $temp );
			unset ( $temp );
		}

		//商户回收控制器
        if ($request->getControllerName() === 'Recycling'){
            // 拦截 @TODO
        }else{

            // 验证商家
            $identif = ! empty ( $_SERVER ['HTTP_DOMAIN'] ) ? $_SERVER ['HTTP_DOMAIN'] : (isset ( $_REQUEST ['identif'] ) ? $_REQUEST ['identif'] : NULL);
            if (empty ( $identif ) && $request->getActionName () != 'notify' && $request->getActionName () != 'notifymargin') {
                $identif = 'test';
                //YDLib::output ( \ErrnoStatus::STATUS_10002 );
            } else {
                if ($request->getActionName () == 'notify' || $request->getActionName () == 'notifymargin') {
                    $uri = $request->getRequestUri ();
                    $identif = substr ( $uri, strpos ( $uri, 'identif' ) + strlen ( 'identif' ) + 1 );
                    if (empty ( $identif )) {
                        $identif = 'test';
                        //YDLib::output ( \ErrnoStatus::STATUS_10002 );
                    }
                }

                $infoDomain = $mem->get ( 'supplier_' . $identif );
                if (! $infoDomain) {
                    $infoDomain = SupplierModel::getInfoByDomain ( $identif );
                    if (! is_array ( $infoDomain ) || count ( $infoDomain ) < 1) {
                        // YDLib::output(\ErrnoStatus::STATUS_10003);
                        // 加载默认test的数据
                        $identif = 'test';
                        $infoDomain = $mem->get ( 'supplier_' . $identif );
                        if (! $infoDomain) {
                            $infoDomain = SupplierModel::getInfoByDomain ( $identif );
                            $mem->delete ( 'supplier_' . $identif );
                            $mem->set ( 'supplier_' . $identif, $infoDomain );
                        }
                    } else {
                        $mem->delete ( 'supplier_' . $identif );
                        $mem->set ( 'supplier_' . $identif, $infoDomain );
                    }
                }

                defined ( 'SUPPLIER_DOMAIN' ) or define ( 'SUPPLIER_DOMAIN', $identif );
                // 获取方法$infoDomain = $mem->get('supplier_'.SUPPLIER_DOMAIN);
                // 商户编号
                defined ( 'SUPPLIER_ID' ) or define ( 'SUPPLIER_ID', $infoDomain ['id'] );
                // 商户短信签名
                //defined ( 'SMS_NAME' ) or define ( 'SMS_NAME', $infoDomain ['sms_name'] ? $infoDomain ['sms_name'] : '趣典航行' );

                $excWeixin = [
                    'echo',
                    'createmenu',
                    'oauth',
                    'code',
                    'pay'
                ];
                // 商户微信appid
                defined ( 'SUPPLIER_WEIXIN_APPID' ) or define ( 'SUPPLIER_WEIXIN_APPID', $infoDomain ['app_id'] );
                // 商户微信app_secret
                defined ( 'SUPPLIER_WEIXIN_APP_SECRET' ) or define ( 'SUPPLIER_WEIXIN_APP_SECRET', $infoDomain ['app_secret'] );
                // 商户微信token
                defined ( 'SUPPLIER_WEIXIN_TOKEN' ) or define ( 'SUPPLIER_WEIXIN_TOKEN', $infoDomain ['app_token'] );
                // 商户微信AES
                defined ( 'SUPPLIER_WEIXIN_AES' ) or define ( 'SUPPLIER_WEIXIN_AES', $infoDomain ['app_aes'] );

                if (in_array ( strtolower ( $request->getControllerName () ), [
                        'weixin',
                        'payment'
                    ] ) && in_array ( strtolower ( $request->getActionName () ), $excWeixin )) {
                    // 判断微信公众号信息是否完整
                    //if ($infoDomain ['is_wechat'] == 1) {
                    //YDLib::output ( \ErrnoStatus::STATUS_10015 );
                    //}
                }
            }

            $ver = $request->getModuleName ();
            defined ( 'API_V' ) or define ( 'API_V', $ver );

            if (substr ( API_V, 1 ) > 1) {
                $excToken = [
                    'paycallback'
                ];

                if (! in_array ( strtolower ( $request->getActionName () ), $excToken ) && ! YDLib::checkToken ( $_REQUEST )) {

                    if (__ENV__ == 'ONLINE') {
                        $log = new Logger ( 'TOKEN/token_error' . date ( "Y-m-d" ), [
                            '$_POST' => var_export ( $_POST, TRUE ),
                            '$_GET' => var_export ( $_GET, TRUE ),
                            '$_SERVER' => var_export ( $_SERVER, TRUE )
                        ], PHP_EOL );

                        $log->write ();
                    }

                    YDLib::output ( \ErrnoStatus::STATUS_10001 );
                }
            }

            // 验证用户ID
            if (isset ( $_REQUEST ['user_id'] ) && ! empty ( $_REQUEST ['user_id'] ) && ! in_array ( strtolower ( $request->getActionName () ), [
                    'getnum',
                    'detail'
                ] )) {
                $user_id = intval ( $_REQUEST ['user_id'] );
                //用户验证
//            UserModel::checkLogin($user_id);
            }

            // 验证手机号
            if (isset ( $_REQUEST ['mobile'] )) {
                if (empty ( $_REQUEST ['mobile'] ) || YDLib::validMobile ( $_REQUEST ['mobile'] )) {
                    YDLib::output ( ErrnoStatus::STATUS_40052 );
                }
            }

            // 分页控制 一次最多查询15条
            if (isset ( $_REQUEST ['rows'] ) && ! empty ( $_REQUEST ['rows'] )) {

                if ($_REQUEST ['rows'] > 15) {
                    YDLib::output ( ErrnoStatus::STATUS_40166 );
                }
            }
        }

	}
	public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		// var_dump("postDispatch");
	}
	public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
		/*
		 * final hook
		 * in this hook user can do loging or implement layout
		 */
		// var_dump("dispatchLoopShutdown");
	}
}
