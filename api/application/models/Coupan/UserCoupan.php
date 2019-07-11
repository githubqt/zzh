<?php

/**
 * 会员优惠券model
 * @version v0.01
 * @author laiqingtao
 * @time 2018-05-19
 */
namespace Coupan;

use Custom\YDLib;
use Admin\AdminModel;
use Product\ProductModel;
use Supplier\SupplierModel;

class UserCoupanModel extends \Common\CommonBase {
	/**
	 * 定义表名后缀
	 */
	protected static $_tableName = 'user_coupan';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
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
	 * 根据表自增ID获取该条记录信息
	 * 
	 * @param int $id
	 *        	表自增ID
	 */
	public static function getInfoByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	/**
	 * 根据用户ID获取该条记录信息
	 * 
	 * @param int $user_id
	 *        	用户ID
	 */
	public static function getInfoUserByID($user_id, $supplier_id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['user_id'] = intval ( $user_id );
		$where ['supplier_id'] = intval ( $supplier_id );
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	/**
	 * 获取会员领取优惠券的数量
	 * 
	 * @param int $id
	 *        	表自增ID
	 */
	public static function getCount($user_id, $coupan_id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['user_id'] = $user_id;
		$where ['coupan_id'] = $coupan_id;
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( ' COUNT(1) num ' )->from ( self::$_tableName )->where ( $where )->getOne ();
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
	 * 根据一条用户ID更新表记录
	 * 
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateUserByID($data, $user_id, $supplier_id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'user_id' => intval ( $user_id ),
				'supplier_id' => intval ( $supplier_id ) 
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
	 * 获取个人优惠券
	 * 
	 * @param array $attribute
	 *        	获取对应的参数
	 * @param integer $page
	 *        	对应的页
	 * @param integer $rows
	 *        	取出的行数
	 * @return array
	 */
	public static function getList($attribute = array(), $page = 1, $rows = 10) {
		$limit = ($page - 1) * $rows;
		if (is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}
		
		$filed = "c.name c_name,c.status c_status,c.time_type,c.start_time,c.end_time,c.use_type,c.sill_type,c.sill_price,c.pre_type,
		c.pre_value,o.id,o.status,o.give_at,o.use_at,o.order_id,o.order_price,o.discount_price,o.coupan_id,o.id user_coupan_id,
		c.use_product_ids,c.is_more";
		
		$sql = "SELECT 
        		    [*] 
        		FROM
		            " . self::getTb () . " o
		        LEFT JOIN 
        		    " . self::$_tablePrefix . "coupan c
        		ON
		            c.id = o.coupan_id  		              
		        WHERE
        		    o.is_del=" . self::DELETE_SUCCESS . "
        		AND
        			o.supplier_id = " . SUPPLIER_ID;
		
		if (isset ( $user_id ) && ! empty ( intval ( $user_id ) )) {
			$sql .= " AND o.user_id = '" . intval ( $user_id ) . "'";
		}
		if (isset ( $coupan_id ) && ! empty ( intval ( $coupan_id ) )) {
			$sql .= " AND o.coupan_id = '" . intval ( $coupan_id ) . "'";
		}
		if (isset ( $user_coupan_id ) && ! empty ( intval ( $user_coupan_id ) )) {
			$sql .= " AND o.id = '" . intval ( $user_coupan_id ) . "'";
		}
		if (isset ( $status ) && ! empty ( intval ( $status ) )) {
			if ($status == 1) { // 未使用未过期
				$sql .= " AND o.status = 1
						  AND c.status = 2
					 	  AND 
					 	  (
							(
					             c.time_type = 1
					         AND
					             c.end_time > NOW()
					         )
						 OR
					 	 	c.time_type = 2	
						 )				
					";
			} else if ($status == 2) { // 已使用
				$sql .= " AND o.status = 2";
			} else if ($status == 3) { // 未使用已过期
				$sql .= " AND o.status = 1
						  AND 
						  (
						  	c.status = 1
						  	OR 
						  	c.status = 3
						 	OR
						 	(
							  c.status = 2
						 	  AND c.time_type = 1
					          AND c.end_time <= NOW()
						 	)
						  )			
					";
			}
		}
		
		$pdo = self::_pdo ( 'db_r' );
		$resInfo = array ();
		$resInfo ['total'] = $pdo->YDGetOne ( str_replace ( '[*]', 'COUNT(1) num', $sql ) );
		
		$sql .= " LIMIT {$limit},{$rows}";
		$resInfo ['rows'] = $pdo->YDGetAll ( str_replace ( '[*]', $filed, $sql ) );
		
		if (is_array ( $resInfo ['rows'] ) && count ( $resInfo ['rows'] ) > 0) {
			foreach ( $resInfo ['rows'] as $key => $value ) {
				
				if ($value ['c_status'] == 2 && $value ['time_type'] == 1 && $value ['end_time'] <= date ( "Y-m-d H:i:s" )) {
					$resInfo ['rows'] [$key] ['c_status'] = 4; // 已过期
				}
				$resInfo ['rows'] [$key] ['c_status_txt'] = self::COUPAN_STATUS_VALUE [$resInfo ['rows'] [$key] ['c_status']];
				$resInfo ['rows'] [$key] ['use_type_txt'] = self::COUPAN_STATUS_VALUE_VALUE [$resInfo ['rows'] [$key] ['use_type']];
				
				$resInfo ['rows'] [$key] ['c_status_txt'] = self::COUPAN_STATUS_VALUE [$resInfo ['rows'] [$key] ['c_status']];
				$resInfo ['rows'] [$key] ['use_type_txt'] = self::COUPAN_STATUS_VALUE_VALUE [$resInfo ['rows'] [$key] ['use_type']];
				
				if ($resInfo ['rows'] [$key] ['sill_type'] == 1) { // 无使用门槛
					$resInfo ['rows'] [$key] ['sill_txt'] = '无金额门槛 ';
				} else if ($resInfo ['rows'] [$key] ['sill_type'] == 2) { // 满N元可用
					$resInfo ['rows'] [$key] ['sill_txt'] = '满' . $resInfo ['rows'] [$key] ['sill_price'] . '元 ';
				}
				if ($resInfo ['rows'] [$key] ['pre_type'] == 1) { // 减免
					$resInfo ['rows'] [$key] ['pre_txt'] = '减免' . $resInfo ['rows'] [$key] ['pre_value'] . '元 ';
				} else if ($resInfo ['rows'] [$key] ['pre_type'] == 2) { // 打折
					$resInfo ['rows'] [$key] ['pre_txt'] = '打' . $resInfo ['rows'] [$key] ['pre_value'] . '折 ';
				}
				if ($resInfo ['rows'] [$key] ['time_type'] == 1) { // 减免
// 					$resInfo ['rows'] [$key] ['time_txt'] = '可用时间：' . $resInfo ['rows'] [$key] ['start_time'] . '至' . $resInfo ['rows'] [$key] ['end_time'];
					$day = self::calcTime($resInfo ['rows'] [$key] ['start_time'] ,$resInfo ['rows'] [$key] ['end_time']);
					$endTime = substr($resInfo ['rows'] [$key] ['end_time'],0,10);
					$resInfo ['rows'] [$key] ['time_txt'] = $endTime.'到期：(仅剩' .$day.')';
				} else if ($resInfo ['rows'] [$key] ['time_type'] == 2) { // 打折
					$resInfo ['rows'] [$key] ['time_txt'] = '可用时间：不限';
				}
				
				if ($value ['status'] == 2) {
					$resInfo ['rows'] [$key] ['status_txt'] = '已使用';
				} else if ($value ['status'] == 1) {
					$resInfo ['rows'] [$key] ['status_txt'] = '未使用';
					if ($resInfo ['rows'] [$key] ['c_status'] == 3 || $resInfo ['rows'] [$key] ['c_status'] == 4) {
						$resInfo ['rows'] [$key] ['status_txt'] = '已失效';
					}
				}
				$resInfo ['rows'] [$key]['product']['total'] = '0';
				//优惠券下的商品
				if ($value['use_type'] == '2') {
			        $info ['ids'] = $value ['use_product_ids'];
				    $list = ProductModel::getList ( $info, '1', '1000' );
				    if ($list['total'] > '0') {
				        foreach ($list['list'] as $k=>$v) {
				            $list['list'][$k]['name'] = self::subtext($v['name'],'14');
				        }
				        $resInfo ['rows'] [$key]['product'] = $list;
				    }
				}
				
				// 是否可用
			}
		}
		return $resInfo;
	}
	
	
	/**
	 * 换算时间
	 * 
	 * @param unknown $fromTime
	 * @param unknown $toTime
	 */
	public static function calcTime($fromTime, $toTime){
	
		//转时间戳
		$fromTime = strtotime($fromTime);
		$toTime = strtotime($toTime);
		//计算时间差
		$newTime = $toTime - $fromTime;
// 		return round($newTime / 86400) . '天' .
// 				round($newTime % 86400 / 3600) . '小时' .
// 				round($newTime % 86400 % 3600 / 60) . '分钟';
		return round($newTime / 86400) . '天' ;
	}	
	
	/**
	 * 获取个人优惠券
	 * 
	 * @param array $attribute
	 *        	获取对应的参数
	 * @param integer $page
	 *        	对应的页
	 * @param integer $rows
	 *        	取出的行数
	 * @return array
	 */
	public static function getNum($attribute = array()) {
		if (is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}
		$sql = "SELECT
        		    [*]
        		FROM
		            " . self::getTb () . " o
		        LEFT JOIN
        		    " . self::$_tablePrefix . "coupan c
        		ON
		            c.id = o.coupan_id
		        WHERE
        		    o.is_del=" . self::DELETE_SUCCESS . "
        		AND
        			o.supplier_id = " . SUPPLIER_ID;
		
		if (isset ( $user_id ) && ! empty ( intval ( $user_id ) )) {
			$sql .= " AND o.user_id = '" . intval ( $user_id ) . "'";
		}
		
		if (isset ( $status ) && ! empty ( intval ( $status ) )) {
			if ($status == 1) { // 未使用未过期
				$sql .= " AND o.status = 1
						  AND c.status = 2
					 	  AND
					 	  (
							(
					             c.time_type = 1
					         AND
					             c.end_time > NOW()
					         )
						 OR
					 	 	c.time_type = 2
						 )
					";
			} else if ($status == 2) { // 已使用
				$sql .= " AND o.status = 2";
			} else if ($status == 3) { // 未使用已过期
				$sql .= " AND o.status = 1
						  AND
						  (
						  	c.status = 1
						  	OR
						  	c.status = 3
						 	OR
						 	(
							  c.status = 2
						 	  AND c.time_type = 1
					          AND c.end_time <= NOW()
						 	)
						  )
					";
			}
		}
		
		$pdo = self::_pdo ( 'db_r' );
		
		return $pdo->YDGetOne ( str_replace ( '[*]', 'COUNT(1) num', $sql ) );
	}
	
	
	
	
	
	
	
	
	
	/**
	 * 获取个人单张优惠券
	 *
	 * @param array $attribute
	 *        	获取对应的参数
	 * @param integer $page
	 *        	对应的页
	 * @param integer $rows
	 *        	取出的行数
	 * @return array
	 */
	public static function getsolaCoupanBy($attribute = array()) {
		
		if (is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}
	
		$filed = "c.name c_name,c.status c_status,c.time_type,c.start_time,c.end_time,c.use_type,c.sill_type,c.sill_price,c.pre_type,
		c.pre_value,o.id,o.status,o.give_at,o.use_at,o.order_id,o.order_price,o.discount_price,o.coupan_id,o.id user_coupan_id,
		c.use_product_ids,c.is_more";
	
		$sql = "SELECT
        		    [*]
        		FROM
		            " . self::getTb () . " o
		        LEFT JOIN
        		    " . self::$_tablePrefix . "coupan c
        		ON
		            c.id = o.coupan_id
		        WHERE
        		    o.is_del=" . self::DELETE_SUCCESS . "
        		AND
        			o.supplier_id = " . SUPPLIER_ID.'
        		AND 
        			o.user_id = '.$user_id.'
        		AND 
        			o.coupan_id = '.$coupan_id.'
        					';
	
	
		if (isset ( $status ) && ! empty ( intval ( $status ) )) {
			if ($status == 1) { // 未使用未过期
				$sql .= " AND o.status = 1
						  AND c.status = 2
					 	  AND
					 	  (
							(
					             c.time_type = 1
					         AND
					             c.end_time > NOW()
					         )
						 OR
					 	 	c.time_type = 2
						 )
					";
			} else if ($status == 2) { // 已使用
				$sql .= " AND o.status = 2";
			} else if ($status == 3) { // 未使用已过期
				$sql .= " AND o.status = 1
						  AND
						  (
						  	c.status = 1
						  	OR
						  	c.status = 3
						 	OR
						 	(
							  c.status = 2
						 	  AND c.time_type = 1
					          AND c.end_time <= NOW()
						 	)
						  )
					";
			}
		}
		
		
		
		$pdo = self::_pdo ( 'db_r' );
		$resInfo ['rows'][] = $pdo->YDGetRow ( str_replace ( '[*]', $filed, $sql ) );
		$company  = SupplierModel::getshopNameBySupplierId(SUPPLIER_ID);
		if (is_array ( $resInfo ['rows'] ) && count ( $resInfo ['rows'] ) > 0) {
			foreach ( $resInfo ['rows'] as $key => $value ) {
				$resInfo ['rows'][$key]['company'] = $company;
				if ($value ['c_status'] == 2 && $value ['time_type'] == 1 && $value ['end_time'] <= date ( "Y-m-d H:i:s" )) {
					$resInfo ['rows'] [$key] ['c_status'] = 4; // 已过期
				}
				$resInfo ['rows'] [$key] ['c_status_txt'] = self::COUPAN_STATUS_VALUE [$resInfo ['rows'] [$key] ['c_status']];
				$resInfo ['rows'] [$key] ['use_type_txt'] = self::COUPAN_STATUS_VALUE_VALUE [$resInfo ['rows'] [$key] ['use_type']];
	
				$resInfo ['rows'] [$key] ['c_status_txt'] = self::COUPAN_STATUS_VALUE [$resInfo ['rows'] [$key] ['c_status']];
				$resInfo ['rows'] [$key] ['use_type_txt'] = self::COUPAN_STATUS_VALUE_VALUE [$resInfo ['rows'] [$key] ['use_type']];
	
				if ($resInfo ['rows'] [$key] ['sill_type'] == 1) { // 无使用门槛
					$resInfo ['rows'] [$key] ['sill_txt'] = '无金额门槛 ';
				} else if ($resInfo ['rows'] [$key] ['sill_type'] == 2) { // 满N元可用
					$resInfo ['rows'] [$key] ['sill_txt'] = '满' . $resInfo ['rows'] [$key] ['sill_price'] . '元 ';
				}
				if ($resInfo ['rows'] [$key] ['pre_type'] == 1) { // 减免
					$resInfo ['rows'] [$key] ['pre_txt'] = '减免' . $resInfo ['rows'] [$key] ['pre_value'] . '元 ';
				} else if ($resInfo ['rows'] [$key] ['pre_type'] == 2) { // 打折
					$resInfo ['rows'] [$key] ['pre_txt'] =  $resInfo ['rows'] [$key] ['pre_value'] . '折 ';
				}
				if ($resInfo ['rows'] [$key] ['time_type'] == 1) { // 减免
					// 					$resInfo ['rows'] [$key] ['time_txt'] = '可用时间：' . $resInfo ['rows'] [$key] ['start_time'] . '至' . $resInfo ['rows'] [$key] ['end_time'];
					$day = self::calcTime($resInfo ['rows'] [$key] ['start_time'] ,$resInfo ['rows'] [$key] ['end_time']);
					$endTime = substr($resInfo ['rows'] [$key] ['end_time'],0,10);
					$resInfo ['rows'] [$key] ['time_txt'] = $endTime.'到期：(仅剩' .$day.')';
				} else if ($resInfo ['rows'] [$key] ['time_type'] == 2) { // 打折
					$resInfo ['rows'] [$key] ['time_txt'] = '可用时间：不限';
				}
	
				if ($value ['status'] == 2) {
					$resInfo ['rows'] [$key] ['status_txt'] = '已使用';
				} else if ($value ['status'] == 1) {
					$resInfo ['rows'] [$key] ['status_txt'] = '未使用';
					if ($resInfo ['rows'] [$key] ['c_status'] == 3 || $resInfo ['rows'] [$key] ['c_status'] == 4) {
						$resInfo ['rows'] [$key] ['status_txt'] = '已失效';
					}
				}
				$resInfo ['rows'] [$key]['product']['total'] = '0';
				//优惠券下的商品
				if ($value['use_type'] == '2') {
					$info ['ids'] = $value ['use_product_ids'];
					$list = ProductModel::getList ( $info, '1', '1000' );
					if ($list['total'] > '0') {
						foreach ($list['list'] as $k=>$v) {
							$list['list'][$k]['name'] = self::subtext($v['name'],'14');
						}
						$resInfo ['rows'] [$key]['product'] = $list;
					}
				}
	
				// 是否可用
			}
			$resInfo ['rows'] [$key] ['end_time'] = substr($resInfo ['rows'] [$key] ['end_time'],0,10);
			$resInfo ['rows'] [$key] ['start_time'] = substr($resInfo ['rows'] [$key] ['start_time'],0,10);
		}
		
		return $resInfo;
	}
	
	
	
}