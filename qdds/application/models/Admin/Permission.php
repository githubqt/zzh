<?php
/**
 * 权限model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-05
 */
namespace Admin;

use Custom\YDLib;
use Common\CommonBase;
 
class PermissionModel extends \BaseModel
{
    protected static $_tableName = 'auth_permission';
   
    
    /* 获取列表*/
    public static function getList($attribute = array(),$page = 0,$rows = 10)
    {
        
        if (!empty($attribute['info']) && is_array($attribute['info']) && count($attribute['info']) > 0) {
            extract($attribute['info']);
        }
       
		$pdo = YDLib::getPDO('db_r');
		
		$sql = 'SELECT 
        		    a.* 
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a 
        		LEFT JOIN
        		    '.CommonBase::$_tablePrefix.self::$_tableName.' b 
        		ON
        		    a.id=b.parent
        		LEFT JOIN
        		    '.CommonBase::$_tablePrefix.self::$_tableName.' c 
        		ON
        		    b.id=c.parent
		        WHERE
        		    a.is_del=2
        		';
		
        if (isset($name) && !empty($name)) {
			$name = trim($name);
			$sql .= " AND (a.name like '%{$name}%' OR b.name like '%{$name}%' OR c.name like '%{$name}%')";
		}
		if (isset($type) && !empty($type)) {
		    $type = trim($type);
		    $sql .= " AND a.type = '{$type}'";
		}
		if (isset($action) && !empty($action)) {
		    $action = trim($action);
		    $sql .= " AND (b.action = '{$action}' OR c.action = '{$action}')";
		}
		if (isset($method) && !empty($method)) {
		    $method = trim($method);
		    $sql .= " AND (b.method = '{$method}' OR c.method = '{$method}')";
		}
		if (isset($modules) && !empty($modules)) {
		    $modules = trim($modules);
		    $sql .= " AND b.modules = '{$modules}'";
		}
		$sql.=" AND a.parent=0 GROUP BY a.id";
		
        $result = $pdo ->YDGetAll($sql);
        
        foreach ($result as &$val) {
            if($val['type'] == 1) {
                $val['type_name'] = '平台';
            } else {
                $val['type_name'] = '商户';
            }
            if($val['is_show'] == 1) {
                $val['is_show'] = '不显示';
            } else {
                $val['is_show'] = '显示';
            }
            
            $childsql = 'select 
        		    b.* 
        		FROM 
                    '.CommonBase::$_tablePrefix.self::$_tableName.' b
                LEFT JOIN
        		    '.CommonBase::$_tablePrefix.self::$_tableName.' c 
        		ON
        		    b.id=c.parent
		        WHERE
                    b.is_del=2 
                AND 
                    b.parent='.$val['id'];
            if (isset($name) && !empty($name)) {
                $name = trim($name);
                $childsql .= " AND (b.name like '%{$name}%' OR c.name like '%{$name}%')";
            }
            if (isset($action) && !empty($action)) {
                $action = trim($action);
                $childsql .= " AND (b.action = '{$action}' OR c.action = '{$action}')";
            }
            if (isset($method) && !empty($method)) {
                $method = trim($method);
                $childsql .= " AND (b.method = '{$method}' OR c.method = '{$method}')";
            }
           
            $val['children'] = $pdo ->YDGetAll($childsql." GROUP BY b.id ORDER BY b.order_num asc");
            if ($val['children']) {
                foreach ($val['children'] as &$child) {
                    if($child['type'] == 1) {
                        $child['type_name'] = '平台';
                    } else {
                        $child['type_name'] = '商户';
                    }
                    if($child['is_show'] == 1) {
                        $child['is_show'] = '不显示';
                    } else {
                        $child['is_show'] = '显示';
                    }
                    
                    $threeChildSql = 'select
            		    *
            		FROM
                        '.CommonBase::$_tablePrefix.self::$_tableName.'
    		        WHERE
                        is_del=2
                    AND
                        parent='.$child['id'];
                    if (isset($name) && !empty($name)) {
                        $name = trim($name);
                        $threeChildSql .= " AND name like '%{$name}%'";
                    }
                    if (isset($action) && !empty($action)) {
                        $action = trim($action);
                        $threeChildSql .= " AND action = '{$action}'";
                    }
                     
                    $child['children'] = $pdo ->YDGetAll($threeChildSql." ORDER BY order_num asc");
                    if ($child['children']) {
                        foreach ($child['children'] as &$tchild) {
                            if($tchild['type'] == 1) {
                                $tchild['type_name'] = '平台';
                            } else {
                                $tchild['type_name'] = '商户';
                            }
                            if($tchild['is_show'] == 1) {
                                $tchild['is_show'] = '不显示';
                            } else {
                                $tchild['is_show'] = '显示';
                            }
                        }
                    }
                }
            }
        }
        
        if ($result) {
            return $result;
        } else {
            return false;
        }
        
    }
  

    /**
     * 添加权限信息
     *
     * @param array $info
     * @return mixed
     *   
     */
    public static function addPermission($info)
    {
        
        $db = YDLib::getPDO('db_w');
        $info['is_del'] = '2';
        $info['status'] = '2';
        $info['created_at'] = date("Y-m-d H:i:s");
		$info['updated_at'] = date("Y-m-d H:i:s");
        $result = $db->insert(self::$_tableName, $info, ['ignore' => true]);
    
        return $result;
    }
    
    
    /**
     * 获取顶级权限
     * @param string $type
     * @return array
     */
    public static function getParentList($type='')
    {
        $where = [
            'parent'=>0,
            'is_del'=>'2'
        ];
        
        if ($type) {
            $where['type'] = $type;
        }
        $pdo = YDLib::getPDO('db_r');
        $ret = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->order('order_num asc')->getAll();
        
        return $ret?$ret:[];
    }

    /**
     * 获取子级权限
     * @param string $parent_id
     * @return array
     */
    public static function getParentTwoList($parent_id)
    {
        $pdo = YDLib::getPDO('db_r');
        $where = ['parent'=>$parent_id,'is_del'=>'2'];
        $where['type'] = PROJECT_TYPE;
        $ret = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->order('order_num asc')->getAll();

        return $ret?$ret:[];
    }
    
    /**
     * 获取所有权限
     * @param 
     * @return array
     */
    public static function getAllList($type = 2)
    {
        $pdo = YDLib::getPDO('db_r');
        $ret = $pdo->clear()->select('*')->from(self::$_tableName)->where(['is_del'=>'2','type'=>$type])->getAll();
        
        return $ret?$ret:[];
    }

    /* 查询*/
    public static function findOneWhere($where)
    {
        $where['is_del'] = self::DELETE_SUCCESS;
        $pdo = self::_pdo('db_r');
        return $pdo->clear()->select('id,parent,action,method,modules,name')->from(self::$_tableName)->where($where)->getRow();
    }
    
    
}