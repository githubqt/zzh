<?php

/**
 * 属性权限model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-09
 */
namespace Attribute;

use Custom\YDLib;
use Common\CommonBase;

class AttributeValueModel extends \Common\CommonBase {
	protected static $_tableName = 'attribute_value';
	
	/**
	 * 添加权限信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addData($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['is_del'] = '2';
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/**
	 * 根据属性id获取
	 * 
	 * @param array $attribute_id        	
	 * @return array
	 */
	public static function getInfoByAttributeId($attribute_id) {
		$pdo = YDLib::getPDO ( 'db_r' );
		$ret = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( [ 
				'attribute_id' => $attribute_id,
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
	public static function deleteByAttributeID($role_id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'attribute_id' => intval ( $role_id ) 
		) );
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