<?php

/**
 * 限时秒杀model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-05
 */
namespace Pushmsg;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
use Product\ProductModel;
use Common\SerialNumber;

class PushmsgRechargeModel extends \Common\CommonBase {
	
	/**
	 * 充值套餐
	 */
	const RECHARGE_ACTION_TYPE_1 = 1;
	const RECHARGE_ACTION_TYPE_2 = 2;
	const RECHARGE_ACTION_TYPE_3 = 3;
	const RECHARGE_ACTION_TYPE_4 = 4;
	const RECHARGE_ACTION_TYPE_VALUE = [ 
			self::RECHARGE_ACTION_TYPE_1 => [ 
					'id' => 1,
					'recharge_num' => 1000,
					'recharge_ament' => 70,
					'unit_price' => 0.07 
			],
			self::RECHARGE_ACTION_TYPE_2 => [ 
					'id' => 2,
					'recharge_num' => 10000,
					'recharge_ament' => 700,
					'unit_price' => 0.07 
			],
			self::RECHARGE_ACTION_TYPE_3 => [ 
					'id' => 3,
					'recharge_num' => 40000,
					'recharge_ament' => 2400,
					'unit_price' => 0.06 
			],
			self::RECHARGE_ACTION_TYPE_4 => [ 
					'id' => 4,
					'recharge_num' => 100000,
					'recharge_ament' => 5000,
					'unit_price' => 0.05 
			] 
	];
	
	/**
	 * 充值类型
	 */
	const RECHARGE_TYPE_1 = 1; // 系统充值
	const RECHARGE_TYPE_2 = 2; // 普通充值
	const RECHARGE_TYPE_VALUE = [ 
			self::RECHARGE_TYPE_1 => '系统充值',
			self::RECHARGE_TYPE_2 => '普通充值' 
	];
	
	/**
	 * 充值状态
	 */
	const RECHARGE_STATUS_1 = 1; // 待支付
	const RECHARGE_STATUS_2 = 2; // 已支付
	const RECHARGE_STATUS_VALUE = [ 
			self::RECHARGE_STATUS_1 => '待支付',
			self::RECHARGE_STATUS_2 => '已支付' 
	];
	protected static $_tableName = 'sms_recharge';
	private function __construct() {
		parent::__construct ();
	}
	
	/**
	 * 添加信息
	 * 
	 * @param int $type
	 *        	套餐类型
	 * @return mixed
	 */
	public static function addData($type) {
		$adminInfo = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () );
		$db = YDLib::getPDO ( 'db_w' );
		
		$info ['recharge_no'] = SerialNumber::createSN ( SerialNumber::SN_SMS );
		$info ['is_del'] = '2';
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$info ['supplier_id'] = $adminInfo ['supplier_id'];
		$info ['status'] = self::RECHARGE_STATUS_1;
		$info ['admin_name'] = $adminInfo ['fullname'];
		$info ['admin_id'] = $adminInfo ['id'];
		$info ['note'] = '';
		$info ['recharge_type'] = self::RECHARGE_TYPE_2;
		$info ['recharge_num'] = self::RECHARGE_ACTION_TYPE_VALUE [$type] ['recharge_num'];
		$info ['recharge_ament'] = self::RECHARGE_ACTION_TYPE_VALUE [$type] ['recharge_ament'];
		$info ['unit_price'] = self::RECHARGE_ACTION_TYPE_VALUE [$type] ['unit_price'];
		
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/* 获取列表 */
	public static function getList($attribute = array(), $page = 0, $rows = 10) {
		$limit = ($page - 1) * $rows;
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
		$result = array ();
		$result ['total'] = $pdo->YDGetOne ( str_replace ( '[*]', 'COUNT(1) num', $sql ) );
		$sql .= " LIMIT {$limit},{$rows}";
		$result ['rows'] = $pdo->YDGetAll ( str_replace ( '[*]', $fileds, $sql ) );
		
		if ($result) {
			if (is_array ( $result ['rows'] ) && count ( $result ['rows'] ) > 0) {
				foreach ( $result ['rows'] as $key => $value ) {
					$result ['rows'] [$key] ['recharge_type_txt'] = self::RECHARGE_TYPE_VALUE [$value ['recharge_type']];
					$result ['rows'] [$key] ['status_txt'] = self::RECHARGE_STATUS_VALUE [$value ['status']];
				}
			}
			
			return $result;
		} else {
			return false;
		}
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
	 * 获取单条数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		$where ['supplier_id'] = SUPPLIER_ID;
		
		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		
		$info ['recharge_type_txt'] = self::RECHARGE_TYPE_VALUE [$info ['recharge_type']];
		$info ['status_txt'] = self::RECHARGE_STATUS_VALUE [$info ['status']];
		if (! $info) {
			return FALSE;
		}
		
		return $info;
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
		return $up;
	}
	
	/**
	 * 获取有效数据
	 *
	 * @param
	 *        	interger
	 * @return mixed
	 *
	 */
	public static function getYesAll() {
		$sql = "SELECT
        		   a.id,a.product_id,a.product_name,a.starttime,a.endtime,a.is_restrictions,a.restrictions_num,
		           a.seckill_price,a.order_del
        		FROM
		             " . CommonBase::$_tablePrefix . self::$_tableName . " a
		        WHERE
        		    a.is_del = 2
		        AND
		            (a.status = 2 OR a.status = 1)
		        AND
                    a.starttime <= '" . date ( 'Y-m-d H:i:s' ) . "'
                AND
                    a.endtime >= '" . date ( 'Y-m-d H:i:s' ) . "'
        		";
		
		$pdo = self::_pdo ( 'db_r' );
		$result ['result'] = $pdo->YDGetAll ( $sql );
		$id = [ ];
		foreach ( $result ['result'] as $k => $value ) {
			$id [$k] = $value ['product_id'];
		}
		
		$result ['ids'] = implode ( ',', $id );
		return $result;
	}
}