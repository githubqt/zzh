<?php

/**
 * 商品库存日志权限model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-11
 */
namespace Product;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
use Product\ProductModel;

class ProductStockLogModel extends \BaseModel {
	protected static $_tableName = 'product_stock_log';
	
	/**
	 * 添加日志
	 * 
	 * @param int $product_id
	 *        	商品id
	 * @param int $num
	 *        	变动数量（减库存为负数）
	 * @return mixed
	 */
	public static function addLog($product_id, $type, $stock_change = 0, $lock_stock_change = 0, $admin_id = 0, $admin_name = '系统') {
		$product = ProductModel::getSingleInfoByID ( $product_id );
		$info = [ ];
		$info ['supplier_id'] = $product ['supplier_id'];
		$info ['product_id'] = $product_id;
		$info ['product_name'] = $product ['name'];
		$info ['stock_old'] = $product ['stock'];
		$info ['lock_stock_old'] = $product ['lock_stock'];
		$info ['stock_change'] = $stock_change;
		$info ['lock_stock_change'] = $lock_stock_change;
		$info ['stock_new'] = bcadd ( $product ['stock'], $stock_change );
		$info ['lock_stock_new'] = bcadd ( $product ['lock_stock'], $lock_stock_change );
		$info ['type'] = $type;
		$info ['note'] = \Services\Stock\StockService::LOG_TYPE [$type];
		$info ['admin_id'] = $admin_id;
		$info ['admin_name'] = $admin_name;
		$info ['is_del'] = self::DELETE_SUCCESS;
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		$result = $pdo->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/**
	 * 记录入库
	 * 
	 * @param array $data
	 *        	表字段名作为key的数组
	 * @return int 入库成功则返回入库记录的自增ID，否则返回FALSE
	 */
	public static function addData($data) {
		$data ['is_del'] = self::DELETE_SUCCESS;
		$data ['created_at'] = date ( "Y-m-d H:i:s" );
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->insert ( self::$_tableName, $data );
	}
}