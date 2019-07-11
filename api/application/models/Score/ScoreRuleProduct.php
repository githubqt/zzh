<?php

/**
 * 角色model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-07-2
 */
namespace Score;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
use Product\ProductModel;

class ScoreRuleProductModel extends \Common\CommonBase {
	protected static $_tableName = 'score_rule_product';
	
	/**
	 * 添加信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addRole($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['supplier_id'] = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () ) ['supplier_id'];
		$info ['type'] = '2';
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
	public static function getInfoByProductId($produt_id, $score_rule_id = '') {
		$pdo = YDLib::getPDO ( 'db_r' );
		
		$sql = 'SELECT
        		   *
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND 
		            a.supplier_id = "' . AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () ) ['supplier_id'] . '"
		        AND
		            a.product_id = "' . $produt_id . '"
        		';
		if ($score_rule_id) {
			$sql .= " AND a.score_rule_id !=" . $score_rule_id;
		}
		
		$ret = $pdo->YDGetAll ( $sql );
		
		return $ret ? $ret : [ ];
	}
	
	/**
	 * 根据id获取
	 * 
	 * @param array $id        	
	 * @return array
	 */
	public static function getinfoByRuleID($rule_id, $supplier_id) {
		$pdo = YDLib::getPDO ( 'db_r' );
		
		$info ['supplier_id'] = $supplier_id;
		$info ['score_rule_id'] = $rule_id;
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
	 * 根据表规则 ID删除记录
	 * 
	 * @param int $score_rule_id
	 *        	表规则 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByRuleID($score_rule_id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'score_rule_id' => intval ( $score_rule_id ) 
		) );
	}
}