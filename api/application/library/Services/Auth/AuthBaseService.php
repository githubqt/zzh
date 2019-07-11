<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/10/13 Time:20:50
// +----------------------------------------------------------------------
namespace Services\Auth;

use Services\BaseService;
use User\UserTokenModel;

abstract class AuthBaseService extends BaseService
{
    //过期时间
    protected $expire_seconds = 24 * 3600 * 7;
    //用户编号
    protected $user_id = null;
    // 商户编号 $user_type = 'admin' 时用
    protected $supplier_id = null;
    // 用户类型
    protected $user_type = 'user';
    protected $device_type = 'wxapp';
    protected $token = null;
    protected $user = [];
    //错误消息
    protected $error = '';
    //错误码
    protected $error_code = 0;

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }
    public function setSupplierId($supplier_id)
    {
        $this->supplier_id = $supplier_id;
    }
    public function setUserType($user_type)
    {
        $this->user_type = $user_type;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function setDeviceType($device_type)
    {
        $this->device_type = $device_type;
    }

    /**
     * 生成token 写入数据库
     */
    public function write()
    {
        try {
            if (is_null($this->user_id)) {
                throw new \Exception('缺少用户ID参数');
            }
            if (is_null($this->token)) {
                throw new \Exception('缺少token参数');
            }
            UserTokenModel::createToken($this->user_id, $this->device_type, $this->expire_seconds, $this->token,$this->supplier_id,$this->user_type);
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * 验证数据库中的 db token是否有效
     */
    public function check()
    {
        try {
            if (is_null($this->token)) {
                throw new \Exception('缺少token参数');
            }
            $user = UserTokenModel::getUserByToken($this->token);
            if (empty($user)) {
                throw new \Exception('token不存在');
            }

            if ($user['expire_time'] < time()) {
                throw new \Exception('token已过期');
            }
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }

    }

    /**
     * 获取错误信息
     * @return string
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * 获取错误码
     * @return int
     */
    public function errorCode()
    {
        return $this->error_code;
    }

    /**
     * 删除token 使其无效
     */
    public function invalidate()
    {
        return UserTokenModel::deleteByUserId($this->user_id,$this->supplier_id,$this->user_type);
    }

}