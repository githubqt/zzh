<?php

/**
 * 在线售卖model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-09
 */
namespace OnlinePawn;

use Custom\YDLib;
use Common\CommonBase;

class OnlinePawnModel extends \Common\CommonBase {
	protected static $_tableName = 'online_pawnshop';
	private function __construct() {
		parent::__construct ();
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
}