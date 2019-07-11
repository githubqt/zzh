<?php
// +----------------------------------------------------------------------
// | 模型验证器
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/16 Time:14:32
// +----------------------------------------------------------------------


namespace Assemble\Support\Traits\Model;


trait HasValidation
{
    // 错误信息
    protected $error;
    // 字段验证规则
    protected $validate;
    // 是否调用了验证
    protected $isCallValidate = false;

    // 是否采用批量验证
    protected $batchValidate = false;
    /**
     * 验证规则
     * @var array
     */
    protected $rule = [];

    /**
     * 错误消息提示
     * @var array
     */
    protected $message = [];

    /**
     * 验证场景
     * @var array
     */
    protected $scene = [
    ];

}