<?php
namespace Weixin;
use Custom\YDLib;
use Weixin\Wechat;
use Weixin\WXBizMsgCrypt;
use Weixin\JSSDK;
use Supplier\SupplierModel;
use Common\CommonBase;
use Weixin\WexinGraphicMaterialModel;
use Weixin\WexinMaterialModel;
use Upload\img;
use CURLFile;
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
        $Wechat = new Wechat($options);
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
        $Wechat = new Wechat($options);
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
        $Wechat = new Wechat($options);
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
        $Wechat = new Wechat($options);
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
        $Wechat = new Wechat($options);
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
    
    
    
    //微信图片上传
    public static function uploadmsg($detail,$url)
    {
       
        $options = array(
            'token'           => '', 		//填写你设定的key
            'encodingaeskey'  => '', 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
            'appid'			  => '', 		//填写高级调用功能的app id
            'appsecret'		  => '' 	//填写高级调用功能的密钥
        );
        $Wechat = new Wechat($options);
        //获取微信授权信息及access_token
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
        $access_token = self::getAccessToken($weixinInfo['supplier_id'],$wechat_app_id, $refresh_token);
    
         
        $res['data'] = substr($url,strpos($url,'com/')+4);
         
        /*提交到微信服务器*/
        $data['media'] = new CURLFile(realpath(RESOURCE_FILE.$res['data']));
        if ($detail['is_con'] == '1') {
    
            $resp = $Wechat->uploadImg($access_token,$data);
        } elseif ($detail['is_con'] == '2') {
            $is_video = false;
            $video_info = array();
            //下一版本开发，文章里边添加视频信息
            if($detail['type'] == 'video'){
                $is_video = true;
                $video_info['title']        = '视频标题';
                $video_info['introduction'] = '视频描述';
            }
             
            $resp = $Wechat->uploadForeverMedia($access_token,$data, $detail['type'],$is_video=false,$video_info=array());
        }
        if (!$resp) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '上传微信出错';
            return  $jsonData;
            exit;
        }
         
        /*发布成功回填本地数据库*/
        $detail['name']       = date("YmdHis");
        $detail['location']   = $url;//全地址url
        $detail['is_publish'] = '2';
        $detail['publish_at'] = date("Y-m-d H:i:s");
        $detail['media_id'] = isset($resp['media_id'])?$resp['media_id']:'';
        $detail['url'] = isset($resp['url'])?$resp['url']:'';
        $resq = WexinMaterialModel::addData($detail);
        if (!$resq) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '保存失败';
            return $jsonData;
            exit;
        }
    
        /*返回midea_id/url*/
        $jsonData['code'] = '200';
        $jsonData['msg'] = '上传成功';
        $jsonData['data'] = array(
            'media_id' => $detail['media_id'],
            'url' => $detail['url'],
            'location' => $detail['location'],
        );
        return $jsonData;
        exit;
    }
    
    
    
    //永久素材上传
    public static function uploadForeverArticles($id)
    {
        $msg_info = WexinGraphicMaterialModel::getInfoByID($id);
        if ($msg_info) {
            $articles                               = [];
            $articles['0']['title']                 = $msg_info['title'];
            $articles['0']['thumb_media_id']        = $msg_info['thumb_media_id'];
            $articles['0']['author']                = $msg_info['author'];
            $articles['0']['digest']                = $msg_info['digest'];
            $articles['0']['show_cover_pic']        = $msg_info['show_cover_pic'] == 2?1:0;
            $articles['0']['content']               = $msg_info['wechat_content'];
            $articles['0']['content_source_url']    = $msg_info['content_source_url'];
            
            if ($msg_info['is_more'] == '2') {
                $msg_child = WexinGraphicMaterialModel::getInfoByGraphicID($id);
                if ($msg_child) {
                    foreach ($msg_child as $k=>$v) {
                        $child                          = [];
                        $child['title']                 = $v['title'];
                        $child['thumb_media_id']        = $v['thumb_media_id'];
                        $child['author']                = $v['author'];
                        $child['digest']                = '';
                        $child['show_cover_pic']        = $v['show_cover_pic'] == 2?1:0;
                        $child['content']               = $v['wechat_content'];
                        $child['content_source_url']    = $v['content_source_url'];
                        
                        $articles[$k+1]                 = $child;
                    }
                }
            }
            $data['articles'] = $articles;
        }
        
        $options = array(
            'token'           => '', 		//填写你设定的key
            'encodingaeskey'  => '', 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
            'appid'			  => '', 		//填写高级调用功能的app id
            'appsecret'		  => '' 	//填写高级调用功能的密钥
        );
        $Wechat = new Wechat($options);
        //获取微信授权信息及access_token
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
        $access_token = self::getAccessToken($weixinInfo['supplier_id'],$wechat_app_id, $refresh_token);
    
        $resp = $Wechat->uploadForeverArticles($access_token,$data);
    
        if ($resp['errcode']) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '发布至微信出错';
            return  $jsonData;
            exit;
        }
        //更新media_id
        if ($resp['media_id']) {
           $up = WexinGraphicMaterialModel::updateByID(['media_id'=>$resp['media_id']],$id);
           if (!$up) {
               $jsonData['code'] = '500';
               $jsonData['msg'] = '更新素材id失败';
               return  $jsonData;
               exit;
           }
        }
       
        $jsonData['code'] = '200';
        $jsonData['msg'] = '发布至微信成功';
        $jsonData['media_id'] = $resp['media_id'];
        return $jsonData;
        exit;
    }
    
    
    
    //永久素材上传（多图文编辑）
    public static function uploadForeverArticlesJust($id)
    {
        $msg_info = WexinGraphicMaterialModel::getInfoByID($id);
        if ($msg_info) {
            $articles                               = [];
            $articles['0']['title']                 = $msg_info['title'];
            $articles['0']['thumb_media_id']        = $msg_info['thumb_media_id'];
            $articles['0']['author']                = $msg_info['author'];
            $articles['0']['digest']                = $msg_info['digest'];
            $articles['0']['show_cover_pic']        = $msg_info['show_cover_pic'] == 2?1:0;
            $articles['0']['content']               = $msg_info['wechat_content'];
            $articles['0']['content_source_url']    = $msg_info['content_source_url'];
    
            if ($msg_info['is_more'] == '2') {
                $msg_child = WexinGraphicMaterialModel::getInfoByGraphicID($id);
                if ($msg_child) {
                    foreach ($msg_child as $k=>$v) {
                        $child                          = [];
                        $child['title']                 = $v['title'];
                        $child['thumb_media_id']        = $v['thumb_media_id'];
                        $child['author']                = $v['author'];
                        $child['digest']                = '';
                        $child['show_cover_pic']        = $v['show_cover_pic'] == 2?1:0;
                        $child['content']               = $v['wechat_content'];
                        $child['content_source_url']    = $v['content_source_url'];
    
                        $articles[$k+1]                 = $child;
                    }
                }
            }
            $data['articles'] = $articles;
        }
    
        $options = array(
            'token'           => '', 		//填写你设定的key
            'encodingaeskey'  => '', 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
            'appid'			  => '', 		//填写高级调用功能的app id
            'appsecret'		  => '' 	//填写高级调用功能的密钥
        );
        $Wechat = new Wechat($options);
        //获取微信授权信息及access_token
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
        $access_token = self::getAccessToken($weixinInfo['supplier_id'],$wechat_app_id, $refresh_token);
    
        $resp = $Wechat->uploadForeverArticles($access_token,$data);
    
        if ($resp['errcode']) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '发布至微信出错';
            return  $jsonData;
            exit;
        }
       
        $jsonData['code'] = '200';
        $jsonData['msg'] = '发布至微信成功';
        $jsonData['media_id'] = $resp['media_id'];
        return $jsonData;
        exit;
    }
    
    
    
    //永久素材修改
    public static function updateForeverArticles($id)
    {
        $msg_info = WexinGraphicMaterialModel::getInfoByID($id);
        if ($msg_info) {
            $data['media_id']                           = $msg_info['media_id'];
            $data['index']                              = '0';
            $data['articles']['title']                  = $msg_info['title'];
            $data['articles']['thumb_media_id']         = $msg_info['thumb_media_id'];
            $data['articles']['author']                 = $msg_info['author'];
            $data['articles']['digest']                 = $msg_info['digest'];
            $data['articles']['show_cover_pic']         = $msg_info['show_cover_pic'] == 2?1:0;
            $data['articles']['content']                = $msg_info['wechat_content'];
            $data['articles']['content_source_url']     = $msg_info['content_source_url'];
    
        }
    
        $options = array(
            'token'           => '', 		//填写你设定的key
            'encodingaeskey'  => '', 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
            'appid'			  => '', 		//填写高级调用功能的app id
            'appsecret'		  => '' 	//填写高级调用功能的密钥
        );
        $Wechat = new Wechat($options);
        //获取微信授权信息及access_token
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
        $access_token = self::getAccessToken($weixinInfo['supplier_id'],$weixinInfo['supplier_id'],$wechat_app_id, $refresh_token);
    
        $resp = $Wechat->updateForeverArticles($access_token,$msg_info['media_id'],$data);
    
        if ($resp['errmsg'] != 'ok') {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '发布至微信出错';
            return  $jsonData;
            exit;
        }
        
         
        $jsonData['code'] = '200';
        $jsonData['msg'] = '发布至微信成功';
        $jsonData['media_id'] = $resp['media_id'];
        return $jsonData;
        exit;
    }
    
    
    
    //删除永久图文素材
    public static function delForeverMedia($media_id)
    {
        $options = array(
            'token'           => '', 		//填写你设定的key
            'encodingaeskey'  => '', 			//填写加密用的EncodingAESKey，如接口为明文模式可忽略
            'appid'			  => '', 		//填写高级调用功能的app id
            'appsecret'		  => '' 	//填写高级调用功能的密钥
        );
        $Wechat = new Wechat($options);
        //获取微信授权信息及access_token
        $weixinInfo = WexinAuthorizationModel::getOneBySupplierId();
        $wechat_app_id = $weixinInfo['authorizer_appid'];
        $refresh_token = $weixinInfo['authorizer_refresh_token'];
        $access_token = self::getAccessToken($weixinInfo['supplier_id'],$wechat_app_id, $refresh_token);
        
        $resp = $Wechat->delForeverMedia($access_token,$media_id);
        
        return $resp;
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