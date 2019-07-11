<?php

/**
 * 活动商品model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-05
 */
namespace Seckill;

use Custom\YDLib;
use Common\CommonBase;

class SeckillProductModel extends \Common\CommonBase {
	protected static $_tableName = 'seckill_product';
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
		$result = $db->insert ( self::$_tableName, $info );

		return $result;
	}

	/* 获取商品列表 */
	public static function getList($attribute = array(), $page = 0, $rows = 10) {
		$limit = ($page) * $rows;

		if (! empty ( $attribute ) && is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}

		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = 'a.id,a.seckill_id,a.product_id,a.product_name,a.supplier_id,a.sale_price,a.group_price,a.oredr_product_num,
			p.logo_url,p.stock,p.on_status,s.starttime,s.endtime,s.number,s.status,s.is_restrictions,s.restrictions_num,s.onlookers_num,
			p.on_status,p.channel_status,
				CASE WHEN p.on_status = 2 THEN (p.on_status = 2) ELSE (p.channel_status = 3) END  
				';
		$sql = 'SELECT 
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN 
		        	 ' . CommonBase::$_tablePrefix . 'seckill s 
		        ON
		        	 a.seckill_id = s.id  
		        LEFT JOIN 
		        	 ' . CommonBase::$_tablePrefix . 'product p 
		        ON
		        	 a.product_id = p.id  
		        WHERE
        		     a.is_del = 2
		        AND
        		     a.supplier_id = ' . SUPPLIER_ID . '
		        AND
		             s.is_del = 2
		        AND
		             s.status = 2
        		AND
        		     p.is_del = 2     
		        AND
		             s.endtime > NOW() 
		        AND
		             p.stock > 0
        		';


		if (isset ( $type ) && ! empty ( $type )) {
			$sql .= " AND s.type = '" . $type . "' ";
		}

		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );

		switch ($order) {
			case 0 :
				$sql .= " GROUP BY a.id DESC ORDER BY s.endtime ASC limit {$limit},{$rows}";
				break;
			case 1 :
				$sql .= " GROUP BY a.id DESC ORDER BY  a.group_price DESC limit {$limit},{$rows}";
				break;
			case 2 :
				$sql .= " GROUP BY  a.id DESC ORDER BY  a.group_price ASC   limit {$limit},{$rows}";
				break;
			case 3 :
				$sql .= " GROUP BY  a.id DESC ORDER BY  s.oredr_product_num ASC   limit {$limit},{$rows}";
				break;
			case 4 :
				$sql .= " GROUP BY  a.id DESC ORDER BY  s.oredr_product_num DESC   limit {$limit},{$rows}";
				break;
		}
		
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		
		if (is_array ( $result ['list'] )) {
			foreach ( $result ['list'] as $key => $val ) {
				if (strtotime ( $val ['starttime'] ) <= time () && strtotime ( $val ['endtime'] ) >= time ()) {
					$result ['list'] [$key] ['status_txt'] = '抢购中';
					$result ['list'] [$key] ['status_num'] = '2';
					$result ['list'] [$key] ['time'] = floor ( strtotime ( $result ['list'] [$key] ['endtime'] ) - strtotime ( date ( 'y-m-d H:i:s', time () ) ) );
				} else if (strtotime ( $val ['starttime'] ) >= time () && strtotime ( $val ['endtime'] ) >= time ()) {
					$result ['list'] [$key] ['status_txt'] = '未开始';
					$result ['list'] [$key] ['status_num'] = '1';
					$result ['list'] [$key] ['time'] = floor ( strtotime ( $result ['list'] [$key] ['starttime'] ) - strtotime ( date ( 'y-m-d H:i:s', time () ) ) );
				} else if (strtotime ( $val ['starttime'] ) <= time () && strtotime ( $val ['endtime'] ) <= time ()) {
					$result ['list'] [$key] ['status_txt'] = '已结束';
					$result ['list'] [$key] ['status_num'] = '3';
				}
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
		$fileds = 'a.id,a.seckill_id,a.product_id,a.product_name,a.supplier_id,a.sale_price,a.group_price,a.oredr_product_num,
			p.logo_url,p.introduction,p.stock,p.brand_id,p.category_id,s.starttime,s.endtime,s.number,s.status,s.is_restrictions,s.restrictions_num,s.type,
				p.on_status,p.channel_status,
				CASE WHEN p.on_status = 2 THEN (p.on_status = 2) ELSE (p.channel_status = 3) END  
				';

		$sql = 'SELECT 
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN 
		        	 ' . CommonBase::$_tablePrefix . 'seckill s 
		        ON
		        	 a.seckill_id = s.id  
		        LEFT JOIN 
		        	 ' . CommonBase::$_tablePrefix . 'product p 
		        ON
		        	 a.product_id = p.id  
		        WHERE
        		     a.is_del = 2
		        AND
		             a.id = ' . $id . '
		        AND
        		     a.supplier_id = ' . SUPPLIER_ID . '
		        AND
		             s.is_del = 2
		        AND
		             s.status = 2
        		';

		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->YDGetRow ( str_replace ( "[*]", $fileds, $sql ) );
		return $info;
	}

	/**
	 * 获取商品活动数据
	 *
	 * @param interger $seckill_id
	 * @param interger $product_id
	 * @return mixed
	 *
	 */
	public static function getSeckillInfo($seckill_id, $product_id) {
		$fileds = 'a.seckill_id,a.product_id,s.starttime,s.endtime,s.is_restrictions,s.restrictions_num,s.type,
				   a.group_price seckill_price,s.number';

		$sql = 'SELECT 
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN 
		        	 ' . CommonBase::$_tablePrefix . 'seckill s 
		        ON
		        	 a.seckill_id = s.id  
		        WHERE
        		     a.is_del = 2
		        AND
		             a.seckill_id = ' . $seckill_id . '
		        AND
		             a.product_id = ' . $product_id . '
		        AND
        		     a.supplier_id = ' . SUPPLIER_ID . '
		        AND
		             s.is_del = 2
		        AND
		             s.status = 2
		        AND 
                     s.starttime <= "' . date ( 'Y-m-d H:i:s' ) . '"  
                AND 
                     s.endtime >= "' . date ( 'Y-m-d H:i:s' ) . '"	             		            
        		';

		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->YDGetRow ( str_replace ( "[*]", $fileds, $sql ) );
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
	 * 根据一条自增ID更新表记录
	 *
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $seckill_id
	 *        	活动ID
	 * @return boolean 更新结果
	 */
	public static function updateBySeckillID($data, $seckill_id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );

		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'seckill_id' => intval ( $seckill_id )
		) );
	}

	/**
	 * 获取拼团商品
	 *
	 * @param interger $id
	 * @return mixed
	 */
	public static function getSeckillProduct($id) {
		$sql = 'SELECT 
        		   *
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . '
		        WHERE
        		    is_del = 2
		        AND
		            seckill_id = ' . $id . '
        		';

		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->YDGetAll ( $sql );
		return $info;
	}

	/**
	 * 获取拼团商品
	 *
	 * @param interger $seckill_id
	 * @param interger $product_id
	 * @return mixed
	 */
	public static function getSeckillProductInfo($seckill_id, $product_id) {
		$sql = 'SELECT 
        		   *
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . '
		        WHERE
		            seckill_id = ' . $seckill_id . '
		        AND
		            product_id = ' . $product_id . '
        		';

		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->YDGetRow ( $sql );
		return $info;
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
}