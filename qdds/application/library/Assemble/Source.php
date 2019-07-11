<?php
// +----------------------------------------------------------------------
// | 定义平台管理员来源类型
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/9 Time:15:25
// +----------------------------------------------------------------------


namespace Assemble;


class Source
{
    const PLATFORM_ID = 1;  //平台标识
    const MERCHANT_ID = 2;  //商户标识 （通常指 商行商户）
    const PROVIDER_ID = 3;  //供应商标识
    const CHANNEL_ID  = 4;  //渠道商标识


    public static function all(){
        return [
            static::PLATFORM_ID => '平台',
            static::MERCHANT_ID => '商户',
            static::PROVIDER_ID => '供应商',
            static::CHANNEL_ID  => '渠道商',
        ];
    }

    /**
     * 通过来源标识获取来源名称
     * @param int $source_id 来源ID
     * @return mixed
     */
    public static function getSourceName($source_id = 1){
        $all = static::all();
        if (!isset($all[$source_id])){
            throw new \Yaf_Exception_TypeError ('Source is not defined $source_id is  '. "'{$source_id}'");
        }
        return $all[$source_id];
    }

}