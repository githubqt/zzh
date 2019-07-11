<?php

/**
 * 购物车model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-16
 */
namespace Cart;

use Custom\YDLib;
use Common\CommonBase;

class CartModel extends \Common\CommonBase {
	protected static $_tableName = 'cart';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
	}
	
	/**
	 * 添加
	 *
	 * @param array $info        	
	 * @return mixed
	 *
	 */
	public static function addData($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['is_del'] = '2';
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$result = $db->insert ( self::$_tableName, $info, [ 
				'ignore' => true 
		] );
		
		$mem = YDLib::getMem ( 'memcache' );
		$key = __CLASS__ . "::getInfoByUserID::" . SUPPLIER_ID . "::" . $info ['user_id'];
		$mem->delete ( $key );
		return $result;
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
		$res = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		
		return $res;
	}
	
	/**
	 * 获取购物车数量
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByUserID($user_id) {
		$mem = YDLib::getMem ( 'memcache' );
		$key = __CLASS__ . "::" . __FUNCTION__ . "::" . SUPPLIER_ID . "::" . $user_id;
		$res = $mem->get ( $key );
		if (! $res) {
			$sql = 'SELECT
        		   [*]
        		FROM
		              ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product b
		       	ON
		       		a.product_id = b.id
		       	LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		a.product_id = c.product_id
		        WHERE
        		    a.is_del = 2
        		AND
        			a.supplier_id = ' . SUPPLIER_ID . '
                 AND
                a.user_id=' . $user_id . '
                
                 AND  b.is_del = 2
                       
        	        ';
			
			$pdo = YDLib::getPDO ( 'db_r' );
			$sql = str_replace ( "[*]", "count(DISTINCT a.product_id) as num", $sql );
			$res = $pdo->YDGetOne ( $sql );
			$mem->delete ( $key );
			$mem->set ( $key, $res );
		}
		
		$res = $res ? $res : '0';
		return $res;
	}
	
	/**
	 * 获取单条数据根据user_id和product_id
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByUserIDAndProductId($user_id, $product_id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['user_id'] = intval ( $user_id );
		$where ['product_id'] = intval ( $product_id );
		$where ['supplier_id'] = SUPPLIER_ID;
		$mem = YDLib::getMem ( 'memcache' );
		$key = __CLASS__ . "::" . __FUNCTION__ . "::" . SUPPLIER_ID . "::" . $user_id . "_" . $product_id;
		$res = $mem->get ( $key );
		if (! $res) {
			$pdo = self::_pdo ( 'db_r' );
			$res = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
			$mem->delete ( $key );
			$mem->set ( $key, $res );
		}
		return $res;
	}
	
	/**
	 * 获取单条数据根据cart_id
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByCartId($cart_id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $cart_id );
		$where ['supplier_id'] = SUPPLIER_ID;
		
		$pdo = self::_pdo ( 'db_r' );
		$res = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		
		return $res;
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
	public static function updateByID($data, $where) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$where ['supplier_id'] = SUPPLIER_ID;
		$pdo = self::_pdo ( 'db_w' );
		$update = $pdo->update ( self::$_tableName, $data, $where );
		if ($update) {
			$mem = YDLib::getMem ( 'memcache' );
			
			$key = __CLASS__ . "::getInfoByUserIDAndProductId::" . SUPPLIER_ID . "::" . $where ['user_id'] . "_" . $where ['product_id'];
			$mem->delete ( $key );
			
			$key = __CLASS__ . "::getInfoByUserID::" . SUPPLIER_ID . "::" . $where ['user_id'];
			$mem->delete ( $key );
			
			return $update;
		}
		return false;
	}
	
	/**
	 * 根据用户 ID和商品id删除记录
	 *
	 * @param int $user_id
	 *        	表用户 ID
	 * @param int $product_id
	 *        	表商品 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID($where) {
		$where ['supplier_id'] = SUPPLIER_ID;
		
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		$del = $pdo->update ( self::$_tableName, $data, $where );
		if ($del) {
			$mem = YDLib::getMem ( 'memcache' );
			
			$key = __CLASS__ . "::getInfoByUserIDAndProductId::" . SUPPLIER_ID . "::" . $where ['user_id'] . "_" . $where ['product_id'];
			$mem->delete ( $key );
			
			$key = __CLASS__ . "::getInfoByUserID::" . SUPPLIER_ID . "::" . $where ['user_id'];
			$mem->delete ( $key );
		}
		return $del;
	}
	
	/* 获取列表 */
	public static function getList($user_id, $page = 1, $rows = 10) {
		$limit = ($page - 1) * $rows;

        $fields = "a.id as cart_id, b.id as product_id,b.name,b.market_price,b.logo_url,a.num,b.stock,a.sale_price,
		    b.on_status,b.channel_status,c.on_status channel_on_status,b.is_return,a.supplier_id,a.multi_point_id,
		    case when b.supplier_id = " . SUPPLIER_ID . " then '自营' else '供应' end product_from 
		    ";
		$sql = 'SELECT
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product b
		        ON
		       		a.product_id = b.id
                LEFT JOIN
                	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		a.product_id = c.product_id
              	AND
                	a.supplier_id = c.supplier_id
		        WHERE
        		    a.is_del = 2
        		AND
        			a.supplier_id = ' . SUPPLIER_ID . '
                 AND
                a.user_id=' . $user_id . '
                
                 AND  b.is_del = 2
                       
        	        ';
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		
		$sort = isset ( $sort ) ? $sort : 'id';
		$order = isset ( $order ) ? $order : 'DESC';
		$sql .= " ORDER BY a.{$sort} {$order} LIMIT {$limit},{$rows}";
		
		$sql = str_replace ( "[*]", $fields, $sql );
		
		$result ['list'] = $pdo->YDGetAll ( $sql );
		
		if (is_array ( $result ['list'] )) {
			foreach ( $result ['list'] as $key => $value ) {
				if (! empty ( $value ['logo_url'] )) {
					$result ['list'] [$key] ['logo_url'] = HOST_FILE . CommonBase::imgSize ( $value ['logo_url'], 6 );
				} else {
					$result ['list'] [$key] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * 获取所有绑定信息
	 *
	 * @return mixed
	 */
	public static function getCartAll($user_id, $multi_point_id, $supplier_id) {
		$pdo = YDLib::getPDO ( 'db_r' );

        $fields = "a.id as cart_id, b.id as product_id,b.name,b.market_price,b.logo_url,a.num,b.stock,a.sale_price,
		    b.on_status,b.channel_status,c.on_status channel_on_status,b.is_return,a.supplier_id,a.multi_point_id,
		    case when b.supplier_id = " . $supplier_id . " then '自营' else '供应' end product_from
		    ";
		$sql = 'SELECT
        		  [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product b
		        ON
		       		a.product_id = b.id
                LEFT JOIN
                	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		a.product_id = c.product_id
		        WHERE
        		    a.is_del = 2
		         AND
		            a.supplier_id = ' . $supplier_id . '
		         AND 
		            a.multi_point_id  = ' . $multi_point_id . '
		         AND
		            a.user_id = ' . $user_id . '
		            		';
		
		$list = $pdo->YDGetAll ( str_replace ( "[*]", $fields, $sql ) );
		
		if (is_array ( $list )) {
			foreach ( $list as $key => $value ) {
				if (! empty ( $value ['logo_url'] )) {
					$list [$key] ['logo_url'] = HOST_FILE . CommonBase::imgSize ( $value ['logo_url'], 6 );
				} else {
					$list [$key] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
				}
			}
		}
		return $list;
	}
}