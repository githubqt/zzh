<?php
/**
 * 管理员model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
namespace Admin;

use Custom\YDLib;
use Common\CommonBase;

class AdminModel extends \Common\CommonBase 
{
    protected static $_tableName = 'admin';
    private function __construct() {
        parent::__construct();
    }
    static  $_login = 'FGRTYUSDS';
    
    /**
     * 判断用户是否登录
     * @return boolean
     */
    public static function isLogin()
    {
        if (!empty($_COOKIE[self::$_login])) {
            $mem = YDLib::getMem ( 'memcache' );
            $data = $mem->get (ADMIN_FORCED_RETURN.json_decode($_COOKIE[self::$_login]));
            if (!$data) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * 返回登录中的用户ID
     * @return int
     */
    public static function getAdminID()
    {
        if(self::isLogin()) {
            return json_decode($_COOKIE[self::$_login]);
        }
        return FALSE;
    }
     
    /**
     * 获取用户登陆信息
     * @param int $adminID 用户ID
     * @return array
     */
    public static function getAdminLoginInfo($adminID)
    {
        return self::getAdminInfo($adminID);
    }


    /**
     * 获取当前登录信息
     * @return array
     */
    public static function getCurrentLoginInfo(){
        $info = [];
        $adminId = static::getAdminID();
        if ($adminId){
            $info = static::getAdminLoginInfo($adminId);
        }
        return $info;
    }
    /**
     * 退出（清除cookie）
     * @return boolean
     */
    public static function signout()
    {
        if(self::isLogin()) {
            return setcookie(self::$_login, FALSE, time() - 315360000 , "/",$_SERVER['HTTP_HOST']);
        }
        return FALSE;
    }
    
   
    /**
     * 验证用户是否存在
     * @param int $uid
     * @return boolean|string
     */
    public static function checkUserId($code,$name) { 
        if (empty($name)) return false;
		$pdo = self::_pdo('db_r');
        $user = $pdo->clear()->select('*')->from(self::$_tableName)->where(['name'=>$name,'supplier_id' => $code,'status'=>'2','type'=>PROJECT_TYPE,'is_del'=>'2'])->getRow();
        
        if (!$user){
        	
            return false;
        } else {
        	
            return $user;
        }
        
    }
    
    /**
     * 通过用户名与密码登录
     * @param string $name 账号
     * @param string $password 密码
     * @return array 非空返回登陆者信息
     * null 空值登陆失败
     */
    public static function login($code,$user,$password)
    {
        $salt = $user['salt'];
        $password = md5($password.$salt);
        $pdo = YDLib::getPDO('db_r');
        $user = $pdo->clear()->select('*')->from(self::$_tableName)->where(['id'=>$user['id'],'supplier_id' => $code,'password'=>$password])->getRow();
        
        if ($user) {
        	
            return $user;
        }
        return false;
    }
    
    
    /**
     * 获取用户信息
     * @param unknown $UserId
     * @param number $headImgSize
     * @return multitype:|Ambigous <unknown, string>
     */
    public static function getAdminInfo($UserId) {
        if (!$UserId) return [];
        
        $pdo = YDLib::getPDO('db_r');
        $user = $pdo->clear()->select('*')->from(self::$_tableName)->where(['id'=>$UserId])->getRow();
        
        if ($user) {
            if (!$user['fullname']){
                $user['fullname'] = $user['name'];
            }
            return $user;
        }
        
        return false;
    }
    
    /**
     * 添加信息
     * @param array $info
     * @return mixed
     */
    public static function addUser($info)
    {
        $db = YDLib::getPDO('db_w');
        $info['is_del'] = '2';
        $info['supplier_id'] = self::getAdminLoginInfo(self::getAdminID())['supplier_id'];
        $info['type'] = PROJECT_TYPE;
        $info['created_at'] = date("Y-m-d H:i:s");
        $info['updated_at'] = date("Y-m-d H:i:s");
        $result = $db->insert(self::$_tableName, $info);
    
        return $result;
    }
    
    /* 获取列表*/
    public static function getList($attribute = array(),$page = 0,$rows = 10)
    {
        $limit = ($page) * $rows;
        if (!empty($attribute['info']) && is_array($attribute['info']) && count($attribute['info']) > 0) {
            extract($attribute['info']);
        }
         
        $pdo = YDLib::getPDO('db_r');
        $fileds = " a.* ";
        $sql = 'SELECT
        		   [*]
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a
		        WHERE
        		    a.is_del = 2
		        AND 
	                 a.type = '.PROJECT_TYPE.'
		        AND 
	                 a.supplier_id='.self::getAdminLoginInfo(self::getAdminID())['supplier_id'];
    
        if (isset($name) && !empty($name)) {
            $sql .= " AND a.name like '%".$name."%' ";
        }
    
        if (isset($id) && !empty($id)) {
            $sql .= " AND a.id like '%".$id."%' ";
        }
    
        if (isset($fullname) && !empty($fullname)) {
            $sql .= " AND a.fullname like '%".$fullname."%' ";
        }
        if (isset($status) && !empty($status)) {
            $sql .= " AND a.status = ".$status." ";
        }
        if (isset($mobile) && !empty($mobile)) {
            $sql .= " AND a.mobile = ".$mobile." ";
        }
        if ((isset($role_id) && !empty($role_id))) {
            $sql .= " AND a.role_id = ".$role_id." ";
        }
         
        if (isset($start_time) && !empty($start_time)) {
            $sql .= " AND a.created_at >= '".$start_time." 00:00:00'";
        }
        
        if (isset($end_time) && !empty($end_time)) {
            $sql .= " AND a.created_at <= '".$end_time." 23:59:59'";
        }
        
        
        $result['total'] = $pdo ->YDGetOne(str_replace("[*]", "count(*) as num", $sql));
		
		$sort = isset($sort)?$sort:'id';	
		$order = isset($order)?$order:'DESC';
		
		$sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
        $result['list'] = $pdo ->YDGetAll(str_replace("[*]", $fileds, $sql));
        
        if ($result) {
            return $result;
        } else {
            return false;
        }
    
    }
    
    /**
     * 根据一条自增ID更新表记录
     * @param array $data 更新字段作为key的数组
     * @param array $id 表自增id
     * @return boolean 更新结果
     */
    public static function updateByID($data, $id)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
    
        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
    }
    
    /**
     * 根据表自增 ID删除记录
     * @param int $id 表自增 ID
     * @return boolean 删除是否成功
     */
    public static function deleteByID($id)
    {
        $data['is_del'] = self::DELETE_FAIL;
        $data['updated_at'] = date("Y-m-d H:i:s");
        $data['deleted_at'] = date("Y-m-d H:i:s");
    
        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
    }
    
    
    /**
     * 生成密码及盐值
     * @param int $password 密码
     * @return array 生成后的密码及盐值
     */
    public static function setPassword($password) {
        $info = [];
        $info['salt'] = self::random('8');
        $info['password'] = md5($password.$info['salt']);
        return $info;
    }
    
    
    /**
     * 产生随机字符串
     *
     * @param    int        $length  输出长度
     * @param    string     $chars   可选的 ，默认为 0123456789
     * @return   string     字符串
     */
    public static function random($length, $chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ!@#$%^&*~') {
        
        
         return substr(str_shuffle($chars), 0, $length);
        
    }
    
    
    
    /**
     * 角色组是否被绑定
     * @param unknown $roleId
     * @param number $headImgSize
     * @return multitype:|Ambigous <unknown, string>
     */
    public static function getRoleById($roleId) {
        if (!$roleId) return [];
    
        $pdo = YDLib::getPDO('db_r');
        $user = $pdo->clear()->select('*')->from(self::$_tableName)->where(['role_id'=>$roleId,'is_del'=>'2'])->getRow();
 
        if ($user) {
            return $user;
        }
    
        return false;
    }

}