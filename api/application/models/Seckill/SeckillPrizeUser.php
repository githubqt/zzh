<?php

/**
 * 限时秒杀model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-22
 */
namespace Seckill;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
use User\UserSupplierModel;
use Coupan\CoupanModel;
use Coupan\UserCoupanModel;
use Product\ProductModel;
use Product\ProductStockLogModel;
use ErrnoStatus;
use Score\UserScoreModel;

class SeckillPrizeUserModel extends \Common\CommonBase {
	protected static $_tableName = 'seckill_prize_user';
	
	/**
	 * 添加信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addData($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['is_del'] = '2';
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$info ['supplier_id'] = SUPPLIER_ID;
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/* 获取列表 */
	public static function getList($attribute = array(), $page = 1, $rows = 10) {
		$limit = ($page - 1) * $rows;
		
		if (! empty ( $attribute ) && is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}
		
		$fileds = " 
		    a.id,a.supplier_id,a.seckill_id,a.level,a.prize_type,a.prize_value,a.note,a.status,a.created_at,u.name,u.user_img
		    ";
		$sql = 'SELECT 
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		     	LEFT JOIN
		     		' . CommonBase::$_tablePrefix . 'user u
		     	ON
		     		u.id = a.user_id	
		        WHERE
        		    a.is_del = 2
        		AND 
        		    a.is_prize = 2
        		AND 
        			a.supplier_id = ' . SUPPLIER_ID . '
        		';
		
		if (isset ( $aid ) && is_numeric ( $aid )) {
			$sql .= " AND a.seckill_id = " . $aid;
		}
		
		if (isset ( $user_id ) && is_numeric ( $user_id )) {
			$sql .= " AND a.user_id = " . $user_id;
		}
		
		if (isset ( $bind_id ) && is_numeric ( $bind_id )) {
			$sql .= " AND a.bind_id = " . $bind_id;
		}
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		
		$sort = isset ( $sort ) ? $sort : 'id';
		$order = isset ( $order ) ? $order : 'DESC';
		$sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
		
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		
		if (is_array ( $result ['list'] ) && count ( $result ['list'] ) > 0) {
			foreach ( $result ['list'] as $key => $value ) {
				if (! empty ( $value ['user_img'] )) {
					$result ['list'] [$key] ['user_img'] = HOST_FILE . $value ['user_img'];
				} else {
					$result ['list'] [$key] ['user_img'] = HOST_STATIC . 'common/images/common.png';
				}
				
				$result ['list'] [$key] ['created_at'] = date ( "m月d日 H:i", strtotime ( $value ['created_at'] ) );
			}
		}
		
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
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	/**
	 * 获取用户摇一摇次数
	 *
	 * @param interger $seckill_id        	
	 * @param interger $user_id        	
	 * @return mixed
	 *
	 */
	public static function getUserCount($seckill_id, $user_id) {
		$sql = 'SELECT 
        		   COUNT(*) num
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . '
		        WHERE
        		    is_del = 2
        		AND 
        			user_id = ' . $user_id . '
        		AND 
        			seckill_id = ' . $seckill_id . '
        		AND 
        			supplier_id = ' . SUPPLIER_ID . '
        		';
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$info ['total'] = $pdo->YDGetOne ( $sql );
		$sql .= ' AND to_days(created_at) = to_days(now()) ';
		$info ['today'] = $pdo->YDGetOne ( $sql );
		
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
	 * 根据一条自增ID更新表记录
	 * 
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $bind_id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateByBindID($data, $bind_id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$pdo = self::_pdo ( 'db_w' );
		$up = $pdo->update ( self::$_tableName, $data, array (
				'bind_id' => intval ( $bind_id ) 
		) );
		if ($up) {
			return $up;
		}
		return false;
	}
	
	/**
	 * 领取奖品接口
	 *
	 * @param interger $prizeInfo        	
	 * @return mixed
	 *
	 */
	public static function getPrize($user_id, $prizeInfo) {
		$pdo = self::_pdo ( 'db_w' );
		$pdo->beginTransaction ();
		$jsonData = [ ];
		try {
			// 更新领取状态
			$data ['status'] = '2';
			$data ['prize_at'] = date ( "Y-m-d H:i:s" );
			$res = self::updateByID ( $data, $prizeInfo ['id'] );
			if (! $res) {
				$pdo->rollback ();
				YDLib::output ( ErrnoStatus::STATUS_60576 );
			}
			if ($prizeInfo ['prize_type'] == '1') { // 更新用户积分
				$res = UserScoreModel::giveScore ( $user_id, 2, $prizeInfo ['id'], $prizeInfo ['prize_value'] );
				if (! $res ['status']) {
					$pdo->rollback ();
					YDLib::output ( $res ['code'] );
				}
			} else if ($prizeInfo ['prize_type'] == '2') { // 卡券发放
				$detail = CoupanModel::getInfoByID ( $prizeInfo ['prize_value'] );
				if ($detail ['remain_num'] <= 0) {
					$pdo->rollback ();
					YDLib::output ( ErrnoStatus::STATUS_60401 );
				}
				$coupandata = [ ];
				$coupandata ['supplier_id'] = SUPPLIER_ID;
				$coupandata ['user_id'] = $prizeInfo ['user_id'];
				$coupandata ['coupan_id'] = $prizeInfo ['prize_value'];
				$coupandata ['status'] = 1;
				$coupandata ['give_at'] = date ( "Y-m-d H:i:s" );
				$res = UserCoupanModel::addData ( $coupandata );
				if (! $res) {
					$pdo->rollback ();
					YDLib::output ( ErrnoStatus::STATUS_60402 );
				}
				$upcoupandata = [ ];
				$upcoupandata ['give_num'] = 1;
				$upcoupandata ['remain_num'] = - 1;
				$res = CoupanModel::autoUpdateByID ( $upcoupandata, $prizeInfo ['prize_value'] );
				if (! $res) {
					$pdo->rollback ();
					YDLib::output ( ErrnoStatus::STATUS_60402 );
				}
			} else if ($prizeInfo ['prize_type'] == '3') { // 商品减库存
				$log_id = ProductStockLogModel::addLog ( $prizeInfo ['prize_value'], 9, 0, - 1 );
				if (! $log_id) {
					$pdo->rollback ();
					YDLib::output ( ErrnoStatus::STATUS_60241 );
				}
				$upData = [ ];
				$upData ['lock_stock'] = - 1;
				$upData ['sale_num'] = 1;
				$res = ProductModel::autoUpdateByID ( $upData, $prizeInfo ['prize_value'] );
				if (! $res) {
					$pdo->rollback ();
					YDLib::output ( ErrnoStatus::STATUS_60241 );
				}
			}
			$pdo->commit ();
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
		} catch ( \Exception $e ) {
			$pdo->rollback ();
			YDLib::output ( ErrnoStatus::STATUS_60576 );
		}
	}
}