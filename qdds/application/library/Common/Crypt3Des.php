<?php

/**
 * 3DES加解密类
 * @Author: xianguo.huang
 * @version: v1.0
 * 2018年6月23日
 */
namespace Common;

use Custom\YDLib;

class Crypt3Des {
	public static $key = "ABCEDFGHIJKLMNOPQ"; // 密钥 要与java的转化成的16进制字符串对应
	                                          
	// 数据加密
	public static function encrypt($input) {
		$size = mcrypt_get_block_size ( MCRYPT_3DES, 'ecb' );
		$input = self::pkcs5_pad ( $input, $size );
		$key = str_pad ( self::$key, 24, '0' );
		$td = mcrypt_module_open ( MCRYPT_3DES, '', 'ecb', '' );
		$iv = @mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
		@mcrypt_generic_init ( $td, $key, $iv );
		$data = mcrypt_generic ( $td, $input );
		mcrypt_generic_deinit ( $td );
		mcrypt_module_close ( $td );
		$data = base64_encode ( $data );
		return $data;
	}
	// 数据解密
	public static function decrypt($encrypted) {
		$encrypted = base64_decode ( $encrypted );
		$key = str_pad ( self::$key, 24, '0' );
		$td = mcrypt_module_open ( MCRYPT_3DES, '', 'ecb', '' );
		$iv = @mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
		$ks = mcrypt_enc_get_key_size ( $td );
		@mcrypt_generic_init ( $td, $key, $iv );
		$decrypted = mdecrypt_generic ( $td, $encrypted );
		mcrypt_generic_deinit ( $td );
		mcrypt_module_close ( $td );
		$y = self::pkcs5_unpad ( $decrypted );
		return $y;
	}
	public static function pkcs5_pad($text, $blocksize) {
		$pad = $blocksize - (strlen ( $text ) % $blocksize);
		return $text . str_repeat ( chr ( $pad ), $pad );
	}
	public static function pkcs5_unpad($text) {
		$pad = ord ( $text {strlen ( $text ) - 1} );
		if ($pad > strlen ( $text )) {
			return false;
		}
		if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad) {
			return false;
		}
		return substr ( $text, 0, - 1 * $pad );
	}
}
?>
