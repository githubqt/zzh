<?php

use Assemble\Support\Traits\Controller\BuildFilePath;
use Assemble\Support\Traits\Controller\ResponseMethod;

/**
 * 基础控制器
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
class BaseController extends Yaf_Controller_Abstract
{
    use BuildFilePath,ResponseMethod;
    //当前登录用户信息
    protected $user = [];
    //用户ID
    protected $user_id = 0;
    //商户ID
    protected $supplier_id = 0;

    public function init()
    {
        header("Access-Control-Allow-Origin","*");
        $this->initUser();
        Yaf_Dispatcher::getInstance()->disableView();
    }


    /**
     * 初始化登录信息
     */
    public function initUser()
    {
        $this->getUser();
        $this->getSupplierID();
    }

    /**
     * 获取当前登录用户信息
     * @return mixed|null
     */
    public function getUser()
    {
        $request = new \Yaf_Request_Http();
        $tokenService = new \Services\Auth\TokenAuthService();
        $user_id = $tokenService->getUserId();

        //如果当前用户没获取到，获取参数中的user_id
        if (!$user_id){
            $user_id = $request->getRequest('user_id');
        }

        if ($user_id) {
            $this->user_id = $user_id;
            $this->user = \User\UserModel::getUserInfo($user_id);
        }

        return $this->user;
    }

    /**
     * 获取当前商户ID
     * @return int|mixed
     */
    public function getSupplierID()
    {
        $this->supplier_id = SUPPLIER_ID;
        return $this->supplier_id;
    }
}
