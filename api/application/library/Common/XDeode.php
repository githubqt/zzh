<?php

/**
 * 加密解密类
 * 该算法仅支持加密数字。比较适用于数据库中id字段的加密解密，以及根据数字显示url的加密。
 * @author xianguo.huang
 * @version alpha
 * @加密原则 标记长度 + 补位 + 数字替换
 * @加密步骤：
 * 将a-z,A-Z,0-9 62个字符打乱，取前M(数字最大的位数)位作为 标记长度字符串，取第M+1 到第M+10位为数字替换字符串，剩余的为补位字符串
 * 1.计算数字长度n,取乱码的第n位作为标记长度。
 * 2.计算补位的长度，加密串的长度N -1 - n 为补位的长度。根据指定的算法得到补位字符串。
 * 3.根据数字替换字符串替换数字，得到数字加密字符串。
 * 标记长度字符 + 补位字符串 + 数字加密字符串 = 加密串
 * Usage:
 *   $obj = new XDeode(9);
 *   $e_txt = $obj->encode(123);
 *   echo $e_txt.'<br/>';
 *   echo $key->decode($e_txt);
 */
namespace Common;

use Custom\YDLib;

class XDeode {
	public static $strbase = "Flpvf70CsakVjqgeWUPXQxSyJizmNH6B1u3b8cAEKwTd54nRtZOMDhoG2YLrI";
	public static $key = 2543.5415412812;
	public static $length = 9;
	public static $codelen = 'Flpvf70Cs';
	public static $codenums = 'akVjqgeWUP';
	public static $codeext = 'XQxSyJizmNH6B1u3b8cAEKwTd54nRtZOMDhoG2YLrI';
	function __construct() {
		/*
		 * $this->key = 2543.5415412812;
		 * $this->length = 9;
		 * $this->codelen = substr($this->strbase,0,$this->length);
		 * $this->codenums = substr($this->strbase,$this->length,10);
		 * $this->codeext = substr($this->strbase,$this->length + 10);
		 */
	}
	public static function encode($nums) {
		$rtn = "";
		$numslen = strlen ( $nums );
		// 密文第一位标记数字的长度
		$begin = substr ( self::$codelen, $numslen - 1, 1 );
		
		// 密文的扩展位
		$extlen = self::$length - $numslen - 1;
		$temp = str_replace ( '.', '', $nums / self::$key );
		$temp = substr ( $temp, - $extlen );
		
		$arrextTemp = str_split ( self::$codeext );
		$arrext = str_split ( $temp );
		foreach ( $arrext as $v ) {
			$rtn .= $arrextTemp [$v];
		}
		
		$arrnumsTemp = str_split ( self::$codenums );
		$arrnums = str_split ( $nums );
		foreach ( $arrnums as $v ) {
			$rtn .= $arrnumsTemp [$v];
		}
		return $begin . $rtn;
	}
	public static function decode($code) {
		$begin = substr ( $code, 0, 1 );
		$rtn = '';
		$len = strpos ( self::$codelen, $begin );
		if ($len !== false) {
			$len ++;
			$arrnums = str_split ( substr ( $code, - $len ) );
			foreach ( $arrnums as $v ) {
				$rtn .= strpos ( self::$codenums, $v );
			}
		}
		
		return $rtn;
	}
}
