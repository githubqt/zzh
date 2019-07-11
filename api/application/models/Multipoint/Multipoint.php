<?php

/**
 * 多网点model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-07-10
 */
namespace Multipoint;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
use Product\ProductMultiPointModel;
use AreaModel;

class MultipointModel extends \Common\CommonBase {
	protected static $_tableName = 'multi_point';
	private function __construct() {
		parent::__construct ();
	}
	
	/**
	 * 添加信息
	 *
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addData($info) {
		$adminInfo = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () );
		$db = YDLib::getPDO ( 'db_w' );
		$info ['is_del'] = '2';
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$info ['supplier_id'] = $adminInfo ['supplier_id'];
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/* 获取列表 */
	public static function getList($id) {
		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = " a.province_id  ";
		$sql = 'SELECT 
        		  [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        WHERE
        		    a.is_del = 2
		        AND
		            a.supplier_id = ' . SUPPLIER_ID . '
        		';
		
		if ($id != '0') {
			$sql .= "AND a.province_id = " . $id;
		}
		
		$list = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		if ($list) {
			$result = self::array_unique_fb ( $list );
		}
		if (is_array ( $list ) && count ( $list ) > 0) {
			foreach ( $result as $key => $val ) {
				
				if (! empty ( $val ['province_id'] )) {
					$areaInfo = AreaModel::getInfoByID ( $val ['province_id'] );
					$result [$key] ['province_txt'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
				} else {
					$result [$key] ['province_txt'] = '';
				}
			}
			
			if ($result) {
				return $result;
			}
		} else {
			$result [0] ['province_id'] = '-90';
			$result [0] ['province_txt'] = '其它';
			return $result;
		}
	}
	public static function array_unique_fb($origin) {
		foreach ( $origin as $key => $v ) {
			$v = json_encode ( $v );
			$temp [$key] = $v;
		}
		$temp = array_unique ( $temp );
		foreach ( $temp as $k => $val ) {
			$temp [$k] = json_decode ( $val, true );
		}
		return $temp;
	}
	
	/**
	 * 获取单条数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByID($id, $longitude, $latitude) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		if ($longitude && $latitude) {
			$info ['distance'] = self::getDistance ( $latitude, $longitude, $info ['longitude'], $info ['dimension'] );
		}
		return $info;
	}
	
	/*
	 * 1.纬度1，经度1，纬度2，经度2
	 * 2.返回结果是单位是kM。
	 * 3.保留一位小数
	 */
	public static function getDistance($lat1, $lng1, $lat2, $lng2) {
		$distance = '';
		
		$EARTH_RADIUS = 6378.137;
		$radLat1 = $lat1 * M_PI / 180.0;
		$radLat2 = $lat2 * M_PI / 180.0;
		$a = $radLat1 - $radLat2;
		$b = $lng1 * M_PI / 180.0 - $lng2 * M_PI / 180.0;
		$s = 2 * asin ( sqrt ( pow ( sin ( $a / 2 ), 2 ) + cos ( $radLat1 ) * cos ( $radLat2 ) * pow ( sin ( $b / 2 ), 2 ) ) );
		$s = $s * $EARTH_RADIUS;
		$s = round ( $s * 10000 ) / 10000;
		$qm = $s * 1000;
		$m = sprintf ( "%.2f", $qm );
		if ($qm < 1000) {
			$distance = $m . "m";
		} else {
			$s = sprintf ( "%.2f", $s );
			$distance = $s . "km";
		}
		return $distance;
	}
	
	/**
	 * 获取最新一条有效数据
	 *
	 * @param
	 *        	interger
	 * @return mixed
	 *
	 */
	public static function getProductById($id) {
		$sql = "SELECT
        		   a.id,a.product_id,b.name as product_name,a.starttime,a.endtime,a.is_restrictions,a.restrictions_num,
		           a.seckill_price,a.order_del,b.stock,b.market_price,b.sale_price
        		FROM
		             " . CommonBase::$_tablePrefix . self::$_tableName . " a
		        LEFT JOIN
		             " . CommonBase::$_tablePrefix . "product b
		        ON
		            a.product_id = b.id
		        WHERE
        		    a.is_del = 2
		        AND
		            a.id = " . $id;
		
		$pdo = self::_pdo ( 'db_r' );
		$result = $pdo->YDGetRow ( $sql );
		return $result;
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
		if ($up) {
			return $up;
		}
		return false;
	}
	
	/**
	 * 根据表自增 ID删除记录
	 *
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID($id) {
		$adminInfo = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () );
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ),
				'supplier_id' => $adminInfo ['supplier_id'] 
		) );
	}
	
	/**
	 * 获得全部数据
	 *
	 * @return mixed
	 */
	public static function getAll() {
		$adminInfo = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () );
		$pdo = YDLib::getPDO ( 'db_r' );
		$sql = 'SELECT
        		   id,name
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.supplier_id = ' . $adminInfo ['supplier_id'];
		$result = $pdo->YDGetAll ( $sql );
		return $result;
	}
	
	/**
	 * 获得全部区级数据
	 *
	 * @return mixed
	 */
	public static function getProvinceAll($id) {
		$pdo = YDLib::getPDO ( 'db_r' );
		$sql = 'SELECT
        		   a.province_id,a.area_id
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND
		             province_id = ' . $id . '	
		        AND
		            a.supplier_id = ' . SUPPLIER_ID;
		
		$list = $pdo->YDGetAll ( $sql );
		if ($list) {
			$result = self::array_unique_fb ( $list );
		}
		if (is_array ( $list ) && count ( $list ) > 0) {
			foreach ( $result as $key => $val ) {
				
				// 区域
				if (! empty ( $val ['area_id'] )) {
					$areaInfo = AreaModel::getInfoByID ( $val ['area_id'] );
					$result [$key] ['area_txt'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
				} else {
					$result [$key] ['area_txt'] = '';
				}
			}
			return $result;
		} else {
			$result [0] ['area_txt'] = '其它';
			$result [0] ['area_id'] = '-90';
			$result [0] ['province_id'] = '-90';
			return $result;
		}
	}
	
	/**
	 * 获得全部区级数据
	 *
	 * @return mixed
	 */
	public static function geCompleteAll($id) {
		$pdo = YDLib::getPDO ( 'db_r' );
		$sql = 'SELECT
        		   *
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND
		             a.area_id = ' . $id . '
		        AND
		            a.supplier_id = ' . SUPPLIER_ID;
		
		$list = $pdo->YDGetAll ( $sql );
		if(is_array ( $list ) && count ( $list ) > 0){
			foreach ( $list as $key => $val ) {
				$list [$key] ['count'] = ProductMultiPointModel::MultipointCount ( $val );
			}
			return $list;
		}else {
			$list[0]['id'] = '-90';
			$list[0]['province_id'] = '-90';
			$list[0]['supplier_id'] = SUPPLIER_ID;
			$list[0]['is_del'] = '2';
			$list[0]['name'] = '暂只支持线上销售';
			return $list;
		}
		
	}
}