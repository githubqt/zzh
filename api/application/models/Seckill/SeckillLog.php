<?php

/**
 * 活动商品model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-05
 */
namespace Seckill;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
use Payment\PaymentModel;
use Payment\PaymentTransactionModel;
use Product\ProductChannelModel;
use Seckill\SeckillOrderModel;
use Order\OrderModel;
use Order\OrderChildModel;
use Services\Stock\OrderStockService;
use Services\Stock\VoidStockService;
use User\UserModel;
use Product\ProductModel;
use Order\OrderChildProductModel;
use Product\ProductStockLogModel;
use Services\Msg\MsgService;

class SeckillLogModel extends \BaseModel {
	protected static $_tableName = 'seckill_log';
	
	/**
	 * 添加信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addData($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['is_del'] = '2';
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/* 获取列表 */
	public static function getList($attribute = array(), $page = 0, $rows = 10) {
		$limit = ($page) * $rows;
		if (! empty ( $attribute ['info'] ) && is_array ( $attribute ['info'] ) && count ( $attribute ['info'] ) > 0) {
			extract ( $attribute ['info'] );
		}
		$adminInfo = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () );
		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = " a.*,b.mobile user_mobile,b.name user_name";
		$sql = 'SELECT 
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
        		LEFT JOIN
		             ' . CommonBase::$_tablePrefix . 'user b 		             
		        ON
        		    a.user_id = b.id
		        WHERE
        		    a.is_del = 2
		        AND
		            a.supplier_id = ' . $adminInfo ['supplier_id'] . '
        		';
		
		if (isset ( $seckill_id ) && ! empty ( $seckill_id )) {
			$sql .= " AND a.seckill_id = '" . $seckill_id . "' ";
		}
		if (isset ( $user_name ) && ! empty ( $user_name )) {
			$sql .= " AND b.name like '%" . $user_name . "%' ";
		}
		if (isset ( $user_mobile ) && ! empty ( $user_mobile )) {
			$sql .= " AND b.mobile like '%" . $user_mobile . "%' ";
		}
		if (isset ( $level ) && ! empty ( $level )) {
			$sql .= " AND a.level = '" . $level . "' ";
		}
		if (isset ( $prize_type ) && ! empty ( $prize_type )) {
			$sql .= " AND a.prize_type = '" . $prize_type . "' ";
		}
		if (isset ( $status ) && ! empty ( $status )) {
			$sql .= " AND a.status = '" . $status . "' ";
		}
		if (isset ( $is_prize ) && ! empty ( $is_prize )) {
			$sql .= " AND a.is_prize = '" . $is_prize . "' ";
		}
		
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		$sql .= " GROUP BY a.id DESC limit {$limit},{$rows}";
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		if (is_array ( $result ['list'] ) && count ( $result ['list'] ) > 0) {
			foreach ( $result ['list'] as $key => $value ) {
				$result ['list'] [$key] ['level_txt'] = SeckillPrizeModel::PRIZE_LEVEL_VALUE [$value ['level']];
				$result ['list'] [$key] ['prize_type_txt'] = SeckillPrizeModel::PRIZE_TYPE_VALUE [$value ['prize_type']];
				$result ['list'] [$key] ['status_txt'] = self::PRIZE_STATUS_VALUE [$value ['status']];
				$result ['list'] [$key] ['is_prize_txt'] = self::PRIZE_IS_PRIZE_VALUE [$value ['is_prize']];
			}
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * 获取单条数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->clear ()->select ( 'id,user_id,seckill_id,seckill_product_id,dump_num,created_at' )->from ( self::$_tableName )->where ( $where )->getRow ();
		return $info;
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
		$up = $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
		return $up;
	}
	
	/**
	 * 更新团员状态
	 * 
	 * @param array $tuan_id
	 *        	团长ID
	 * @return boolean 更新结果
	 */
	public static function updateByTuanID($data, $tuan_id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		$up = $pdo->update ( self::$_tableName, $data, array (
				'tuan_id' => intval ( $tuan_id ) 
		) );
		return $up;
	}
	
	/**
	 * 获取拼团数据
	 * 
	 * @param array $seckill_id
	 *        	关联活动id
	 * @param array $order_id
	 *        	订单id
	 * @return mixed
	 */
	public static function getInfos($seckill_product_id, $order_id) {
		$pdo = self::_pdo ( 'db_r' );
		$sql = "
        	SELECT 
        		a.*,b.on_status,CASE WHEN b.on_status = 2 THEN (b.on_status = 2) ELSE (b.channel_status = 3) END  
        	FROM 
        		" . CommonBase::$_tablePrefix . self::$_tableName . " a
        	LEFT JOIN
		        	" . CommonBase::$_tablePrefix . "product  b
		   	ON
		       	a.product_id = b.id      
        	WHERE 
        		a.seckill_product_id = {$seckill_product_id}
        	AND
        		a.order_id = {$order_id}
        	AND
        		a.is_del = 2		
        ";
		$info = $pdo->YDGetRow ( $sql );
		
		return $info;
	}
	
	/**
	 * 获取付款成功的团员数量
	 * 
	 * @param array $tuan_id
	 *        	团长ID
	 * @param array $order_status
	 *        	订单状态
	 * @return mixed
	 */
	public static function getCountTuan($tuan_id, $order_status = 0) {
		$pdo = self::_pdo ( 'db_w' ); // 事务提交统计
		$sql = "
        	SELECT 
        		COUNT(*) num
        	FROM 
        		" . CommonBase::$_tablePrefix . self::$_tableName . "
        	WHERE 
        		tuan_id = {$tuan_id}
        	AND
        		is_del = 2		
        ";
		
		if ($order_status != 0) {
			$sql .= " AND order_status = " . $order_status;
		}
		
		$info = $pdo->YDGetOne ( $sql );
		return $info;
	}
	
	/**
	 * 团购成团判断
	 * 
	 * @param array $id
	 *        	关联活动id
	 * @param array $order_id
	 *        	订单id
	 * @return boolean 更新结果
	 *        
	 *         需要保证团员已付款，才能招团员
	 *         需要保证团员数量够的时候不能再申请
	 */
	public static function groupPrve($id, $order_id) {
		// 查询拼团信息
		$logInfo = self::getInfos ( $id, $order_id );
		
		if ($logInfo ['tuan_type'] == 1) {
			// 发起团购通知
			$user_info = UserModel::getAdminInfo ( $logInfo ['user_id'] );
			$msgData = [ 
					'params' => [ 
							'0' => '' 
					] 
			];
			
			/* 发送短信 */
			MsgService::fireMsg ( '15', $user_info ['mobile'], $user_info ['id'], $msgData );
		} else {
			// 参团通知
			$user_info = UserModel::getAdminInfo ( $logInfo ['user_id'] );
			$msgData = [ 
					'params' => [ 
							'0' => '' 
					] 
			];
			
			/* 发送短信 */
			MsgService::fireMsg ( '9', $user_info ['mobile'], $user_info ['id'], $msgData );
		}
		
		// 更新支付状态
		$data = [ ];
		$data ['order_status'] = '2';
		$res = self::updateByID ( $data, $logInfo ['id'] );
		if (! $res) {
			YDLib::testlog ( "更新支付状态失败 , logid:" . $logInfo ['id'] );
			return FALSE;
		}
		
		// 更新剩余参与数量
		$upsdata = [ ];
		$upsdata ['dump_num'] = $logInfo ['dump_num'] - 1;
		$res = self::updateByTuanID ( $upsdata, $logInfo ['tuan_id'] );
		if (! $res) {
			YDLib::testlog ( "更新剩余参与数量失败 , logid:" . $logInfo ['id'] );
			return FALSE;
		}
		
		if ($logInfo ['dump_num'] == 1) {
			// 更新团购下的所有订单状态
			$groupPrivList = self::getGroupPrivList ( array (
					'tuan_id' => $logInfo ['tuan_id'] 
			) );
			foreach ( $groupPrivList as $key => $value ) {
				if ($value ['order_status'] == '2') { // 已支付的更新为已成团
				                                     // 更新团购信息
					$upgroup = [ ];
					$upgroup ['status'] = 2; // 拼团成功
					$res = self::updateByID ( $upgroup, $value ['id'] );
					if (! $res) {
						YDLib::testlog ( "更新团员状态为拼团成功失败 , tuan_id:" . $logInfo ['tuan_id'] );
						return FALSE;
					}
					$res = OrderModel::updateByID ( [ 
							'status' => CommonBase::STATUS_ALREADY_PAID 
					], $value ['order_id'] );
					if ($res === FALSE) {
						YDLib::testlog ( "更新团购下的所有订单为已成团状态faild-21: id: " . $value ['order_id'] . ",supplier_id: " . SUPPLIER_ID );
						return FALSE;
					}
					$res = OrderChildModel::updateByOrderID ( [ 
							'child_status' => CommonBase::STATUS_ALREADY_PAID 
					], $value ['order_id'] );
					if ($res === FALSE) {
						YDLib::testlog ( "更新团购下的所有订单为已成团状态faild-21: id: " . $value ['order_id'] . ",supplier_id: " . SUPPLIER_ID );
						return FALSE;
					}
					// 拼团订单生成采购单
					$product = OrderChildProductModel::getInfoByOrderID ( $value ['order_id'] );
					// 组装采购商品数据
					$purchase_products = [ ];
					foreach ( $product as $k => $v ) {
						if ($v ['is_channel'] == '2') { // 供应订单
							$purchase_product = [ ];
							$purchase_product ['id'] = $v ['product_id'];
							$purchase_product ['num'] = $v ['sale_num'];
							$purchase_product ['order_child_product_id'] = $v ['id'];
							$purchase_products [] = $purchase_product;
						}
					}
					if (count ( $purchase_products ) > 0) {
						// 查询支付方式
						$paymentInfo = PaymentTransactionModel::getInfo ( SUPPLIER_ID, 'order', $product [0] ['order_no'] );
						$res = PaymentModel::createPurchase ( $purchase_products, $product [0] ['child_order_id'], $paymentInfo ['pay_type'] );
						if (! $res) {
							YDLib::testlog ( "拼团订单生成采购单失败 , 订单id:" . $value ['order_id'] );
							return FALSE;
						}
					}
					
					// 发起团购通知
					$user_info = UserModel::getAdminInfo ( $value ['user_id'] );
					$msgData = [ 
							'params' => [ 
									'0' => '' 
							] 
					];
					
					/* 发送短信 */
					MsgService::fireMsg ( '16', $user_info ['mobile'], $user_info ['id'], $msgData );
				} else if ($value ['order_status'] == '1') { // 未支付的更新为客服取消
				                                            // 更新团购信息
					$upgroup = [ ];
					$upgroup ['status'] = 3; // 拼团失败
					$res = self::updateByID ( $upgroup, $value ['id'] );
					if (! $res) {
						YDLib::testlog ( "更新团员状态为拼团失败失败 , tuan_id:" . $logInfo ['tuan_id'] );
						return FALSE;
					}
					$res = OrderModel::updateByID ( [ 
							'status' => CommonBase::STATUS_CUSTOM_CANCEL 
					], $value ['order_id'] );
					if ($res === FALSE) {
						YDLib::testlog ( "更新团购下的所有订单为客服取消faild-90: id: " . $value ['order_id'] . ",supplier_id: " . SUPPLIER_ID );
						return FALSE;
					}
					$res = OrderChildModel::updateByOrderID ( [ 
							'child_status' => CommonBase::STATUS_CUSTOM_CANCEL 
					], $value ['order_id'] );
					if ($res === FALSE) {
						YDLib::testlog ( "更新团购下的所有订单为客服取消faild-90: id: " . $value ['order_id'] . ",supplier_id: " . SUPPLIER_ID );
						return FALSE;
					}
					
					$product = OrderChildProductModel::getInfoByOrderID ( $value ['order_id'] );
					foreach ( $product as $k => $v ) {
						if ($v ['is_channel'] == '2') { // 供应订单
							/**
							 * 虚拟商品解锁返还库存
							 */
							$channel_product = ProductChannelModel::find ( $v ['channel_id'] );
							$stock = new VoidStockService ( $channel_product->toArray () );
							$stock->setType ( OrderStockService::LOG_TYPE_12 );
							$stock->setLockNum ( $v ['sale_num'] );
							$stock->revert ();
							/**
							 * 供应商品解锁返还库存
							 */
							$product = ProductModel::find ( $v ['product_id'] );
							$stock = new OrderStockService ( $product->toArray () );
							$stock->setType ( OrderStockService::LOG_TYPE_12 );
							$stock->setLockNum ( $v ['sale_num'] );
							$stock->revert ();
						} else {
							// 库存返还
							$log_id = ProductStockLogModel::addLog ( $v ['product_id'], 6, $v ['sale_num'], - $v ['sale_num'] );
							if (! $log_id) {
								YDLib::testlog ( "库存返还失败-90: id: " . $value ['order_id'] . ",supplier_id: " . SUPPLIER_ID );
								return FALSE;
							}
							// 变动库存
							$productInfo = ProductModel::getInfoByID ( $v ['product_id'] );
							$upData ['stock'] = bcadd ( $productInfo ['stock'], $v ['sale_num'] );
							$upData ['lock_stock'] = bcadd ( $productInfo ['lock_stock'], - $v ['sale_num'] );
							$res = ProductModel::updateByID ( $upData, $v ['product_id'] );
							if (! $res) {
								YDLib::testlog ( "库存返还失败-90: id: " . $value ['order_id'] . ",supplier_id: " . SUPPLIER_ID );
								return FALSE;
							}
						}
					}
				}
			}
		} else {
			// 更新订单状态为待成团
			$res = OrderModel::updateByID ( [ 
					'status' => CommonBase::STATUS_ALREADY_PIN 
			], $order_id );
			if ($res === FALSE) {
				YDLib::testlog ( "更新订单状态为待成团faild-22: id: " . $order_id . ",supplier_id: " . SUPPLIER_ID );
				return FALSE;
			}
			$res = OrderChildModel::updateByOrderID ( [ 
					'child_status' => CommonBase::STATUS_ALREADY_PIN 
			], $order_id );
			if ($res === FALSE) {
				YDLib::testlog ( "更新订单状态为待成团faild-22: id: " . $order_id . ",supplier_id: " . SUPPLIER_ID );
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/**
	 * 拼团待成团团购列表
	 * 
	 * @return mixed
	 */
	public static function getGroupList($attribute = array(), $page = 0, $rows = 10) {
		$limit = ($page) * $rows;
		if (! empty ( $attribute ) && is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}
		
		$pdo = self::_pdo ( 'db_r' );
		$fileds = "id,user_id,seckill_id,seckill_product_id,dump_num,created_at";
		$sql = "
        	SELECT 
        		[*]
        	FROM 
        		" . CommonBase::$_tablePrefix . self::$_tableName . "
        	WHERE 
        		status = 1		
        	AND
        		order_status = 2		
        	AND
        		tuan_type = 1		
        	AND
        		is_del = 2		
        	AND
        		seckill_type = 4		
        	AND
        		unix_timestamp(created_at) + 24*60*60 > unix_timestamp(now())	
        	AND 
        		dump_num > 0	
        ";
		
		if (isset ( $seckill_product_id ) && ! empty ( $seckill_product_id )) {
			$sql .= ' AND seckill_product_id = ' . $seckill_product_id;
		}
		
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		$sql .= " limit {$limit},{$rows}";
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		
		return $result;
	}
	
	/**
	 * 查询会员参团信息
	 * 
	 * @return mixed
	 */
	public static function getCanyuInfo($user_id, $tuan_id) {
		$pdo = self::_pdo ( 'db_r' );
		$fileds = "id,user_id,seckill_id,seckill_product_id,dump_num,created_at";
		$sql = "
        	SELECT 
        		[*]
        	FROM 
        		" . CommonBase::$_tablePrefix . self::$_tableName . "
        	WHERE 
        		user_id = {$user_id}	
        	AND
        		tuan_id = {$tuan_id}	
        	AND
        		is_del = 2		
        	AND
        		seckill_type = 4	
        	AND
        		status != 3		
        ";
		return $pdo->YDGetRow ( str_replace ( "[*]", $fileds, $sql ) );
	}
	
	/**
	 * 拼团待成团团购团员列表
	 * 
	 * @return mixed
	 */
	public static function getGroupPrivList($attribute = array()) {
		if (! empty ( $attribute ) && is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}
		
		$pdo = self::_pdo ( 'db_w' ); // 事务内查询
		$fileds = "id,user_id,seckill_id,seckill_product_id,dump_num,created_at,tuan_type,order_id,order_status,status";
		$sql = "
        	SELECT 
        		[*]
        	FROM 
        		" . CommonBase::$_tablePrefix . self::$_tableName . "
        	WHERE 
        		is_del = 2	
        	AND
        		seckill_type = 4	        			
        ";
		
		if (isset ( $tuan_id ) && ! empty ( $tuan_id )) {
			$sql .= " AND tuan_id = '" . $tuan_id . "' ";
		}
		
		if (isset ( $order_status ) && ! empty ( $order_status )) {
			$sql .= " AND order_status = '" . $order_status . "' ";
		}
		
		return $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
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
	 * 通过订单编号号查询团购信息
	 * 
	 * @return mixed
	 */
	public static function getGroupInfoByOrderNo($orderNo) {
		$pdo = self::_pdo ( 'db_r' );
		$sql = "
        	SELECT 
        		a.*
        	FROM 
        		" . CommonBase::$_tablePrefix . self::$_tableName . " a
        	LEFT JOIN 
        		" . CommonBase::$_tablePrefix . "order b
        	ON 
        		a.order_id = b.id
        	WHERE 
        		b.order_no = {$orderNo}       			
        ";
		return $pdo->YDGetRow ( $sql );
	}
	
	/*
	 *
	 * 获取竞价拍 出价数量
	 *
	 */
	public static function getCount($info) {
		$pdo = YDLib::getPDO ( 'db_r' );
		$sql = 'SELECT
        		   count(*) as num
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.seckill_type = 3
		        AND
		            a.product_id = ' . $info ['product_id'] . '
		        AND
		            a.seckill_id = ' . $info ['id'] . '
		        AND
		            a.supplier_id = ' . SUPPLIER_ID . '
        		';
		$count = $pdo->YDGetOne ( $sql );
		if ($count) {
			return $count;
		} else {
			return false;
		}
	}
	
	/*
	 *
	 * 获取竞价拍出价记录列表
	 *
	 */
	public static function getBidderAll($info, $page = 0, $rows = 10) {
		$page = ($page - 1) * $rows;
		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = " a.*";
		$sql = 'SELECT
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.seckill_type = 3
		        AND
		            a.seckill_id = ' . $info ['id'] . '
		        AND
		            a.product_id = ' . $info ['product_id'] . '
		        AND
		            a.supplier_id = ' . SUPPLIER_ID . '
        		';
		
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		$sql .= " GROUP BY a.id DESC limit {$page},{$rows}";
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		
		foreach ( $result ['list'] as $key => $val ) {
			
			if ($result ['list'] [$key] ['status'] == '2') {
				$result ['list'] [$key] ['status_txt'] = '出局';
			} else {
				$result ['list'] [$key] ['status_txt'] = '领先';
			}
			
			// $user =SeckillOrderModel::getPersonalOrderByID($result['list'][$key]);
			
			if ($val ['is_robot'] == '2') {
				$result ['list'] [$key] ['user_txt'] = substr_replace ( $val ['robot_mobile'], "****", 3, 4 );
				$result ['list'] [$key] ['created_at'] = substr ( $result ['list'] [$key] ['created_at'], 5, 14 );
				$result ['list'] [$key] ['name'] = mb_strimwidth ( $val ['robot_name'], 0, 2 ) . "**";
			} else {
				// 取用户手机号
				$user = UserModel::getAdminInfo ( $result ['list'] [$key] ['user_id'] );
				$result ['list'] [$key] ['user_txt'] = substr_replace ( $user ['mobile'], "****", 3, 4 );
				$result ['list'] [$key] ['created_at'] = substr ( $result ['list'] [$key] ['created_at'], 5, 14 );
				if ($user ['name']) {
					$result ['list'] [$key] ['name'] = mb_strimwidth ( $user ['name'], 0, 2 ) . "**";
				} else {
					$result ['list'] [$key] ['name'] = "***";
				}
			}
		}
		
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * 获取出价单条数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getBidderByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		return $info;
	}
	
	/*
	 * 根据订单id 获取上一条信息
	 *
	 */
	public static function getTypeInfoByID($info) {
		$pdo = self::_pdo ( 'db_r' );
		
		$sql = 'SELECT
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.seckill_type = 3
		        AND
		            a.id < ' . $info ['id'] . '
		        AND
		           a.seckill_id = ' . $info ['seckill_id'] . '
		        AND
		            a.supplier_id = ' . SUPPLIER_ID . '
	
		        ORDER BY a.id DESC
        		';
		
		$info = $pdo->YDGetRow ( str_replace ( "[*]", "*", $sql ) );
		return $info;
	}
	
	/*
	 * 根据商品id 一条信息生成订单
	 *
	 */
	public static function getOrderInfoByID($info) {
		$pdo = self::_pdo ( 'db_r' );
		
		$sql = 'SELECT
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.seckill_type = 3
		        AND
		           a.seckill_id = ' . $info ['seckill_id'] . '
		        AND
		            a.supplier_id = ' . SUPPLIER_ID . '
		          AND
		            a.product_id = '.$info['product_id'].'
		        ORDER BY a.id DESC
        		';

		$info = $pdo ->YDGetRow(str_replace("[*]", "*", $sql));
		return $info;

	}


    /* 查询*/
    public static function findWhereOne($where)
    {
        $where['is_del'] = self::DELETE_SUCCESS;
        $pdo = self::_pdo('db_r');
        $data = $pdo->clear()->select('*')->from(self::table())->where($where)->getRow();

        return $data;
    }


}