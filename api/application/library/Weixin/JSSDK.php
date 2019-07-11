<?php

namespace Weixin;

use Custom\YDLib;

class JSSDK {
	private $appId;
	private $appSecret;
	private $redis;
	public function __construct($appId, $appSecret) {
		$this->appId = $appId;
		$this->appSecret = $appSecret;
	}
	public function getSignPackage($url) {
		$jsapiTicket = $this->getJsApiTicket ();
		$url = urldecode ( $url );
		$timestamp = time ();
		$nonceStr = $this->createNonceStr ();
		
		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
		
		$signature = sha1 ( $string );
		
		$signPackage = array (
				"appId" => $this->appId,
				"nonceStr" => $nonceStr,
				"timestamp" => $timestamp,
				"url" => $url,
				"signature" => $signature,
				"rawString" => $string 
		);
		return $signPackage;
	}
	private function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for($i = 0; $i < $length; $i ++) {
			$str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
		}
		return $str;
	}
	private function getJsApiTicket() {
		$mem = YDLib::getMem ( 'memcache' );
		$key = __CLASS__ . "::" . __FUNCTION__ . "::" . SUPPLIER_ID;
		$data = $mem->get ( $key );
		
		if ($data) {
			$ticket = $data;
		} else {
			$accessToken = $this->getAccessToken ();
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
			$res = json_decode ( $this->httpGet ( $url ) );
			$ticket = $res->ticket;
			if ($ticket) {
				$mem->delete ( $key );
				$mem->set ( $key, $ticket );
			}
		}
		
		return $ticket;
	}
	private function getAccessToken() {
		$mem = YDLib::getMem ( 'memcache' );
		$key = __CLASS__ . "::" . __FUNCTION__ . "::" . SUPPLIER_ID;
		$data = $mem->get ( $key );
		
		if ($data) {
			$access_token = $data;
		} else {
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
			$res = json_decode ( $this->httpGet ( $url ) );
			$access_token = $res->access_token;
			if ($access_token) {
				$mem->delete ( $key );
				$mem->set ( $key, $access_token, 7000 );
			}
		}
		return $access_token;
	}
	private function httpGet($url) {
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 500 );
		curl_setopt ( $curl, CURLOPT_URL, $url );
		
		$res = curl_exec ( $curl );
		curl_close ( $curl );
		
		return $res;
	}
}
?>