<?php

/**
 * 退单model
 * @version v0.01
 * @author laiqingtao
 * @time 2018-05-08
 */
namespace Purchase;

use Custom\YDLib;
use Common\SerialNumber;
use Purchase\PurchaseReturnProductModel;
use Purchase\PurchaseModel;
use Purchase\PurchaseOrderChildModel;
use Services\Purchase\PurchaseService;
use Purchase\PurchaseProductModel;
use Common\CommonBase;
use Product\ProductModel;

class PurchaseReturnModel extends \Common\CommonBase {
	/**
	 * 定义表名后缀
	 */
	protected static $_tableName = 'purchase_return';
	
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
	
	/**
	 * 根据表自增 ID删除记录
	 *
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteReturnByID($id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
	
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id )
		) );
	}
	
	
	
	/**
	 * 记录入库
	 *
	 * @param array $data
	 *        	表字段名作为key的数组
	 * @return int 入库成功则返回入库记录的自增ID，否则返回FALSE
	 */
	public static function addReturn($data, $item) {
		$pdo = self::_pdo ( 'db_w' );
		$pdo->beginTransaction ();
		try {
			
			$jsonData = [ ];
			$childInfo = PurchaseOrderChildModel::getChildOrderProductBy ( $data ['order'] ); // print_r($childInfo);die;
			
			if (! is_array ( $childInfo )) {
				$jsonData ['code'] = '500';
				$jsonData ['msg'] = '无采购订单！';
				return $jsonData;
			}
			
				
				$orderInfo = PurchaseModel::getPurchaseById ( $childInfo ['purchase_id'] );
				$Purchase_num = 0;
				foreach ( $childInfo  ['products'] as $k => $v ) {
					
					if ($data ['return_num'] < $v ['num'] && $data ['return_num'] != $v ['num']) {
						$info ['return_status'] = 1; // 1部分退货
						$info ['return_number'] = intval ( $v ['num'] - $data ['return_num'] );
						$Purchase_num = $info ['return_number'];
					} else {
						$info ['return_status'] = 2; // 全部退货
						$info ['return_number'] = $data ['return_num'];
						$Purchase_num = $info ['return_number'];
					}
					
					$productData ['product_id'] = $v ['product_id'];
					$productData ['product_name'] = $v ['product_name'];
					$productData ['self_code'] = $v ['self_code'];
					$productData ['custom_code'] = $v ['custom_code'];
					$productData ['product_cover'] = $v ['product_cover'];
					$productData ['category_name'] = $v ['category_name'];
					$productData ['brand_name'] = $v ['brand_name'];
					$productData ['introduction'] = $v ['introduction'];
					$productData ['return_num'] = $data ['return_num'];
					$productData ['return_price'] = $data ['money'];
					$productData ['return_no'] = $data ['order'];
					$productData ['num'] = $v ['num'];
					$productData ['price'] = $v['price'];
					$productData ['note'] = '';
					$productData ['market_price'] = $v['market_price'];
					$productData ['channel_price'] = $v['channel_price'];
					$productData ['supplier_id'] = $v['supplier_id'];
					$productData ['type'] = '2';
					$productData ['purchase_status'] = $orderInfo ['is_demand'];
					$productInfo = PurchaseReturnProductModel::addData ( $productData );
					if (! $productInfo) {
						$jsonData ['code'] = '500';
						$jsonData ['msg'] = '写入退货商品失败！';
						return $jsonData;
					}
					
					// // 更新采购单商品明细表采购数量和采购小计
					// $returnPrice = bcsub ( $v ['price'], $data ['money'], 2 );
					// $products = PurchaseProductModel::updateNumByChildOrder ( $v ['b_id'], $Purchase_num, $returnPrice );
					// if (! $products) {
					// $jsonData ['code'] = '500';
					// $jsonData ['msg'] = '更新商品数量错误！';
					// return $jsonData;
					// }
					
					// // 取出商品库存
					// $getProduct = ProductModel::getSingleInfoByID ( $v ['product_id'] );
					
					// // 更新商品库存数量
					// $stock ['stock'] = intval ( $getProduct ['stock'] + $info ['return_number'] );
					// $upbteStock = ProductModel::updateStockByID ( $stock , $v ['product_id'] );
				}
				
				$info ['return_no'] = SerialNumber::createSN ( SerialNumber::SN_ORDER_RETURN );
				$info ['supplier_id'] = $childInfo ['supplier_id'];
				$info ['purchase_supplier_id'] = $childInfo ['purchase_supplier_id'];
				$info ['purchase_order_id'] = $childInfo ['order_id'];
				$info ['purchase_child_id'] = $childInfo ['id'];
				$info ['order_no'] = $childInfo ['order_no'];
				$info ['child_order_no'] = $childInfo ['child_order_no'];
				$info ['return_number'] = $data ['return_num'];
				$info ['return_money'] = $data ['money'];
				$info ['order_return_charge_money'] = 0; // 退货运费
				$info ['order_status'] = PurchaseService::PURCHASE_ORDER_STATUS_9; // 退货状态
				$info ['purchase_status'] = $orderInfo ['is_demand']; // 是否需求代购单
				$info ['return_type'] = $data ['role_id'];
				$info ['child_pay_type'] = $childInfo ['child_pay_type'];
				$info ['note'] = $data ['note']; // 备注
				$info ['type'] = '2'; // 来源
				$info ['purchase_id'] = $childInfo ['purchase_id'];
				$addReturnInfo = self::addData ( $info );
				
				if ($addReturnInfo) {
					// 存入图片
					$pdo->update ( 'img', [ 
							'is_del' => '1',
							'deleted_at' => date ( 'Y-m-d h:i:s' ) 
					], [ 
							'obj_id' => $addReturnInfo,
							'type' => 'salesreturn',
							'is_del' => '2' 
					] );
					if (is_array ( $item ) && count ( $item ) > 0) {
						foreach ( $item as $key => $value ) {
							$imgList = [ ];
							$imgList ['supplier_id'] = $data ['supplier_id'];
							$imgList ['img_url'] = $value;
							$imgList ['obj_id'] = $addReturnInfo;
							$imgList ['type'] = 'salesreturn';
							$imgList ['img_type'] = pathinfo ( $value, PATHINFO_EXTENSION );
							$imgList ['is_del'] = 2;
							$lastId = $pdo->insert ( 'img', $imgList, [ 
									'ignore' => true 
							] );
							if (! $lastId) {
								$db->rollback ();
								$jsonData ['code'] = '500';
								$jsonData ['msg'] = '图片写入失败';
								return $jsonData;
							}
						}
					}
					
					// // 更新子单采购数量
					// $childData = PurchaseOrderChildModel::updateNumByChildOrder ( $childInfo [$key] ['child_order_no'], $Purchase_num );
					// if (! $childData) {
					// $jsonData ['code'] = '500';
					// $jsonData ['msg'] = '更新子订单商品数量错误！';
					// return $jsonData;
					// }
					
					// 更新子订单状态
					$updateChildOrder = PurchaseOrderChildModel::updateStatusByChildOrderNo ( $childInfo ['child_order_no'], PurchaseService::PURCHASE_ORDER_STATUS_9 );
					if (! $updateChildOrder) {
						$jsonData ['code'] = '500';
						$jsonData ['msg'] = '更新子订单状态错误！';
						return $jsonData;
					}
				} else {
					$jsonData ['code'] = '500';
					$jsonData ['msg'] = '写入退款订单失败！';
					return $jsonData;
				}
			
			// 写入操作日志
			purchaseTrackingLog ( $childInfo ['purchase_id'], $childInfo ['child_order_no'], '创建', $data ['note'],2);
			
			$pdo->commit ();
			$jsonData ['code'] = '200';
			$jsonData ['msg'] = '订单退货成功';
			return $jsonData;
		} catch ( \Exception $e ) {
			$pdo->rollback ();
			$jsonData ['code'] = '500';
			$jsonData ['msg'] = '订单退货失败';
			return $jsonData;
		}
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
		$result = $db->insert ( self::$_tableName, $info );
		return $result;
	}
	
	
	/**
	 * 根据表自增ID获取该条记录信息
	 * @param int $id 表自增ID
	 */
	public static function getInfoByID($id)
	{
		$where['is_del'] = self::DELETE_SUCCESS;
		$where['id'] = intval($id);
	
		$pdo = self::_pdo('db_r');
		return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
	}
	
	
	
	/**
	 * 通过子订单id获取商品列表
	 * @param $order_child_no
	 * @return array
	 */
	public static function getChildOrderReturnProduct($id)
	{
		$orders = self::getInfoByID($id);
		if($orders){
			$orders['products'] = PurchaseReturnProductModel::getProductByChildOrderNO($orders['child_order_no']);
		}
		return $orders;
	}
	
	
	
	
	/**
	 * 通过子订单号获取单条信息查询ID
	 * @param $order_child_no
	 * @return array
	 */
	public static function getExpressByChildOrderNo($child_order_no){
		$where['is_del'] = self::DELETE_SUCCESS;
		$where['child_order_no'] = $child_order_no;
		$pdo = self::_pdo('db_r');
		return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
	}
	
	
	
	
	/**
	 * 通过子订单号获取商品列表
	 * @param $order_child_no
	 * @return array
	 */
	public static function getReturnOrderProduct($child_order_no)
	{
		$orders = self::getExpressByChildOrderNo($child_order_no);
		if($orders){
			$orders['products'] = PurchaseReturnProductModel::getProductByChildOrderNO($orders['child_order_no']);
		}
		return $orders;
	}
	
	
	
	
	
}