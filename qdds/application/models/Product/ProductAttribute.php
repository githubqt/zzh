<?php
/**
 * 商品属性权限model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-09
 */
namespace Product;

use Custom\YDLib;
use Common\CommonBase;
  
class ProductAttributeModel extends \BaseModel
{
    protected static $_tableName = 'product_attribute';
   
    

    /**
     * 添加权限信息
     * @param array $info
     * @return mixed
     */
    public static function addData($info)
    {
        $db = YDLib::getPDO('db_w');
        $info['is_del'] = '2';
        $info['created_at'] = date("Y-m-d H:i:s");
		$info['updated_at'] = date("Y-m-d H:i:s");
        $result = $db->insert(self::$_tableName, $info);
    
        return $result;
    }
    
   
    
    /**
     * 根据属性id获取
     * @param int $product_id
     * @return array
     */
    public static function getInfoByProductId($product_id)
    {
       
        $sql = "
			SELECT
				a.*,b.input_type
			FROM
                ".CommonBase::$_tablePrefix.self::$_tableName." as a
            LEFT JOIN
				" .CommonBase::$_tablePrefix. "attribute as b
		    ON
		        b.id=a.attribute_id	
        	WHERE 
				a.product_id = {$product_id}	
			AND
			     a.is_del = 2
        				";
        $pdo = self::_pdo('db_r');
        return $pdo ->YDGetAll($sql);
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
        return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
         
    }
    
    /**
     * 根据角色ID删除记录
     * @param int $id 表自增 ID
     * @return boolean 删除是否成功
     */
    public static function deleteByProductID($role_id)
    {
        $data['is_del'] = self::DELETE_FAIL;
        $data['updated_at'] = date("Y-m-d H:i:s");
        $data['deleted_at'] = date("Y-m-d H:i:s");
    
        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('product_id' => intval($role_id)));
    }
    
    
    
    
    /**
     * 获取组对应的权限
     * @param int $roleId 组id
     * @return array
     */
    public static function getAuthPermissionRole($roleID)
    {
        $sql = "
			select
				a.*
			from
				" .CommonBase::$_tablePrefix. "auth_permission as a,".CommonBase::$_tablePrefix.self::$_tableName." as b
    				where
    				b.role_id = {$roleID}
    				and
    				b.permission_id = a.id
    				and
    				a.is_del = 2
    				and
    				b.is_del = 2
			";
        $pdo = self::_pdo('db_r');
        return $pdo ->YDGetAll($sql);
    }
    
    
}