<?php
header ( 'Content-type:text/html;charset=utf-8' );
include_once '../func/secureUtil.php';

// 初始化日志
$log = new PhpLog ( SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL );
$log->LogInfo ( "===========处理查询请求开始============" );

$_POST ['tranCode'] = 'YS2002';
$_POST ['merchantNo'] = MERCHANTNO;
$_POST ['version'] = VERSION;
$_POST ['channelNo'] = CHANNELNO;

// 签名
sign ( $_POST );

// 发送请求，接收json响应结果
$result = post ( $_POST, HFB_PAY_URL, $errMsg );
if (! $result) { // 没收到200应答的情况
	echo "查询结果：" . $result . "<BR>";
	echo "POST请求失败：" . $errMsg;
	return;
}
$resultData = convertStringToArray ( $result );
// 验签
$flag = verify ( $resultData );

if ($flag) {
	$code = $resultData ['rtnCode'];
	$msg = $resultData ['rtnMsg'];
	
	echo "查询成功：" . "<BR>";
	echo "code：" . $code . "<BR>";
	echo "msg:" . $msg;
} else {
	echo "查询结果：" . $result . "<BR>";
	echo "验签失败！";
}

$log->LogInfo ( "查询返回结果为>" . $result );

$log->LogInfo ( "===========处理查询请求结束============" );

