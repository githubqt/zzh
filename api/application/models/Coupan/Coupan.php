<?php

/**
 * 优惠券model
 * @version v0.01
 * @author laiqingtao
 * @time 2018-05-19
 */
namespace Coupan;

use Custom\YDLib;

class CoupanModel extends \Common\CommonBase {
	/**
	 * 定义表名后缀
	 */
	protected static $_tableName = 'coupan';
	
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
		$adminInfo = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () );
		
		$data ['is_del'] = self::DELETE_SUCCESS;
		$data ['remain_num'] = $data ['total_num'];
		$data ['supplier_id'] = $adminInfo ['supplier_id'];
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
		$info = $pdo->clear ()->select ( 'id,supplier_id,name,type,status,total_num,give_num,remain_num,use_num,use_type,use_product_ids,
				sill_type,sill_price,pre_type,pre_value,time_type,start_time,end_time,give_type,give_value,is_more' )->from ( self::$_tableName )->where ( $where )->getRow ();
		if (! $info) {
			return FALSE;
		}
		
		$info ['status_txt'] = self::COUPAN_STATUS_VALUE [$info ['status']];
		$info ['use_type_txt'] = self::COUPAN_STATUS_VALUE_VALUE [$info ['use_type']];
		
		if ($info ['sill_type'] == 1) { // 无使用门槛
			$info ['sill_txt'] = '无使用门槛 ';
		} else if ($info ['sill_type'] == 2) { // 满N元可用
			$info ['sill_txt'] = '满' . $info ['sill_price'] . '元 ';
		}
		if ($info ['pre_type'] == 1) { // 减免
			$info ['pre_txt'] = '减免' . $info ['pre_value'] . '元 ';
		} else if ($info ['pre_type'] == 2) { // 打折
			$info ['pre_txt'] = '打' . $info ['pre_value'] . '折 ';
		}
		if ($info ['time_type'] == 1) { // 减免
			$info ['time_txt'] = '可用时间：' . $info ['start_time'] . '至' . $info ['end_time'];
		} else if ($info ['time_type'] == 2) { // 打折
			$info ['time_txt'] = '可用时间：不限';
		}
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
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
	
	/**
	 * 字段自更新
	 * 
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function autoUpdateByID($data, $id) {
		$sql = "UPDATE " . self::getTb () . " SET ";
		foreach ( $data as $key => $val ) {
			if ($val > 0)
				$val = '+' . $val;
			$sql .= "`{$key}` = (`{$key}` {$val}),";
		}
		$sql = substr ( $sql, 0, - 1 );
		
		$sql .= " WHERE id = " . $id;
		
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
	public static function getList($attribute = array(), $page = 1, $rows = 10) {
		$limit = ($page - 1) * $rows;
		if (is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}
		
		$filed = "id,supplier_id,name,type,status,total_num,give_num,remain_num,use_num,use_type,use_product_ids,
				sill_type,sill_price,pre_type,pre_value,time_type,start_time,end_time,give_type,give_value,is_more
			";
		$sql = "SELECT 
        		    [*] 
        		FROM
		            " . self::getTb () . "
		        WHERE
        		    is_del=" . self::DELETE_SUCCESS . "
				AND	
					supplier_id = " . SUPPLIER_ID . "	
				AND 
					status = 2
			 	AND 
			 	 	(
						(
				             time_type = 1
				         AND
				             end_time > NOW()
				         )
					 OR
				 	 	time_type = 2	
				 	)	
			";
		
		if ($remain_num) {
		    $sql .= " AND remain_num > '0'";
		}
		
		if ($coupan_id) {
		    $sql .= " AND id = ".$coupan_id;
		}
		
		$pdo = self::_pdo ( 'db_r' );
		$resInfo = array ();
		$resInfo ['total'] = $pdo->YDGetOne ( str_replace ( '[*]', 'COUNT(1) num', $sql ) );
		
		$sql .= " LIMIT {$limit},{$rows}";
		$resInfo ['rows'] = $pdo->YDGetAll ( str_replace ( '[*]', $filed, $sql ) );
		if (is_array ( $resInfo ['rows'] ) && count ( $resInfo ['rows'] ) > 0) {
			foreach ( $resInfo ['rows'] as $key => $value ) {
				
				$resInfo ['rows'] [$key] ['status_txt'] = self::COUPAN_STATUS_VALUE [$resInfo ['rows'] [$key] ['status']];
				$resInfo ['rows'] [$key] ['use_type_txt'] = self::COUPAN_STATUS_VALUE_VALUE [$resInfo ['rows'] [$key] ['use_type']];
				
				if ($resInfo ['rows'] [$key] ['sill_type'] == 1) { // 无使用门槛
					$resInfo ['rows'] [$key] ['sill_txt'] = '无使用门槛 ';
				} else if ($resInfo ['rows'] [$key] ['sill_type'] == 2) { // 满N元可用
					$resInfo ['rows'] [$key] ['sill_txt'] = '满' . $resInfo ['rows'] [$key] ['sill_price'] . '元 ';
				}
				if ($resInfo ['rows'] [$key] ['pre_type'] == 1) { // 减免
					$resInfo ['rows'] [$key] ['pre_txt'] = '减免' . $resInfo ['rows'] [$key] ['pre_value'] . '元 ';
				} else if ($resInfo ['rows'] [$key] ['pre_type'] == 2) { // 打折
					$resInfo ['rows'] [$key] ['pre_txt'] = '打' . $resInfo ['rows'] [$key] ['pre_value'] . '折 ';
				}
				if ($resInfo ['rows'] [$key] ['time_type'] == 1) { // 减免
					$resInfo ['rows'] [$key] ['time_txt'] = '可用时间：' . $resInfo ['rows'] [$key] ['start_time'] . '至' . $resInfo ['rows'] [$key] ['end_time'];
				} else if ($resInfo ['rows'] [$key] ['time_type'] == 2) { // 打折
					$resInfo ['rows'] [$key] ['time_txt'] = '可用时间：不限';
				}
				
				//该用户是否还可以领取
				$resInfo ['rows'] [$key] ['user_is_ok'] = 'ok';
				if ($user_id) {
				    if ($value ['give_type'] == 2) {
				        $count = UserCoupanModel::getCount ( $user_id, $value['id'] );
				        if ($count >= $value ['give_value']) {
				            $resInfo ['rows'] [$key] ['user_is_ok'] = 'no';
				        }
				    }
				}
				
			}
		}
		return $resInfo;
	}
	
	/**
	 * 根据商户ID获取该条记录信息
	 * 
	 * @param int $supplier_id
	 *        	商户id
	 */
	public static function getInfoUserByID($supplier_id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['supplier_id'] = intval ( $supplier_id );
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	/**
	 * 根据一条商户ID更新表记录
	 * 
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateUserByID($data, $supplier_id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'user_id' => intval ( $supplier_id ) 
		) );
	}
}