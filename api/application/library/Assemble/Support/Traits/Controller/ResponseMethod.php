<?php

// +----------------------------------------------------------------------
// | 响应JSON请求方法
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/11/14 Time:17:49
// +----------------------------------------------------------------------
namespace Assemble\Support\Traits\Controller;

trait ResponseMethod
{

    /**
     * 响应成功提示
     * @param array $data 返回的数据
     */
    public function success(array $data = [])
    {
        $filterErrno = \Yaf_Registry::get('filterErrno');
        $jsonArr = [
            'errmsg' => $filterErrno[\ErrnoStatus::STATUS_SUCCESS],
            'errno' => \ErrnoStatus::STATUS_SUCCESS,
            'result' => !empty($data) ? \Custom\YDLib::lcFirstRecursive($data) : $data
        ];
        return $this->response(json_encode($jsonArr));
    }

    /**
     * 响应错误提示 状态码
     * @param string $errno 错误码
     * @param array $data 返回的数据
     */
    public function error($errno, array $data = [])
    {
        $exceptionMessage = '';
        $filterErrno = \Yaf_Registry::get('filterErrno');
        if (is_numeric($errno) && isset ($filterErrno [$errno])) {
            $errmsg = $filterErrno[$errno];
        } else {
            $exceptionMessage = $errno;
            $errno = \ErrnoStatus::STATUS_CODE_ERROR;
            $errmsg = $filterErrno[$errno];
        }

        $jsonArr = [
            'errmsg' => $errmsg,
            'errno' => $errno,
            'result' => !empty($data) ? \Custom\YDLib::lcFirstRecursive($data) : $data
        ];
        if ($exceptionMessage) {
            $jsonArr['exception_message'] = $exceptionMessage;
        }
        return $this->response(json_encode($jsonArr));
    }


    /**
     * json响应
     * @param $data
     */
    /**
     * json响应
     * @param $data
     */
    public function response($data = '')
    {
        if (!empty($_REQUEST['jsonpcallback'])) {
            echo $_REQUEST['jsonpcallback'] . "(" . json_encode(json_decode($data)) . ")";
            exit();
        } else {
            $response = new \Yaf_Response_Http();
            $response->setHeader('Content-Type', 'application/json');
            $response->setBody($data);
            $response->response();
        }
        exit();
    }

}