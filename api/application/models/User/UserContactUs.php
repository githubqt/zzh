<?php

/**
 * 用户建议图片model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-15
 */
namespace User;

use Custom\YDLib;
use Common\CommonBase;

class UserContactUsModel extends \BaseModel {
	protected static $_tableName = 'user_contact_us';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
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
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$result = $db->insert ( self::$_tableName, $info );
		
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
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
	
	/**
	 * 根据表自增 ID删除记录
	 * 
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID($id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}

    /**
     * 通过手机查询详细
     * @param $mobile
     * @return bool|null
     */
	public static function getInfoByMobile($mobile)
    {
        $where ['is_del'] = self::DELETE_SUCCESS;
        $where ['mobile'] = $mobile;
        $where ['supplier_id'] = SUPPLIER_ID;

        $pdo = self::newRead();
        $info = $pdo->clear ()->select ( 'id,user_id,supplier_id,mobile,note,status,admin_id,admin_name' )
            ->from ( self::$_tableName )->where ( $where )
            ->order('id desc')
            ->getRow ();
        if (is_null($info)) {
            return FALSE;
        }
        return $info;
    }
}