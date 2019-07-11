<?php

/**
 * 子订单详情model
 * @version v0.01
 * @author laiqingtao
 * @time 2018-05-08
 */
namespace Order;

use Custom\YDLib;
use Common\CommonBase;

class OrderChildProductModel extends \Common\CommonBase {
	/**
	 * 定义表名后缀
	 */
	protected static $_tableName = 'order_child_product';
	
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
	 * 根据子单号查询商品详情
	 * 
	 * @param int $id
	 *        	子单ID
	 */
	public static function getInfoByChildID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['child_order_id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		$list = $pdo->clear ()->select ( 'id,product_id,logo_url,product_name,sale_price,market_price,sale_num,self_code,
		actual_amount,supplier_id,discount_type,discount_id,discount_product_id,is_channel,channel_id,user_id,order_id' )->from ( self::$_tableName )->where ( $where )->getAll ();
		
		if (is_array ( $list )) {
			foreach ( $list as $key => $value ) {
				if (! empty ( $value ['logo_url'] )) {
					$list [$key] ['logo_url'] = HOST_FILE . CommonBase::imgSize ( $value ['logo_url'], 2 );
				} else {
					$list [$key] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
				}
			}
		}
		return $list;
	}
	
	/**
	 * 根据主单号查询商品详情
	 * 
	 * @param int $id
	 *        	子单ID
	 */
	public static function getInfoByOrderID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['order_id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		$list = $pdo->clear ()->select ( 'id,product_id,logo_url,product_name,sale_price,market_price,sale_num,self_code,
		actual_amount,supplier_id,discount_type,discount_id,discount_product_id,is_channel,channel_id,discount_amount,
		coupan_discount_amount,child_order_id,order_no' )->from ( self::$_tableName )->where ( $where )->getAll ();
		
		if (is_array ( $list )) {
			foreach ( $list as $key => $value ) {
				if (! empty ( $value ['logo_url'] )) {
					$list [$key] ['logo_url'] = HOST_FILE . CommonBase::imgSize ( $value ['logo_url'], 4 );
				} else {
					$list [$key] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
				}
			}
		}
		return $list;
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

        $fields = "id,company,province_id,city_id,area_id,address,contact,mobile,created_at";
		
		$sql = "SELECT 
        		    [*] 
        		FROM
		            " . self::getTb () . "
		        WHERE
        		    is_del=" . self::DELETE_SUCCESS . "
		        AND
        		    status=" . self::STATUS_SUCCESS;
		
		if (isset ( $id ) && ! empty ( intval ( $id ) )) {
			$sql .= " AND id = '" . intval ( $id ) . "'";
		}
		if (isset ( $company ) && ! empty ( trim ( $company ) )) {
			$sql .= " AND company LIKE '%" . trim ( $company ) . "%'";
		}
		if (isset ( $province_id ) && ! empty ( intval ( $province_id ) )) {
			$sql .= " AND province_id = '" . intval ( $province_id ) . "'";
		}
		if (isset ( $city_id ) && ! empty ( intval ( $city_id ) )) {
			$sql .= " AND city_id = '" . intval ( $city_id ) . "'";
		}
		if (isset ( $area_id ) && ! empty ( intval ( $area_id ) )) {
			$sql .= " AND area_id = '" . intval ( $area_id ) . "'";
		}
		if (isset ( $contact ) && ! empty ( trim ( $contact ) )) {
			$sql .= " AND contact LIKE '%" . trim ( $contact ) . "%'";
		}
		if (isset ( $mobile ) && ! empty ( trim ( $mobile ) )) {
			$sql .= " AND mobile LIKE '%" . trim ( $mobile ) . "%'";
		}
		if (isset ( $start_at ) && ! empty ( trim ( $start_at ) )) {
			$sql .= " AND created_at >= '" . trim ( $start_at ) . "'";
		}
		if (isset ( $end_at ) && ! empty ( trim ( $end_at ) )) {
			$sql .= " AND created_at <= '" . trim ( $end_at ) . "'";
		}
		
		$pdo = self::_pdo ( 'db_r' );
		$resInfo = array ();
		$resInfo ['total'] = $pdo->YDGetOne ( str_replace ( '[*]', 'COUNT(1) num', $sql ) );
		
		$sort = isset ( $sort ) ? $sort : 'id';
		$order = isset ( $order ) ? $order : 'DESC';
		
		$sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
		$resInfo ['rows'] = $pdo->YDGetAll ( str_replace ( '[*]', $fields, $sql ) );
		return $resInfo;
	}
	
	/**
	 * 根据表商品skuID和子订单id获取该条记录信息
	 * 
	 * @param
	 *        	int product_item_sku_id 商品skuID
	 * @param
	 *        	int order_child_id 子订单ID
	 */
	public static function getInfoBySkuIDAndChild_order_id($order_child_id, $product_id) {
		$pdo = self::_pdo ( 'db_r' );
		$sql = "SELECT
                     *
                 FROM
                     " . self::getTb () . "
                 WHERE
                     is_del=" . self::DELETE_SUCCESS . "
                     AND child_order_id=" . intval ( $order_child_id ) . "
                     AND product_id=" . intval ( $product_id ) . " ";
		return $pdo->YDGetRow ( $sql );
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
    				COUNT(a.id) num
        		FROM
		            " . self::getTb () . " a
		        LEFT JOIN
		                " . CommonBase::$_tablePrefix . "order_child b
		        ON
		            a.child_order_id = b.id
		        WHERE
        		    a.supplier_id = " . SUPPLIER_ID . "
				AND
					a.user_id =" . intval ( $user_id ) . "
				AND
					a.discount_id = " . intval ( $type_id ) . "
			    AND
					b.child_status NOT IN(70,80)
				AND
					a.is_del" . self::DELETE_SUCCESS . "
				AND
					b.is_del" . self::DELETE_SUCCESS;
		
		$pdo = self::_pdo ( 'db_r' );
		
		return $pdo->YDGetOne ( $sql );
	}
	
	/**
	 * 根据子单号查询商品
	 * 
	 * @param int $id
	 *        	子单ID
	 */
	public static function getProductByChildID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['child_order_id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		$list = $pdo->clear ()->select ( 'id,product_id,logo_url,product_name,sale_price,market_price,sale_num,self_code,
		actual_amount,supplier_id,discount_type,discount_id' )->from ( self::$_tableName )->where ( $where )->getAll ();
		
		return $list;
	}
	
}