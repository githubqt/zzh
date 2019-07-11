<?php
header ( 'Content-type:text/html;charset=utf-8' );
include_once '../func/secureUtil.php';
include_once '../func/HFBConfig.php';

// 初始化日志
$log = new PhpLog ( SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL );
$log->LogInfo ( "===========处理支付请求开始============" );

// 加密敏感数据
if (! empty ( $_POST ['buyerName'] )) {
	$buyerName = $_POST ['buyerName']; // 买家姓名
	$buyerName = encryptData ( $buyerName );
	$_POST ['buyerName'] = $buyerName;
	
	if (! empty ( $_POST ['contact'] )) {
		$contact = $_POST ['contact']; // 买家联系方式
		$contact = encryptData ( $contact );
		$_POST ['contact'] = $contact;
	}
} else {
	echo '买家姓名为空';
	return;
}
date_default_timezone_set ( "PRC" );
$_POST ['tranCode'] = 'YS1003';
$_POST ['merchantNo'] = MERCHANTNO;
$_POST ['version'] = VERSION;
$_POST ['channelNo'] = CHANNELNO;
list ( $s1, $s2 ) = explode ( ' ', microtime () );
$millisecond = ( float ) sprintf ( '%.0f', (floatval ( $s1 ) + floatval ( $s2 )) * 1000 );
$data = time ();
$_POST ['tranFlow'] = $millisecond;
$_POST ['tranDate'] = date ( "Ymd" );
$_POST ['tranTime'] = date ( "His" );
// 签名
sign ( $_POST );

$result = post ( $_POST, HFB_PAY_URL, $errMsg );
$resultData = convertStringToArray ( $result );
$flag = verify ( $resultData );

echo $flag;

