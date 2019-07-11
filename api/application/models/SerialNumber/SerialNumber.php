<?php

/** 
 * @desc  用于订单号生成
 * @version v1.0
 * @package SerialNumberModel
 * @author xianguo.huang
 * @time  2018-5-17
 */
namespace SerialNumber;

use Custom\YDLib;
use Common\CommonBase;

class SerialNumberModel extends \Common\CommonBase {
	protected static $_tableName = 'serial_number';
	private function __construct() {
		parent::__construct ();
	}
	
	/**
	 * 添加
	 *
	 * @param array $info        	
	 * @return mixed
	 *
	 */
	public static function addData($info) {
		$info ['is_del'] = '2';
		$db = YDLib::getPDO ( 'db_w' );
		$result = $db->insert ( self::$_tableName, $info, [ 
				'ignore' => true 
		] );
		
		return $result;
	}
}