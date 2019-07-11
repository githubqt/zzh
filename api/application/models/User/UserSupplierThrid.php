<?php

/**
 * 用户商户model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-11
 */
namespace User;

use Custom\YDLib;
use Common\CommonBase;

class UserSupplierThridModel extends \Common\CommonBase {
	protected static $_tableName = 'user_supplier_thrid';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
	}
	
	/**
	 * 验证账户是否存在
	 * 
	 * @param int $userId
	 *        	用户ID
	 * @return boolean|string
	 */
	public static function getInfoByUserId($userId, $thirdparty = 'wechat') {
		if (empty ( $userId ))
			return false;
		
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['user_id'] = $userId;
		$where ['thirdparty'] = $thirdparty;
		
		$pdo = self::_pdo ( 'db_r' );
		$user = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		
		if (! $user) {
			return false;
		} else {
			return $user;
		}
	}
	
	/**
	 * 根据其他字段验证是否存在
	 * 
	 * @param int $where
	 *        	其他字段
	 * @return boolean|string
	 */
	public static function getInfoByOtherId($where, $thirdparty = 'wechat') {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['thirdparty'] = $thirdparty;
		
		$pdo = self::_pdo ( 'db_r' );
		$user = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		
		if (! $user) {
			return false;
		} else {
			
			// 获取本商户是否有该用户信息
			$supplier_user = $pdo->clear ()->select ( '*' )->from ( 'user_supplier' )->where ( [
					'user_id' => $user ['id'],
					'supplier_id' => SUPPLIER_ID,
					'is_del' => '2'
			] )->getRow ();
			$user ['supplier'] = $supplier_user;
			
			return $user;
		}
	}
	
	/**
	 * 添加信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addUser($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$db->beginTransaction ();
		try {
			$info ['is_del'] = '2';
			$info ['supplier_id'] = SUPPLIER_ID;
			$info ['created_at'] = date ( "Y-m-d H:i:s" );
			$info ['updated_at'] = date ( "Y-m-d H:i:s" );
			
			$last_id = $db->insert ( self::$_tableName, $info );
			
			$db->commit ();
			return $last_id;
		} catch ( \Exception $e ) {
			$db->rollback ();
			return FALSE;
		}
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
	public static function updateByID($data, $id, $thirdparty = 'wechat') {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		$update = $pdo->update ( self::$_tableName, $data, array (
				'user_id' => intval ( $id ),
				'supplier_id' => SUPPLIER_ID,
				'thirdparty' => $thirdparty 
		) );
		if ($update) {
			return $update;
		}
		return false;
	}
	
	/**
	 * 根据表自增 ID删除记录
	 * 
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByUserID($user_id, $thirdparty = 'wechat') {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		
		return $pdo->update ( self::$_tableName, $data, array (
				'user_id' => intval ( $user_id ),
				'supplier_id' => SUPPLIER_ID,
				'thirdparty' => $thirdparty 
		) );
	}
}