<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/10/13 Time:20:48
// +----------------------------------------------------------------------


namespace User;


class UserTokenModel extends \BaseModel
{

    /**
     * 获取用户token
     * @param $user_id
     * @param $supplier_id
     * @param $user_type
     * @return array
     */
    public static function getTokenInfoByUserId($user_id, $supplier_id,$user_type)
    {
        $sql = "SELECT * FROM `" . self::getFullTable() . "`  where
        user_id = '{$user_id}' 
        and supplier_id = '{$supplier_id}' 
        and user_type = '{$user_type}' 
        ";
        $row = self::newRead()->YDGetRow($sql);
        return $row;
    }

    /**
     * 创建token
     * @param $user_id
     * @param $device_type
     * @param $expire_second
     * @param $token
     * @param null $supplier_id
     * @param string $user_type
     * @return UserTokenModel
     */
    public static function createToken($user_id, $device_type, $expire_second,$token,$supplier_id = null,$user_type = 'user')
    {

        if ($user_type === 'user'){
            $supplier_id = SUPPLIER_ID;
        }

        $currentTime = time();
        $expireTime = $currentTime + $expire_second;
        //删除以前的
        self::deleteByUserId($user_id,$supplier_id,$user_type);

        //创建新的
        $self = new  static();
        $self->user_id = $user_id;
        $self->supplier_id = $supplier_id;
        $self->user_type = $user_type;
        $self->expire_time = $expireTime;
        $self->create_time = $currentTime;
        $self->token = $token;
        $self->device_type = $device_type;
        $self->save();
        return $self;
    }

    /**
     * 通过token获取用户信息
     * @param $token
     * @return array
     */
    public static function getUserByToken($token)
    {
        $sql = "SELECT a.* FROM `" . self::getFullTable() . "` a 
        left join " . self::$_tablePrefix . "user b  on a.user_id = b.id
        
        where token = '{$token}' 
        ";
        $row = self::newRead()->YDGetRow($sql);
        return $row;
    }

    /**
     * 删除用户token
     * @param $user_id
     * @return bool|int
     */
    public static function deleteByUserId($user_id,$supplier_id,$user_type)
    {
        return self::newWrite()->delete(self::table(),[
            'user_id'=> $user_id,
            'supplier_id' => $supplier_id,
            'user_type' => $user_type,
        ]);
    }
}