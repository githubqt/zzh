<?php
// +----------------------------------------------------------------------
// | 商品库存操作接口
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/29 Time:17:24
// +----------------------------------------------------------------------

namespace Services\Stock;

interface StockInterface
{
    /**
     * 锁定库存：
     * 该方法定义用于增加商品锁定的库存数,库存数相应减少; 例如：锁定 +1，库存 -1；
     * @return mixed
     */
    public function lock();

    /**
     * 解锁库存：
     * 该方法定义用于减去商品锁定的库存数,对商品库存数无影响；例如：锁定 -1，库存不变;
     * @return mixed
     */
    public function unlock();

    /**
     * 出库扣减商品库存：
     * 该方法定义用于减去商品库存数,对商品锁定库存数无影响; 例如：库存 -1, 锁定库存不变;
     * @return mixed
     */
    public function out();

    /**
     * 入库增加商品库存：
     * 该方法定义用于增加商品库存数,对商品锁定库存数无影响; 例如：库存 +1, 锁定库存不变;
     * @return mixed
     */
    public function in();

    /**
     * 还原锁定库存
     * 该方法定义用于撤销已锁定的库存,同时恢复锁定前的商品库存数; 例如：锁定 -1，库存 +1;
     * @return mixed
     */
    public function revert();
}