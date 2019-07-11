<?php
/**
 * 消息设置model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-10-27
 */
namespace Sms;
use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
 
class SmsSetModel extends \Common\CommonBase 
{
    protected static $_tableName = 'sms_set';
    private function __construct() {
        parent::__construct();
    }
    
    public static $SMS_SET_TYPE = [
        '1'=>'订单催付提醒',
        '2'=>'付款成功通知',
        '3'=>'发货提醒',
        '4'=>'优惠券/代金券到期提醒',
        '5'=>'商家同意退款',
        '6'=>'买家发起退款提醒',
        '7'=>'竞拍成功提醒',
        '8'=>'参团成功提醒',
        '9'=>'领取优惠券提醒',
        '10'=>'买家已退货提醒',
        '11'=>'会员等级变动通知',
        '12'=>'成长值变更通知',
        '13'=>'会员储值成功提醒',
        '14'=>'储值余额变动提醒',
    ];
    
    /**
     * 添加信息
     * @param array $info
     * @return mixed
     */
    public static function addData($info)
    {
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        $db = YDLib::getPDO('db_w');
        $info['is_del'] = '2';
        $info['created_at'] = date("Y-m-d H:i:s");
		$info['updated_at'] = date("Y-m-d H:i:s");
		$info['supplier_id'] = $adminInfo['supplier_id'];
        $result = $db->insert(self::$_tableName, $info);
    
        return $result;
    }
    
    
  

    /**
     * 获得全部数据
     *
     * @return mixed
     */
    public static function getAll()
    {
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        $pdo = YDLib::getPDO('db_r');
		$sql = 'SELECT 
        		   *
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a 
		        WHERE
        		    a.is_del = 2
		        AND
		            a.supplier_id = '.$adminInfo['supplier_id'];
    	$result = $pdo ->YDGetAll($sql);
    	$list = [];
    	if ($result) {
    	    foreach($result as $key=>$val) {
    	        $list[$val['remind_type']] = $val;
    	    }
    	}
    	
        return $list;
    }
    
	/**
     * 获取单条数据
     *
     * @param interger $id
     * @return mixed
     *   
     */
	public static function getInfoByID($id)
	{
		$where['is_del'] = self::DELETE_SUCCESS;
		$where['id'] = intval($id);

		$pdo = self::_pdo('db_r');	
		$info = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
		
		if (!$info) {
			return FALSE;
		}
		
		return $info;				 
   
	}
	
	/**
	 * 获取单条数据
	 *
	 * @param interger $type
	 * @return mixed
	 *
	 */
	public static function getInfoByType($type)
	{
	    $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
	    $where['is_del'] = self::DELETE_SUCCESS;
	    $where['remind_type'] = intval($type);
	    $where['supplier_id'] = $adminInfo['supplier_id'];
	    
	    $pdo = self::_pdo('db_r');
	    $info = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
	
	    if (!$info) {
	        return FALSE;
	    }
	
	    return $info;
	     
	}
	

	
	/**
	 * 根据一条自增ID更新表记录
	 * @param array $data 更新字段作为key的数组
	 * @param array $id 表自增id
	 * @return boolean 更新结果
	 */
	public static function updateByID($data, $id)
	{
	    $data['updated_at'] = date("Y-m-d H:i:s");
	
	    $pdo = self::_pdo('db_w');
	    return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
	}
	

}