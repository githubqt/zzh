<?php
// +----------------------------------------------------------------------
// | 返回文件完整路径
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/11/14 Time:17:07
// +----------------------------------------------------------------------


namespace Assemble\Support\Traits\Controller;

use Common\CommonBase;

trait BuildFilePath
{
    /**
     * 构建补全文件完整路径地址
     * @param string $file_path
     * @param int $type
     * @return string
     */
    public function buildPath(string $file_path, int $type = 2)
    {
        $host = rtrim(HOST_FILE, '/');
        if (!$file_path) {
            return "{$host}/common/images/common.png";
        }

        if ($type === 0){
            return "{$host}/" .ltrim($file_path, '/');
        }
        return "{$host}/" . CommonBase::imgSize(ltrim($file_path, '/'), $type);
    }
}