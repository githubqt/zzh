<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/10/13 Time:20:57
// +----------------------------------------------------------------------


namespace Services\Auth;
require APPLICATION_PATH . '/vendor/autoload.php';

use Common\CommonBase;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use User\UserModel;

class TokenAuthService extends AuthBaseService
{
    protected $token_id = 'TZB2SRvmaNNXIQK6';
    protected $signature = 'P9fiXHqDnxSyNsYQMMzenHkYXDAsf0QM';
    protected $builder = null;
    protected $parser = null;
    protected $signer = null;

    public function __construct($user_id = null)
    {
        $this->user_id = $user_id;
        $this->builder = new Builder();
        $this->parser = new Parser();
        $this->signer = new Sha256();
    }


    public function builder()
    {
        return $this->builder;
    }

    /**
     * 生存token
     * @return \Lcobucci\JWT\Token
     */
    public function getJWTToken()
    {
        //设置发行人
        $this->builder->setIssuer('');
        //设置观众
        $this->builder->setAudience('');
        $this->builder->setId($this->token_id, true);
        $this->builder->setIssuedAt(time());
//        $this->builder->setNotBefore(time() + 1);
        $this->builder->setExpiration(time() + $this->expire_seconds);
        $this->builder->set('user_id', $this->user_id); // 设置用户ID
        $this->user_type == 'admin' and $this->builder->set('supplier_id', $this->supplier_id); // 设置商户ID
        $this->builder->sign($this->signer, $this->signature);
        $this->token = $this->builder->getToken();
        //写入数据库
        $this->write();
        return $this->token;
    }

    /**
     * 使用解析器从JWT字符串创建新token
     * @return \Lcobucci\JWT\Token
     */
    public function parser()
    {
        return $this->parser->parse($this->token);
    }

    /**
     * 验证jwt token
     * @return bool
     */
    public function validateJWT()
    {
        $data = new ValidationData();
        //设置发行人
        $data->setIssuer('');
        //设置观众
        $data->setAudience('');

        $data->setId($this->token_id);

        try {
            if (!$this->parser()->verify($this->signer, $this->signature)) {
                throw new \Exception('token 签名错误');
            }

            if (!$this->parser()->validate($data)) {
                throw new \Exception('token 验证失败');
            }
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * 排除uri
     * @return array
     */
    public function expect()
    {
        $expect = require_once "TokenAuthExpectService.php";
        return $expect;
    }

    /**
     * 返回user_id
     * @return bool|mixed
     */
    public function getUserId()
    {
        $user_id = 0;
        $request = new \Yaf_Request_Http();
        if ($this->user_type == 'admin'){
            $token = $this->getAdminHeaderToken($request);
        }else{
            $token = $this->getHeaderToken($request);
        }
        if ($token == null) {
            return $user_id;
        }
        $this->setToken($token);
        if (!$this->validateJWT() || !$this->check()) {
            return $user_id;
        }
        $this->user_id = $this->parser()->getClaim('user_id',0);
        if ($this->user_type == 'admin'){
            $this->supplier_id = $this->parser()->getClaim('supplier_id',0);
        }
        return $this->user_id ;
    }

    /**
     * 获取商户ID
     * @return null
     */
    public function getSupplierId()
    {
        return $this->supplier_id;
    }

    /**
     * 清除认证token
     * @param $user_id
     * @param int $supplier_id
     * @param string $user_type
     */
    public static function remove($user_id,$supplier_id = 0,$user_type = 'user')
    {
        $tokenUtil = new TokenUtil();
        $tokenUtil->setUserId($user_id);
        $tokenUtil->setSupplierId($supplier_id);
        $tokenUtil->setUserType($user_type);
        $tokenUtil->remove();
    }

    /**
     * 获取请求头 X_AUTHORIZATION_TOKEN
     * @param $request
     * @return mixed
     */
    public function getHeaderToken($request)
    {
        if (isset($_SERVER['HTTP_X_AUTHORIZATION_TOKEN'])&&$_SERVER['HTTP_X_AUTHORIZATION_TOKEN']){
            return $_SERVER['HTTP_X_AUTHORIZATION_TOKEN'];
        }

        return $request->getRequest('token');

    }
    /**
     * 获取请求头 X_AUTHORIZATION_TOKEN
     * @param $request
     * @return mixed
     */
    public function getAdminHeaderToken($request)
    {
        if (isset($_SERVER['HTTP_X_ADMIN_AUTHORIZATION_TOKEN'])&&$_SERVER['HTTP_X_ADMIN_AUTHORIZATION_TOKEN']){
            return $_SERVER['HTTP_X_ADMIN_AUTHORIZATION_TOKEN'];
        }

        return $request->getRequest('token');

    }

}