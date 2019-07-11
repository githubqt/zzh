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

class ScoreRuleModel extends \Common\CommonBase {
	protected static $_tableName = 'score_rule';
	
	/* 获取列表 */
	public static function getList($attribute = array(), $page = 0, $rows = 10) {
		$limit = ($page) * $rows;
		if (! empty ( $attribute ['info'] ) && is_array ( $attribute ['info'] ) && count ( $attribute ['info'] ) > 0) {
			extract ( $attribute ['info'] );
		}
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = " a.* ";
		$sql = 'SELECT 
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        WHERE
        		    a.is_del = 2
		        AND 
	                 a.supplier_id=' . SUPPLIER_ID;
		
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		$sql .= " limit {$limit},{$rows}";
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * 添加
	 *
	 * @param array $info
	 *        	规则主要信息
	 * @param array $product
	 *        	商品信息
	 * @return mixed
	 *
	 */
	public static function addData($info, $product) {
		$adminId = AdminModel::getAdminID ();
		$adminInfo = AdminModel::getAdminLoginInfo ( $adminId );
		$db = YDLib::getPDO ( 'db_w' );
		$db->beginTransaction ();
		try {
			// $info['admin_id'] = $adminId;
			// $info['admin_name'] = $adminInfo['fullname'];
			$info ['supplier_id'] = $adminInfo ['supplier_id'];
			$info ['is_del'] = '2';
			$lastId = $db->insert ( self::$_tableName, $info, [ 
					'ignore' => true 
			] );
			if ($lastId == false) {
				$db->rollback ();
				return FALSE;
			}
			if ($info ['product_type'] == '1' && is_array ( $product ) && count ( $product ) > 0) {
				foreach ( $product as $key => $product_id ) {
					$product_info = ProductModel::getSingleInfoByID ( $product_id );
					$productList = [ ];
					$productList ['score_rule_id'] = $lastId;
					$productList ['product_id'] = $product_info ['id'];
					$productList ['product_name'] = $product_info ['name'];
					$productList ['peoduct_self_code'] = $product_info ['self_code'];
					$productList ['supplier_id'] = $adminInfo ['supplier_id'];
					$productList ['is_del'] = 2;
					$productId = $db->insert ( 'score_rule_product', $productList, [ 
							'ignore' => true 
					] );
					if (! $productId) {
						$db->rollback ();
						return FALSE;
					}
				}
			}
			
			$db->commit ();
			return $lastId;
		} catch ( \Exception $e ) {
			$db->rollback ();
			return FALSE;
		}
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
	 * 根据id获取
	 * 
	 * @param array $id        	
	 * @return array
	 */
	public static function getAll() {
		$pdo = YDLib::getPDO ( 'db_r' );
		
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['is_del'] = '2';
		$ret = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $info )->getAll ();
		
		return $ret ? $ret : [ ];
	}
	
	/**
	 * 编辑
	 *
	 * @param array $info
	 *        	规则主要信息
	 * @param array $product
	 *        	商品信息
	 * @return mixed
	 *
	 */
	public static function editData($info, $product, $id) {
		$adminId = AdminModel::getAdminID ();
		$adminInfo = AdminModel::getAdminLoginInfo ( $adminId );
		$db = YDLib::getPDO ( 'db_w' );
		$db->beginTransaction ();
		try {
			$info ['updated_at'] = date ( 'Y-m-d H:i:s' );
			$lastId = $db->update ( self::$_tableName, $info, array (
					'id' => intval ( $id ) 
			) );
			if ($lastId == false) {
				$db->rollback ();
				return FALSE;
			}
			// 删除原规则
			ScoreRuleProductModel::deleteByRuleID ( $id );
			if ($info ['product_type'] == '1' && is_array ( $product ) && count ( $product ) > 0) {
				foreach ( $product as $key => $product_id ) {
					$product_info = ProductModel::getSingleInfoByID ( $product_id );
					$productList = [ ];
					$productList ['score_rule_id'] = $id;
					$productList ['product_id'] = $product_info ['id'];
					$productList ['product_name'] = $product_info ['name'];
					$productList ['peoduct_self_code'] = $product_info ['self_code'];
					$productList ['supplier_id'] = $adminInfo ['supplier_id'];
					$productList ['is_del'] = 2;
					$productId = $db->insert ( 'score_rule_product', $productList, [ 
							'ignore' => true 
					] );
					if (! $productId) {
						$db->rollback ();
						return FALSE;
					}
				}
			}
			
			$db->commit ();
			return $lastId;
		} catch ( \Exception $e ) {
			$db->rollback ();
			return FALSE;
		}
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