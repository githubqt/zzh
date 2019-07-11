<?php
/** 
 * 微信方法
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-14
 */
use Custom\YDLib;
use Weixin\Wechat; 
use Weixin\WXBizMsgCrypt;
use Weixin\JSSDK;
use Common\CommonBase;
use User\UserModel;
use User\UserSupplierThridModel;
use User\UserSupplierBindModel;
use Weixin\WexinAuthorizationModel;
use Common\Crypt3Des;

class WeixinController extends BaseController
{
    const WECHATOPENID				=  "MMGFTSYERYHDGA_".SUPPLIER_ID;	//微信openID
    const WECHATASSEDTOKE			=  "MMGFTSYERYTOKE_".SUPPLIER_ID;	//微信openID
	/**
     * 微信回复消息接口
     *
     * <pre>
     *   GET参数
     *     identif : 供应商标识
     * </pre>
     *
     * <pre>
     *    调用方式：
     *        正式：   http://api.qudiandang.com/v1/weixin/echo/?identif=test
     *        测试：   http://testapi.qudiandang.com/v1/weixin/echo/?identif=test
     *
     * </pre>
     *
     * @return string 返回XML数据格式
     * <pre>
	 * 
     * </pre>
     */
	public function echoAction()
	{
		$options = array(
				'token'           => SUPPLIER_WEIXIN_TOKEN, //填写你设定的key
		        'encodingaeskey'  => SUPPLIER_WEIXIN_AES //填写加密用的EncodingAESKey，如接口为明文模式可忽略
			);
	
		$weObj = new Wechat($options);
		$weObj->valid();//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败
		$type = $weObj->getRev()->getRevType();
		switch ($type) {
			case Wechat::MSGTYPE_TEXT:
					$weObj->text("hello, I'm wechat")->reply();
					exit;
					break;
			case Wechat::MSGTYPE_EVENT:
					break;
			case Wechat::MSGTYPE_IMAGE:
					break;
			default:
					$weObj->text("help info")->reply();
		}
	}
	
	
	/**
     * 创建微信菜单
     *
     * <pre>
     *   POST参数
     *     identif : 供应商标识
	 *     menu :   菜单数据  参考微信官方菜单文档
	 *         {"button":{{"type":"click","name":"最新消息","key":"MENU_KEY_NEWS"},{"type":"view","name":"商城","url":"http://test.testm.qudiandang.com/"},{"type":"view","name":"首页","key":"http://www.qudiandang.com/"}}}
	 * 
     * </pre>
     *
     * <pre>
     *    调用方式：
     *        正式：   http://api.qudiandang.com/v1/weixin/createMenu/?identif=test
     *        测试：   http://testapi.qudiandang.com/v1/weixin/createMenu/?identif=test
     *
     * </pre>
     *
     * @return string 返回JSON数据格式
     * <pre>
	 * 
     * </pre>
     */
	public function createMenuAction()
	{
		$newmenu = $this->_request->getPost("menu");

		if (empty($newmenu)) {
            YDLib::output(ErrnoStatus::STATUS_40094);
        }

		$options = array(
				'token'           => SUPPLIER_WEIXIN_TOKEN, 		//填写你设定的key
		        'encodingaeskey'  => SUPPLIER_WEIXIN_AES, 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
				'appid'			  => SUPPLIER_WEIXIN_APPID, 		//填写高级调用功能的app id
 				'appsecret'		  => SUPPLIER_WEIXIN_APP_SECRET 	//填写高级调用功能的密钥
			);
	
		$weObj = new Wechat($options);
		$artt = $weObj->getMenu();			
 	 	$result = $weObj->createMenu($newmenu);	
		YDLib::output_v2($weObj->errCode,$weObj->errMsg);
	}
	
	
	
	
	public function oauthAction()
    {
    	$redirectUrl = $this->_request->get("redirectUrl");
		if (empty($redirectUrl)) {
			header("Location: http://www.qudiandang.com/");
			exit;
		}
    	$options = array(
				'token'           => SUPPLIER_WEIXIN_TOKEN, 		//填写你设定的key
		        'encodingaeskey'  => SUPPLIER_WEIXIN_AES, 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
				'appid'			  => SUPPLIER_WEIXIN_APPID, 		//填写高级调用功能的app id
 				'appsecret'		  => SUPPLIER_WEIXIN_APP_SECRET 	//填写高级调用功能的密钥
			);
		$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
		$redirectUrl = !empty($redirectUrl) ? 
							$http_type."://".$_SERVER['HTTP_HOST'] . $redirectUrl : 
								$http_type."://".$_SERVER['HTTP_HOST']."/v1/weixin/code/?identif=".$_REQUEST['identif'];
		$weObj = new Wechat($options);
    	$url = $weObj->getOauthRedirect($redirectUrl);
        header("Location: ".$url);
		exit;
	}
	
	//微信支付授权
	public function oauthPayAction()
    {
    	$redirectUrl = $this->_request->get("redirectUrl");
		if (empty($redirectUrl)) {
			header("Location: http://www.qudiandang.com/");
			exit;
		}
//		if (__ENV__ == 'ONLINE') {//线上支付授权
	    	$options = array(
					'token'           => WEIXIN_TOKEN, 		//填写你设定的key
			        'encodingaeskey'  => WEIXIN_AES, 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
					'appid'			  => WEIXIN_APPID, 		//填写高级调用功能的app id
	 				'appsecret'		  => WEIXIN_APPSECRET 	//填写高级调用功能的密钥
			);			
//		} else {//测试支付授权，咱不可用
//		    $options = array(
//				'token'           => SUPPLIER_WEIXIN_TOKEN, 		//填写你设定的key
//		        'encodingaeskey'  => SUPPLIER_WEIXIN_AES, 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
//				'appid'			  => SUPPLIER_WEIXIN_APPID, 		//填写高级调用功能的app id
//				'appsecret'		  => SUPPLIER_WEIXIN_APP_SECRET 	//填写高级调用功能的密钥
//			);
//		}
		
		$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
		$redirectUrl = !empty($redirectUrl) ? 
							$http_type."://".$_SERVER['HTTP_HOST'] . $redirectUrl : 
								$http_type."://".$_SERVER['HTTP_HOST']."/v1/weixin/code/?identif=".$_REQUEST['identif'];
		$weObj = new Wechat($options);
    	$url = $weObj->getOauthRedirect($redirectUrl);
        header("Location: ".$url);
		exit;
	}
	
	
	/**
	 * 微信授权
	 *
	 * <pre>
	 *  url：安全域名 必填
	 * </pre>
	 *
	 * <pre>
	 *    调用方式：
	 *        正式：   http://api.qudiandang.com/v1/weixin/getJsSign
	 *        测试：   http://testapi.qudiandang.com/v1/weixin/getJsSign
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 * <pre>
	 *
	 * </pre>
	 */	
	public function getJsSignAction()
    {
		$url = $this->_request->get('url');	
		$url = urldecode($url);
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
	    $options = array(
	        'api_authorizer_token'  => $this->getToken($wechat_app_id,$refresh_token), 	//填写加密用的EncodingAESKey，如接口为明文模式可忽略
	        'appid'			  => WEIXIN_OPEN_APPID, 		//填写高级调用功能的app id
	        'appsecret'		  => WEIXIN_OPEN_APPSECRET 	//填写高级调用功能的密钥
	    );
		$weObj = new Wechat($options);
		
    	$res = $weObj->getOpenJsSign($url);
		if (!$res) {
			YDLib::output(ErrnoStatus::STATUS_60567);	
		}
		YDLib::output(ErrnoStatus::STATUS_SUCCESS,$res);	
	}

    /**
     * Sing微信授权
     *
     * <pre>
     *  url：安全域名 必填
     * </pre>
     *
     * <pre>
     *    调用方式：
     *        正式：   http://api.qudiandang.com/v1/weixin/getSingJsSign
     *        测试：   http://testapi.qudiandang.com/v1/weixin/getSingJsSign
     *
     * </pre>
     *
     * @return string 返回JSON数据格式
     * <pre>
     *
     * </pre>
     */
    public function getSingJsSignAction()
    {
        $url = $this->_request->get('url');
        $url = urldecode($url);
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
        $options = array(
            'api_authorizer_token'  => $this->getToken($wechat_app_id,$refresh_token), 	//填写加密用的EncodingAESKey，如接口为明文模式可忽略
            'appid'			  => $wechat_app_id, 		//填写高级调用功能的app id
            'appsecret'		  => $refresh_token 	//填写高级调用功能的密钥
        );
        $weObj = new Wechat($options);

        $res = $weObj->getOpenJsSign($url);
        if (!$res) {
            YDLib::output(ErrnoStatus::STATUS_60567);
        }
        YDLib::output(ErrnoStatus::STATUS_SUCCESS,$res);
    }

    /**
     * 代公众号发起网页授权
     *
     * <pre>
     *  redirect_url：授权成功后跳转的微商城url，.com之后的地址开头不要带'/',请先encodeURIComponent 不填默认首页
     *  调用后，前台通过url获取返回的user_id记录进前台缓存
     * </pre>
     *
     * <pre>
     *    调用方式：
     *        正式：   http://api.qudiandang.com/v1/weixin/oauth2
     *        测试：   http://testapi.qudiandang.com/v1/weixin/oauth2
     *
     * </pre>
     *
     * @return string 返回JSON数据格式
     * <pre>
     *
     * </pre>
     */
	public function oauth2Action()
    {
        $redirectUrl = $this->_request->get("redirectUrl");
        if (empty($redirectUrl)) {
            header("Location: http://www.qudiandang.com/");
            exit;
        }
        $options = array(
            'appid'			  => SUPPLIER_WEIXIN_APPID, 		//填写高级调用功能的app id
            'component_appid' => WEIXIN_OPEN_APPID
        );
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
        $redirectUrl = !empty($redirectUrl) ?
            $http_type."://".$_SERVER['HTTP_HOST'] . $redirectUrl :
            $http_type."://".$_SERVER['HTTP_HOST']."/v1/weixin/code/?identif=".$_REQUEST['identif'];
        $weObj = new Wechat($options);
        $url = $weObj->getOpenOauthRedirect($redirectUrl);
        header("Location: ".$url);
        exit;
    }


	
	/**
	 * 微信授权登录
	 *
	 * <pre>
	 *  redirect_url：授权成功后跳转的微商城url，.com之后的地址开头不要带'/',请先encodeURIComponent 不填默认首页
	 *  调用后，前台通过url获取返回的user_id记录进前台缓存
	 * </pre>
	 *
	 * <pre>
	 *    调用方式：
	 *        正式：   http://api.qudiandang.com/v1/weixin/wechatlogin
	 *        测试：   http://testapi.qudiandang.com/v1/weixin/wechatlogin
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 * <pre>
	 *
	 * </pre>
	 */
	public function wechatloginAction()
	{
	    $redirect_url = $this->_request->get('redirect_url');
	    $and = '?';
	    $all_url = FALSE;
	    if ($redirect_url) {
	         $redirect_url = urldecode($redirect_url);
	         
	         //是否是全域名
	         if (strpos($redirect_url, 'http') !== FALSE || strpos($redirect_url, 'https') !== FALSE) {
	             $all_url = TRUE;
	         }
	         
	         //是否带参数
	         if (strpos($redirect_url, '?')) {  
	             $and = '&';
	         }
	    }
	    
	    
	  
	    
	    
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
	    $options = array(
	        'appid'			  => $wechat_app_id, 		//填写高级调用功能的app id
            'component_appid' => WEIXIN_OPEN_APPID,
            'component_access_token' => $this->getAccessToken()
	    );
		//YDLib::testlog($options);
	    $weObj = new Wechat($options);
	    $wxinfo = [];
	    if (!empty($_COOKIE[self::WECHATOPENID]) && !empty($_COOKIE[self::WECHATASSEDTOKE])) {
	        $wxinfo['openid'] = $_COOKIE[self::WECHATOPENID];
	        $wxinfo['access_token'] = $_COOKIE[self::WECHATASSEDTOKE];
	    } else {
	        //获取微信授权
	        if (!isset($_GET['code'])){
	            $redirectUrl = "/v1/Weixin/wechatlogin/?identif=".$_REQUEST['identif']."&redirect_url=".$redirect_url;
	            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
	            $url = $http_type."://".$_SERVER['HTTP_HOST']."/v1/weixin/oauth2/?redirectUrl=".urlencode($redirectUrl)."&identif=".$_REQUEST['identif'];
	            YDLib::testlog($redirectUrl);
	            YDLib::testlog($url);
	            header("Location: ".$url);
	            exit;
	        }
	
	        //$wxinfo = $weObj->getOauthAccessToken();
            $wxinfo = $weObj->getOpenOauthAccessToken();
            YDLib::testLog('88888888888888');
            YDLib::testLog($wxinfo);
	        setcookie(self::WECHATOPENID,$wxinfo['openid'],0,"/",COOKIE_DOMAIN);
	        setcookie(self::WECHATASSEDTOKE,$wxinfo['access_token'],0,"/",COOKIE_DOMAIN);
	    }
		//YDLib::testlog('-----------------------------------------------');
        YDLib::testLog($wxinfo);
	    if ($wxinfo['openid']) {
	        //查数据库
	        $user = UserSupplierThridModel::getInfoByOtherId(['openid'=>$wxinfo['openid']]);
	        if ($user) {
	            $user_id = $user['user_id'];
				
				//获取绑定id
				$search = [];
				$search['user_id'] = $user['user_id'];
				$search['thrid_id'] = $user['id'];
				$search['type'] = CommonBase::USER_THRID_TYPE_1;
				$bindInfo = UserSupplierBindModel::getInfo($search);
				if (!$bindInfo) {
					$bind_id = UserSupplierBindModel::addData($search);
					if (!$bind_id) {
                        YDLib::testLog('6666666666');
    	                header("Location: ".SHOM_URL."mobile/login");
    	                exit;						
					}
				} else {
					$bind_id = $bindInfo['id'];
				}				

	            //登陆
				$token = (string)Publicb::getLoginToken($user_id);

	            if ($all_url === TRUE) {
	                header("Location: ".$redirect_url.$and."user_id=".$user_id."&token={$token}&bind_id=".$bind_id);
	            } else {
	                header("Location: ".M_URL.$redirect_url.$and."user_id=".$user_id."&token={$token}&bind_id=".$bind_id);
	            }
	            exit;
	        } else {
	            $userinfo = $weObj->getOauthUserinfo($wxinfo['access_token'],$wxinfo['openid']);
	            if ($userinfo) {
    	            //存数据库
    	            $user = [];
    	            $user['user_img']               = Publicb::getImage($userinfo['headimgurl']);
    	            $user['name']                  = $userinfo['nickname'];
    	            $user['sex']                   = $userinfo['sex'];
    	            if ($userinfo['country'] == 'CN') {
    	                $province = AreaModel::getProvinceByPinyin(CommonBase::getBigTosmall($userinfo['province']));
    	                $user['province_id']       = $province['area_id'];
    	                $city = AreaModel::getProvinceByPinyin(CommonBase::getBigTosmall($userinfo['city']),$user['province_id']);
    	                $user['city_id']           = $city['area_id'];
    	            }
    	            $last_id = UserModel::addUser($user);
    	            if (!$last_id) {
                        YDLib::testLog('555555555');
    	                header("Location: ".M_URL."mobile/login");
    	                exit;
    	            }
    	            //添加第三方信息
    	            $thrid = [];
    	            $thrid['user_id']              = $last_id;
    	            $thrid['openid']               = $userinfo['openid'];
    	            $thrid['nickname']             = $userinfo['nickname'];
    	            $thrid['sex']                  = $userinfo['sex'];
    	            $thrid['head_img_url']         = $user['user_img'];
    	            $thrid['source_file']          = json_encode($userinfo);
    	            if ($userinfo['country'] == 'CN') {
    	                $thrid['province_name']    = $province['area_name'];
    	                $thrid['city_name']        = $city['area_name'];
    	            }
    	            if (isset($userinfo['unionid'])) {
    	                $thrid['unionid']          = $userinfo['unionid'];
    	            }
    	            $thrid_add = UserSupplierThridModel::addUser($thrid);
    	            if ($thrid_add == false) {
    	                $del = UserModel::deleteByID($last_id);
                        YDLib::testLog('44444444444');
    	                header("Location: ".M_URL."mobile/login");
    	                exit;
    	            }
					
					//获取绑定id
					$search = [];
					$search['user_id'] = $last_id;
					$search['thrid_id'] = $thrid_add;
					$search['type'] = CommonBase::USER_THRID_TYPE_1;
					$bind_id = UserSupplierBindModel::addData($search);
					if (!$bind_id) {
                        YDLib::testLog('3333333333');
    	                header("Location: ".M_URL."mobile/login");
    	                exit;						
					}				

                    //登陆
                    $token = (string)Publicb::getLoginToken($last_id);
    	            
    	            if ($all_url === TRUE) {
    	                header("Location: ".$redirect_url.$and."user_id=".$last_id."&token={$token}&bind_id=".$bind_id);
    	            } else {
    	                header("Location: ".M_URL.$redirect_url.$and."user_id=".$last_id."&token={$token}&bind_id=".$bind_id);
    	            }
	                exit;
	            }
                YDLib::testLog('22222222');
	            header("Location: ".M_URL."mobile/login");
	            exit;
	        }
	    }
	    YDLib::testLog('11111111');
	    header("Location: ".M_URL."mobile/login");
	    exit;
	
	}
	
	
	
	
	public function getOpenid( $authorizer_appid )
	{
		$openid = session($authorizer_appid.'openid');
		if(!$openid && I('get.code') ){
			//取得对应openid
			$url = 'https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=' . $authorizer_appid . '&code=' . I( 'get.code' ) . '&grant_type=authorization_code&component_appid=' . WEIXIN_APPID . '&component_access_token=' . $this->getComToken();
			$data = get( $url );
			$openid = $data['openid'];
			session( $authorizer_appid.'openid', $openid );
		}
		if(!$openid){
			//跳转授权获取openid
			$redirect_uri = getUrl();
			$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $authorizer_appid . "&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=STATE&component_appid=" . WEIXIN_APPID . "#wechat_redirect";
			redirect( $url );
		}
		return $openid;
	}


    /**
     * 获得微信三方待认证token
     * @param $authorizer_appid
     * @param $refresh_token
     * @return mixed
     */
	private function getToken( $authorizer_appid ,$refresh_token)
	{

        $mem = YDLib::getMem('memcache');
        $authorizer_access_token = $mem->get( 'authorizer_access_token' );
        if ( !$authorizer_access_token ) {
            $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token='.$this->getComToken();

            $param['component_appid'] = WEIXIN_OPEN_APPID;
            $param['authorizer_appid'] = $authorizer_appid;
            $param['authorizer_refresh_token'] = $refresh_token;
            $param = json_encode($param);
            $data = YDLib::curlPostRequsetByWeixin( $url, $param );
            $authorizer_access_token = $data['authorizer_access_token'];
            $authorizer_refresh_token = $data['authorizer_refresh_token'];
            if ($authorizer_access_token) {
                $mem->set( 'authorizer_access_token', $authorizer_access_token,6000 );
            }
            if ($authorizer_refresh_token) {//更新刷新令牌
                $up = WexinAuthorizationModel::updateBySupplierID(['authorizer_refresh_token'=>$authorizer_refresh_token]);
            }
        }

        return $authorizer_access_token;

	}

    /**
     *  获得三方access_token
     * @param $wechat_app_id
     * @param $refresh_token
     * @return mixed
     */
    private function getAccessToken() {
        $mem = YDLib::getMem('memcache');
        $component_access_token = $mem->get( 'component_access_token_'.SUPPLIER_ID );
        if ( !$component_access_token ) {
            $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';

            $param['component_appid'] = WEIXIN_OPEN_APPID;
            $param['component_appsecret'] = WEIXIN_OPEN_APPSECRET;
            $param['component_verify_ticket'] = $mem->get('wechat_component_verify_ticket');
            $param = json_encode($param);
            $data = YDLib::curlPostRequsetByWeixin( $url, $param );
            $component_access_token = $data['component_access_token'];
            if ($component_access_token) {
                $mem->set( 'component_access_token_'.SUPPLIER_ID, $component_access_token,7200 );
            }
        }

        return $component_access_token;
    }
	
	/**
	 * 获得授权token 
	 */
	private function getComToken()
	{
		$mem = YDLib::getMem('memcache');
		$component_access_token = $mem->get( 'component_access_token' );
		if ( !$component_access_token ) {
			
			$param = array(
				'component_appid'			=> WEIXIN_OPEN_APPID,
				'component_appsecret'		=> WEIXIN_OPEN_APPSECRET,
				'component_verify_ticket'	=> $mem->get('wechat_component_verify_ticket')
			);
			$param = json_encode($param);
			$url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
			$data = YDLib::curlPostRequset( $url, $param );
			
			$component_access_token = $data['component_access_token'];
			$mem->set('component_access_token',$component_access_token,7000);
		}

		return $component_access_token;	
	}

	/**
	 * 获得APP授权信息
	 */
	public function getAppInfo( $authorizer_appid )
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token='.$this->getComToken();
		$param = array(
			'component_appid'	=> WEIXIN_OPEN_APPID,
			'authorizer_appid'	=> $authorizer_appid
		);
		$param = json_encode($param);
		$res = YDLib::curlPostRequset( $url, $param );
		return $res;
	}
	
	/**
	 * 获得三方授权
	 */
	private function auth()
	{
		$authorization_code =  $_GET['auth_code'];
        if( !$authorization_code ){
            $url = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid='.WEIXIN_OPEN_APPID.'&pre_auth_code='.$this->getPreAuthCode() .'&redirect_uri='.$this->getUrl();

            exit( "<script>window.location.href='".$url."';</script>");
        }
        //获取授权信息
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token='.$this->getComToken();
        $param = array(
            'component_appid' => WEIXIN_OPEN_APPID,
            'authorization_code' => $authorization_code
        );
		$param = json_encode($param);
        $res = YDLib::curlPostRequset($url,$param);
        if(!$res['authorization_info'] ){
            exit( '出错' );
        }
        $authorizer_appid = $res['authorization_info']['authorizer_appid'];
        var_dump( $this->getAppInfo( $authorizer_appid ) );
        exit('授权成功');
	}
	
	/**
	 * 获得三方登录令牌
	 */
	public function setTicketAction() 
	{


        $timestamp = $_REQUEST['timestamp'];
        $nonce = $_REQUEST['nonce'];
        $msg_sign = $_REQUEST['msg_signature'];

        $encryptMsg = file_get_contents ( 'php://input' ); 
        YDLib::testlog("setTicketAction call encryptMsg: ". $encryptMsg );
	
        $pc = new WXBizMsgCrypt (WEIXIN_OPEN_TOKEN, WEIXIN_OPEN_KEY, WEIXIN_OPEN_APPID );

		$xml_tree = new DOMDocument();
		$xml_tree->loadXML($encryptMsg);
		$array_e = $xml_tree->getElementsByTagName('Encrypt');
		$encrypt = $array_e->item(0)->nodeValue;
		
        // $postArr = YDLib::xml2array ( $encryptMsg );
	
        $format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";  
        $from_xml = sprintf ( $format, $encrypt );  
		YDLib::testlog("setTicketAction call from_xml: ". $from_xml );
        $msg = '';
	
        $errCode = $pc->DecryptMsg ( $msg_sign, $timestamp, $nonce, $from_xml, $msg );
		YDLib::testlog("setTicketAction call errCode: ". $errCode );
	
       	if ($errCode == 0) {  
            $param = YDLib::xml2array ( $msg );  
			
			$xml_tree = new DOMDocument();
			$xml_tree->loadXML($msg);
			$array_e = $xml_tree->getElementsByTagName('InfoType');
			$array_c = $xml_tree->getElementsByTagName('ComponentVerifyTicket');
			$InfoType = $array_e->item(0)->nodeValue;
		
			YDLib::testlog("setTicketAction call back: ". $InfoType );
            switch ($InfoType) {  
                case 'component_verify_ticket' :

                $component_verify_ticket = $array_c->item(0)->nodeValue;;
                YDLib::testlog("component_verify_ticket call back: ". $component_verify_ticket );
				$mem = YDLib::getMem('memcache');
                $mem->set('wechat_component_verify_ticket',$component_verify_ticket); 

                break;  

                case 'unauthorized' : 

                $status = 2;  

                break;  
                case 'authorized' :

                $status = 1;  

                break;  
                case 'updateauthorized' :
                break;  
            }  
        }  

        exit('success');

    }
	
	/**
     * 获得三方授权
     *
     * <pre>
     *   GET参数
     *     identif : 供应商标识
     * </pre>
     *
     * <pre>
     *    调用方式：
     *        正式：   https://api.qudiandang.com/v1/weixin/threeAuth/?identif=test
     *        测试：   https://testapi.qudiandang.com/v1/weixin/threeAuth/?identif=test
     *
     * </pre>
     *
     * @return string 返回XML数据格式
     * <pre>
	 * 
     * </pre>
     */
	public function threeAuthAction()
	{
 		$this->auth();
    }
	
	/**
	 * 获得预授权码
	 */
    private function getPreAuthCode()
    {
        $mem = YDLib::getMem('memcache');
        $pre_auth_code = $mem->get('pre_auth_code');
        if (!$pre_auth_code) {
            $url = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=' .  $this->getComToken();
    
    	    $param['component_appid'] = WEIXIN_OPEN_APPID;
    		$param = json_encode($param);
            $data = YDLib::curlPostRequset( $url, $param );
            $pre_auth_code = $data['pre_auth_code'];
    
            if($pre_auth_code){
                $mem->set( 'pre_auth_code', $pre_auth_code, 500);
            }    
        }
        return $pre_auth_code;
    }
	
	/**
	 * 三方授权回调地址
	 */
	private function getUrl()
	{
		return urlencode($_REQUEST['callurl']);
	}
	
	
	public function callback($appid,$identif)
	{
		
	}
	
	/**
     * 获取授权openId
     *
     * <pre>
     *   GET参数
     *     identif : 供应商标识
     *     identif : 供应商标识
     * </pre>
     *
     * <pre>
     *    调用方式：
     *        正式：   https://api.qudiandang.com/v1/weixin/getopen/?identif=test&code=***
     *        测试：   https://testapi.qudiandang.com/v1/weixin/getopen/?identif=test&code=***
     *
     * </pre>
     *
     * @return string 返回json数据格式
     * <pre>
	 * 
     * </pre>
     */	
	public function getopenAction()
	{
		$code = $this->_request->get("code");
	    $appid = MINI_WEIXIN_APPID;
	    $appsecret = MINI_WEIXIN_APPSECRET;
		$url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$appsecret&js_code=".$code."&grant_type=authorization_code";
	    $weixin =  file_get_contents($url);//通过code换取网页授权access_token
	    $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
	    $array = get_object_vars($jsondecode);//转换成数组
	    YDLib::output(ErrnoStatus::STATUS_SUCCESS,$array);	
											
	}
	
	/**
     * 获得分享js
     *
     * <pre>
     *   GET参数
     *     identif : 供应商标识
	 *     url : 分享地址链接
     * </pre>
     *
     * <pre>
     *    调用方式：
     *        正式：   https://api.qudiandang.com/v1/weixin/jsSignPackage/?identif=test
     *        测试：   https://testapi.qudiandang.com/v1/weixin/jsSignPackage/?identif=test
     *
     * </pre>
     *
     * @return string 返回XML数据格式
     * <pre>
	 * 
     * </pre>
     */
	public function jsSignPackageAction()
	{
		$url = $this->_request->get('url');			
		$jssdk = new JSSDK(SUPPLIER_WEIXIN_APPID,SUPPLIER_WEIXIN_APP_SECRET);
		$signPackage = $jssdk->getSignPackage($url);	
		YDLib::output(ErrnoStatus::STATUS_SUCCESS,$signPackage);	
	}
	
	
	
	
	
	
	/*
	 *功能：php实现下载远程图片保存到本地
	 *参数：文件url,保存文件目录,保存文件名称，使用的下载方式
	 *当保存文件名称为空时则使用远程文件原来的名称
	 */
	public static function getImageAction()
	{
	    $type='1';
	    $j=106;
	    $all = [];
	    for ($i=01;$i<$j;$i++) {
	        $all["'".$i."'"] = "'1',";
	    }
	    print_r( $all);
	}
	
}
