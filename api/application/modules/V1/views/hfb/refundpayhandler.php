<?php
header ( 'Content-type:text/html;charset=utf-8' );
include_once '../func/secureUtil.php';

// 初始化日志
$log = new PhpLog ( SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL );
$log->LogInfo ( "===========处理退款请求开始============" );
date_default_timezone_set ( "PRC" );
$_POST ['tranCode'] = 'YS9001';
$_POST ['merchantNo'] = MERCHANTNO;
$_POST ['version'] = VERSION;
$_POST ['channelNo'] = CHANNELNO;
list ( $s1, $s2 ) = explode ( ' ', microtime () );
$millisecond = ( float ) sprintf ( '%.0f', (floatval ( $s1 ) + floatval ( $s2 )) * 1000 );
$data = time ();
$_POST ['tranSerialNum'] = $millisecond;
$_POST ['tranDate'] = date ( "Ymd" );
$_POST ['tranTime'] = date ( "His" );

// 签名
sign ( $_POST );

$result = post ( $_POST, HFB_PAY_URL, $errMsg );
$resultData = convertStringToArray ( $result );
// 验签
$flag = verify ( $resultData );

if ($flag) {
	$code = $resultData ['rtnCode'];
	$msg = $resultData ['rtnMsg'];
	
	echo "退款成功：" . "<BR>";
	echo "code：" . $code . "<BR>";
	echo "msg:" . $msg;
} else {
	echo "退款结果：" . $result . "<BR>";
	echo "验签失败！";
}

$log->LogInfo ( "退款返回结果为>" . $result );

$log->LogInfo ( "===========处理退款请求结束============" );
