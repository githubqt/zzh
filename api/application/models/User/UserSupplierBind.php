<?php

/**
 * 用户商户三方绑定model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-11
 */
namespace User;

use Custom\YDLib;
use Common\CommonBase;

class UserSupplierBindModel extends \Common\CommonBase {
	protected static $_tableName = 'user_supplier_bind';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
	}
	
	/**
	 * 根据表自增ID获取该条记录信息
	 * 
	 * @param int $id
	 *        	表自增ID
	 */
	public static function getInfoByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		$where ['supplier_id'] = SUPPLIER_ID;
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	/**
	 * 根据表自增ID获取该条记录信息
	 * 
	 * @param int $id
	 *        	表自增ID
	 */
	public static function getInfo($where) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['supplier_id'] = SUPPLIER_ID;
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	/**
	 * 添加信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addData($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['is_del'] = '2';
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$info ['supplier_id'] = SUPPLIER_ID;
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
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
		$up = $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
		return $up;
	}
	
	
	
	/**
	 * 根据表自增 ID删除记录
	 *
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByUserID($user_id, $type = '1') {
	    $data ['is_del'] = self::DELETE_FAIL;
	    $data ['updated_at'] = date ( "Y-m-d H:i:s" );
	    $data ['deleted_at'] = date ( "Y-m-d H:i:s" );
	
	    $pdo = self::_pdo ( 'db_w' );
	
	    return $pdo->update ( self::$_tableName, $data, array (
	        'user_id' => intval ( $user_id ),
	        'supplier_id' => SUPPLIER_ID,
	        'type' => $type
	    ) );
	}
	
	
}