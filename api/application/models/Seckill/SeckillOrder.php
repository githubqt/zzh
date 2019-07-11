<?php

/**
 * 竞价拍定金表model
 * @version v0.01
 * @time 2018-05-05
 */
namespace Seckill;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
use User\UserModel;

class SeckillOrderModel extends \Common\CommonBase {
	protected static $_tableName = 'seckill_order';
	private function __construct() {
		parent::__construct ();
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
	
	/*
	 *
	 * 获取用户是否已交定金
	 *
	 */
	public static function getOrderRow($info, $user_id) {
		if (! empty ( $info  ) && is_array ( $info ) && count ( $info   ) > 0) {
			extract ( $info  );
		}
		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = " a.*";
		$sql = 'SELECT
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.seckill_id = ' . $info ['id'] . '
		        AND
		            a.user_id = ' . $user_id . '
		        AND
		            a.product_id = ' . $info ['product_id'] . '
		        AND
		            a.supplier_id = ' . $info ['supplier_id'] . '
        		';
		
		$result ['list'] = $pdo->YDGetRow ( str_replace ( "[*]", $fileds, $sql ) );
		if ($result) {
			return $result;
		} else {
			return false;
		}
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
	/*
	 * 根据自增id 获取条有效信息
	 *
	 */
	public static function getInfoByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	/*
	 * 根据订单id 获取条有效信息
	 *
	 */
	public static function getSeckillOrderByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['order_no'] = $id;
		$where ['supplier_id'] = SUPPLIER_ID;
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	
	
	/*
	 * 根据用户 id,商品id 获取一条有效信息
	 *
	 */
	public static function getOrderByID($info) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['seckill_id'] = intval ( $info['seckill_id'] );
		$where ['user_id'] = intval ( $info['user_id'] );
		$where ['is_margin'] = 2;
		$where ['product_id'] = intval ( $info['product_id'] );
		$where ['supplier_id'] = SUPPLIER_ID;
	
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	
	/**
	 * 根据用户ID 关联ID 商户ID 商品ID 更新表记录
	 *
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateOrderByID($data, $info) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
	
		$pdo = self::_pdo ( 'db_w' );
		$up = $pdo->update ( self::$_tableName, $data, array ('seckill_id' => intval ( $info['seckill_id'] ) , 'product_id' => intval ( $info['product_id'] ) ,'supplier_id' => intval ( $info['supplier_id'] )
				, 'user_id'  => intval( $info['user_id'] ) , 'is_margin' => 2  ));
		if ($up) {
			//$adminInfo = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () );
			//$mem = YDLib::getMem ( 'memcache' );
			//$mem->delete ( 'Indexdata_' . $adminInfo ['supplier_id'] );
			//$mem->delete ( 'shareSeckill_' . $id );
				
			return $up;
		}
		return false;
	}
	
	
	
	/*
	 * 根据用户 id,状态 获取多条有效信息
	 *
	 */
	public static function getPersonalByID($info) {
		  
		$pdo = self::_pdo ( 'db_r' );
		$sql = 'SELECT
        		   a.*,b.on_status,s.is_del,CASE WHEN a.status in (2,3) THEN 2 ELSE 1 END manner
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product b
		       	ON
		       		b.id = a.product_id  
		         LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'seckill s
		       	ON
		       		s.id = a.seckill_id  
		        WHERE
        		    a.is_del = 2
		        AND
		            a.is_margin IN(2,3)
		        AND
		            a.status IN(1,2,3,6)
		        AND
		        	s.is_del = 2
		        AND
		            a.user_id = '.$info['user_id'].'
		        AND 
		            a.supplier_id = '.SUPPLIER_ID.'     
        		';
		
		if ($info['status'] != 0) {
			$sql .= " AND a.status = '".$info['status']."' ";
		}
		$sql .= "ORDER BY  field(manner,1,2) DESC,a.created_at DESC" ;
		$result  = $pdo->YDGetAll (  $sql  );
		
		return $result;
	}
	
	
	/*
	 * 根据关联 id,获取多条有效信息生成退款信息
	 *
	 */
	public static function getSeckillidByID($seckill_id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['seckill_id'] = intval ( $seckill_id);
		$where ['is_margin'] = 2;
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getAll ();
	}
	
	
	
	
	/*
	 * 根据用户 id,商品id 获取一条有效信息
	 *
	 */
	public static function getPersonalOrderByID($info) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['seckill_id'] = intval ( $info['seckill_id'] );
		$where ['user_id'] = intval ( $info['user_id'] );
		$where ['product_id'] = intval ( $info['product_id'] );
		$where ['supplier_id'] = SUPPLIER_ID;
	
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	
	
	
	
	/*
	 * 根据用户 id,状态 获取一条数据
	 *
	 */
	public static function getBubbleCount($user_id) {
	
		$pdo = self::_pdo ( 'db_r' );
		$sql = 'SELECT
        		   count(*) as num
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product b
		       	ON
		       		b.id = a.product_id
		         LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'seckill s
		       	ON
		       		s.id = a.seckill_id
		        WHERE
        		    a.is_del = 2
		        AND
		            a.is_margin IN(2,3)
		        AND
		            a.status IN(1,2)
		       	AND
		            b.on_status = 2
		        AND
		        	s.is_del = 2
		        AND
		            a.user_id = '.$user_id.'
		        AND
		            a.supplier_id = '.SUPPLIER_ID.'
        		';
	
		
	    $result	= $pdo->YDGetOne (  $sql  );
	   
		return $result;
	}
	
	
	
	
}