<?php
/**
 * 商品库存日志权限model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-11
 */
namespace Product;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
use Product\ProductModel;
  
class ProductStockLogModel extends \BaseModel
{
    protected static $_tableName = 'product_stock_log';

    /**
     * 添加权限信息
     * @param int $product_id 商品id
     * @param int $num 变动数量（减库存为负数）
     * @return mixed
     */
    public static function addData($product_id,$data)
    {
        $db = self::_pdo('db_w');
        //获取登陆人信息
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        if ($data['type'] != \Services\Stock\StockService::LOG_TYPE_1) {
            $product = ProductModel::getInfoByID($product_id);
        } else {
            $product = [
                'name' => $data['name'],
                'lock_stock' => '0',
                'stock' => '0'
            ];
        }
        $info = [];
        $info['supplier_id'] = $adminInfo['supplier_id'];
        $info['product_id'] = $product_id;
        $info['product_name'] = $product['name'];
        $info['lock_stock_old'] = $product['lock_stock']?$product['lock_stock']:'0';
        $info['stock_old'] = $product['stock'];
        $info['stock_change'] = isset($data['num'])?$data['num']:'0';
        $info['stock_new'] = bcadd($product['stock'], $info['stock_change']);
        $info['lock_stock_change'] = isset($data['lock_num'])?$data['lock_num']:'0';
        $info['lock_stock_new'] = bcadd($info['lock_stock_old'], $info['lock_stock_change']);

        $info['type'] = $data['type'];
        $info['note'] = \Services\Stock\StockService::LOG_TYPE[$data['type']];
        $info['admin_id'] = $adminInfo['id'];
        $info['admin_name'] = $adminInfo['fullname']?$adminInfo['fullname']:$adminInfo['name'];
        $info['is_del'] = '2';
        $info['created_at'] = date("Y-m-d H:i:s");
        $info['updated_at'] = date("Y-m-d H:i:s");

        $result = $db->insert(self::$_tableName, $info);

        return $result;
    }

    /**
     * 添加权限信息
     * @param int $product_id 商品id
     * @param int $num 变动数量（减库存为负数）
     * @return mixed
     */
    public static function addDataOld($data)
    {
        $db = self::_pdo('db_w');
        $data['is_del'] = '2';
        $data['created_at'] = date("Y-m-d H:i:s");
        $data['updated_at'] = date("Y-m-d H:i:s");

        $result = $db->insert(self::$_tableName, $data);

        return $result;
    }

    /**
     * 添加日志
     * @param int $product_id 商品id
     * @param int $num 变动数量（减库存为负数）
     * @return mixed
     */
    public static function addLog($product_id,$type,$stock_change=0,$lock_stock_change=0,$admin_id=0,$admin_name='系统')
    {
		$adminId = AdminModel::getAdminID();
		$adminInfo = AdminModel::getAdminLoginInfo($adminId);    	
        $product = ProductModel::getSingleInfoByID($product_id);
        $info = [];
        $info['supplier_id'] = $adminInfo['supplier_id'];
        $info['product_id'] = $product_id;
        $info['product_name'] = $product['name'];
        $info['stock_old'] = $product['stock'];
		$info['lock_stock_old'] = $product['lock_stock'];
        $info['stock_change'] = $stock_change;
		$info['lock_stock_change'] = $lock_stock_change;		
        $info['stock_new'] = bcadd($product['stock'], $stock_change);
        $info['lock_stock_new'] = bcadd($product['lock_stock'], $lock_stock_change);
        $info['type'] = $type;
		$info['note'] = \Services\Stock\StockService::LOG_TYPE[$type];
        $info['admin_id'] = $admin_id;
        $info['admin_name'] = $admin_name;
        $info['is_del'] = self::DELETE_SUCCESS;
        $info['created_at'] = date("Y-m-d H:i:s");
		$info['updated_at'] = date("Y-m-d H:i:s");
		$pdo = self::_pdo('db_w');
        $result = $pdo->insert(self::$_tableName, $info);
    
        return $result;
    }

    
   
    
    /**
     * 根据属性id获取
     * @param array $product_id
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
    
	
	 /* 获取列表*/
    public static function getList($attribute = array(),$page = 0,$rows = 10)
    {
        $limit = ($page) * $rows;
        if (!empty($attribute['info']) && is_array($attribute['info']) && count($attribute['info']) > 0) {
            extract($attribute['info']);
        }
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
		$pdo = YDLib::getPDO('db_r');
		$fileds = " a.*,p.name as pname,p.self_code";
		$sql = 'SELECT 
        		   [*]
        		FROM
		            '.CommonBase::$_tablePrefix.self::$_tableName.' a 
		        LEFT JOIN
		        	'.CommonBase::$_tablePrefix.'product p
		       	ON
		       		p.id = a.product_id   
		       	LEFT JOIN
		        	'.CommonBase::$_tablePrefix.'brand b
		       	ON
		       		b.id = p.brand_id  	   
		        WHERE
        		    a.is_del = 2
        		AND 
        			a.supplier_id = ' . $adminInfo['supplier_id'] . '
        		';
	
		if (isset($name) && !empty($name)) {
			$sql .= " AND p.name like '%".$name."%' ";
		}
		
		if (isset($self_code) && !empty($self_code)) {
			$sql .= " AND p.self_code like '%".$self_code."%' ";
		}
			
		if (isset($admin_name) && !empty($admin_name)) {
			$sql .= " AND (a.admin_name like  '%".$admin_name."%')";
		}
		
		if (isset($brand_name) && is_array($brand_name) && count($brand_name) > 0) {
            $brand_name = implode(",",$brand_name);
			$sql .= " AND b.id in (".$brand_name.")";
		}
     
	 	if (isset($note) && !empty($note)) {
			$sql .= " AND a.note like '%".$note."%' ";
		}
	 
		if (isset($start_time) && !empty($start_time)) {
		    $sql .= " AND a.created_at >= '".$start_time." 00:00:00'";
		}
		
		if (isset($end_time) && !empty($end_time)) {
		    $sql .= " AND a.created_at <= '".$end_time." 23:59:59'";
		}
        $result['total'] = $pdo ->YDGetOne(str_replace("[*]", "count(*) as num", $sql));
        if ($sort && $order) {
            $sql .= " ORDER BY a.{$sort} {$order} limit {$limit},{$rows}";
        } else {
            $sql .= " ORDER BY a.id DESC limit {$limit},{$rows}";
        }
        $result['list'] = $pdo ->YDGetAll(str_replace("[*]", $fileds, $sql));
		if (is_array( $result['list']) && count($result['list']) > 0) {
			foreach ($result['list'] as $key => $value) {
				$result['list'][$key]['type'] = \Services\Stock\StockService::LOG_TYPE[$value['type']];
			}
		}
        if ($result) {
            return $result;
        } else {
            return false;
        }
        
    }
    
}