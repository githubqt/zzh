<?php
namespace Weixin;
use Custom\YDLib;
use Weixin\Wechatmsg;
use Weixin\WXBizMsgCrypt;
use Weixin\JSSDK; 
use Supplier\SupplierModel;
use Common\CommonBase;
use BaseController;

class WeixinFunction
{
    
    /**
     * 模板消息 获取设置的所属行业
     * @param int $access_token  
     * @return boolean|array
     */
    public static function GetTMIndustry() {
        $options = array(
            'token'           => '', 		//填写你设定的key
            'encodingaeskey'  => '', 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
            'appid'			  => '', 		//填写高级调用功能的app id
            'appsecret'		  => '' 	//填写高级调用功能的密钥
        );
        $Wechat = new Wechatmsg($options);
        //获取微信授权信息及access_token
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
        $access_token = self::getAccessToken($weixinInfo['supplier_id'],$wechat_app_id, $refresh_token);
    
        //默认行业为IT科技，互联网/电子商务
        $list = $Wechat->getIndustry($access_token);
        if (!$list || !empty($list['errcode'])) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '获取行业信息失败';
            return  $jsonData;
            exit;
        }
    
        $jsonData['code'] = '200';
        $jsonData['msg'] = '获取行业信息成功';
        $jsonData['list'] = $list;
        return  $jsonData;
        exit;
    }
    
    
    
    /**
     * 模板消息 设置所属行业
     * @param int $id1  公众号模板消息所属行业编号，参看官方开发文档 行业代码
     * @param int $id2  同$id1。但如果只有一个行业，此参数可省略
     * @return boolean|array
     */
    public static function setTMIndustry() {
        $options = array(
            'token'           => '', 		//填写你设定的key
            'encodingaeskey'  => '', 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
            'appid'			  => '', 		//填写高级调用功能的app id
            'appsecret'		  => '' 	//填写高级调用功能的密钥
        );
        $Wechat = new Wechatmsg($options);
        //获取微信授权信息及access_token
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
        $access_token = self::getAccessToken($weixinInfo['supplier_id'],$wechat_app_id, $refresh_token);
        
        //默认行业为IT科技，互联网/电子商务
        $add = $Wechat->setTMIndustry($access_token, '1');
        if (!$add || !empty($add['errcode'])) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '添加行业信息失败';
            return  $jsonData;
            exit;
        }
        
        $jsonData['code'] = '200';
        $jsonData['msg'] = '添加行业信息成功';
        return  $jsonData;
        exit;
    }
    
    
    /**
     * 模板消息 添加消息模板
     * 成功返回消息模板的调用id
     * @param string $tpl_id 模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     * @return boolean|string
     */
    public static function addTemplateMessage($tpl_id) {
        $options = array(
            'token'           => '', 		//填写你设定的key
            'encodingaeskey'  => '', 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
            'appid'			  => '', 		//填写高级调用功能的app id
            'appsecret'		  => '' 	//填写高级调用功能的密钥
        );
        $Wechat = new Wechatmsg($options);
        //获取微信授权信息及access_token
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
        $access_token = self::getAccessToken($weixinInfo['supplier_id'],$wechat_app_id, $refresh_token);
    
        //添加消息模板
        $model_id = $Wechat->addTemplateMessage($access_token, $tpl_id);
        if (!$model_id || !empty($model_id['errcode'])) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '添加消息模板失败';
            return  $jsonData;
            exit;
        }
    
        $jsonData['code'] = '200';
        $jsonData['msg'] = '添加消息模板成功';
        $jsonData['tpl_id'] = $model_id;
        return  $jsonData;
        exit;
    }
    
    
    
    /**
     * 模板消息 获取所有消息模板
     * 成功返回本公众号的消息模板
     * @return boolean|string
     */
    public static function getAllPrivateTemplate() {
        $options = array(
            'token'           => '', 		//填写你设定的key
            'encodingaeskey'  => '', 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
            'appid'			  => '', 		//填写高级调用功能的app id
            'appsecret'		  => '' 	//填写高级调用功能的密钥
        );
        $Wechat = new Wechatmsg($options);
        //获取微信授权信息及access_token
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
        $access_token = self::getAccessToken($weixinInfo['supplier_id'],$wechat_app_id, $refresh_token);
    
        //获取消息模板列表
        $model_list = $Wechat->getAllPrivateTemplate($access_token);
        if (!$model_list || !empty($model_list['errcode'])) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '获取模板消息列表失败';
            return  $jsonData;
            exit;
        }
    
        $jsonData['code'] = '200';
        $jsonData['msg'] = '获取模板消息列表成功';
        $jsonData['model_list'] = $model_list;
        return  $jsonData;
        exit;
    }
    
    
    /**
     * 发送模板消息
     * @param array $data 消息结构
     * {
        "touser":"OPENID",
        "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
        "url":"http://weixin.qq.com/download",
        "topcolor":"#FF0000",
        "data":{
            "参数名1": {
                "value":"参数",
                "color":"#173177"	 //参数颜色
            },
            "Date":{
                "value":"06月07日 19时24分",
                "color":"#173177"
            },
            "CardNumber":{
                "value":"0426",
                "color":"#173177"
            },
            "Type":{
                "value":"消费",
                "color":"#173177"
            }
        }
    }
     * @return boolean|array
     */
    public static function sendTemplateMessage($data) {
        $options = array(
            'token'           => '', 		//填写你设定的key
            'encodingaeskey'  => '', 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
            'appid'			  => '', 		//填写高级调用功能的app id
            'appsecret'		  => '' 	//填写高级调用功能的密钥
        );
        $Wechat = new Wechatmsg($options);
        //获取微信授权信息及access_token
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
        $access_token = self::getAccessToken($weixinInfo['supplier_id'],$wechat_app_id, $refresh_token);
    
        //发送模板消息
        $fire_msg = $Wechat->sendTemplateMessage($access_token, $data);
        if (!$fire_msg || !empty($fire_msg['errcode'])) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '发送模板消息失败';
            return  $jsonData;
            exit;
        }
    
        $jsonData['code'] = '200';
        $jsonData['msg'] = '发送模板消息成功';
        return  $jsonData;
        exit;
    }
    
    
    
    
    
    
    public static function getOpenid( $authorizer_appid )
    {
        $openid = session($authorizer_appid.'openid');
        if(!$openid && I('get.code') ){
            //取得对应openid
            $url = 'https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=' . $authorizer_appid . '&code=' . I( 'get.code' ) . '&grant_type=authorization_code&component_appid=' . WEIXIN_APPID . '&component_access_token=' . self::getComToken();
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
     * 获得预授权码
     */
    public static function getPreAuthCode()
    {
        $mem = YDLib::getMem('memcache');
        $pre_auth_code = $mem->get( 'pre_auth_code' );
        if ( !$pre_auth_code ) {
            $url = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=' .  self::getComToken();
    
            $param['component_appid'] = WEIXIN_OPEN_APPID;
            $param = json_encode($param);
            $data = YDLib::curlPostRequsetByWeixin( $url, $param );
            $pre_auth_code = $data['pre_auth_code'];
    
            if($pre_auth_code){
                $mem->set( 'pre_auth_code', $pre_auth_code,500 );
            }
        }
    
        return $pre_auth_code;
    }
    


    /**
     * 获得APP授权信息
     */
    public static function getAppInfo( $authorizer_appid )
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token='.self::getComToken();
        $param = array(
            'component_appid'	=> WEIXIN_OPEN_APPID,
            'authorizer_appid'	=> $authorizer_appid
        );
        $param = json_encode($param);
        $res = YDLib::curlPostRequsetByWeixin( $url, $param );
        return $res;
    }
    
    
    
    
    /**
     * 获得授权token
     */
    public static function getComToken()
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
            $data = YDLib::curlPostRequsetByWeixin( $url, $param );
            	
            $component_access_token = $data['component_access_token'];
            $mem->set('component_access_token',$component_access_token,5000);
        }
    
        return $component_access_token;
    }
    
    public static function getAccessToken($supplier_id,$wechat_app_id,$refresh_token) {
        $mem = YDLib::getMem('memcache');
        $authorizer_access_token = $mem->get( 'authorizer_access_token_'.$supplier_id );
        if ( !$authorizer_access_token ) {
            $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token='.self::getComToken();
             
            $param['component_appid'] = WEIXIN_OPEN_APPID;
            $param['authorizer_appid'] = $wechat_app_id;
            $param['authorizer_refresh_token'] = $refresh_token;
            $param = json_encode($param);
            $data = YDLib::curlPostRequsetByWeixin( $url, $param );
            $authorizer_access_token = $data['authorizer_access_token'];
            $authorizer_refresh_token = $data['authorizer_refresh_token'];
            if ($authorizer_access_token) {
                $mem->set( 'authorizer_access_token_'.$supplier_id, $authorizer_access_token,6000 );
            }
            if ($authorizer_refresh_token) {//更新刷新令牌
                $up = WexinAuthorizationModel::updateBySupplierID(['authorizer_refresh_token'=>$authorizer_refresh_token]);
            }
        }
         
        return $authorizer_access_token;
    }
}