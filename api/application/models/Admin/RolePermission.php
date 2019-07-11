<?php

/**
 * 角色权限model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-08
 */
namespace Admin;

use Custom\YDLib;
use Common\CommonBase;

class RolePermissionModel extends \Common\CommonBase {
	protected static $_tableName = 'auth_role_permission';
	
	/* 获取列表 */
	public static function getList($attribute = array(), $page = 0, $rows = 10) {
		if (! empty ( $attribute ['info'] ) && is_array ( $attribute ['info'] ) && count ( $attribute ['info'] ) > 0) {
			extract ( $attribute ['info'] );
		}
		
		$pdo = YDLib::getPDO ( 'db_r' );
		
		$sql = 'SELECT 
        		    a.* 
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
        		LEFT JOIN
        		    ' . CommonBase::$_tablePrefix . self::$_tableName . ' b 
        		ON
        		    a.id=b.parent
        		LEFT JOIN
        		    ' . CommonBase::$_tablePrefix . self::$_tableName . ' c 
        		ON
        		    b.id=c.parent
		        WHERE
        		    a.is_del=2
        		';
		
		if (isset ( $name ) && ! empty ( $name )) {
			$name = trim ( $name );
			$sql .= " AND (a.name like '%{$name}%' OR b.name like '%{$name}%' OR c.name like '%{$name}%')";
		}
		if (isset ( $type ) && ! empty ( $type )) {
			$type = trim ( $type );
			$sql .= " AND a.type = '{$type}'";
		}
		if (isset ( $action ) && ! empty ( $action )) {
			$action = trim ( $action );
			$sql .= " AND (b.action = '{$action}' OR c.action = '{$action}')";
		}
		if (isset ( $method ) && ! empty ( $method )) {
			$method = trim ( $method );
			$sql .= " AND (b.method = '{$method}' OR c.method = '{$method}')";
		}
		if (isset ( $modules ) && ! empty ( $modules )) {
			$modules = trim ( $modules );
			$sql .= " AND b.modules = '{$modules}'";
		}
		$sql .= " AND a.parent=0 GROUP BY a.id";
		
		$result = $pdo->YDGetAll ( $sql );
		
		foreach ( $result as &$val ) {
			if ($val ['type'] == 1) {
				$val ['type_name'] = '平台';
			} else {
				$val ['type_name'] = '商户';
			}
			if ($val ['is_show'] == 1) {
				$val ['is_show'] = '不显示';
			} else {
				$val ['is_show'] = '显示';
			}
			
			$childsql = 'select 
        		    b.* 
        		FROM 
                    ' . CommonBase::$_tablePrefix . self::$_tableName . ' b
                LEFT JOIN
        		    ' . CommonBase::$_tablePrefix . self::$_tableName . ' c 
        		ON
        		    b.id=c.parent
		        WHERE
                    b.is_del=2 
                AND 
                    b.parent=' . $val ['id'];
			if (isset ( $name ) && ! empty ( $name )) {
				$name = trim ( $name );
				$childsql .= " AND (b.name like '%{$name}%' OR c.name like '%{$name}%')";
			}
			if (isset ( $action ) && ! empty ( $action )) {
				$action = trim ( $action );
				$childsql .= " AND (b.action = '{$action}' OR c.action = '{$action}')";
			}
			if (isset ( $method ) && ! empty ( $method )) {
				$method = trim ( $method );
				$childsql .= " AND (b.method = '{$method}' OR c.method = '{$method}')";
			}
			
			$val ['children'] = $pdo->YDGetAll ( $childsql . " GROUP BY b.id ORDER BY b.order_num asc" );
			if ($val ['children']) {
				foreach ( $val ['children'] as &$child ) {
					if ($child ['type'] == 1) {
						$child ['type_name'] = '平台';
					} else {
						$child ['type_name'] = '商户';
					}
					if ($child ['is_show'] == 1) {
						$child ['is_show'] = '不显示';
					} else {
						$child ['is_show'] = '显示';
					}
					
					$threeChildSql = 'select
            		    *
            		FROM
                        ' . CommonBase::$_tablePrefix . self::$_tableName . '
    		        WHERE
                        is_del=2
                    AND
                        parent=' . $child ['id'];
					if (isset ( $name ) && ! empty ( $name )) {
						$name = trim ( $name );
						$threeChildSql .= " AND name like '%{$name}%'";
					}
					if (isset ( $action ) && ! empty ( $action )) {
						$action = trim ( $action );
						$threeChildSql .= " AND action = '{$action}'";
					}
					
					$child ['children'] = $pdo->YDGetAll ( $threeChildSql . " ORDER BY order_num asc" );
					if ($child ['children']) {
						foreach ( $child ['children'] as &$tchild ) {
							if ($tchild ['type'] == 1) {
								$tchild ['type_name'] = '平台';
							} else {
								$tchild ['type_name'] = '商户';
							}
							if ($tchild ['is_show'] == 1) {
								$tchild ['is_show'] = '不显示';
							} else {
								$tchild ['is_show'] = '显示';
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
	 */
	public static function addRolePermission($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['is_del'] = '2';
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/**
	 * 根据角色id获取
	 * 
	 * @param array $id        	
	 * @return array
	 */
	public static function getInfoByPermissionId($role_id) {
		$pdo = YDLib::getPDO ( 'db_r' );
		$ret = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( [ 
				'role_id' => $role_id,
				'is_del' => '2' 
		] )->getAll ();
		
		return $ret ? $ret : [ ];
	}
	
	/**
	 * 根据角色ID删除记录
	 * 
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByRoleID($role_id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'role_id' => intval ( $role_id ) 
		) );
	}
	
	/**
	 * 获取组对应的权限
	 * 
	 * @param int $roleId
	 *        	组id
	 * @return array
	 */
	public static function getAuthPermissionRole($roleID) {
		$sql = "
			select
				a.*
			from
				" . CommonBase::$_tablePrefix . "auth_permission as a," . CommonBase::$_tablePrefix . self::$_tableName . " as b
    				where
    				b.role_id = {$roleID}
    				and
    				b.permission_id = a.id
    				and
    				a.is_del = 2
    				and
    				b.is_del = 2
			";
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->YDGetAll ( $sql );
	}
}