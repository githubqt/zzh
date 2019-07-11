<?php
include_once APPLICATION_PATH . "/application/library/Pay/Hfb/Hfb/func/HFBCommon.php";
include_once APPLICATION_PATH . "/application/library/Pay/Hfb/Hfb/func/HFBConfig.php";
use Custom\YDLib;
/**
 * 签名
 *
 * @param String $params_str        	
 */
function sign(&$params) {
	if (isset ( $params ['sign'] )) {
		unset ( $params ['sign'] );
	}
	// 转换成key=val&串
	$params_str = createLinkString ( $params, false );
	
	// 签名证书路径
	$cert_path = HFB_PRIVATE_CERT_PATH;
	
	$private_key = getPrivateKey ( $cert_path );
	// 签名
	$sign_falg = openssl_sign ( $params_str, $sign, $private_key, OPENSSL_ALGO_SHA1 );
	if ($sign_falg) {
		$sign_base64 = base64_encode ( $sign );
		$params ['sign'] = $sign_base64;
	} else {
		YDLib::testLogHfb ( $params );
		YDLib::testLogHfb ( '签名失败' );
	}
}

/**
 * 验签
 *
 * @param String $params_str        	
 * @param String $sign_str        	
 */
function verify($params) {
	print_r ( $params );
	// 公钥
	$public_key = getPublicKey ( HFB_PUBLIC_CERT_PATH );
	// 签名串
	$sign_str = $params ['sign'];
	$sign_str = str_replace ( " ", "+", $sign_str );
	// 转码
	unset ( $params ['sign'] );
	$params_str = createLinkString ( $params, false );
	$sign = base64_decode ( $sign_str );
	$isSuccess = openssl_verify ( $params_str, $sign, $public_key );
	if ($isSuccess == '1') {
		return $params_str;
	} else {
		YDLib::testLogHfb ( '验签失败' );
		YDLib::testLogHfb ( $params );
		$params_str = $params_str . '&sign=' . $sign_str . '&msg=验签失败';
		return $params_str;
	}
}

/**
 * 取证书公钥 -验签
 *
 * @return string
 */
function getPublicKey($cert_path) {
	return file_get_contents ( $cert_path );
}
/**
 * 返回(签名)证书私钥 -
 *
 * @return unknown
 */
function getPrivateKey($cert_path) {
	$pkcs12 = file_get_contents ( $cert_path );
	openssl_pkcs12_read ( $pkcs12, $certs, HFB_PRIVATE_CERT_PWD );
	return $certs ['pkey'];
}

/**
 * 加密数据
 * 
 * @param string $data数据        	
 * @param string $cert_path
 *        	证书配置路径
 * @return unknown
 */
function encryptData($data, $cert_path = HFB_PUBLIC_CERT_PATH) {
	$public_key = getPublicKey ( $cert_path );
	openssl_public_encrypt ( $data, $crypted, $public_key );
	return base64_encode ( $crypted );
}

/**
 * 解密数据
 * 
 * @param string $data数据        	
 * @param string $cert_path
 *        	证书配置路径
 * @return unknown
 */
function decryptData($data, $cert_path = HFB_PRIVATE_CERT_PATH) {
	$data = base64_decode ( $data );
	$private_key = getPrivateKey ( $cert_path );
	openssl_private_decrypt ( $data, $crypted, $private_key );
	return $crypted;
}


