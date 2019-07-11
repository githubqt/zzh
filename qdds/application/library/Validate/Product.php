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


class Product
{
    /**
     * 定义数据验证规则
     * @var array
     */
    static $rule = [
        'name' => 'require|max:100|unique:product',
        'self_code' => 'require|max:20',
        'sale_price' => 'float',
    ];
    /**
     * 定义数据验证错误提示
     * @var array
     */
    static $message = [
        'name.require' => '商品名称必须',
        'name.max' => '商品名称最多100字符',
        'name.unique' => '商品名称已存在',
        'self_code.require' => '自定义编码必填',
        'self_code.max' => '自定义编码最多20字符',
        'sale_price.require' => '销售价必填',
        'sale_price.number' => '销售价必须是数字',
    ];
    /**
     * 定义数据验证场景
     * @var array
     */
    static $scene = [
        'add' => ['name'],
        'edit' => ['self_code'],
        'sale' => ['sale_price'],//定价销售
    ];
}