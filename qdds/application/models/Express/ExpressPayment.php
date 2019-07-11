<?php
/**
 * 快递查询model
 * @time 2018-05-08
 */
namespace Express;

use Admin\AdminModel;

class ExpressPaymentModel extends \BaseModel
{
    /**
     * 定义表名后缀
     */	
    protected static $_tableName = 'express_payment';
	
    /**
     * 获取表名
     */	
    public static function getTb()
    {
        return self::$_tablePrefix . self::$_tableName;
    }
	
    
    /**
     * 获取相应的单条信息
     * @return array
     */
    public static function getRow()
    {
    	$adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
    	$sql = "SELECT
        		    *
        		FROM
		            ".self::getTb()."
		        WHERE
        		    is_del=".self::DELETE_SUCCESS."
            	AND	
            		supplier_id = ".$adminInfo['supplier_id']
    		;
    
            $pdo = self::_pdo('db_r');
            $resInfo = array();
    
            $resInfo = $pdo->YDGetRow($sql);
    
            return $resInfo;
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
	
	
	
}