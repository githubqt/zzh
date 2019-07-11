<?php

/**
 * 权限model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-05
 */
namespace Sms;

class SmsModelModel extends \Common\CommonBase {
	protected static $_tableName = 'sms_model';
	
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
	 * 获取单条数据根据消息类型
	 *
	 * @param interger $type
	 * @return mixed
	 *
	 */
	public static function getInfoByType($type) {
	    $where ['is_del'] = self::DELETE_SUCCESS;
	    $where ['sms_set_type'] = intval ( $type );
	
	    $pdo = self::_pdo ( 'db_r' );
	    return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
}