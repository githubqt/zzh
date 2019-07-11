<?php

/**
 * 地区运费表model
 * @version v0.01
 * @author laiqingtao
 * @time 2018-05-19
 */
namespace Freight;

use Custom\YDLib;

class FreightAreasModel extends \Common\CommonBase {
	/**
	 * 定义表名后缀
	 */
	protected static $_tableName = 'freight_areas';
	
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
		$data ['supplier_id'] = SUPPLIER_ID;
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
		$info = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		return $info;
	}
	
	/**
	 * 获取某个省份的运费
	 * 
	 * @param int $province_id
	 *        	省份id
	 */
	public static function getInfoByProvinceID($province_id) {
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['province_id'] = intval ( $province_id );
        $where ['is_del'] = self::DELETE_SUCCESS;
		
		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
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
		
		$filed = "id,province_id,province_name,freight";
		
		$sql = "SELECT 
        		    [*] 
        		FROM
		            " . self::getTb () . "
		        WHERE
        		    is_del=" . self::DELETE_SUCCESS . "
				AND	
					supplier_id = " . SUPPLIER_ID;
		
		if (isset ( $province_name ) && ! empty ( trim ( $province_name ) )) {
			$sql .= " AND province_name LIKE '%" . trim ( $province_name ) . "%'";
		}
		
		$pdo = self::_pdo ( 'db_r' );
		$resInfo = array ();
		$resInfo ['total'] = $pdo->YDGetOne ( str_replace ( '[*]', 'COUNT(1) num', $sql ) );
		
		$sql .= " LIMIT {$limit},{$rows}";
		$resInfo ['rows'] = $pdo->YDGetAll ( str_replace ( '[*]', $filed, $sql ) );
		return $resInfo;
	}
	
	/**
	 * 获取未设置运费的省份
	 * 
	 * @return array
	 */
	public static function getPer() {
		$sql = "SELECT 
					  a.area_id,a.area_name  
				FROM 
					" . self::$_tablePrefix . "areas a
				LEFT JOIN
					" . self::getTb () . " b
				ON
					a.area_id = b.province_id AND b.supplier_id = " . SUPPLIER_ID . "	
				WHERE
					a.parent_id = 0
				AND
					(
						b.id is null
					OR
						b.is_del = 1
					)		
				";
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->YDGetAll ( $sql );
	}
}