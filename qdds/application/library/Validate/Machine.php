<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/17 Time:16:08
// +----------------------------------------------------------------------


namespace Validate;


class Machine
{
    /**
     * 定义数据验证规则
     * @var array
     */
    static $rule = [
        'name' => 'require|max:100',
        'custom_code' => 'max:20'
    ];
    /**
     * 定义数据验证错误提示
     * @var array
     */
    static $message = [
        'name.require' => '设备名称必填',
        'name.max' => '设备名称最多100字符',
        'custom_code.max' => '自定义码最多20字符',
    ];
    /**
     * 定义数据验证场景
     * @var array
     */
    static $scene = [
        'add' => ['name','custom_code'],
        'edit' => ['name','custom_code']
    ];
}