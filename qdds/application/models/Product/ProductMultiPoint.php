<?php
/**
 * 商品多网点model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-07-12
 */
namespace Product;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
  
class ProductMultiPointModel extends \Common\CommonBase 
{
    protected static $_tableName = 'product_multi_point';
   
    

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
     * @param array $product_id
     * @return array
     */
    public static function getInfoByProductId($product_id)
    {
        $adminId = AdminModel::getAdminID();
        $adminInfo = AdminModel::getAdminLoginInfo($adminId);
        $sql = "
			SELECT
				a.*
			FROM
                ".CommonBase::$_tablePrefix.self::$_tableName." as a
        	WHERE 
				a.product_id = {$product_id}	
			AND
			     a.is_del = 2
			AND a.supplier_id = {$adminInfo['supplier_id']}
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
        $adminId = AdminModel::getAdminID();
        $adminInfo = AdminModel::getAdminLoginInfo($adminId);
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['id'] = intval($id);
        $where['supplier_id'] = $adminInfo['supplier_id'];
        
        $pdo = self::_pdo('db_r');
        return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
         
    }
    
    
    /**
     * 获取搜索的商品id
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getProductIdByID($id)
    {
        $adminId = AdminModel::getAdminID();
        $adminInfo = AdminModel::getAdminLoginInfo($adminId);
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['multi_point_id'] = intval($id);
        $where['supplier_id'] = $adminInfo['supplier_id'];
    
        $pdo = self::_pdo('db_r');
        $multi = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getAll();
         $ids = [];
         if ($multi) {
             foreach ($multi as $key=>$val) {
                 $ids[$key] = $val['product_id'];
             }
             $ids = implode(',',$ids);
         }
         return $ids;
    }
    
    /**
     * 根据商品ID删除记录
     * @param int $id 表商品 ID
     * @return boolean 删除是否成功
     */
    public static function deleteByProductID($role_id)
    {
        $adminId = AdminModel::getAdminID();
        $adminInfo = AdminModel::getAdminLoginInfo($adminId);
        $data['is_del'] = self::DELETE_FAIL;
        $data['updated_at'] = date("Y-m-d H:i:s");
        $data['deleted_at'] = date("Y-m-d H:i:s");
    
        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('product_id' => intval($role_id),'supplier_id'=>$adminInfo['supplier_id']));
    }
    

    
}