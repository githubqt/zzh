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

class RoleModel extends \Common\CommonBase {
	protected static $_tableName = 'auth_role';
	
	/* 获取列表 */
	public static function getList($attribute = array(), $page = 0, $rows = 10) {
		if (! empty ( $attribute ['info'] ) && is_array ( $attribute ['info'] ) && count ( $attribute ['info'] ) > 0) {
			extract ( $attribute ['info'] );
		}
		
		$pdo = YDLib::getPDO ( 'db_r' );
        $fields = " a.* ";
		$sql = 'SELECT 
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        WHERE
        		    a.is_del = 2
        		AND
        			a.type = 1    
        		';
		
		if (isset ( $name ) && ! empty ( $name )) {
			$sql .= " AND a.name like '%" . $name . "%' ";
		}
		
		if (isset ( $id ) && ! empty ( $id )) {
			$sql .= " AND a.id like '%" . $id . "%' ";
		}
		
		if (isset ( $note ) && ! empty ( $note )) {
			$sql .= " AND a.note like '%" . $note . "%' ";
		}
		if (isset ( $status ) && ! empty ( $status )) {
			$sql .= " AND a.status = " . $status . " ";
		}
		
		if (isset ( $start_time ) && isset ( $end_time ) && ! empty ( $start_time ) && ! empty ( $end_time )) {
			$sql .= " AND a.created_at >= '" . $start_time . " 00:00:00' ";
			$sql .= " AND a.created_at <= '" . $end_time . " 23:59:59' ";
		}

		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fields, $sql ) );
		
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * 添加信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addRole($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['is_del'] = '2';
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/**
	 * 根据id获取
	 * 
	 * @param array $id        	
	 * @return array
	 */
	public static function getInfoById($id) {
		$pdo = YDLib::getPDO ( 'db_r' );
		$ret = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( [ 
				'id' => $id,
				'is_del' => '2' 
		] )->getRow ();
		
		return $ret ? $ret : [ ];
	}
	
	/**
	 * 获取所有
	 * 
	 * @param
	 *        	array
	 * @return array
	 */
	public static function getAll() {
		$pdo = YDLib::getPDO ( 'db_r' );
		$ret = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( [ 
				'status' => '2',
				'is_del' => '2',
				'type' => '1' 
		] )->getAll ();
		
		return $ret ? $ret : [ ];
	}
	
	/**
	 * 根据一条自增ID更新表记录
	 * 
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateByID($data, $id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
	
	/**
	 * 根据表自增 ID删除记录
	 * 
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID($id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
}