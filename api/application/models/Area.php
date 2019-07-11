<?php

/**
 * Created by PhpStorm.
 * User: lqt
 * Date: 2018/4/23
 * Time: 下午12:51
 */
use Custom\YDLib;
use Core\Queue;
use Common\DataType;
class AreaModel extends \Common\CommonBase {
	protected static $_tableName = 'areas';
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
	}
	
	/**
	 * 获取对应的列表
	 * 
	 * @param interger $pid
	 *        	获取对应的参数
	 * @return array
	 */
	public static function getChild($pid = 0) {
		$mem = YDLib::getMem ( 'memcache' );
		$area = $mem->get ( 'area_' . $pid );
		if (! $area) {
			
			$sql = "SELECT 
						  area_id,parent_id,area_name  
					FROM 
						" . self::getTb () . " 
					WHERE
						parent_id = {$pid}
				";
			$pdo = self::_pdo ( 'db_r' );
			$area = $pdo->YDGetAll ( $sql );
			
			$mem->delete ( 'area_' . $pid );
			$mem->set ( 'area_' . $pid, $area );
		}
		
		return $area;
	}
	
	/**
	 * 获取省自治区列表
	 * 
	 * @param bool $ref
	 *        	是否返回所有数据
	 * @return array
	 */
	public static function getProvince() {
		$sql = "SELECT
                     *
                 FROM
                     " . self::getTb () . "
                 WHERE parent_id = '0'";
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->YDGetAll ( $sql );
	}
	/**
	 * 根据表自增ID获取该条记录信息
	 * 
	 * @param int $id
	 *        	表自增ID
	 */
	public static function getInfoByID($id) {
		$where ['area_id'] = intval ( $id );
		
		$mem = YDLib::getMem ( 'memcache' );
		$key = __CLASS__ . "::" . __FUNCTION__ . "::" . $id;
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
	 * 获取省市县
	 * 
	 * @param integer $province
	 *        	省id
	 * @param integer $city
	 *        	市id
	 * @param integer $area
	 *        	县id
	 * @return string
	 */
	public static function getPca($province, $city, $area) {
		$province = $province ? $province : 0;
		$city = $city ? $city : 0;
		$area = $area ? $area : 0;
		$sql = "SELECT area_name FROM " . self::getTb () . " WHERE area_id={$province} OR area_id={$city} OR area_id={$area} ORDER BY level";
		$pdo = self::_pdo ( 'db_r' );
		$resInfo = $pdo->YDGetAll ( $sql );
		$result = '';
		foreach ( $resInfo as $value ) {
			$result .= $value ['area_name'] . ' ';
		}
		return $result;
	}
	
	/**
	 * 根据拼音获取省自治区
	 * 
	 * @param bool $ref
	 *        	是否返回所有数据
	 * @return array
	 */
	public static function getProvinceByPinyin($pinyin, $parent_id = '0') {
		$sql = "SELECT
                     *
                 FROM
                     " . self::getTb () . "
                 WHERE parent_id = '" . $parent_id . "'
                 AND area_pinyin = '" . $pinyin . "' ";
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->YDGetRow ( $sql );
	}
}