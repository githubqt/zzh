<?php

namespace Core;

class SmsSingleSender {
	private $url = "https://sms.yundashi.com/intf/sendsms";
	private $appid = '1400099230';
	private $appkey = "7c9a385fd6c8a34d14ac8d9db53cde1c";
	private $util;
	public function init($appid, $appkey) {
	}
	private function getRandom() {
		return rand ( 100000, 999999 );
	}
	private function calculateSig($appkey, $random, $curTime, $phoneNumbers) {
		$phoneNumbersString = $phoneNumbers [0];
		for($i = 1; $i < count ( $phoneNumbers ); $i ++) {
			$phoneNumbersString .= ("," . $phoneNumbers [$i]);
		}
		return hash ( "sha256", "appkey=" . $appkey . "&random=" . $random . "&time=" . $curTime . "&mobile=" . $phoneNumbersString );
	}
	private function calculateSigForTemplAndPhoneNumbers($appkey, $random, $curTime, $phoneNumbers) {
		$phoneNumbersString = $phoneNumbers [0];
		for($i = 1; $i < count ( $phoneNumbers ); $i ++) {
			$phoneNumbersString .= ("," . $phoneNumbers [$i]);
		}
		return hash ( "sha256", "appkey=" . $appkey . "&random=" . $random . "&time=" . $curTime . "&mobile=" . $phoneNumbersString );
	}
	private function phoneNumbersToArray($nationCode, $phoneNumbers) {
		$i = 0;
		$tel = array ();
		do {
			$telElement = new \stdClass ();
			$telElement->nationcode = $nationCode;
			$telElement->mobile = $phoneNumbers [$i];
			array_push ( $tel, $telElement );
		} while ( ++ $i < count ( $phoneNumbers ) );
		return $tel;
	}
	private function calculateSigForTempl($appkey, $random, $curTime, $phoneNumber) {
		$phoneNumbers = array (
				$phoneNumber 
		);
		return $this->calculateSigForTemplAndPhoneNumbers ( $appkey, $random, $curTime, $phoneNumbers );
	}
	private function sendCurlPost($url, $dataObj) {
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_HEADER, 0 );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $curl, CURLOPT_POST, 1 );
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, json_encode ( $dataObj ) );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
		$ret = curl_exec ( $curl );
		if (false == $ret) {
			// curl_exec failed
			$result = "{ \"result\":" . - 2 . ",\"errmsg\":\"" . curl_error ( $curl ) . "\"}";
		} else {
			$rsp = curl_getinfo ( $curl, CURLINFO_HTTP_CODE );
			if (200 != $rsp) {
				$result = "{ \"result\":" . - 1 . ",\"errmsg\":\"" . $rsp . " " . curl_error ( $curl ) . "\"}";
			} else {
				$result = $ret;
			}
		}
		curl_close ( $curl );
		return $result;
	}
	
	/**
	 * 普通单发，明确指定内容，如果有多个签名，请在内容中以【】的方式添加到信息内容中，否则系统将使用默认签名
	 * 
	 * @param int $type
	 *        	短信类型，0 为普通短信，1 营销短信
	 * @param string $nationCode
	 *        	国家码，如 86 为中国
	 * @param string $phoneNumber
	 *        	不带国家码的手机号
	 * @param string $msg
	 *        	信息内容，必须与申请的模板格式一致，否则将返回错误
	 * @param string $ext
	 *        	服务端原样返回的参数，可填空串
	 * @return string json string { "result": xxxxx, "errmsg": "xxxxxx" ... }，被省略的内容参见协议文档
	 */
	public function send($type, $nationCode, $phoneNumber, $msg, $ext = "") {
		$random = self::getRandom ();
		$curTime = time ();
		$wholeUrl = $this->url . "?sdkappid=" . $this->appid . "&random=" . $random;
		
		// 按照协议组织 post 包体
		$data = new \stdClass ();
		$tel = new \stdClass ();
		$tel->nationcode = "" . $nationCode;
		$tel->mobile = "" . $phoneNumber;
		
		$data->tel = $tel;
		$data->type = ( int ) $type;
		$data->msg = $msg;
		$data->sig = hash ( "sha256", "appkey=" . $this->appkey . "&random=" . $random . "&time=" . $curTime . "&mobile=" . $phoneNumber, FALSE );
		$data->time = $curTime;
		$data->ext = $ext;
		return $this->sendCurlPost ( $wholeUrl, $data );
	}
	
	/**
	 * 指定模板单发
	 * 
	 * @param string $nationCode
	 *        	国家码，如 86 为中国
	 * @param string $phoneNumber
	 *        	不带国家码的手机号
	 * @param int $templId
	 *        	模板 id
	 * @param array $params
	 *        	模板参数列表，如模板 {1}...{2}...{3}，那么需要带三个参数
	 * @param string $sign
	 *        	签名，如果填空串，系统会使用默认签名
	 * @param string $ext
	 *        	服务端原样返回的参数，可填空串
	 * @return string json string { "result": xxxxx, "errmsg": "xxxxxx" ... }，被省略的内容参见协议文档
	 */
	public function sendWithParam($nationCode, $phoneNumber, $templId = 0, $params, $sign = "", $ext = "") {
		$random = self::getRandom ();
		$curTime = time ();
		$wholeUrl = $this->url . "?sdkappid=" . $this->appid . "&random=" . $random;
		
		// 按照协议组织 post 包体
		$data = new \stdClass ();
		$tel = new \stdClass ();
		$tel->nationcode = "" . $nationCode;
		$tel->mobile = "" . $phoneNumber;
		
		$data->tel = $tel;
		$data->sig = $this->calculateSigForTempl ( $this->appkey, $random, $curTime, $phoneNumber );
		$data->tpl_id = $templId;
		$data->params = $params;
		$data->sign = $sign;
		$data->time = $curTime;
		$data->ext = $ext;
		return $this->sendCurlPost ( $wholeUrl, $data );
	}
}