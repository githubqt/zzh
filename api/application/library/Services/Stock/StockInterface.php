<?php
// +----------------------------------------------------------------------
// | 商品库存操作接口
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/29 Time:17:24
// +----------------------------------------------------------------------

namespace Services\Stock;

interface StockInterface
{
    public function lock();

    public function unlock();

    public function out();

    public function in();

    public function revert();
}