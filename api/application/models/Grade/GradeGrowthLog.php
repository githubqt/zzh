<?php

/**
 * 成长值日志model
 * @time 2018-05-14
 */
namespace Grade;

use Custom\YDLib;
use Common\CommonBase;

class GradeGrowthLogModel extends \Common\CommonBase {
	protected static $_tableName = 'user_growth_log';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
	}
	
	/**
	 * 根据一条自增ID更新表记录
	 *
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param integer $user_id
	 *        	用户ID
	 * @param integer $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateByID($data, $id, $supplier_id) {
		$pdo = YDLib::getPDO ( 'db_w' );
		
		if ($supplier_id) {
			$rights = array ();
			$rights ['updated_at'] = date ( "Y-m-d H:i:s" );
			$right = $pdo->update ( self::$_tableName, $rights, array (
					'id' => intval ( $id ),
					'supplier_id' => $supplier_id,
					'is_del' => '2' 
			) );
			
			return $right;
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
	public static function deleteByID($id, $supplier_id) {
		$pdo = self::_pdo ( 'db_w' );
		$supdate = $pdo->delete ( self::$_tableName, array (
				'id' => intval ( $id ),
				'supplier_id' => $supplier_id,
				'is_del' => '2' 
		) );
		
		return $supdate;
	}
	
	/**
	 * 添加信息
	 *
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addData($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['is_del'] = self::DELETE_SUCCESS;
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
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
		
		$pdo = self::_pdo ( 'db_r' );
		
		$uers = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		return $uers;
	}
}