<?php

/**
 * 商品绑定店铺model
 * @version v0.01
 */
namespace Product;

use Custom\YDLib;
use Common\CommonBase;
use Product\ProductModel;

class ProductMultiPointModel extends \BaseModel {
	protected static $_tableName = 'product_multi_point';
	
	
	
	/**
	 *  获取绑定信息
	 *
	 * @return mixed
	 */
	public static function getCartProductBy($product_id,$supplier_id) {
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$sql = 'SELECT
        		   a.multi_point_id
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.product_id = ' . $product_id .'
		         AND 
		            a.supplier_id = '.$supplier_id.'
		           ';
		        
		$list = $pdo->YDGetRow ( $sql );
		return $list;
	}
	
	
	
	/**
	 * 获取所有绑定信息
	 *
	 * @return mixed
	 */
	public static function getCartProductAll($multi_point_id, $supplier_id) {
		$pdo = YDLib::getPDO ( 'db_r' );
		$sql = 'SELECT
        		   *
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		         AND
		            a.supplier_id = ' . $supplier_id.'
		         AND
		            a.multi_point_id = '.$multi_point_id.'
		            		';
	
		$list = $pdo->YDGetAll ( $sql );
		return $list;
	}
	
	
	
	
	
	/**
	 * 查询网点绑定数量
	 *
	 * @param $where 条件array
	 * @return num
	 */
	public static function MultipointCount($info) {
		
	
		$sql = "
    			SELECT
    				COUNT(a.id) num
        		FROM
		            " . CommonBase::$_tablePrefix . self::$_tableName . " a
		         LEFT JOIN
		             " . CommonBase::$_tablePrefix . "product b
		        ON
		            a.product_id = b.id
		        WHERE
        		    a.is_del=" . self::DELETE_SUCCESS."
        		 AND 
        		    a.multi_point_id = ".$info['id']."
        		 AND
        		    a.supplier_id = ".$info['supplier_id']."
        		 AND 
        		   b.on_status  = 2
        		 AND
        		   b.is_del = 2
        		 AND 
        		   b.stock > 0 
        		    ";
	
		
		$pdo = self::_pdo ( 'db_r' );
		
		return $pdo->YDGetOne ( $sql );
	}
	
	
	
	
	
}