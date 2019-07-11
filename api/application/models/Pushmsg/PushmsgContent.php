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

class PushmsgContentModel extends \Common\CommonBase {
	/**
	 * 短信类型
	 */
	const SMS_TYPE_1 = 1; // 普通短信
	const SMS_TYPE_2 = 2; // 营销短信
	const SMS_TYPE_VALUE = [ 
			self::SMS_TYPE_1 => '普通短信',
			self::SMS_TYPE_2 => '营销短信' 
	];
	protected static $_tableName = 'sms_log';
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
		$limit = ($page - 1) * $rows;
		if (! empty ( $attribute ['info'] ) && is_array ( $attribute ['info'] ) && count ( $attribute ['info'] ) > 0) {
			extract ( $attribute ['info'] );
		}
		$adminInfo = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () );
		
		$pdo = YDLib::getPDO ( 'db_r' );
		
		$fileds = " a.*,b.name model_name,b.type";
		$sql = 'SELECT 
        		   [*]
        		FROM
		           ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        LEFT JOIN
		           ' . CommonBase::$_tablePrefix . 'sms_model b 
				ON
					a.model_id = b.id     
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
					$result ['rows'] [$key] ['type_txt'] = self::SMS_TYPE_VALUE [$value ['type']];
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
		
		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		
		$info ['status_txt'] = self::COUPAN_STATUS_VALUE [$info ['status']];
		$info ['use_type_txt'] = self::COUPAN_STATUS_VALUE_VALUE [$info ['use_type']];
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
		if ($up) {
			//$adminInfo = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () );
			//$mem = YDLib::getMem ( 'memcache' );
			//$mem->delete ( 'Indexdata_' . $adminInfo ['supplier_id'] );
			//$mem->delete ( 'shareSeckill_' . $id );
			
			return $up;
		}
		return false;
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