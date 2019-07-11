<?php

/**
 * 商品model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
namespace User;

use Custom\YDLib;
use Common\CommonBase;
use Image\ImageModel;
use Product\ProductModel;

class UserConcernModel extends \Common\CommonBase {
	protected static $_tableName = 'user_concern';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
	}
	
	/* 获取列表 */
	public static function getList($user_id, $page = 1, $rows = 10) {
		$limit = ($page - 1) * $rows;

        $fields = " b.id,b.name,b.market_price,b.logo_url,
                CASE WHEN b.supplier_id = " . SUPPLIER_ID . " THEN b.sale_price ELSE c.sale_price END sale_price";
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
		       		c.product_id = b.id 
		       	AND
		       		c.supplier_id = '. SUPPLIER_ID.'    
		       	AND
		       		c.is_del = '.self::DELETE_SUCCESS.'   		       		     		       		
		        WHERE
        		    a.is_del = 2
        		AND 
        			a.supplier_id = ' . SUPPLIER_ID . '
         	    AND 
        			a.user_id=' . $user_id . '       			
        		AND 
        			(b.supplier_id = ' . SUPPLIER_ID . '
        			OR
        			  c.id is not null 
        			)
                AND (
                        (
                            b.supplier_id = ' . SUPPLIER_ID . ' 
                        AND 
                            b.on_status = 2
                        AND 
                            b.is_del = 2
                        )
                        OR
                        (
                            b.supplier_id != ' . SUPPLIER_ID . ' 
                        AND 
                            c.on_status = 2 
                        AND 
                            b.channel_status = 3
                        )
                     )
        	        ';
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		
		$sort = isset ( $sort ) ? $sort : 'id';
		$order = isset ( $order ) ? $order : 'DESC';
		$sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fields, $sql ) );
		if (is_array ( $result ['list'] )) {
			foreach ( $result ['list'] as $key => $value ) {
				if (! empty ( $value ['logo_url'] )) {
					$result ['list'] [$key] ['logo_url'] = HOST_FILE . self::imgSize ( $value ['logo_url'], 1 );
				} else {
					$result ['list'] [$key] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
				}
			}
		}
		
		return $result;
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
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/**
	 * 根据表自增 ID删除记录
	 * 
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID($user_id, $product_id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		$data ['supplier_id'] = SUPPLIER_ID;
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'product_id' => intval ( $product_id ),
				'user_id' => $user_id 
		) );
	}
	
	/**
	 * 获取单条数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByID($user_id, $product_id) {
		$where ['user_id'] = intval ( $user_id );
		$where ['product_id'] = intval ( $product_id );
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['is_del'] = '2';
		
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
		$data ['supplier_id'] = SUPPLIER_ID;
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
}