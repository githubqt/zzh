<?php

/**
 * 子订单model
 * @version v0.01
 * @author laiqingtao
 * @time 2018-05-08
 */
namespace Order;

use Custom\YDLib;

class OrderChildModel extends \Common\CommonBase {
	/**
	 * 定义表名后缀
	 */
	protected static $_tableName = 'order_child';

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
		$where ['supplier_id'] = SUPPLIER_ID;

		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( 'id,user_id,order_id,order_no,child_order_actual_amount,child_order_no,
			child_status,sale_num,child_order_original_amount,is_comment,express_id,express_name,express_pinyin,express_no,
		    province_name,city_name,area_name,street_name,address,accept_name,accept_mobile,child_order_discount_amount,
		    child_freight_charge_actual_amount,child_product_actual_amount,is_give_score,delivery_type,delivery_no' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}

	/**
	 * 根据自提码获取该条记录信息
	 *
	 * @param int $code
	 *        	自提码
	 */
	public static function getInfoByCode($code) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['delivery_no'] = trim ( $code );
		$where ['supplier_id'] = SUPPLIER_ID;

		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( 'id,user_id,order_id,order_no,child_order_actual_amount,child_order_no,
			child_status,sale_num,child_order_original_amount,is_comment,created_at,child_order_discount_amount,
		    child_freight_charge_actual_amount,child_product_actual_amount,is_give_score,delivery_type,delivery_no' )->from ( self::$_tableName )->where ( $where )->getRow ();
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
		$where ['id'] = intval ( $id );
		$where ['supplier_id'] = SUPPLIER_ID;

		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, $where );
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
	public static function updateByOrderID($data, $id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$where ['order_id'] = intval ( $id );
		$where ['supplier_id'] = SUPPLIER_ID;

		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, $where );
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

		$filed = "id,child_order_no,child_status,sale_num,child_order_original_amount,order_id,child_order_actual_amount,delivery_type,delivery_no,note";

		$sql = "SELECT 
        		    [*] 
        		FROM
		            " . self::getTb () . "
		        WHERE
        		    is_del=" . self::DELETE_SUCCESS . "
		        AND
        		    supplier_id=" . SUPPLIER_ID;

		if (isset ( $user_id ) && ! empty ( intval ( $user_id ) )) {
			$sql .= " AND user_id = '" . intval ( $user_id ) . "'";
		}

		if (isset ( $status ) && ! empty ( trim ( $status ) )) {
			$sql .= " AND child_status IN (" . trim ( $status ) . ")";
		}

		if (isset ( $is_after_sales ) && ! empty ( intval ( $is_after_sales ) )) {
			$sql .= " AND is_after_sales = '" . intval ( $is_after_sales ) . "'";
		}

		$pdo = self::_pdo ( 'db_r' );
		$resInfo = array ();
		$resInfo ['total'] = $pdo->YDGetOne ( str_replace ( '[*]', 'COUNT(1) num', $sql ) );
		$sql .= " ORDER BY id DESC LIMIT {$limit},{$rows}";
		$resInfo ['list'] = $pdo->YDGetAll ( str_replace ( '[*]', $filed, $sql ) );
		return $resInfo;
	}

	/**
	 * 根据表订单id获取记录信息
	 */
	public static function getInfoByOrderId($order_id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['order_id'] = intval ( $order_id );
		$where ['supplier_id'] = SUPPLIER_ID;

		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
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
	 * 根据表自增ID获取该条记录信息
	 *
	 * @param int $id
	 *        	表自增ID
	 */
	public static function getInfoByTypeIDAndUserId($type_id, $user_id) {
		$sql = "
    			SELECT
    				COUNT(id) num
        		FROM
		            " . self::getTb () . "
		        WHERE
        		    supplier_id = " . SUPPLIER_ID . "
				AND
					user_id =" . intval ( $user_id ) . "
				AND	    
					discount_id = " . intval ( $type_id ) . "
			    AND
					child_status NOT IN(70,80)
				AND	    
					is_del = " . self::DELETE_SUCCESS;

		$pdo = self::_pdo ( 'db_r' );

		return $pdo->YDGetOne ( $sql );
	}

	/**
	 * 获取已完成未赠送规则的未申请过售后订单
	 */
	public static function getInfoByUserIdAndStatus($user_id, $id) {
		$pdo = self::_pdo ( 'db_r' );

		$sql = "
    			SELECT
    				id,user_id,is_give_score
        		FROM
		            " . self::getTb () . "
		        WHERE
        		    supplier_id = " . SUPPLIER_ID . "
				AND
					user_id =" . intval ( $user_id ) . "
				AND
					is_give_score = 1
				AND
					is_after_sales = 1
			    AND
					child_status =" . OrderModel::STATUS_CLOSED . "
			    AND id != " . $id . "
				AND
					is_del = " . self::DELETE_SUCCESS;

		return $pdo->YDGetAll ( $sql );
	}
}