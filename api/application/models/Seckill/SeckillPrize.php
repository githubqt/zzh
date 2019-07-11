<?php

/**
 * 摇一摇奖品model
 * @version v0.01
 * @author lqt
 * @time 2018-06-21
 */
namespace Seckill;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
use Product\ProductModel;

class SeckillPrizeModel extends \Common\CommonBase {
	
	/**
	 * 奖品等级
	 */
	const PRIZE_LEVEL_1 = 1; // 一等奖
	const PRIZE_LEVEL_2 = 2; // 二等奖
	const PRIZE_LEVEL_3 = 3; // 三等奖
	const PRIZE_LEVEL_4 = 4; // 普通奖
	const PRIZE_LEVEL_VALUE = [ 
			self::PRIZE_LEVEL_1 => '一等奖',
			self::PRIZE_LEVEL_2 => '二等奖',
			self::PRIZE_LEVEL_3 => '三等奖',
			self::PRIZE_LEVEL_4 => '普通奖' 
	];
	
	/**
	 * 奖品类型
	 */
	const PRIZE_TYPE_1 = 1; // 一等奖
	const PRIZE_TYPE_2 = 2; // 二等奖
	const PRIZE_TYPE_3 = 3; // 三等奖
	const PRIZE_TYPE_VALUE = [ 
			self::PRIZE_TYPE_1 => '赠送积分',
			self::PRIZE_TYPE_2 => '赠送优惠券',
			self::PRIZE_TYPE_3 => '赠送商品' 
	];
	protected static $_tableName = 'seckill_prize';
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
	public static function getList($attribute = array(), $page = 0, $rows = 10) {
		$limit = ($page) * $rows;
		if (! empty ( $attribute ['info'] ) && is_array ( $attribute ['info'] ) && count ( $attribute ['info'] ) > 0) {
			extract ( $attribute ['info'] );
		}
		$adminInfo = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () );
		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = " a.* ";
		$sql = 'SELECT 
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        WHERE
        		    a.is_del = 2
		        AND
		            a.supplier_id = ' . $adminInfo ['supplier_id'] . '
        		';
		
		if (isset ( $name ) && ! empty ( $name )) {
			$sql .= " AND a.name like '%" . $name . "%' ";
		}
		
		if (isset ( $type ) && ! empty ( $type )) {
			$sql .= " AND a.type = '" . $type . "' ";
		}
		
		if (isset ( $product_name ) && ! empty ( $product_name )) {
			$sql .= " AND a.product_name like '%" . $product_name . "%' ";
		}
		
		if (isset ( $status ) && ! empty ( $status )) {
			if ($status == '1') { // 待审核
				$sql .= " AND a.status = " . $status . " ";
			} else if ($status == '2') { // 未开始
				$sql .= " 
		          AND 
                      a.starttime >= '" . date ( 'Y-m-d H:i:s' ) . "'  
                  AND 
                      a.endtime >= '" . date ( 'Y-m-d H:i:s' ) . "' 
                  AND status = 2";
			} else if ($status == '3') { // 进行中
				$sql .= " 
		          AND 
                      a.starttime <= '" . date ( 'Y-m-d H:i:s' ) . "'  
                  AND 
                      a.endtime >= '" . date ( 'Y-m-d H:i:s' ) . "'
                  AND 
                      a.status = 2";
			} else if ($status == '4') { // 已结束
				$sql .= " 
		          AND 
                      a.starttime <= '" . date ( 'Y-m-d H:i:s' ) . "'  
                  AND 
                      a.endtime <= '" . date ( 'Y-m-d H:i:s' ) . "' 
                  AND 
                      a.status = 2";
			} else if ($status == '5') { // 已失效
				$sql .= "  AND a.status = 3";
			} else if ($status == '6') { // 已取消
				$sql .= " AND a.status = 4";
			}
		}
		
		if (isset ( $alias_name ) && ! empty ( $alias_name )) {
			$sql .= " AND a.alias_name like '%" . $alias_name . "%' ";
		}
		
		if (isset ( $start_time ) && isset ( $end_time ) && ! empty ( $start_time ) && ! empty ( $end_time )) {
			$sql .= " AND a.starttime >= '" . $start_time . " 00:00:00' ";
			$sql .= " AND a.endtime <= '" . $end_time . " 23:59:59'";
		}
		
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		$sql .= " GROUP BY a.id DESC limit {$limit},{$rows}";
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		if ($result) {
			return $result;
		} else {
			return false;
		}
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
		$info = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		return $info;
	}
	
	/**
	 * 获取奖品设定
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getPrize($id) {
		$sql = 'SELECT 
        		   *
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . '
		        WHERE
        		    is_del = 2
		        AND
		            seckill_id = ' . $id . '
		        ORDER BY level   
        		';
		
		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->YDGetAll ( $sql );
		foreach ( $info as $key => $value ) {
			$info [$key] ['level_txt'] = self::PRIZE_LEVEL_VALUE [$value ['level']];
			$info [$key] ['prize_type_txt'] = self::PRIZE_TYPE_VALUE [$value ['prize_type']];
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
		$up = $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
		if ($up) {
			return $up;
		}
		return false;
	}
	
	/**
	 * 获取奖品信息
	 * 
	 * @param int $seckill_id        	
	 * @param int $level        	
	 * @return boolean 更新结果
	 */
	public static function getInfo($seckill_id, $level) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['seckill_id'] = $seckill_id;
		$where ['level'] = $level;
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
}