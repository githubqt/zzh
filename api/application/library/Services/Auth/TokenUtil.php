<?php
// +----------------------------------------------------------------------
// | token 工具类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/10/16 Time:17:07
// +----------------------------------------------------------------------


namespace Services\Auth;

use Custom\YDLib;

class TokenUtil
{
    //用户ID
    protected $user_id = 0;
    //商户ID
    protected $supplier_id = 0;
    //用户类型
    protected $user_type = 'user';
    //缓存键
    protected $cache_key = '';
    //缓存对象
    protected $cache = null;

    /**
     * 初始化 memcache 对象
     * TokenUtil constructor.
     */
    public function __construct()
    {
        $this->cache = YDLib::getMem('memcache');
    }

    /**
     * 设置用户ID
     * @param $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * 设置商户ID
     * @param $supplier_id
     */
    public function setSupplierId($supplier_id)
    {
        $this->supplier_id = $supplier_id;
    }

    /**
     * 设置用户类型
     * @param $user_type
     */
    public function setUserType($user_type)
    {
        $this->user_type = $user_type;
    }

    /**
     * 获取用户ID
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * 获取商户ID
     * @return int
     */
    public function getSupplierId()
    {
        return $this->supplier_id;
    }

    /**
     * 获取用户类型
     * @return string
     */
    public function getUserType()
    {
        return $this->user_type;
    }
    /**
     * 获取缓存键
     * @return string
     */
    public function getCacheKey()
    {
        $this->cache_key = "_JWT_TOKEN_CACHE_{$this->user_type}_AND_{$this->supplier_id}_AND_{$this->user_id}";
        return $this->cache_key;
    }

    /**
     * 获取缓存数据
     * @return mixed
     */
    public function getItem()
    {
        return $this->cache->get($this->getCacheKey());
    }

    /**
     * 缓存数据
     * @param $data
     * @return mixed
     */
    public function setItem($data)
    {
        $this->cache->set($this->getCacheKey(), $data);
        return $data;
    }

    /**
     * 移除 token
     * @return mixed
     */
    public function remove()
    {
        // 删除数据库中token记录
        $pdo = YDLib::getPDO('db_w');
        $pdo->delete('user_token', [
            'user_id' => $this->user_id,
            'supplier_id' => $this->supplier_id,
            'user_type' => $this->user_type,
        ]);
        return $this->cache->delete($this->getCacheKey());
    }

}