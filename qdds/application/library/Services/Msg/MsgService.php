<?php
// +----------------------------------------------------------------------
// | 发送消息操作类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：黄献国
// +----------------------------------------------------------------------
// | Author: 黄献国  Date:2018/10/30 Time:10:40
// +----------------------------------------------------------------------


namespace Services\Msg;


use Sms\SmsSetModel;
use Sms\SmsModelModel;
use Admin\AdminModel;
use Supplier\SupplierModel;
use Custom\YDLib;
use User\UserSupplierThridModel;
use Weixin\WexinUserMsgContentModel;
use Weixin\WexinGraphicMaterialModel;
use Weixin\WexinUserMsgModel;
use Weixin\Wechat;
use Weixin\WexinAuthorizationModel;
use Weixin\WeixinFunction;
use Services\BaseService;

class MsgService extends BaseService
{
    
    /**
     * 消息设置发送消息
     * $remind_type  消息类型
     * ,$mobile  手机号
     * ,$user_id  用户id
     *  $data  短信匹配的内容数组一个params另一个weixin_params
     *  $data[
     *      'params' => [
     *          //短信匹配的内容
     *      ],
     *      'weixin_params'=>[
     *          //微信模板内容
     *      ]
     *  ]
     *  
     */
    public static function fireMsg($remind_type,$mobile,$user_id,$data = [])
    {
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        $suppplier_detail = SupplierModel::getInfoByID($adminInfo['supplier_id']);
        
       //获取该类型的设置属性
       $sms_set = SmsSetModel::getInfoByType($remind_type);
       if ($sms_set) {
           //短信
           if ($sms_set['message_remind'] == '2') {
               $url = API_URL.'v1/common/commonSms';
               $postdata = [];
               $postdata['identif'] = $suppplier_detail['domain'];
               $postdata['mobile'] = $mobile;
               $postdata['model_id'] = $sms_set['sms_model_id'];
               if ($data['params']) {
                   $postdata['params'] = json_encode($data['params']);
               }
               $res = YDLib::curlPostRequset($url,$postdata);
           }
           
           //微信
           $userThrid = UserSupplierThridModel::getInfoByUserId($user_id);
           if ($userThrid && $userThrid['openid']) {
               // 通过模板ID查询模板内容
               $model_info = SmsModelModel::getInfoByID ($sms_set['sms_model_id']);
               
               //微信粉丝消息
               if ($sms_set['weixin_message_remind'] == '2') {
                   $open_id = $userThrid['openid'];
                   $content = $model_info ['content'];
                   if ($data['params']) {
                       foreach ($data['params'] as $key => $value) {
                           $content = str_replace ('{' . ($key + 1) . '}', $value, $content);
                       }
                   }
                   
                   $info ['msg_content'] = '【' . $suppplier_detail['sms_name'] . '】' . $content;
                   
                   self::kfMsgFire($info, $open_id);
               }
               
               //微信模板消息
               if ($sms_set['weixin_template_remind'] == '2') {
                   $sms_set['old_model_id'] = $model_info['wechat_model_id'];
                   $weixin_params = $data['weixin_params']?$data['weixin_params']:[];
                   
                   $shop_name = $suppplier_detail['shop_name']?$suppplier_detail['shop_name']:'本店铺';
                   $weixin_params['data']['remark'] = '感谢您对'.$shop_name.'的支持！';
                   
                   $fire = self::fireWechatModelMsg($sms_set,$weixin_params,$userThrid['openid']);
               }
           }
           
           
           
       }
       
       
    }

    //客服发送消息接口
    public static function kfMsgFire($content,$touser_id) 
    {
        if ($content) {
            
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
            $access_token = WeixinFunction::getAccessToken($weixinInfo['supplier_id'],$wechat_app_id, $refresh_token);
            
            
            //是否发送过消息查询weixin_user_msg表
            $user_msg = WexinUserMsgModel::getInfoByOpenID($touser_id);              
            
            //添加发送消息信息
            $fire_msg_content = [];
            if ($user_msg) {
                $fire_msg_content['user_msg_id'] = $user_msg['id'];
            }
            $fire_msg_content['supplier_id'] = $weixinInfo['supplier_id'];
            $fire_msg_content['msg_type']    = '2';
            $fire_msg_content['is_reply']    = '2';
            
            
            //查询是否有客服
            $kfList = $Wechat->getCustomServiceKFlist($access_token);
            if (!$kfList) {
                //添加客服账号
                $account = 'admin@'.$weixinInfo['user_name'];
                $nickname = $weixinInfo['nick_name'];
                $password = '123456';
                $addkf = $Wechat->addKFAccount($account,$nickname,$password,$access_token);
                if ($addkf == false) {
                    return false;
                }
            }
            
            
            if ($content['msg_content']) {
                $fire_msg_content['msg_content'] = $content['msg_content'];
                $msg_content_id = WexinUserMsgContentModel::addData($fire_msg_content);
                $data = [];
                $data['touser'] = $touser_id;
                $data['msgtype'] = 'text';
                $data['text'] = ['content' => $content['msg_content']];
                $fire = $Wechat->sendCustomMessage($data,$access_token);
            }
            
            
        }
        return true;
    }
    
    
    
    //发送微信模板消息
    public static function fireWechatModelMsg($sms_set,$params,$openid)
    {
        //没有模板id
        if (!$sms_set['wechat_model_id']) {
            //是否设置行业
            $industry = WeixinFunction::GetTMIndustry();
            $is_ok = false;
            if ($industry['code'] == '200') {
                foreach ($industry['list'] as $key=>$val) {
                    if ($val['second_class'] == '互联网|电子商务') {
                        $is_ok = true;
                    }
                    if ($val['first_class'] == 'IT科技') {
                        $is_ok = true;
                    }
                }
            }
            
            //添加行业信息
            if ($is_ok == false || $industry['code'] == '500') {
                $setIndustry = WeixinFunction::setTMIndustry();
                if ($setIndustry['code'] == '500') {
                    return $setIndustry;
                }
            }
            
            //添加模板信息
            $addTemplat = WeixinFunction::addTemplateMessage($sms_set['old_model_id']);
            if ($addTemplat['code'] == '500') {
                return $setIndustry;
            } 
            
            $sms_set['wechat_model_id'] = $addTemplat['tpl_id'];
            //更新设置表模板id
            $upTemplat = SmsSetModel::updateByID(['wechat_model_id'=>$addTemplat['tpl_id']], $sms_set['id']);
            if (!$upTemplat) {
                $jsonData = [];
                $jsonData['code'] = '500';
                $jsonData['msg'] = '更新消息设置表失败';
                return $jsonData;
            }
        }
        
        //发送消息 组装信息
        $params['touser'] = $openid;
        $params['template_id'] = $sms_set['wechat_model_id'];
        
        $sendMessage = WeixinFunction::sendTemplateMessage($params);
        return $sendMessage;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    

}