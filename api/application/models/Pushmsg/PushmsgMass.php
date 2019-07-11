<?php
/**
 * 短信群发model
 * @version v0.01
 * @author lqt
 * @time 2018-08-13
 */
namespace Pushmsg;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;

class PushmsgMassModel extends \Common\CommonBase 
{
	
	/** 发送类型 */ 
	const  MASS_TYPE_1			= 1;
	const  MASS_TYPE_2			= 2;
	
	/** 状态 */ 
	const  MASS_STATUS_1		= 1;
	const  MASS_STATUS_2		= 2;	
	const  MASS_STATUS_3		= 3;	
	
	const MASS_TYPE_VALUE 		= [
									self::MASS_TYPE_1=>'立即发送',
									self::MASS_TYPE_2=>'定时发送'
								];	
								
	const MASS_STATUS_VALUE 	= [
									self::MASS_STATUS_1=>'未启动',
									self::MASS_STATUS_2=>'启动',
									self::MASS_STATUS_3=>'完成'
								];	
	
    /** 定义表名后缀 */	
    protected static $_tableName = 'sms_mass';
	
    /**
     * 获取表名
     */	
    public static function getTb()
    {
        return self::$_tablePrefix . self::$_tableName;
    }	
	
	 /**
     * 记录入库
     * @param array $data 表字段名作为key的数组
     * @return int 入库成功则返回入库记录的自增ID，否则返回FALSE
     */	
    public static function addData($data)
    {
        $data['is_del'] = self::DELETE_SUCCESS;
		$data['status'] = self::STATUS_SUCCESS;
		$data['created_at'] = date("Y-m-d H:i:s");
		$data['updated_at'] = date("Y-m-d H:i:s");
		$data['supplier_id'] = SUPPLIER_ID;		

		$pdo = self::_pdo('db_w');
		return $pdo->insert(self::$_tableName, $data);
    }			
	
    /**
     * 根据表自增ID获取该条记录信息
     * @param int $id 表自增ID
     */
    public static function getInfoByID($id)
    {   	
        $where['is_del'] = self::DELETE_SUCCESS;
		$where['id'] = intval($id);

		$pdo = self::_pdo('db_r');	
		return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();		 
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
    
    /**
     * 根据表自增 ID删除记录
     * @param int $id 表自增 ID
     * @return boolean 删除是否成功
     */
    public static function deleteByID($id)
    {    	
        $data['is_del'] = self::DELETE_FAIL;
		$data['updated_at'] = date("Y-m-d H:i:s");
		$data['deleted_at'] = date("Y-m-d H:i:s");
		
		$pdo = self::_pdo('db_w');			 
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
    }	
	
	/**
	 * 获取对应的list列表
	 * @param array  $attribute 获取对应的参数    
	 * @param integer $page 对应的页
	 * @param integer $rows 取出的行数
	 * @return array 
	 */
    public static function getList($attribute = array(),$page = 1,$rows = 10)
    {
        $limit = ($page-1) * $rows;
		
		if (is_array($attribute) && count($attribute) > 0) {
			extract($attribute);
		}
		
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());		
		
		$filed = "id,supplier_id,model_id,content,sms_num_total,sms_num_ok,mobiles,mobile_num_total,mobile_num_ok,type,send_time,status,created_at";		
		
		$sql = "SELECT 
        		    [*] 
        		FROM
		            ".self::getTb()."
		        WHERE
        		    is_del=".self::DELETE_SUCCESS."
		        AND
        		    supplier_id=".$adminInfo['supplier_id'];	
		
		$pdo = self::_pdo('db_r');	
		
		$resInfo = array();
		$resInfo['total'] = $pdo->YDGetOne(str_replace('[*]', 'COUNT(1) num', $sql));
						
		$sort = isset($sort)?$sort:'id';	
		$order = isset($order)?$order:'DESC';
		
		$sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
		$resInfo['rows'] = $pdo ->YDGetAll(str_replace("[*]", $filed, $sql));
		
		if (is_array($resInfo['rows']) && count($resInfo['rows']) > 0) {
			foreach ($resInfo['rows'] as $key => $value) {
				$resInfo['rows'][$key]['type_txt'] = self::MASS_TYPE_VALUE[$value['type']];
				$resInfo['rows'][$key]['status_txt'] = self::MASS_STATUS_VALUE[$value['status']];
			}
		}
		
		return $resInfo;
        
    }	

}