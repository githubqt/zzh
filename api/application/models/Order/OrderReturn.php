<?php

/**
 * 退单model
 * @version v0.01
 * @author laiqingtao
 * @time 2018-05-08
 */
namespace Order;

use Custom\YDLib;
use Order\OrderChildModel;
use User\UserModel;
use Common\SerialNumber;
use Order\OrderChildProductModel;
use Order\OrderReturnProductModel;
use Image\ImageModel;
use ErrnoStatus;
use Services\Msg\MsgService;
use Supplier\SupplierModel;

class OrderReturnModel extends \Common\CommonBase {
	/**
	 * 定义表名后缀
	 */
	protected static $_tableName = 'order_return';
	/**
	 * 退货单
	 */
	const ORDER_RETURN_TYPE_BACK = 1;
	/**
	 * 换货单
	 */
	const ORDER_RETURN_TYPE_CHANGE = 2;
	protected static $return_status = [ 
			'10' => '待审核',
			'11' => '客服驳回',
			'12' => '已取消',
			'20' => '待发货',
			'30' => '待收货',
			'31' => '待质检',
			'32' => '待入库',
			'40' => '待退款',
			'50' => '退货完成',
			'60' => '退款驳回' 
	];
	protected static $return_type = [ 
			'1' => '退货单',
			'2' => '换货单' 
	];
	
	/**
	 * 待审核
	 */
	const ORDER_RETURN_STATUS_AUDIT = 10;
	
	/**
	 * 驳回(备注)
	 */
	const ORDER_RETURN_STATUS_AUDIT_REJECT = 11;
	
	/**
	 * 用户取消售后
	 */
	const ORDER_RETURN_STATUS_AUDIT_REJECT_USER = 12;
	
	/**
	 * 售后发货(取件)
	 */
	const ORDER_RETURN_STATUS_AUDIT_PICKUP = 20;
	
	/**
	 * 待收货
	 */
	const ORDER_RETURN_STATUS_RECEIPT_GOODS = 30;
	
	/**
	 * 待质检
	 */
	const ORDER_RETURN_STATUS_QUALITY_CONTROL = 31;
	
	/**
	 * 待入库
	 */
	const ORDER_RETURN_STATUS_STORAGE = 32;
	
	/**
	 * 待退款
	 */
	const ORDER_RETURN_STATUS_RETURN_GOODS = 40;
	
	/**
	 * 售后完成
	 */
	const ORDER_RETURN_STATUS_SUCCESS = 50;
	
	/**
	 * 退款驳回
	 */
	const ORDER_RETURN_STATUS_REFUND_REJECT = 60;
	
	/**
	 * 记录入库
	 * 
	 * @param array $data
	 *        	表字段名作为key的数组
	 * @return int 入库成功则返回入库记录的自增ID，否则返回FALSE
	 */
	public static function addReturn($data) {
		$pdo = self::_pdo ( 'db_w' );
		
		$childInfo = OrderChildModel::getInfoByID ( $data ['order_child_id'] ); 
		if (! is_array ( $childInfo ) || $childInfo ['child_status'] != '60' || $childInfo ['is_after_sales'] == '2')
			return FALSE;
		
		$adminInfo = UserModel::getAdminInfo ( $data ['user_id'] );
		$info ['return_order_no'] = SerialNumber::createSN ( SerialNumber::SN_ORDER_RETURN );
		$info ['order_id'] = $childInfo ['order_id'];
		$info ['order_no'] = $childInfo ['order_no'];
		$info ['child_order_id'] = $childInfo ['id'];
		$info ['child_order_no'] = $childInfo ['child_order_no'];
		$info ['child_order_actual_amount'] = $childInfo ['child_order_actual_amount'];
		$info ['user_id'] = $childInfo ['user_id'];
		$info ['status'] = self::ORDER_RETURN_STATUS_AUDIT;
		$info ['type'] = self::ORDER_RETURN_TYPE_BACK;
		$info ['user_name'] = $adminInfo ['name'];
		$info ['mobile'] = $adminInfo ['mobile'];
		$info ['note'] = $data ['note'];
		$info ['supplier_id'] = SUPPLIER_ID;
		
		/* 退货详情数据 */
		$num = 0;
		$product = [ ];
		$childProduct = OrderChildProductModel::getInfoBySkuIDAndChild_order_id ( $data ['order_child_id'], $data ['product_id'] );
		if ($childProduct ['sale_num'] < $data ['num']) {
			YDLib::output ( ErrnoStatus::STATUS_60564 );
		}
		if (! is_array ( $childProduct ) || $childProduct ['is_after_sales'] == self::SERVICE_CUSTOMER)
			return FALSE;
		$product ['child_order_id'] = $childProduct ['child_order_id'];
		$product ['child_order_no'] = $childProduct ['child_order_no'];
		$product ['product_id'] = $childProduct ['product_id'];
		$product ['self_code'] = $childProduct ['self_code'];
		$product ['product_name'] = $childProduct ['product_name'];
		$product ['num'] = $data ['num'];
		$num = $num + $data ['num'];
		//去除优惠券后的实际单价（总价-优惠金额）/数量
		$product_one_price = bcdiv(bcsub($childProduct['actual_amount'],$childProduct['discount_amount'], 2),$childProduct['sale_num'],2);
		//实际单价*退货数量
		$product_price = bcmul ( $product_one_price, $data ['num'], 2 );
		
		$info ['back_money'] = $product_price;
		$info ['num'] = $num;
		
		$pdo->beginTransaction ();
		try {
			
			$orderId = self::addData ( $info );
			if ($orderId == FALSE) {
				$pdo->rollback ();
				YDLib::output ( ErrnoStatus::STATUS_60552 );
			}
			
			/* 订单退货明细表 */
			$product ['order_return_id'] = $orderId;
			$childId = OrderReturnProductModel::addData ( $product );
			if ($childId == FALSE) {
				$pdo->rollback ();
				YDLib::output ( ErrnoStatus::STATUS_60553 );
			}
			$resProduct = OrderChildProductModel::updateByID ( [ 
					'is_after_sales' => self::SERVICE_CUSTOMER,
					'return_order_id' => $orderId 
			], $childProduct ['id'] );
			
			if ($resProduct == FALSE) {
				$pdo->rollback ();
				YDLib::output ( ErrnoStatus::STATUS_60553 );
			}
			
			// 更新子订单表状态
			$edit = OrderChildModel::updateByID ( [ 
					'is_after_sales' => self::SERVICE_CUSTOMER 
			], $data ['order_child_id'] );
			if ($edit == FALSE) {
				$pdo->rollback ();
				YDLib::output ( ErrnoStatus::STATUS_60553 );
			}
			if (is_array ( $data ['items'] ) && count ( $data ['items'] ) > 0) {
				foreach ( $data ['items'] as $key => $value ) {
					$imgItems = [ ];
					$imgItems ['supplier_id'] = SUPPLIER_ID;
					$imgItems ['obj_id'] = $orderId;
					$imgItems ['img_url'] = $value['url'];
					$imgItems ['type'] = 'order_return';
					$imgItems ['img_type'] = substr ( $value['url'], strrpos ( $value['url'], '.' ) + 1 );
					$childImgId = ImageModel::addData ( $imgItems );
					if ($childImgId == FALSE) {
						$pdo->rollback ();
						YDLib::output ( ErrnoStatus::STATUS_60553 );
					}
				}
			}
			
			$pdo->commit ();
			
			$suppplier_detail = SupplierModel::getInfoByID(SUPPLIER_ID);
			$user_info = UserModel::getAdminInfo($data['user_id']);
			$msgData = [
					'params' => [
							'0' => $user_info['name'],
							'1' =>$childInfo ['order_no'],
					]
			];
				
			MsgService::fireMsg('6', $suppplier_detail ['mobile'], 0,$msgData);
			
			return TRUE;
		} catch ( \Exception $e ) {
			$pdo->rollback ();
			YDLib::output ( ErrnoStatus::STATUS_60505 );
		}
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
				'id' => intval ( $id ),
				'supplier_id' => SUPPLIER_ID 
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
	 * 获取对应的list列表
	 * 
	 * @param array $attribute
	 *        	获取对应的参数
	 * @param integer $page
	 *        	对应的页
	 * @param integer $rows
	 *        	取出的行数
	 * @return array
	 */
	public static function getList($user_id, $page = 1, $rows = 10) {
		$limit = ($page - 1) * $rows;
        $fields = "*";
		$sql = "SELECT 
        		    [*] 
        		FROM
		            " . self::$_tablePrefix . self::$_tableName . "
		        WHERE
        		    is_del=" . self::DELETE_SUCCESS . "
        		AND 
        		    user_id=" . $user_id . " AND supplier_id=" . SUPPLIER_ID;
		
		$pdo = self::_pdo ( 'db_r' );
		$sort = isset ( $sort ) ? $sort : 'id';
		$order = isset ( $order ) ? $order : 'DESC';
		$items = [ ];
		$items ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		$sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
		$returnList = $pdo->YDGetAll ( str_replace ( '[*]', $fields, $sql ) );
		$item = [ ];
		if ($returnList) {
			foreach ( $returnList as $key => $order ) {
				$item [$key] ['id'] = $order ['id']; // 售后单Id
				$item [$key] ['order_no'] = $order ['return_order_no'];
				$item [$key] ['user_id'] = $order ['user_id'];
				$item [$key] ['child_order_actual_amount'] = $order ['child_order_actual_amount']; // 子订单实际支付金额
				$item [$key] ['back_money'] = $order ['back_money']; // 退款金额
				$item [$key] ['child_status'] = $order ['status'];
				$item [$key] ['child_status_name'] = self::$return_status [$order ['status']];
				$item [$key] ['type'] = self::$return_type [$order ['type']];
				$item [$key] ['num'] = $order ['num'];
				
				$product_list = OrderReturnProductModel::getInfoByReturnID ( $order ['id'] );
				
				foreach ( $product_list as $k => $goods ) {
					
					$item [$key] ['product_list'] [$k] ['product_name'] = $goods ['product_name'];
					$item [$key] ['product_list'] [$k] ['product_id'] = $goods ['product_id'];
					$item [$key] ['product_list'] [$k] ['sale_num'] = $goods ['num'];
					$sku = OrderChildProductModel::getInfoBySkuIDAndChild_order_id ( $goods ['child_order_id'], $goods ['product_id'] );
					$item [$key] ['product_list'] [$k] ['sale_price'] = $sku ['sale_price'];
					$item [$key] ['product_list'] [$k] ['market_price'] = $sku ['market_price'];
					// 列表图
					if (! empty ( $sku ['logo_url'] )) {
						$item [$key] ['product_list'] [$k] ['logo_url'] = HOST_FILE . self::imgSize ( $sku ['logo_url'], 6 );
					} else {
						$item [$key] ['product_list'] [$k] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
					}
				}
			}
		}
		$items ['list'] = $item;
		
		return $items;
	}
	
	/**
	 * 填写物流单号接口
	 * 
	 * @param $order_return_id 退货单id        	
	 * @param $data 物流信息        	
	 * @return array
	 */
	public static function setExpressInfo($order_return_id, $data) {
		$order = self::getInfoByID ( $order_return_id );
		if (! $order || $order ['status'] != self::ORDER_RETURN_STATUS_AUDIT_PICKUP) {
			YDLib::output ( ErrnoStatus::STATUS_60080 );
		}
		$info = [ ];
		$pdo = self::_pdo ( 'db_w' );
		$pdo->beginTransaction ();
		try {
			$info ['express_company'] = $data ['express_name'];
			// $info['mobile'] = $data['mobile'];
			$info ['express_no'] = $data ['express_num'];
			$info ['express_note'] = $data ['express_note'];
			$info ['status'] = self::ORDER_RETURN_STATUS_RECEIPT_GOODS; // 更改状态为待收货
			$orderId = self::updateByID ( $info, $order_return_id );
			if ($orderId == false) {
				$pdo->rollback ();
				return false;
			}
			
			$pdo->commit ();
			$suppplier_detail = SupplierModel::getInfoByID(SUPPLIER_ID);
			$user_info = UserModel::getAdminInfo($data['user_id']);
			$msgData = [
					'params' => [
							'0' => $user_info['name'],
							'1' =>$order ['child_order_no'],
					]
			];
			
			MsgService::fireMsg('7', $suppplier_detail ['mobile'], 0,$msgData);
			
			return TRUE;
		} catch ( \Exception $e ) {
			$pdo->rollback ();
			YDLib::output ( ErrnoStatus::STATUS_60076 );
		}
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
	
	/**
	 * 查询订单数
	 * 
	 * @return num
	 */
	public static function count() {
		$sql = "
    			SELECT
    				COUNT(id) num
        		FROM
		            " . self::getTb () . "
		        WHERE
        		    supplier_id = " . SUPPLIER_ID . "
				AND
					to_days(created_at) = to_days(now()) ";
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->YDGetOne ( $sql );
	}
	
	/**
	 * 获取对应的info
	 * 
	 * @param array $attribute
	 *        	获取对应的参数
	 * @param integer $page
	 *        	对应的页
	 * @param integer $rows
	 *        	取出的行数
	 * @return array
	 */
	public static function getAllById($return_id) {
        $fields = "*";
		$sql = "SELECT
        		    [*]
        		FROM
		            " . self::$_tablePrefix . self::$_tableName . "
		        WHERE
        		    is_del=" . self::DELETE_SUCCESS . "
        		AND
        		    id=" . $return_id . " AND supplier_id=" . SUPPLIER_ID;
		
		$pdo = self::_pdo ( 'db_r' );
		
		$returnList = $pdo->YDGetRow ( str_replace ( '[*]', $fields, $sql ) );
		$item = [ ];
		if ($returnList) {
			$item ['id'] = $returnList ['id']; // 售后单Id
			$item ['order_no'] = $returnList ['return_order_no'];
			$item ['user_id'] = $returnList ['user_id'];
			$item ['child_order_actual_amount'] = $returnList ['child_order_actual_amount']; // 子订单实际支付金额
			$item ['back_money'] = $returnList ['back_money']; // 退款金额
			$item ['child_status'] = $returnList ['status'];
			$item ['child_status_name'] = self::$return_status [$returnList ['status']];
			$item ['type'] = self::$return_type [$returnList ['type']];
			$item ['num'] = $returnList ['num'];
			$item ['note'] = $returnList ['note'];
			$item ['express_company'] = $returnList ['express_company'];
			$item ['express_no'] = $returnList ['express_no'];
			$item ['express_note'] = $returnList ['express_note'];
			$item ['img'] = ImageModel::getInfoByTypeOrID ( $returnList ['id'], 'order_return' );
			foreach ( $item ['img'] as $k => $val ) {
				if (! empty ( $val ['img_url'] )) {
					$item ['img'] [$k] ['img_url'] = HOST_FILE . self::imgSize ( $val ['img_url'], 4 );
				} else {
					$item ['img'] [$k] ['img_url'] = HOST_STATIC . 'common/images/common.png';
				}
			}
			$product_list = OrderReturnProductModel::getInfoByReturnID ( $returnList ['id'] );
			
			foreach ( $product_list as $k => $goods ) {
				
				$item ['product_list'] [$k] ['product_name'] = $goods ['product_name'];
				$item ['product_list'] [$k] ['product_id'] = $goods ['product_id'];
				$item ['product_list'] [$k] ['sale_num'] = $goods ['num'];
				$sku = OrderChildProductModel::getInfoBySkuIDAndChild_order_id ( $goods ['child_order_id'], $goods ['product_id'] );
				$item ['product_list'] [$k] ['sale_price'] = $sku ['sale_price'];
				$item ['product_list'] [$k] ['market_price'] = $sku ['market_price'];
				// 列表图
				if (! empty ( $sku ['logo_url'] )) {
					$item ['product_list'] [$k] ['logo_url'] = HOST_FILE . self::imgSize ( $sku ['logo_url'], 2 );
				} else {
					$item ['product_list'] [$k] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
				}
			}
		}
		
		return $item;
	}
}