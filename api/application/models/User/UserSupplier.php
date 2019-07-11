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

class UserSupplierModel extends \BaseModel {
	protected static $_tableName = 'user_supplier';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
	}
	
	/**
	 * 验证账户是否存在
	 * 
	 * @param int $uid        	
	 * @return boolean|string
	 */
	public static function checkUserId($name) {
		if (empty ( $name ))
			return false;
		
		$pdo = self::_pdo ( 'db_r' );
		$user = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( [ 
				'name' => $name,
				'status' => '2',
				'is_del' => '2' 
		] )->getRow ();
		
		if (! $user) {
			return false;
		} else {
			return $user;
		}
	}
	
	/**
	 * 获取用户信息
	 * 
	 * @param unknown $UserId        	
	 * @param number $headImgSize        	
	 * @return multitype:|Ambigous <unknown, string>
	 */
	public static function getAdminInfo($UserId) {
		if (! $UserId)
			return [ ];

			$pdo = YDLib::getPDO ( 'db_r' );
			$user = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( [ 
					'user_id' => $UserId,
					'supplier_id' => SUPPLIER_ID,
					'is_del' => '2' 
			] )->getRow ();

            //$mem = YDLib::getMem ( 'memcache' );
			//$mem->delete ( 'user_' . SUPPLIER_ID . '_' . session_id () . '_info_' . $UserId );
			//$mem->set ( 'user_' . SUPPLIER_ID . '_' . session_id () . '_info_' . $UserId, $user );
		
		
		return $user;
	}
	
	/**
	 * 获取单条数据
	 *
	 * @param interger $user_id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByUserID($user_id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['user_id'] = $user_id;
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	/**
	 * 添加信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addUser($user_id) {
		$db = YDLib::getPDO ( 'db_w' );
		
		$info ['user_id'] = $user_id;
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['is_del'] = '2';
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$result = $db->insert ( self::$_tableName, $info );
		
		$mem = $mem ? $mem : YDLib::getMem ( 'memcache' );
		$mem->delete ( 'user_' . SUPPLIER_ID . '_' . session_id () . '_info_' . $user_id );
		
		return $result;
	}
	
	/**
	 * 更新会员首次消费时间
	 * 
	 * @param array $user_id        	
	 * @return boolean 更新结果
	 */
	public static function updateInfo($user_id, $order_actual_amount) {
		// 首次消费时间
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['user_id'] = $user_id;
		
		$pdo = self::_pdo ( 'db_r' );
		
		$info = $pdo->clear ()->select ( 'id,first_pay_at,last_pay_at' )->from ( self::$_tableName )->where ( $where )->getRow ();
		
		$updata ['last_pay_at'] = date ( "Y-m-d H:i:s" );
		$updata ['last_pay_amount'] = $order_actual_amount;
		if (empty ( $info ['first_pay_at'] )) {
			$updata ['first_pay_at'] = date ( "Y-m-d H:i:s" );
			$updata ['first_pay_amount'] = $order_actual_amount;
		}
		$pdo = self::_pdo ( 'db_w' );
		$res = $pdo->update ( self::$_tableName, $updata, array (
				'id' => $info ['id'] 
		) );
		
		return $res;
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
	 * 根据一条自增ID更新表记录
	 * 
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $user_id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateByUserID($data, $user_id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );

        $mem = $mem ? $mem : YDLib::getMem ( 'memcache' );
        $mem->delete ( 'user_' . SUPPLIER_ID . '_' . session_id () . '_info_' . $user_id );

		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'user_id' => intval ( $user_id ),
				'supplier_id' => SUPPLIER_ID 
		) );
	}
	
	/**
	 * 字段自更新
	 * 
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $user_id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function autoUpdateByUserID($data, $user_id) {
		$sql = "UPDATE " . self::getTb () . " SET ";
		foreach ( $data as $key => $val ) {
			if ($val > 0)
				$val = '+' . $val;
			$sql .= "`{$key}` = (`{$key}` {$val}),";
		}
		$sql = substr ( $sql, 0, - 1 );
		
		$sql .= " WHERE user_id = " . $user_id . " AND supplier_id = " . SUPPLIER_ID;
		
		$pdo = self::_pdo ( 'db_w' );
		
		return $pdo->YDExecute ( $sql );
	}
	
	/**
	 * 根据表自增 ID删除记录
	 * 
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID($user_id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		
		$mem = $mem ? $mem : YDLib::getMem ( 'memcache' );
		$mem->delete ( 'user_' . SUPPLIER_ID . '_' . session_id () . '_info_' . $user_id );
		
		return $pdo->update ( self::$_tableName, $data, array (
				'user_id' => intval ( $user_id ),
				'supplier_id' => SUPPLIER_ID 
		) );
	}
}