<?php
/**
 * 商户model
 * @version v0.01
 * @author laiqingtao
 * @time 2018-05-08
 */
namespace Supplier;

use Custom\YDLib;
use Core\Queue;
use Common\DataType;
use Admin\AdminModel;

class SupplierModel extends \BaseModel
{
    /**
     * 定义表名后缀
     */	
    protected static $_tableName = 'supplier';
	
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
		
		$data['province_id'] = isset($data['province_id'])?$data['province_id']:1;//默认北京
		$data['city_id'] = isset($data['city_id'])?$data['city_id']:51892;//默认北京
		$data['area_id'] = isset($data['area_id'])?$data['area_id']:72;//默认朝阳区
		
		$pdo = self::_pdo('db_w');
		return $pdo->insert(self::$_tableName, $data);
    }		

    /**
     * 根据domain获取该条记录信息
     * @param int $domain 唯一标识
	 * @param int $id 表自增ID 大于零排除
     */
    public static function getInfoByDomain($domain,$id=0)
    {   	
		$sql = "SELECT 
        		    * 
        		FROM
		            ".self::getTb()."
		        WHERE
        		    is_del=".self::DELETE_SUCCESS."
		        AND
        		    domain='{$domain}'";
		if ($id > 0) {
			$sql .= " AND id <> {$id}";
		}						

		$pdo = self::_pdo('db_r');	
		return $pdo->YDGetRow($sql);		 
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
     * 根据表自增ID获取该条记录信息
     * @param int $id 表自增ID
     */
    public static function getInfoBySupplierID()
    {
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['id'] = intval($adminInfo['supplier_id']);
    
        $pdo = self::_pdo('db_r');
        return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
    }
    
    /**
     * 根据一条自增ID更新表记录
     * @param array $data 更新字段作为key的数组
     * @param array $id 表自增id
     * @return boolean 更新结果
     */
    public static function updateBySupplierID($data)
    {
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        $id = $adminInfo['supplier_id'];
        
        $detail = SupplierModel::getInfoByID($id);
        //删除memcache
        $mem = YDLib::getMem('memcache');
        $mem->delete('supplier_'.$detail['domain']);

        //删除原access_token
        $mem->delete( 'authorizer_access_token_'.$id );
        $mem->delete( 'component_access_token_'.$id );

        $data['updated_at'] = date("Y-m-d H:i:s");
        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
    }
    
    /**
     * 根据一条自增ID更新表记录
     * @param array $data 更新字段作为key的数组
     * @param array $id 表自增id
     * @return boolean 更新结果
     */
    public static function updateByID($data, $id)
    {
        $detail = SupplierModel::getInfoByID($id);
        //删除memcache
        $mem = YDLib::getMem('memcache');
        $mem->delete('supplier_'.$detail['domain']);
        
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
        $detail = SupplierModel::getInfoByID($id);
        //删除memcache
        $mem = YDLib::getMem('memcache');
        $mem->delete('supplier_'.$detail['domain']);
        
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
		
		$filed = "id,company,domain,province_id,city_id,area_id,address,contact,mobile,created_at";		
		
		$sql = "SELECT 
        		    [*] 
        		FROM
		            ".self::getTb()."
		        WHERE
        		    is_del=".self::DELETE_SUCCESS."
		        AND
        		    status=".self::STATUS_SUCCESS;	
								
		if (isset($id) && !empty(intval($id))) {
		    $sql .= " AND id = '".intval($id)."'";
		}
		if (isset($company) && !empty(trim($company))) {
		    $sql .= " AND company LIKE '%".trim($company)."%'";
		}
		if (isset($province_id) && !empty(intval($province_id))) {
		    $sql .= " AND province_id = '".intval($province_id)."'";
		}		
		if (isset($city_id) && !empty(intval($city_id))) {
		    $sql .= " AND city_id = '".intval($city_id)."'";
		}		
		if (isset($area_id) && !empty(intval($area_id))) {
		    $sql .= " AND area_id = '".intval($area_id)."'";
		}		
		if (isset($contact) && !empty(trim($contact))) {
		    $sql .= " AND contact LIKE '%".trim($contact)."%'";
		}		
		if (isset($mobile) && !empty(trim($mobile))) {
		    $sql .= " AND mobile LIKE '%".trim($mobile)."%'";
		}			
		if (isset($start_at) && !empty(trim($start_at))) {
		    $sql .= " AND created_at >= '".trim($start_at)."'";
		}		
		if (isset($end_at) && !empty(trim($end_at))) {
		    $sql .= " AND created_at <= '".trim($end_at)."'";
		}		
		
		$pdo = self::_pdo('db_r');	
		$resInfo = array();
		$resInfo['total'] = $pdo->YDGetOne(str_replace('[*]', 'COUNT(1) num', $sql));
						
		$sort = isset($sort)?$sort:'id';	
		$order = isset($order)?$order:'DESC';
		
		$sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
		$resInfo['rows'] = $pdo->YDGetAll(str_replace('[*]', $filed, $sql));
		return $resInfo;
        
    }



    /**
     * 获取商户名称
     * @param $supplier_id
     * @return mixed
     */
    public static function getCompanyBySupplierId($supplier_id)
    {
        $row = self::find($supplier_id,['company']);
        return $row->company;
    }
}