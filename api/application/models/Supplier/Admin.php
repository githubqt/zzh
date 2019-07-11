<?php

/**
 * 管理员model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
namespace Supplier;

use Custom\YDLib;
use Common\CommonBase;

class AdminModel extends \BaseModel {
	protected static $_tableName = 'admin';
	static $_login = 'FGRTYUSDS';
	
	/**
	 * 获取用户信息
	 * 
	 * @param unknown $UserId        	
	 * @param number $headImgSize        	
	 * @return multitype:|Ambigous <unknown, string>
	 */
	public static function getAdminInfo($UserId) {
		if (! $UserId)
			return [ ];
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$user = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( [ 
				'id' => $UserId 
		] )->getRow ();
		
		if ($user) {
			return $user;
		}
		
		return false;
	}
	
	/* 获取列表 */
	public static function getList($attribute = array(), $page = 0, $rows = 10) {
		if (! empty ( $attribute ['info'] ) && is_array ( $attribute ['info'] ) && count ( $attribute ['info'] ) > 0) {
			extract ( $attribute ['info'] );
		}
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = " a.* ";
		$sql = 'SELECT
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND a.type = 2
        		';
		
		if (isset ( $name ) && ! empty ( $name )) {
			$sql .= " AND a.name like '%" . $name . "%' ";
		}
		
		if (isset ( $id ) && ! empty ( $id )) {
			$sql .= " AND a.id like '%" . $id . "%' ";
		}
		
		if (isset ( $fullname ) && ! empty ( $fullname )) {
			$sql .= " AND a.fullname like '%" . $fullname . "%' ";
		}
		if (isset ( $status ) && ! empty ( $status )) {
			$sql .= " AND a.status = " . $status . " ";
		}
		if (isset ( $mobile ) && ! empty ( $mobile )) {
			$sql .= " AND a.mobile = " . $mobile . " ";
		}
		if (isset ( $role_id ) && ! empty ( $role_id )) {
			$sql .= " AND a.role_id = " . $role_id . " ";
		}
		
		if (isset ( $start_time ) && isset ( $end_time ) && ! empty ( $start_time ) && ! empty ( $end_time )) {
			$sql .= " AND a.created_at >= '" . $start_time . " 00:00:00' ";
			$sql .= " AND a.created_at <= '" . $end_time . " 23:59:59' ";
		}
		
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		if ($result) {
			return $result;
		} else {
			return false;
		}
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
	 * 删除商户下的所有管理员
	 * 
	 * @param array $id
	 *        	商户ID
	 * @return boolean 更新结果
	 */
	public static function deleteBySupplierID($id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		$res = $pdo->update ( self::$_tableName, $data, array (
				'supplier_id' => intval ( $id ) 
		) );
		return $res;
	}
	
	/**
	 * 添加商户管理员信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addUser($info) {
		$pdo = self::_pdo ( 'db_w' );
		$info ['type'] = '2';
		$info ['role_id'] = '0';
		$info ['status'] = '2';
		$info ['is_del'] = '2';
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$id = $pdo->insert ( self::$_tableName, $info );
		
		return $id;
	}

    /**
     * 验证用户是否存在
     * @param $supplier_id
     * @param $name
     * @return bool
     */
    public static function getUser($supplier_id,$name) {
        if (empty($name)) return false;
        $pdo = self::_pdo('db_r');
        $user = $pdo->clear()->select('*')->from(self::$_tableName)->where(['name'=>$name,'supplier_id' => $supplier_id,'status'=>'2','type'=>'2','is_del'=>'2'])->getRow();

        if (!$user){

            return false;
        } else {

            return $user;
        }

    }

    /**
     *
     * 通过用户名与密码登录
     * @param $supplier_id
     * @param $user
     * @param $password
     * @return bool|null
     */
    public static function login($supplier_id,$user,$password)
    {
        $salt = $user['salt'];
        $password = md5($password.$salt);
        $pdo = YDLib::getPDO('db_r');
        $user = $pdo->clear()->select('*')->from(self::$_tableName)->where(['id'=>$user['id'],'supplier_id' => $supplier_id,'password'=>$password])->getRow();

        if ($user) {

            return $user;
        }
        return false;
    }
}