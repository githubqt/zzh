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
use Seckill\SeckillPrizeUserModel;
use Seckill\SeckillPrizeModel;
use ErrnoStatus;
use Image\ImageModel;
use Brand\BrandModel;
use Product\ProductModel;

class SeckillModel extends \Common\CommonBase {
	protected static $_tableName = 'seckill';
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
		    a.id,a.starttime,a.endtime,a.seckill_price,b.id as product_id,b.name as product_name,a.apply_num,
		    b.self_code,b.market_price,b.sale_price,b.logo_url,b.stock,a.onlookers_num,b.on_status,b.channel_status,
				CASE WHEN b.on_status = 2 THEN (b.on_status = 2) ELSE (b.channel_status = 3) END  
		    ";
		$sql = 'SELECT 
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product b
		       	ON
		       		b.id = a.product_id      
		        WHERE
        		    a.is_del = 2
        		AND 
        			a.supplier_id = ' . SUPPLIER_ID . '
                AND 
                    a.status = 2
        	    AND
        			b.is_del = 2
        	    AND
        			b.stock > 0
        		';
		
		if (isset ( $type ) && ! empty ( $type )) {
			$sql .= " AND a.type = '" . $type . "' ";
			if ($type == 1) {
				$sql .= " AND a.apply_num IN(1,2) ";
			}
		}
		
		if ($status == 0 && $status != '') {
			$sql .= " 
		          AND 
                      a.starttime >= '" . date ( 'Y-m-d H:i:s' ) . "'  
                  AND 
                      a.endtime >= '" . date ( 'Y-m-d H:i:s' ) . "' 
                  ";
		}
		
		if ($status == 1 && $status != '') {
			$sql .= " 
		          AND 
                      a.starttime <= '" . date ( 'Y-m-d H:i:s' ) . "'  
                  AND 
                      a.endtime >= '" . date ( 'Y-m-d H:i:s' ) . "'
                  ";
		}
		if ($status == 2 && $status != '') {
			$sql .= "
		          AND 
                      a.starttime <= '" . date ( 'Y-m-d H:i:s' ) . "'  
                  AND 
                      a.endtime <= '" . date ( 'Y-m-d H:i:s' ) . "' 
                  ";
		}
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		
		switch ($order) {
			case 0 :
				$sql .= " ORDER BY field(a.apply_num,2,1,3) ASC ,a.id DESC LIMIT {$limit},{$rows}";
				break;
			case 1 :
				$sql .= " ORDER BY field(a.apply_num,2,1,3) ASC,a.seckill_price DESC   limit {$limit},{$rows}";
				break;
			case 2 :
				$sql .= " ORDER BY field(a.apply_num,2,1,3) ASC ,a.seckill_price  ASC  limit {$limit},{$rows}";
				break;
			case 3 :
				$sql .= " ORDER BY field(a.apply_num,2,1,3) ASC ,a.order_people_num ASC  limit {$limit},{$rows}";
				break;
			case 4 :
				$sql .= " ORDER BY field(a.apply_num,2,1,3) ASC,a.order_people_num   DESC  limit {$limit},{$rows}";
				break;
		}
		
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		
		if (is_array ( $result ['list'] )) {
			foreach ( $result ['list'] as $key => $val ) {
				if (! empty ( $val ['logo_url'] )) {
					$result ['list'] [$key] ['logo_url'] = HOST_FILE . self::imgSize ( $val ['logo_url'], 2 );
				} else {
					$result ['list'] [$key] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
				}
				
				if (strtotime ( $val ['starttime'] ) <= time () && strtotime ( $val ['endtime'] ) >= time ()) {
					$result ['list'] [$key] ['status_txt'] = '抢购中';
					$result ['list'] [$key] ['status_num'] = '2';
					$result ['list'] [$key] ['time'] = floor ( strtotime ( $result ['list'] [$key] ['endtime'] ) - strtotime ( date ( 'y-m-d H:i:s', time () ) ) );
					
					$data ['apply_num'] = '2';
					self::updateByID ( $data, $result ['list'] [$key] ['id'] );
				} else if (strtotime ( $val ['starttime'] ) >= time () && strtotime ( $val ['endtime'] ) >= time ()) {
					$result ['list'] [$key] ['status_txt'] = '未开始';
					$result ['list'] [$key] ['status_num'] = '1';
					$result ['list'] [$key] ['time'] = floor ( strtotime ( $result ['list'] [$key] ['starttime'] ) - strtotime ( date ( 'y-m-d H:i:s', time () ) ) );
					$result ['list'] [$key] ['starttime_txt'] = self::wordTime ( $result ['list'] [$key] ['time'] );
					$data ['apply_num'] = '1';
					self::updateByID ( $data, $result ['list'] [$key] ['id'] );
				} else if (strtotime ( $val ['starttime'] ) <= time () && strtotime ( $val ['endtime'] ) <= time ()) {
					$result ['list'] [$key] ['status_txt'] = '已结束';
					$result ['list'] [$key] ['status_num'] = '3';
					
					$data ['apply_num'] = '3';
					self::updateByID ( $data, $result ['list'] [$key] ['id'] );
				}
				if ($val ['stock'] <= '0') {
					$result ['list'] [$key] ['status_txt'] = '抢购完';
				}
			}
		}
		return $result;
	}
	public static function wordTime($times) {
		// 天数
		$day = floor ( $times / 86400 );
		// 小时
		$hour = floor ( ($times - 86400 * $day) / 3600 );
		// 分钟
		$minute = floor ( ($times - 86400 * $day - 3600 * $hour) / 60 );
		return $day . '天' . $hour . '小时' . $minute . '分钟';
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
	 * 获得全部数据
	 *
	 * @return mixed
	 */
	public static function getAll() {
		$pdo = YDLib::getPDO ( 'db_r' );
		$sql = 'SELECT 
        		   id,name,en_name
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        WHERE
        		    a.is_del = 2
        		';
		$result = $pdo->YDGetAll ( $sql );
		return $result;
	}
	
	/**
	 * 获取最新一条有效数据
	 *
	 * @param
	 *        	interger
	 * @return mixed
	 *
	 */
	public static function getLastOne() {
		$sql = "SELECT
        		   a.id,a.product_id,b.name as product_name,a.starttime,a.endtime,a.is_restrictions,a.restrictions_num,
		           a.seckill_price,a.order_del,b.stock,b.market_price,b.sale_price,b.on_status,b.channel_status,
					CASE WHEN b.on_status = 2 THEN (b.on_status = 2) ELSE (b.channel_status = 3) END  
        		FROM
		             " . CommonBase::$_tablePrefix . self::$_tableName . " a
		        LEFT JOIN
		             " . CommonBase::$_tablePrefix . "product b
		        ON
		            a.product_id = b.id
		        WHERE
        		    a.is_del = 2
		        AND 
		            a.status = 2
		        AND 
		            a.type = 1
		       -- AND 
                   -- a.starttime <= '" . date ( 'Y-m-d H:i:s' ) . "'  
                AND 
                    a.endtime >= '" . date ( 'Y-m-d H:i:s' ) . "'
                AND
		            b.stock > 0 
                AND
                    a.supplier_id ='" . SUPPLIER_ID . "' 
                AND
                    b.supplier_id ='" . SUPPLIER_ID . "' 
        		";
		
		$pdo = self::_pdo ( 'db_r' );
		$result = $pdo->YDGetRow ( $sql );
		return $result;
	}
	
	/**
	 * 获取最新一条有效数据根据商品id
	 *
	 * @param
	 *        	interger
	 * @return mixed
	 *
	 */
	public static function getInfoByProductId($product_id) {
		$sql = "SELECT
        		   a.id seckill_id,a.product_id,a.starttime,a.endtime,a.is_restrictions,a.restrictions_num,a.type,
        		   a.seckill_price,a.order_del,a.onlookers_num
        		FROM
		             " . CommonBase::$_tablePrefix . self::$_tableName . " a
		        WHERE
        		    a.is_del = 2
		        AND 
                    a.starttime <= '" . date ( 'Y-m-d H:i:s' ) . "'  
                AND 
                    a.endtime >= '" . date ( 'Y-m-d H:i:s' ) . "'
		        AND 
		            a.status = 2
		        AND 
		            a.type = 1
		        AND
        		    a.supplier_id = " . SUPPLIER_ID . "		            
		        AND
		            a.product_id = " . $product_id;
		
		$pdo = self::_pdo ( 'db_r' );
		$result = $pdo->YDGetRow ( $sql );
		return $result;
	}
	
	/**
	 * 获取最新一条数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getNewInfoByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		
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
		
		$pdo = self::_pdo ( 'db_w' );
		$up = $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
		if ($up) {
			
			$mem = YDLib::getMem ( 'memcache' );
			$mem->delete ( 'Indexdata_' . SUPPLIER_ID );
			return $up;
		}
		return false;
	}
	
	/**
	 * 更新商品销售数量
	 *
	 * @param integer $num
	 *        	更新字段作为key的数组
	 * @param integer $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function addSaleNumByID($id, $num) {
		$sql = "UPDATE  " . self::getTb () . " SET
					sale_num = sale_num + " . intval ( $num ) . ",
					updated_at = '" . date ( "Y-m-d H:i:s" ) . "'
				WHERE
				    id = " . intval ( $id ) . " 
				AND 
					supplier_id = " . SUPPLIER_ID . "	
					";
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->YDExecute ( $sql );
	}
	
	/**
	 * 字段自更新
	 *
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function autoUpdateByID($data, $id) {
		$sql = "UPDATE " . CommonBase::$_tablePrefix . self::$_tableName . " SET ";
		foreach ( $data as $key => $val ) {
			if ($val > 0)
				$val = '+' . $val;
			$sql .= "`{$key}` = (`{$key}` {$val}),";
		}
		$sql = substr ( $sql, 0, - 1 );
		
		$sql .= " WHERE id = " . $id;
		
		$pdo = self::_pdo ( 'db_w' );
		// YDLib::testlog($sql);
		return $pdo->YDExecute ( $sql );
	}
	
	/**
	 * 摇奖
	 *
	 * @return boolean 更新结果
	 */
	public static function shake($aid, $user_id, $pass_note, $count, $bind_id) {
		$redis = YDLib::getRedis ( 'redis', 'r' );
		$num = $redis->sSize ( "shake_" . SUPPLIER_ID . "_id_" . $aid );
		if (empty ( $num ) || $num <= 0) { // 没有库存了
			YDLib::output ( ErrnoStatus::STATUS_60560 );
		}
		
		// 随机取队列
		$redis = YDLib::getRedis ( 'redis', 'w' );
		$prize = $redis->sPop ( "shake_" . SUPPLIER_ID . "_id_" . $aid );
		if (empty ( $prize )) { // redis不存在
			YDLib::output ( ErrnoStatus::STATUS_60560 );
		}
		
		// 处理获奖信息
		$level = explode ( '_', $prize );
		$level = $level [0];
		
		$data = [ ];
		$data ['seckill_id'] = $aid;
		$data ['level'] = $level;
		$data ['prize_type'] = '0';
		$data ['prize_value'] = '0';
		$data ['note'] = $pass_note;
		$data ['user_id'] = $user_id;
		$data ['bind_id'] = $bind_id;
		$data ['status'] = '1'; // 1待领取2已领取
		$data ['is_prize'] = '1'; // 1未中奖2中奖
		
		$prizeData = [ ];
		$prizeData ['tips'] = '很遗憾！~';
		
		if ($level > 0) {
			$data ['is_prize'] = '2';
			// 查询中奖信息
			$prizeInfo = SeckillPrizeModel::getInfo ( $aid, $level );
			$data ['prize_type'] = $prizeInfo ['prize_type'];
			$data ['prize_value'] = $prizeInfo ['prize_value'];
			$data ['note'] = $prizeInfo ['note'];
			$prizeData ['tips'] = '恭喜您！~';
		}
		$res = SeckillPrizeUserModel::addData ( $data );
		if (empty ( $res )) { // 未中奖
			YDLib::output ( ErrnoStatus::STATUS_60573 );
		}
		
		$prizeData ['is_prize'] = $data ['is_prize'];
		$prizeData ['level'] = $data ['level'];
		$prizeData ['note'] = $data ['note'];
		$prizeData ['count'] = $count - 1;
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $prizeData );
	}
	
	/*
	 * 竞价拍获取全部有效列表数据
	 */
	public static function getBiddingAll($attribute = array(), $page = 1, $rows = 10) {
		$page = $page < 1 ? 1 : $page;
		$limit = ($page - 1) * $rows;
		if (is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}
		
		$pdo = self::_pdo ( 'db_r' );
		$fileds = " a.id,a.product_name,a.starttime,a.endtime,a.status,a.start_price,a.end_price,a.bid_lncrement,a.total_price,
				a.onlookers_num,a.apply_num,a.bigding_price,a.action_num,a.supplier_id,a.product_id,a.spey,b.on_status,b.channel_status,
				CASE WHEN b.on_status = 2 THEN (b.on_status = 2) ELSE (b.channel_status = 3) END ,
				CASE WHEN a.`is_restrictions` in (2,1) THEN 2 ELSE 1 END order_num
				";
		$sql = 'SELECT
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		             ' . CommonBase::$_tablePrefix . 'product b
		        ON
		            a.product_id = b.id
		        WHERE
        		    a.is_del = 2
		        AND
		            a.type = 3
		        AND 
		            a.is_restrictions IN(1,2)
		        AND 
		            a.status= 2
		        AND
		            a.supplier_id = ' . SUPPLIER_ID . '
        		';
		
		if ($info ['status'] == 0 && $info ['status'] != '') {
			// $sql .= "
			// AND
			// a.starttime >= '".date('Y-m-d H:i:s')."'
			// AND
			// a.endtime >= '".date('Y-m-d H:i:s')."'
			// ";
		}
		
		if ($info ['status'] == 1 && $info ['status'] != '') {
			$sql .= " 
		          AND 
                      a.starttime <= '" . date ( 'Y-m-d H:i:s' ) . "'  
                  AND 
                      a.endtime >= '" . date ( 'Y-m-d H:i:s' ) . "'
                  ";
		}
		if ($info ['status'] == 2 && $info ['status'] != '') {
			
			$sql .= "
					          AND
			                      a.starttime >= '" . date ( 'Y-m-d H:i:s' ) . "'
			                  AND
			                      a.endtime >= '" . date ( 'Y-m-d H:i:s' ) . "'
			                  ";
		}
		
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		
		switch ($info ['order']) {
			case 0 :
				if ($sort && $order) {
					$sql .= " ORDER BY a.{$sort} {$order} limit {$limit},{$rows}";
				} else {
					$sql .= " ORDER BY  field(a.is_restrictions,2,1,3) ASC, a.starttime DESC   limit {$limit},{$rows}";
				}
				break;
			case 1 :
				$sql .= " ORDER BY  field(a.is_restrictions,2,1,3) ASC,a.bigding_price  DESC   limit {$limit},{$rows}";
				break;
			case 2 :
				$sql .= " ORDER BY field(a.is_restrictions,2,1,3) ASC, a.bigding_price  ASC  limit {$limit},{$rows}";
				break;
			case 3 :
				$sql .= " ORDER BY field(a.is_restrictions,2,1,3) ASC, a.order_people_num  ASC  limit {$limit},{$rows}";
				break;
			case 4 :
				$sql .= " ORDER BY  field(a.is_restrictions,2,1,3) ASC,a.order_people_num  DESC  limit {$limit},{$rows}";
				break;
		}
		
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		
		foreach ( $result ['list'] as $key => $val ) {
			
			switch ($result ['list'] [$key] ['status']) {
				case 1 :
					break;
				case 2 :
					$result ['list'] [$key] ['status_txt'] = '进行中';
					
					if (strtotime ( $result ['list'] [$key] ['starttime'] ) <= time () && strtotime ( $result ['list'] [$key] ['endtime'] ) >= time ()) {
						$result ['list'] [$key] ['status_txt'] = '进行中';
						$result ['list'] [$key] ['status'] = '6'; // 进行中
						$result ['list'] [$key] ['time'] = floor ( strtotime ( $result ['list'] [$key] ['endtime'] ) - strtotime ( date ( 'y-m-d H:i:s', time () ) ) );
						
						$second = $result ['list'] [$key] ['time'];
						$time_d = floor ( $second / (24 * 60 * 60) );
						$second -= $time_d * 24 * 60 * 60;
						$time_h = floor ( $second / (60 * 60) );
						$second -= $time_h * 60 * 60;
						$time_i = floor ( $second / 60 );
						$second -= $time_i * 60;
						$time_s = $second % 60;
						
						$result ['list'] [$key] ['time_txt'] = '距离结束仅剩:' . $time_d . '天' . $time_h . '时' . $time_i . '分';
						$result ['list'] [$key] ['price_name'] = '当前价:';
						$result ['list'] [$key] ['total_price_txt'] = $result ['list'] [$key] ['total_price'];
						
						// 拍卖结束写入说明 2代表 进行中
						$data ['is_restrictions'] = '2';
						self::updateByID ( $data, $result ['list'] [$key] ['id'] );
					} else if (strtotime ( $result ['list'] [$key] ['starttime'] ) >= time () && strtotime ( $result ['list'] [$key] ['endtime'] ) >= time ()) {
						$result ['list'] [$key] ['status_txt'] = '预告中';
						$result ['list'] [$key] ['status'] = '5'; // 未开始
						$result ['list'] [$key] ['time'] = floor ( strtotime ( $result ['list'] [$key] ['starttime'] ) - strtotime ( date ( 'y-m-d H:i:s', time () ) ) );
						
						$second = $result ['list'] [$key] ['time'];
						$time_d = floor ( $second / (24 * 60 * 60) );
						$second -= $time_d * 24 * 60 * 60;
						$time_h = floor ( $second / (60 * 60) );
						$second -= $time_h * 60 * 60;
						$time_i = floor ( $second / 60 );
						$second -= $time_i * 60;
						$time_s = $second % 60;
						
						$result ['list'] [$key] ['time_txt'] = '距离开始仅剩:' . $time_d . '天' . $time_h . '时' . $time_i . '分';
						$result ['list'] [$key] ['price_name'] = '起拍价:';
						$result ['list'] [$key] ['total_price_txt'] = $result ['list'] [$key] ['start_price'];
						// 拍卖结束写入说明 1代表 预告中
						$data ['is_restrictions'] = '1';
						self::updateByID ( $data, $result ['list'] [$key] ['id'] );
					} else if (strtotime ( $result ['list'] [$key] ['starttime'] ) <= time () && strtotime ( $result ['list'] [$key] ['endtime'] ) <= time ()) {
						$result ['list'] [$key] ['status_txt'] = '已结束';
						$result ['list'] [$key] ['status'] = '7'; // 已结束
						$result ['list'] [$key] ['time'] = 0;
						$result ['list'] [$key] ['time_txt'] = '已结束:' . $result ['list'] [$key] ['endtime'];
						$result ['list'] [$key] ['price_name'] = '结束价:';
						$result ['list'] [$key] ['total_price_txt'] = $result ['list'] [$key] ['total_price'];
						// 拍卖结束写入说明 3代表 已结束
						$data ['is_restrictions'] = '3';
						self::updateByID ( $data, $result ['list'] [$key] ['id'] );
					}
					break;
				case 3 :
					break;
			}
		}
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * 根据商品id获取一条有效得数据获取保证金
	 *
	 * @param
	 *        	interger
	 * @return mixed
	 *
	 */
	public static function getInfoByBiddingId($seckill_id) {
		$sql = 'SELECT
        		   a.id,a.product_id,a.starttime,a.endtime,a.product_name,a.status,a.supplier_id,a.start_price,a.end_price
				,a.bid_lncrement,a.total_price,a.onlookers_num,a.apply_num,a.bigding_price,a.action_num,a.order_sale_price
				,a.is_restrictions
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.type = 3
		        AND
		            a.supplier_id = ' . SUPPLIER_ID . '
		        AND
		            a.id = ' . $seckill_id;
		
		$pdo = self::_pdo ( 'db_r' );
		$result = $pdo->YDGetRow ( $sql );
		
		return $result;
	}
	
	/*
	 * 竞价拍获取预告中和进行中的数据
	 */
	public static function getProceedAll($attribute = array(), $page = 1, $rows = 10) {
		$page = $page < 1 ? 1 : $page;
		$limit = ($page - 1) * $rows;
		if (is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}
		
		$pdo = self::_pdo ( 'db_r' );
		$fileds = "a.id,a.product_name,a.starttime,a.endtime,a.status,a.start_price,a.end_price,a.bid_lncrement,a.total_price,
				a.onlookers_num,a.apply_num,a.bigding_price,a.action_num,a.supplier_id,a.product_id,b.on_status,b.channel_status,
				CASE WHEN b.on_status = 2 THEN (b.on_status = 2) ELSE (b.channel_status = 3) END 
				";
		$sql = 'SELECT
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		             ' . CommonBase::$_tablePrefix . 'product b
		        ON
		            a.product_id = b.id
		        WHERE
		           a.is_restrictions IN(1,2)
		        AND
        		    a.is_del = 2
		        AND
		            a.type = 3
		        AND
		            a.status= 2
		        AND
		            a.supplier_id = ' . SUPPLIER_ID . '
        		';
		
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		if ($sort && $order) {
			$sql .= " ORDER BY a.{$sort} {$order} limit {$limit},{$rows}";
		} else {
			$sql .= "ORDER BY  field(a.is_restrictions,2,1,3)  ASC , a.endtime ASC limit {$limit},{$rows}";
		}
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * 获取最新一条有效数据根据id
	 *
	 * @param
	 *        	interger
	 * @return mixed
	 *
	 */
	public static function getnEndProductById($id) {
		$sql = "SELECT
        		a.id,a.product_id,a.product_name,a.starttime,a.endtime,a.is_restrictions,a.order_sale_price,a.status,a.supplier_id,
				a.type,a.spey,a.start_price,a.total_price,a.bigding_price
				FROM
		             " . CommonBase::$_tablePrefix . self::$_tableName . " a
		        WHERE
        		    a.is_del = 2
                AND
                    a.endtime <= '" . date ( 'Y-m-d H:i:s' ) . "'
		        AND
		            a.status = 2
		        AND
		            a.type = 3
		        AND
        		    a.supplier_id = " . SUPPLIER_ID . "
		        AND
		            a.id = " . $id;
		
		$pdo = self::_pdo ( 'db_r' );
		$result = $pdo->YDGetRow ( $sql );
		return $result;
	}
	
	/**
	 * 根据id 查询一条有效数据来展示在个人付款中心
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getUserOrderByID($id) {
		$pdo = YDLib::getPDO ( 'db_r' );
		
		$sql = 'SELECT 
        		   a.product_id,a.product_name,b.on_status,a.starttime,a.endtime,a.is_restrictions,a.order_sale_price
				,a.type,a.spey,a.number,a.start_price,a.bid_lncrement,a.total_price,a.onlookers_num,a.apply_num,a.bigding_price,b.logo_url
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product  b
		       	ON
		       		a.product_id = b.id      
		        WHERE
        		    a.is_del = 2
		        AND
		        	a.id = ' . $id . '
        		';
		
		$resInfo = $pdo->YDGetRow ( $sql );
		
		return $resInfo;
	}
}