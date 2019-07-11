<?php

/**
 * 地址model
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-14
 */
namespace Grade;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;

class GradeRightsModel extends \Common\CommonBase {
	protected static $_tableName = 'user_rights';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
	}
	
	/**
	 * 获取对应的list列表
	 *
	 * @param array $attribute
	 *        	获取对应的参数
	 * @param integer $page
	 *        	对应的页
	 * @param integer $rows
	 *        	取出的行数
	 * @return array
	 */
	public static function getList() {
		$adminId = AdminModel::getAdminID ();
		$adminInfo = AdminModel::getAdminLoginInfo ( $adminId );
		
		$pdo = YDLib::getPDO ( 'db_r' );
        $fields = " a.id,b.postage,b.id b_id,b.is_discount,b.discount,b.is_feedback,b.discount_txt,b.is_coupons,b.coupons,b.is_integral,b.integral,b.gift_txt";
		$giftsql = 'SELECT 
        		   [*]
        		FROM
				' . CommonBase::$_tablePrefix . 'user_grade a 
				LEFT  JOIN ' . CommonBase::$_tablePrefix . self::$_tableName . ' b
        		ON a.grade_id = b.user_grade_id
				AND b.supplier_id = ' . $adminInfo ['supplier_id'] . '
 				AND a.is_del =  2
				AND b.is_del =  2';
		
		$giftList = array ();
		$giftList ['list'] = $pdo->YDGetAll ( str_replace ( '[*]', $fields, $giftsql ) );
		return $giftList;
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
	public static function updatInfoByID($data, $id) {
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
	 * @param integer $user_id
	 *        	用户ID
	 * @param integer $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateByID($data, $id, $supplier_id) {
		$pdo = YDLib::getPDO ( 'db_w' );
		
		if ($supplier_id) {
			$rights = array ();
			$rights ['updated_at'] = date ( "Y-m-d H:i:s" );
			$rights ['discount'] = $data ['discount'];
			$rights ['feedback'] = $data ['feedback'];
			$right = $pdo->update ( 'user_rights', $rights, array (
					'id' => intval ( $id ),
					'supplier_id' => $supplier_id,
					'is_del' => '2' 
			) );
			
			return $right;
		}
		
		return false;
	}
	
	/**
	 * 根据表自增 ID删除记录
	 *
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID($id, $supplier_id) {
		$pdo = self::_pdo ( 'db_w' );
		$supdate = $pdo->delete ( CommonBase::$_tablePrefix . self::$_tableName, array (
				'id' => intval ( $id ),
				'supplier_id' => $supplier_id,
				'is_del' => '2' 
		) );
		
		return $supdate;
	}
	
	/**
	 * 禁用等级
	 *
	 * @param int $user_grade_id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteInfoByID($user_grade_id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		$data ['is_del'] = 1;
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'user_grade_id' => intval ( $user_grade_id ) 
		) );
	}
	
	/**
	 * 添加信息
	 *
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addData($info) {
		$supplier_id = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () ) ['supplier_id'];
		
		$db = YDLib::getPDO ( 'db_w' );
		$info ['is_del'] = '2';
		$info ['supplier_id'] = $supplier_id;
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/**
	 * 根据等级ID获取该条记录信息is_del也包含
	 *
	 * @param int $id
	 *        	表自增ID
	 */
	public static function getRigntsInfoByGradeIDOutDel($grade_id) {
		$supplier_id = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () ) ['supplier_id'];
		
		$where ['grade_id'] = intval ( $grade_id );
		$where ['supplier_id'] = intval ( $supplier_id );
		
		$pdo = self::_pdo ( 'db_r' );
		
		$users = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		return $users;
	}
	
	/**
	 * 根据表自增ID获取该条记录信息
	 *
	 * @param int $id
	 *        	表自增ID
	 */
	public static function getInfoByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		
		$uers = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		return $uers;
	}
	
	/*
	 *
	 * 根据用户ID获取该条等级优惠卷
	 *
	 * @param int $user_id
	 * 等级ID
	 *
	 */
	public static function getInfoByUserGradeId($grade_id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['grade_id'] = intval ( $grade_id );
		
		$pdo = self::_pdo ( 'db_r' );
		
		$uers = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		return $uers;
	}
	
	/**
	 * 根据等级ID获取该条等级权益
	 *
	 * @param int $grade_id
	 *        	等级ID
	 */
	public static function getInfoByGradeID($grade_id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['grade_id'] = intval ( $grade_id );
		$where ['supplier_id'] = SUPPLIER_ID;
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
}