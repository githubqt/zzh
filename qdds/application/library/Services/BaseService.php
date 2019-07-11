<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/21 Time:15:07
// +----------------------------------------------------------------------


namespace Services;


use Custom\YDLib;

abstract class BaseService
{
    public function newRead()
    {
        return YDLib::getPDO('db_r');
    }

    public function newWrite()
    {
        return YDLib::getPDO('db_w');
    }

}