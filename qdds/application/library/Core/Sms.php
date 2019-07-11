<?php
/**
 * 短信接口
 * 
 * @package library
 * @subpackage Core
 * @author 赖清涛 <laiqingtao@zhahehe.com>
 */

namespace Core;
use Admin\AdminModel;
use Custom\YDLib;
use Supplier\SupplierModel;
class Sms
{
	/**
	 * 发送发货短信
	 *
	 * @param string $mobile	手机号
	 * @param string $order_no	订单号
	 * @return string
	 */
	public static function SendFireSms($mobile, $order_no)
	{
		
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
		$suppplier_detail = SupplierModel::getInfoByID($adminInfo['supplier_id']);	
						
		$url = API_URL.'v1/common/commonSms';
		$postdata = [];
		$postdata['identif'] = $suppplier_detail['domain'];
		$postdata['mobile'] = $mobile;
		$postdata['model_id'] = '5';
		$postdata['params'] = json_encode(array($order_no));
		$res = YDLib::curlPostRequset($url,$postdata);		
		if (isset($res['errno']) && $res['errno'] == '0') {
			return TRUE;				
		} else {				
			return FALSE;
		}
	}
	/**
	 * 发送群发短信
	 *
	 * @param string $id	群发id
	 * @return string
	 */
	public static function SendMassSms($id)
	{
		
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
		$suppplier_detail = SupplierModel::getInfoByID($adminInfo['supplier_id']);	
						
		$url = API_URL.'v1/common/commonSmsMassID';
		$postdata = [];
		$postdata['identif'] = $suppplier_detail['domain'];
		$postdata['id'] = $id;
		$res = YDLib::curlPostRequset($url,$postdata);	
		//YDLib::testlog($res);	
		if (isset($res['errno']) && $res['errno'] == '0') {
			return TRUE;				
		} else {				
			return FALSE;
		}
	}
	
	
	
	/**
	 * 发送短信微信
	 *
	 * @return string
	 */
	public static function SendFireWechatSms($type,$mobile, $user_id,$msgData)
	{
	
	    $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
	    $suppplier_detail = SupplierModel::getInfoByID($adminInfo['supplier_id']);
	
	    $url = API_URL.'v1/common/commonWechatSms';
	    $postdata = [];
	    $postdata['identif'] = $suppplier_detail['domain'];
	    $postdata['mobile'] = $mobile;
	    $postdata['remind_type'] = $type;
	    $postdata['user_id'] = $user_id;
	    $postdata['data'] = json_encode($msgData);
	    $res = YDLib::curlPostRequset($url,$postdata);
	    if (isset($res['errno']) && $res['errno'] == '0') {
	        return TRUE;
	    } else {
	        return FALSE;
	    }
	}

    /**
     * 发送短信
     * @param string $mobile	手机号
     * @param string $model_id	模板ID
     * @param string $domain	domain
     * @param string $mobile	手机号
     * @return string
     */
    public static function SendSms($mobile, $model_id,$domain,$params=[])
    {
        $url = API_URL.'v1/common/commonSms';
        $postdata = [];
        $postdata['identif'] = $domain;
        $postdata['mobile'] = $mobile;
        $postdata['model_id'] = $model_id;
        $postdata['params'] = json_encode($params);
        YDLib::curlPostRequset($url,$postdata);
    }
}	
