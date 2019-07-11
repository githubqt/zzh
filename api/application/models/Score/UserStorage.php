<?php

/**
 * 用户资产model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-07-03
 */
namespace Score;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;

class UserStorageModel extends \Common\CommonBase {
	protected static $_tableName = 'user_storage';
	
	/**
	 * 添加信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addData($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['supplier_id'] = SUPPLIER_ID;
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
	public static function getInfoById($id,$db=null) {
		$pdo = $db?$db:YDLib::getPDO ( 'db_r' );
		$ret = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( [ 
				'user_id' => $id,
				'is_del' => '2',
				'supplier_id' => SUPPLIER_ID 
		] )->getRow ();
		
		return $ret ? $ret : [ ];
	}
	
	/**
	 * 根据id获取
	 * 
	 * @param array $id        	
	 * @return array
	 */
	public static function getAll() {
		$pdo = YDLib::getPDO ( 'db_r' );
		
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['type'] = '2';
		$info ['status'] = '2';
		$info ['is_del'] = '2';
		$ret = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $info )->getAll ();
		
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