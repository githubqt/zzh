<?php
/**
 * 角色model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-08
 */
namespace Admin;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
  
class RoleModel extends \Common\CommonBase 
{
    protected static $_tableName = 'auth_role';
   
    
    /* 获取列表*/
    public static function getList($attribute = array(),$page = 0,$rows = 10)
    {
        $limit = ($page) * $rows;
        if (!empty($attribute['info']) && is_array($attribute['info']) && count($attribute['info']) > 0) {
            extract($attribute['info']);
        }
       
		$pdo = YDLib::getPDO('db_r');
		$fileds = " a.* ";
		$sql = 'SELECT 
        		   [*]
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a 
		        WHERE
        		    a.is_del = 2
		        AND 
	                 a.type = '.PROJECT_TYPE.'
		        AND 
	                 a.supplier_id='.AdminModel::getAdminLoginInfo(AdminModel::getAdminID())['supplier_id'];
        		
		
		if (isset($name) && !empty($name)) {
			$sql .= " AND a.name like '%".$name."%' ";
		}
		
		if (isset($id) && !empty($id)) {
			$sql .= " AND a.id like '%".$id."%' ";
		}
		
		if (isset($note) && !empty($note)) {
			$sql .= " AND a.admin_name like '%".$note."%' ";
		}
		if (isset($status) && !empty($status)) {
		    $sql .= " AND a.status = ".$status." ";
		}
     
        if (isset($start_time) && !empty($start_time)) {
            $sql .= " AND a.created_at >= '".$start_time." 00:00:00'";
        }
        
        if (isset($end_time) && !empty($end_time)) {
            $sql .= " AND a.created_at <= '".$end_time." 23:59:59'";
        } 
     	$result['total'] = $pdo ->YDGetOne(str_replace("[*]", "count(*) as num", $sql));
		$sort = isset($sort)?$sort:'id';	
		$order = isset($order)?$order:'DESC';
		
		$sql .= " ORDER BY a.{$sort} {$order} LIMIT {$limit},{$rows}";		

        $result['list'] = $pdo ->YDGetAll(str_replace("[*]", $fileds, $sql));
        if ($result) {
            return $result;
        } else {
            return false;
        }
        
    }
  

    /**
     * 添加信息
     * @param array $info
     * @return mixed
     */
    public static function addRole($info)
    {
        $db = YDLib::getPDO('db_w');
        $info['supplier_id'] = AdminModel::getAdminLoginInfo(AdminModel::getAdminID())['supplier_id'];
        $info['type'] = PROJECT_TYPE;
        $info['admin_id']=AdminModel::getAdminLoginInfo(AdminModel::getAdminID())['id'];;
        $info['admin_name']=AdminModel::getAdminLoginInfo(AdminModel::getAdminID())['fullname'];;
        $info['is_del'] = '2';
        $info['created_at'] = date("Y-m-d H:i:s");
		$info['updated_at'] = date("Y-m-d H:i:s");
        $result = $db->insert(self::$_tableName, $info);
    
        return $result;
    }
    
   
    
    /**
     * 根据id获取
     * @param array $id
     * @return array
     */
    public static function getInfoById($id)
    {
        $pdo = YDLib::getPDO('db_r');
        $ret = $pdo->clear()->select('*')->from(self::$_tableName)->where(['id'=>$id,'is_del'=>'2'])->getRow();
        
        return $ret?$ret:[];
    }
    
    /**
     * 根据id获取
     * @param array $id
     * @return array
     */
    public static function getAll()
    {
        $pdo = YDLib::getPDO('db_r');
        
        $info['supplier_id'] = AdminModel::getAdminLoginInfo(AdminModel::getAdminID())['supplier_id'];
        $info['type'] = PROJECT_TYPE;
        $info['status'] = '2';
        $info['is_del'] = '2';
        $ret = $pdo->clear()->select('*')->from(self::$_tableName)->where($info)->getAll();
    
        return $ret?$ret:[];
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