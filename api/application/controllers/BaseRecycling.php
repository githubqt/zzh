<?php
// +----------------------------------------------------------------------
// | 回收基控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/11/13 Time:11:42
// +----------------------------------------------------------------------

use Assemble\Support\Traits\Controller\BuildFilePath;
use Assemble\Support\Traits\Controller\ResponseMethod;

class BaseRecyclingController extends Yaf_Controller_Abstract
{
    use BuildFilePath, ResponseMethod;
    //当前登录用户信息
    protected $user = [];
    //用户ID
    protected $user_id = 0;
    //商户ID
    protected $supplier_id = 0;

    protected $request;

    public function init()
    {
        $request = new \Yaf_Request_Http();
        $this->request = $request;
        $this->initUser();
        Yaf_Dispatcher::getInstance()->disableView();
    }

    public function getRequestUri()
    {
        return $this->request->getRequestUri();
    }

    public function getBaseUri()
    {
        return $this->request->getBaseUri();
    }

    public function getParams()
    {
        return $this->request->getParams();
    }

    public function isXmlHttpRequest()
    {
        return $this->request->isXmlHttpRequest();
    }


    public function getParam(string $name, string $default = '')
    {
        return $this->request->getParam($name, $default);
    }

    public function get(string $name, string $default = '')
    {
        return $this->request->get($name, $default);
    }


    public function getPost(string $name, string $default = '')
    {
        return $this->request->getPost($name, $default);
    }

    public function getQuery(string $name, string $default = '')
    {
        return $this->request->getQuery($name, $default);
    }

    public function getMethod()
    {
        return $this->request->getMethod();
    }

    public function getRequest()
    {
        return $this->request->getRequest();
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
        $tokenService = new \Services\Auth\TokenAuthService();
        $tokenService->setUserType('admin');
        $user_id = $tokenService->getUserId();

        if ($user_id) {
            $this->user_id = $user_id;
            $this->user = \Supplier\AdminModel::getAdminInfo($user_id);
        }
        $this->supplier_id = $tokenService->getSupplierId();
        return $this->user;
    }

    /**
     * 获取当前商户ID
     * @return int|mixed
     */
    public function getSupplierID()
    {
        return $this->supplier_id;
    }
}