<?php

/**
 * 地址model
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-14
 */
namespace User;

use Custom\YDLib;
use Common\CommonBase;
use AreaModel;

class UserAddressModel extends \Common\CommonBase {
	protected static $_tableName = 'user_address';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
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
		$page = $page < 1 ? 1 : $page;
		$limit = ($page - 1) * $rows;
		if (is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}

        $fields = "id,name,mobile,province,city,area,street,address,user_id,is_default";
		
		$sql = "SELECT 
        		    [*] 
        		FROM
		            " . self::getTb () . "
		        WHERE
        		    is_del=" . self::DELETE_SUCCESS . "
        		AND 
        			 supplier_id=" . SUPPLIER_ID;
		
		if (! empty ( $user_id ) && is_numeric ( $user_id )) {
			$sql .= " AND user_id = " . $user_id;
		}
		
		$pdo = self::_pdo ( 'db_r' );
		$resInfo = array ();
		$resInfo ['row'] = $pdo->YDGetOne ( str_replace ( '[*]', 'COUNT(1) num', $sql ) );
		$sort = isset ( $sort ) ? $sort : 'is_default';
		$order = isset ( $order ) ? $order : 'DESC';
		$sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
		$resList = $pdo->YDGetAll ( str_replace ( '[*]', $fields, $sql ) );
		if (is_array ( $resList ) && count ( $resList ) > 0) {
			$is_extens = false;
			foreach ( $resList as $key => $value ) {
				if ($value ['is_default'] == '2') {
					$is_extens = true;
				}
				
				// 省份
				if (! empty ( $value ['province'] )) {
					$areaInfo = AreaModel::getInfoByID ( $value ['province'] );
					$resList [$key] ['province'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
				} else {
					$resList [$key] ['province'] = '';
				}
				// 城市
				if (! empty ( $value ['city'] )) {
					$areaInfo = AreaModel::getInfoByID ( $value ['city'] );
					$resList [$key] ['city'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
				} else {
					$resList [$key] ['city'] = '';
				}
				
				// 区域
				if (! empty ( $value ['area'] )) {
					$areaInfo = AreaModel::getInfoByID ( $value ['area'] );
					$resList [$key] ['area'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
				} else {
					$resList [$key] ['area'] = '';
				}
				
				// 街道
				if (! empty ( $value ['street'] )) {
					$areaInfo = AreaModel::getInfoByID ( $value ['street'] );
					$resList [$key] ['street'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
				} else {
					$resList [$key] ['street'] = '';
				}
			}
			if ($is_extens == false) {
				$resList ['0'] ['is_default'] = '2';
			}
		}
		
		$resInfo ['list'] = $resList;
		$resInfo ['page'] = $page;
		return $resInfo;
	}
	
	/**
	 * 添加信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addAddress($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['is_del'] = '2';
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/**
	 * 获得默认地址
	 * 
	 * @param int $user_id
	 *        	用户ID
	 * @return array
	 */
	public static function getDefault($user_id) {
		$where ['user_id'] = $user_id;
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['is_del'] = '2';
		$where ['is_default'] = 2;
		
		$mem = YDLib::getMem ( 'memcache' );
		$key = __CLASS__ . "::" . __FUNCTION__ . "::" . SUPPLIER_ID . "::" . $user_id;
		// $res = $mem->delete($key);
		$res = $mem->get ( $key );
		$pdo = YDLib::getPDO ( 'db_w' );
		if (! $res) {
			
			$res = $pdo->clear ()->select ( '`id`,`name`,`mobile`,`province` ,`city`,`area` ,`street`,`address` ,`supplier_id`,`user_id`,`is_default`' )->from ( self::$_tableName )->where ( $where )->getRow ();
			
			$mem->delete ( $key );
			$mem->set ( $key, $res );
		}
		
		if (! $res) {
			return FALSE;
		}
		
		if (! empty ( $res ['province'] )) {
			$areaInfo = AreaModel::getInfoByID ( $res ['province'] );
			$res ['province_txt'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
		} else {
			$res ['province_txt'] = '';
		}
		// 城市
		if (! empty ( $res ['city'] )) {
			$areaInfo = AreaModel::getInfoByID ( $res ['city'] );
			$res ['city_txt'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
		} else {
			$res ['city_txt'] = '';
		}
		
		// 区域
		if (! empty ( $res ['area'] )) {
			$areaInfo = AreaModel::getInfoByID ( $res ['area'] );
			$res ['area_txt'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
		} else {
			$res ['area_txt'] = '';
		}
		
		// 街道
		if (! empty ( $res ['street'] )) {
			$areaInfo = AreaModel::getInfoByID ( $res ['street'] );
			$res ['street_txt'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
		} else {
			$res ['street_txt'] = '';
		}
		
		return $res;
	}
	
	/**
	 * 根据表自增ID获取该条记录信息
	 * 
	 * @param int $id
	 *        	表自增ID
	 * @param int $user_id
	 *        	用户ID
	 * @return array
	 */
	public static function getInfoByID($id, $user_id = null) {
		$where ['id'] = intval ( $id );
		if ($user_id) {
			$where ['user_id'] = $user_id;
		}
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['is_del'] = 2;
		$mem = YDLib::getMem ( 'memcache' );
		$key = __CLASS__ . "::" . __FUNCTION__ . "::" . $id . "::" . $user_id;
		$res = $mem->get ( $key );
		if (! $res) {
			$pdo = self::_pdo ( 'db_r' );
			
			$res = $pdo->clear ()->select ( '`id`,`name`,`mobile`,`province` ,`city`,`area` ,`street`,`address` ,`supplier_id`,`user_id`,`is_default`' )->from ( self::$_tableName )->where ( $where )->getRow ();
			
			// 用户地址不存在
			if (! $res) {
				return FALSE;
			}
			
			if (! empty ( $res ['province'] )) {
				$areaInfo = AreaModel::getInfoByID ( $res ['province'] );
				$res ['province_txt'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
			} else {
				$res ['province_txt'] = '';
			}
			// 城市
			if (! empty ( $res ['city'] )) {
				$areaInfo = AreaModel::getInfoByID ( $res ['city'] );
				$res ['city_txt'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
			} else {
				$res ['city_txt'] = '';
			}
			
			// 区域
			if (! empty ( $res ['area'] )) {
				$areaInfo = AreaModel::getInfoByID ( $res ['area'] );
				$res ['area_txt'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
			} else {
				$res ['area_txt'] = '';
			}
			
			// 街道
			if (! empty ( $res ['street'] )) {
				$areaInfo = AreaModel::getInfoByID ( $res ['street'] );
				$res ['street_txt'] = isset ( $areaInfo ['area_name'] ) ? $areaInfo ['area_name'] : '';
			} else {
				$res ['street_txt'] = '';
			}
			
			$mem->delete ( $key );
			$mem->set ( $key, $res );
		}
		
		return $res;
	}
	
	/**
	 * 根据一条自增ID更新表记录
	 * 
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param integer $user_id
	 *        	用户ID
	 * @param integer $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateByID($data, $user_id, $id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$pdo = self::_pdo ( 'db_w' );
		$res = $pdo->update ( self::$_tableName, $data, array (
				'supplier_id' => SUPPLIER_ID,
				'id' => intval ( $id ),
				'user_id' => intval ( $user_id ) 
		) );
		if ($res) {
			$mem = YDLib::getMem ( 'memcache' );
			$key = __CLASS__ . "::getInfoByID::" . $id;
			$mem->delete ( $key );
			
			$key = __CLASS__ . "::getDefault::" . SUPPLIER_ID . "::" . $user_id;
			$mem->delete ( $key );
		}
		
		return $res;
	}
	
	/**
	 * 设置所有收货地址为非默认地址
	 * 
	 * @param integer $user_id
	 *        	用户ID
	 * @return boolean 更新结果
	 */
	public static function setNoDefault($user_id) {
		$pdo = self::_pdo ( 'db_w' );
		$data ['is_default'] = 1;
		$res = $pdo->update ( self::$_tableName, $data, array (
				'supplier_id' => SUPPLIER_ID,
				'user_id' => intval ( $user_id ) 
		) );
		if ($res) {
			$mem = YDLib::getMem ( 'memcache' );
			$key = __CLASS__ . "::getDefault::" . SUPPLIER_ID . "::" . $user_id;
			$mem->delete ( $key );
		}
		
		return $res;
	}
	
	/**
	 * 根据表自增 ID删除记录
	 * 
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID($user_id, $id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		$data ['supplier_id'] = SUPPLIER_ID;
		
		$pdo = self::_pdo ( 'db_w' );
		$res = $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
		if ($res) {
			$mem = YDLib::getMem ( 'memcache' );
			$key = __CLASS__ . "::getInfoByID::" . $id;
			$mem->delete ( $key );
			
			$key = __CLASS__ . "::getDefault::" . SUPPLIER_ID . "::" . $user_id;
			$mem->delete ( $key );
		}
		
		return $res;
	}
}