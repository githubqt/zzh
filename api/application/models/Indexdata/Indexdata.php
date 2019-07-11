<?php

/**
 * 首页轮播model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-21
 */
namespace Indexdata;

use Overtrue\Pinyin;
use Custom\YDLib;
use Common\CommonBase;

class IndexdataModel extends \Common\CommonBase {
	protected static $_tableName = 'index_data';
	private function __construct() {
		parent::__construct ();
	}
	
	/* 获取列表 */
	public static function getList() {
		$mem = YDLib::getMem ( 'memcache' );
		$result = $mem->get ( 'Indexdata_' . SUPPLIER_ID );
		if (! $result) {
			$pdo = YDLib::getPDO ( 'db_r' );
			$fileds = " a.id,a.title_name,a.data_type,a.details,a.img_path,a.description";
			$sql = 'SELECT 
            		   [*]
            		FROM
    		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
    		        WHERE
            		    a.is_del = 2
    		        AND
    		            a.supplier_id = ' . SUPPLIER_ID . '
            		';
			
			$sql .= " 
    		          AND 
                          ((a.starttime <= '" . date ( 'Y-m-d H:i:s' ) . "'  
                      AND 
                          a.endtime >= '" . date ( 'Y-m-d H:i:s' ) . "') OR a.time_type = 2) 
                      AND 
                          a.status = 2";
			
			$result = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
			$mem->delete ( 'Indexdata_' . SUPPLIER_ID );
			$mem->set ( 'Indexdata_' . SUPPLIER_ID, $result );
		}
		
		if ($result) {
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
		        AND
		            a.supplier_id = ' . SUPPLIER_ID . '
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
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
}