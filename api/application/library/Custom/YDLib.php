<?php

/**
 * 自定义库文件
 * 
 * @package lib
 * @subpackage custom
 * @author zhaoyu
 * 
 */
namespace Custom;

final class YDLib {
	/**
	 * API统一输出函数
	 *
	 * @param integer $code
	 *        	返回代码, 0表示成功
	 * @param array|string $result
	 *        	返回结果
	 * @param bool $conv        	
	 */
	public static function output($code = 0, $result = '', $conv = true) {
//	    header('Content-type: application/json');
        header('Content-Type: text/html;charset=utf-8');
        header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
        header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
        header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
        header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with, Origin'); // 设置允许自定义请求头的字段
		if ($code === \ErrnoStatus::STATUS_SUCCESS && $result === [ ]) {
			$json = '{"errno": "0","errmsg":"未找到数据", "result": {}}';
		} else {
			$ret = $result;
			if (is_array ( $result ) && count ( $result ) > 0) {
				$ret = self::lcFirstRecursive ( $result );
			}
			$filterErrno = \Yaf_Registry::get ( 'filterErrno' );
			if (isset ( $filterErrno [$code] )) {
				$json = [ 
						'errno' => $code,
						'errmsg' => $filterErrno [$code],
						'result' => $ret 
				];
			} else {
				$json = [ 
						'errno' => \ErrnoStatus::STATUS_CODE_ERROR,
						'errmsg' => $filterErrno [\ErrnoStatus::STATUS_CODE_ERROR],
						'result' => $ret 
				];
			}
			
			// $json = $conv ? json_encode($json, JSON_NUMERIC_CHECK) : json_encode($json);
			$json = json_encode ( $json );
			
			$errno = json_last_error ();
			if ($errno != JSON_ERROR_NONE) {
				self::log ( 'json_output', [ 
						'errno: ' . $errno,
						'errmsg: ' . json_last_error_msg (),
						'result: ' . serialize ( $ret ),
						'trace:' . serialize ( debug_backtrace () ) 
				] );
			}
		}
		die ( $json );
	}
	
	/**
	 * API统一输出函数
	 *
	 * @param integer $code
	 *        	返回代码, 0表示成功
	 * @param string $errmsg
	 *        	错误
	 * @param array|string $result
	 *        	返回结果
	 * @param bool $conv        	
	 */
	public static function output_v2($code = 0, $errmsg = '', $result = [], $conv = true) {
		// header('Content-type: application/json');
		if ($code === \ErrnoStatus::STATUS_SUCCESS && $result === [ ]) {
			
			$json = '{"errno": 0,"errmsg":"empty data", "result": {}}';
		} else {
			$ret = $result;
			if (is_array ( $result ) && count ( $result ) > 0) {
				$ret = self::lcFirstRecursive ( $result );
			}
			
			$json = [ 
					'errno' => $code,
					'errmsg' => $errmsg,
					'result' => $ret 
			];
			
			$json = $conv ? json_encode ( $json, JSON_NUMERIC_CHECK ) : json_encode ( $json );
			// $json = json_encode($json);
			
			$errno = json_last_error ();
			if ($errno != JSON_ERROR_NONE) {
				self::log ( 'json_output', [ 
						'errno: ' . $errno,
						'errmsg: ' . json_last_error_msg (),
						'result: ' . serialize ( $ret ),
						'trace:' . serialize ( debug_backtrace () ) 
				] );
			}
		}
		die ( $json );
	}
	public static function lcFirstRecursive($arr) {
		if (! $arr)
			return [ ];
		
		$tmp = [ ];
		
		foreach ( $arr as $k => $v ) {
			if (is_array ( $v )) {
				$tmp [lcfirst ( $k )] = self::lcFirstRecursive ( $v );
			} else {
				$tmp [lcfirst ( $k )] = $v; // is_numeric($v) && (strlen($v) <= 10) ? (is_float($v) ? (float) $v : (int) $v) : $v;
			}
		}
		
		return $tmp;
	}
	
	/**
	 * 验证手机是否可用
	 * 
	 * @param string $Mobile        	
	 * @return boolean
	 */
	public static function validMobile($Mobile) {
		return ! preg_match ( '/^1[0-9]{10}$/', $Mobile );
	}
	
	/**
	 * 验证所有字符串
	 * 
	 * @param string $name        	
	 * @return boolean
	 */
	public static function validData($name) {
		return TRUE;
	}
	
	/**
	 * 终端类型
	 * 
	 * @return boolean
	 */
	public static function checkUserAgent() {
		$agent = isset ( $_SERVER ['HTTP_USER_AGENT'] ) ? strtolower ( $_SERVER ['HTTP_USER_AGENT'] ) : '';
		$data ['is_ipad'] = (stripos ( $agent, 'ipad' )) ? true : false;
		$data ['is_pc'] = (stripos ( $agent, 'windows nt' )) ? true : false;
		$data ['is_iphone'] = (stripos ( $agent, 'iphone' )) ? true : false;
		$data ['is_android'] = (stripos ( $agent, 'android' )) ? true : false;
		$data ['is_weixin_browser'] = (stripos ( $agent, 'micromessenger' )) ? true : false;
		$data ['isChrome'] = (stripos ( $agent, 'chrome' )) ? true : false;
		$data ['isSafari'] = (stripos ( $agent, 'safari' )) ? true : false;
		$data ['isFirefox'] = (stripos ( $agent, 'firefox' )) ? true : false;
		$data ['isOpera'] = (stripos ( $agent, 'opr' )) ? true : false;
		$data ['isAppleWebkit'] = (stripos ( $agent, 'applewebkit' )) ? true : false;
		$data ['mozilla'] = $agent == strtolower ( 'Mozilla/4.0' ) ? true : false;
		return $data;
	}
	
	/**
	 * 记录日志
	 *
	 * @param string $path
	 *        	日志路径, 如果不是以MobileApi/开头则自动加上
	 * @param mixed $params
	 *        	日志参数, 不传入则记录下所有GET/POST数据
	 * @return void
	 */
	public static function log($path, $params = false) {
		if (strpos ( $path, 'MobileApi/' ) !== 0) {
			$path = 'MobileApi/' . $path;
		}
		if ($params === false) {
			$params = [ 
					'appid: ' . $_GET ['appid'],
					'version: ' . $_GET ['_v'],
					'method: ' . $_GET ['_method'],
					'date:' . date ( "Y-m-d H:i:s" ),
					'$_GET: ' . json_encode ( $_GET ),
					'$_POST: ' . json_encode ( $_POST ) 
			];
		}
		if (! is_array ( $params )) {
			$params = [ 
					$params 
			];
		}
		
		self::runtime_log ( $path, $params );
	}
	
	/**
	 * GET 请求 完成
	 * 
	 * @param string $url        	
	 * @return mixed 返回数组
	 */
	public static function curlGetRequset($url) {
		$ch = curl_init ( $url );
		$ssl = substr ( $url, 0, 8 ) == "https://" ? TRUE : FALSE;
		if ($ssl) {
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE ); // 禁用后cURL将终止从服务端进行验证
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE ); // 以上两项为https使用
		}
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true ); // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 3 );
		
		$tmpInfo = curl_exec ( $ch );
		if (curl_errno ( $ch )) {
			self::testLog ( 'Errno ' . curl_error ( $ch ) . ' request url: ' . $url );
		}
		curl_close ( $ch );
		$tmpInfo = json_decode ( $tmpInfo, true );
		return $tmpInfo;
	}
	
	/**
	 * POST请求
	 * 
	 * @param unknown $url        	
	 * @param string $data        	
	 * @return mixed 返回数组
	 */
	public static function curlPostRequset($url, $data = '') {
		$ch = curl_init ( $url );
		$ssl = substr ( $url, 0, 8 ) == "https://" ? TRUE : FALSE;
		if ($ssl) {
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE ); // 禁用后cURL将终止从服务端进行验证
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE ); // 以上两项为https使用
		}
		curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" ); // 自定义请求类型
		curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 ); // 设置header
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 ); // 要求结果为字符串且输出到屏幕上
		curl_setopt ( $ch, CURLOPT_POST, 1 ); // post提交方式
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data ); // post方式发送数据
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true ); // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
		$tmpInfo = curl_exec ( $ch );
		if (curl_errno ( $ch )) {
			self::testLog ( 'Errno' . curl_error ( $ch ) );
		}
		curl_close ( $ch );
		$tmpInfo = json_decode ( $tmpInfo, true );
		return $tmpInfo;
	}
	public static function file_get_contents_use_post($uri, $data = array()) {
		if (empty ( $data )) {
			return file_get_contents ( $uri );
		}
		$postdata = http_build_query ( $data );
		$opts = array (
				'http' => array (
						'method' => 'POST',
						'header' => 'Content-type: application/x-www-form-urlencoded; charset=UTF-8',
						'content' => $postdata,
						'timeout' => 3 
				) 
		);
		
		$context = stream_context_create ( $opts );
		return file_get_contents ( $uri, false, $context );
		/*
		 * $headers = "POST ".$url['protocol'].$url['host'].$url['path']." HTTP/1.0".PHPEOL.
		 * "Host: ".$url['host'].PHPEOL.
		 * "Referer: ".$url['protocol'].$url['host'].$url['path'].PHPEOL.
		 * "Content-Type: application/x-www-form-urlencoded".PHPEOL.
		 * "Content-Length: ".strlen($url['query']).PHPEOL.
		 * "Authorization: Basic ".base64_encode("$https_user:$https_password").PHPEOL.
		 * $url['query'];
		 *
		 * Accept:* / *
		 * Accept-Encoding:gzip, deflate
		 * Accept-Language:zh,zh-CN;q=0.8
		 * Connection:keep-alive
		 * Content-Length:136
		 * Content-Type:application/x-www-form-urlencoded; charset=UTF-8
		 * Cookie:xxx
		 * Origin:http://www.baidu.com
		 * Referer:http://www.baidu.com/
		 * User-Agent:Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.76 Safari/537.36
		 * X-Requested-With:XMLHttpRequest
		 */
	}
	
	/**
	 * 生成邀请码
	 * 
	 * @param number $size        	
	 * @param string $Ignore        	
	 * @param string $excNum        	
	 * @param string $tolow        	
	 * @return Ambigous <unknown, string>
	 */
	public static function createInviteCode($size = 10, $Ignore = false, $excNum = false, $tolow = false) {
		$InviteCode = '';
		$excludeArr = $Ignore ? [ 
				58,
				59,
				60,
				61,
				62,
				63,
				64,
				65,
				66,
				67,
				68,
				69,
				70,
				71,
				72,
				73,
				74,
				75,
				76,
				77,
				78,
				79,
				80,
				81,
				82,
				83,
				84,
				85,
				86,
				87,
				88,
				89,
				90,
				91,
				92,
				93,
				94,
				95,
				96 
		] : [ 
				58,
				59,
				60,
				61,
				62,
				63,
				64,
				91,
				92,
				93,
				94,
				95,
				96 
		];
		
		if ($excNum)
			$excludeArr = array_merge ( $excludeArr, [ 
					48,
					49,
					50,
					51,
					52,
					53,
					54,
					55,
					56,
					57 
			] );
		
		for($i = 0; $i < $size; $i ++) {
			while ( true ) {
				$chr = mt_rand ( 48, 122 );
				
				if (! in_array ( $chr, $excludeArr ))
					break;
			}
			$InviteCode .= chr ( $chr );
		}
		
		$InviteCode = $tolow ? strtolower ( $InviteCode ) : $InviteCode;
		return $InviteCode;
	}
	
	/**
	 * 分表hash计算
	 *
	 * @param integer $id
	 *        	项目ID
	 * @return string
	 */
	public static function table($id) {
		return sprintf ( '%02x', intval ( $id ) % 256 );
	}
	
	/**
	 * 写测试日志
	 * 
	 * @param mixed $data        	
	 * @param string $path        	
	 * @param string $mode        	
	 */
	public static function testLog($data, $mode = '') {
		$mode = empty ( $mode ) ? 'a+' : $mode;
		$logDir = APPLICATION_PATH . "/../data/api/logs/";
		if (! file_exists ( $logDir )) {
			mkdir ( $logDir, 0777, TRUE );
		}
		$path = $logDir . 'apiinfo_' . date ( "Y-m-d" ) . ".log";
		$fp = fopen ( $path, $mode );
		fwrite ( $fp, self::getCurrentDate () . PHP_EOL );
		fwrite ( $fp, print_r ( $data, true ) );
		fwrite ( $fp, PHP_EOL );
		fclose ( $fp );
	}
	
	/**
	 * 写测试日志
	 * 
	 * @param mixed $data        	
	 * @param string $path        	
	 * @param string $mode        	
	 */
	public static function testLogHfb($data, $mode = '') {
		$mode = empty ( $mode ) ? 'a+' : $mode;
		$logDir = APPLICATION_PATH . "/../data/api/logs/";
		if (! file_exists ( $logDir )) {
			mkdir ( $logDir, 0777, TRUE );
		}
		$path = $logDir . 'hfb_' . date ( "Y-m-d" ) . ".log";
		$fp = fopen ( $path, $mode );
		fwrite ( $fp, self::getCurrentDate () . PHP_EOL );
		fwrite ( $fp, print_r ( $data, true ) );
		fwrite ( $fp, PHP_EOL );
		fclose ( $fp );
	}
	
	/**
	 * 隐藏号码昵称
	 *
	 * @param string $mobile
	 *        	手机号码
	 * @return string
	 */
	public static function hideMobileName($mobile) {
		return substr ( $mobile, 0, 4 ) . '***' . substr ( $mobile, - 1 );
	}
	
	/**
	 * 对Smarty中的html标记添加引号的过滤
	 *
	 * @param String $str        	
	 * @return String
	 */
	public static function _htmlspecialchars($str) {
		return htmlspecialchars ( $str, ENT_QUOTES );
	}
	
	/**
	 * 根据用户ID 获取头像
	 *
	 * @param integer $userid
	 *        	用户ID
	 * @param integer $size
	 *        	头像尺寸 240/180/114/80/64
	 * @param integer $ver
	 *        	头像版本, >1 才体现
	 * @param integer $server
	 *        	服务器, 默认2
	 * @return string 返回头像地址
	 */
	public static function headImg($userid, $size = 114, $ver = 0, $server = 2) {
		$allow_size = [ 
				'240' => 1,
				'180' => 1,
				'114' => 1,
				'80' => 1,
				'64' => 1 
		];
		
		$hash1 = sprintf ( "%02x", $userid % 256 );
		$hash2 = sprintf ( "%02x", $userid / 256 % 256 );
		$serverid = $userid % $server;
		$size = $allow_size [$size] === 1 ? $size : '114';
		$ver = $ver > 1 ? '?v=' . $ver : '';
		
		return "http://head{$serverid}.zhahehe.com/{$hash1}/{$hash2}/{$userid}_{$size}.jpg{$ver}";
	}
	
	/**
	 * 记录日志
	 *
	 * @param string $path
	 *        	保存路径和文件名前缀，必选。如"a/b/c", "a/b"为目录， "c"为文件名前缀
	 * @param string|array $data
	 *        	需要保存的数据，必选。如"abcdefg"或array('a','b','c','d')
	 * @param string $title
	 *        	自定义日志文件名部分，非必选。默认为空，如果设置则文件名跟着变化，如设置d，文件名则是：c_d_20120315.log或c_d.log
	 * @param boolean $logtime
	 *        	设置文件名是否需要按时间命名。默认true, 文件名格式：c_20120315.log, 可设置false，文件名格式：c.log
	 * @param string $sep
	 *        	设置数据的分隔符，默认为\t
	 * @return boolean 返回记录日志是否成功。 true|false
	 */
	public static function runtime_log($path, $data, $title = "", $logtime = true, $sep = "\t") {
		if (! $path || ! $data)
			return false;
		
		static $_static = [ ];
		if (! isset ( $_static [$path] )) {
			$logger = new \Core\Logger ();
			$logger->setPath ( $path );
			
			if ($title)
				$logger->setTitle ( $title );
			if ($logtime === false)
				$logger->setNoTime ( $logtime );
			if ($sep != "\t")
				$logger->setSep ( $sep );
			$_static [$path] = $logger;
		} else
			$logger = $_static [$path];
		
		$logger->resetData ();
		$logger->setData ( $data );
		$flag = $logger->write ();
		
		return $flag;
	}
	public static function getCurrentDate($format = 'Y-m-d H:i:s', $time = 0) {
		if ($time == 0)
			$time = $_SERVER ['REQUEST_TIME'];
		return date ( $format, $time );
	}

    /**
     *
     * 获取Elasticsearch
     *
     * @param string $dbname
     * @return \Core\PDOQuery
     */
    public static function getES($dbname) {
        /** @var callable $changeDB */
        // $changeDB = \Yaf_Registry::get('PDO');
        // return $changeDB($dbname);
        static $dbpools = [ ];
        if (! isset ( $dbpools [$dbname] )) {
            $changeDB = \Yaf_Registry::get ( 'ES' );
            $dbpools [$dbname] = $changeDB ( $dbname );
        }

        return $dbpools [$dbname];
    }
	
	/**
	 *
	 * 获取PDO
	 * 
	 * @param string $dbname        	
	 * @return \Core\PDOQuery
	 */
	public static function getPDO($dbname) {
		/** @var callable $changeDB */
		// $changeDB = \Yaf_Registry::get('PDO');
		// return $changeDB($dbname);
		static $dbpools = [ ];
		if (! isset ( $dbpools [$dbname] )) {
			$changeDB = \Yaf_Registry::get ( 'PDO' );
			$dbpools [$dbname] = $changeDB ( $dbname );
		}
		
		return $dbpools [$dbname];
	}
	
	/**
	 * getRedis
	 * 
	 * @param string $dbname        	
	 * @param string $oprator        	
	 * @return \Redis
	 */
	public static function getRedis($dbname, $oprator = 'w') {
		static $redisPools = [ ];
		
		$changeDB = $oprator == 'w' ? 'Redis_w' : 'Redis_r';
		if (! isset ( $redisPools [$dbname] )) {
			/** @var callable $changeDB */
			$changeDB = \Yaf_Registry::get ( $changeDB );
			$redisPools [$dbname] = $changeDB ( $dbname );
			;
		}
		
		return $redisPools [$dbname];
	}
	
	/**
	 *
	 * 获取MEM
	 * 
	 * @param unknown $dbname        	
	 */
	public static function getMem($dbname) {
		/** @var callable $changeDB */
		$changeDB = \Yaf_Registry::get ( 'Mem' );
		return $changeDB ( $dbname );
	}
	
	/**
	 * 校验参数
	 * 
	 * @param unknown $postData        	
	 * @return boolean
	 */
	public static function checkToken($data = [], $token = '') {
		if ($data) {
			if (isset ( $data ['_v'] )) {
				switch (strtolower ( $data ['_v'] )) {
					case 'v1' :
						;
						break;
					
					case 'v2' :
						
						break;
					
					default :
						;
						break;
				}
				unset ( $data ['_v'] );
			}
			
			if (isset ( $data ['_method'] ))
				unset ( $data ['_method'] );
			if (isset ( $data ['access_token'] )) {
				if (empty ( $token ))
					$token = $data ['access_token'];
				unset ( $data ['access_token'] );
			}
			
			ksort ( $data );
			$str = '';
			foreach ( $data as $key => $value )
				$str .= $key . '=' . $value;
			if ($token == md5 ( $str ))
				return true;
			else {
				if (__ENV__ != 'ONLINE')
					self::output ( \ErrnoStatus::STATUS_10001, [ 
							'originData' => $str,
							'originToken' => $token,
							'md5Token' => md5 ( $str ) 
					] );
				return false;
			}
		} else
			return false;
	}
	public static function createBonudAPISgin() {
		$token = '065c9f26552c1e9e618071fc6a8c575c';
		$timestamp = $_SERVER ['REQUEST_TIME'];
		$nonce = mt_rand ( 1000, 9999 );
		$tmpArr = array (
				$token,
				$timestamp,
				$nonce 
		);
		
		sort ( $tmpArr, SORT_STRING );
		$tmpStr = implode ( $tmpArr );
		$signature = sha1 ( $tmpStr );
		return [ 
				'signature' => $signature,
				'timestamp' => $timestamp,
				'nonce' => $nonce 
		];
	}
	
	/**
	 * 校验验证码
	 * 
	 * @param string $mobile        	
	 * @param mixed $code        	
	 * @param int $type        	
	 * @return boolean
	 */
	public static function checkCode($mobile, $code, $type) {
		$sms = \Core\Sms::getLastSms ( $mobile, $type );
		if (! empty ( $sms ['SmsId'] )) {
			$time = strtotime ( $sms ['CreateTime'] );
			if ($sms ['Flag'] == $code && time () - $time <= 3600)
				return true;
			else
				return false;
		}
	}
	
	/**
	 *
	 * @param unknown $value        	
	 * @param unknown $inData        	
	 * @return boolean
	 */
	public static function checkValueInArr($value, $inData = []) {
		if (empty ( $value ) || empty ( $inData ))
			return false;
			// value是字符串，则比较是区分大小写的。 这里不检查类型
		return in_array ( $value, $inData );
	}
	
	/**
	 * 快速检查是否含有非法字符
	 *
	 * @param string $content
	 *        	需要检查的内容
	 * @return boolean 含有非法内容返回true,否则返回false
	 */
	public static function fastCheck($content) {
		$key_words = \Yaf_Registry::get ( 'filterName' );
		foreach ( $key_words as $v )
			if (stripos ( $content, $v ) !== false)
				return true;
		return false;
	}
	
	/**
	 * 快速替换非法关键字
	 *
	 * @param string $content
	 *        	需要检查的内容
	 * @param string $replace
	 *        	需要替换的内容
	 * @return string 返回结果
	 */
	public static function fastReplace($content, $replace = "**") {
		$key_words = \Yaf_Registry::get ( 'filterName' );
		return str_ireplace ( $key_words, $replace, $content );
	}
	
	/**
	 * 验证必要参数是否为空
	 * 
	 * @param mixed $data        	
	 * @return boolean
	 */
	public static function checkData($data = NULL) {
		if (is_string ( $data ) && ! isset ( $data [0] )) {
			return false;
		} else if (is_array ( $data )) {
			foreach ( $data as $v ) {
				if (! self::checkData ( $v )) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * 转换sql IN
	 * 
	 * @param array|string $data        	
	 * @param string $key        	
	 * @return array
	 */
	public static function convertSqlIN($data, $key) {
		if (empty ( $data ))
			return '';
		
		$data = is_array ( $data ) ? $data : explode ( ',', $data );
		$num = count ( $data );
		if ($num == 1)
			$item ["$key"] = $data [0];
		else if ($num > 1)
			$item ["$key IN"] = $data;
		
		return $item;
	}
	
	/**
	 * 生成唯一orderSN
	 * 
	 * @param int $ShopId        	
	 * @return boolean|string
	 */
	public static function createOrderUniqId($ShopId = NULL) {
		return $ShopId ? date ( 'YmdHis' ) . sprintf ( '%07d', $ShopId ) . mt_rand ( 10000, 99999 ) : date ( 'YmdHis' ) . mt_rand ( 10000000000, 99999999999 );
	}
	public static function createCouponSn() {
		return mt_rand ( 100000, 999999 );
	}
	
	/**
	 * 发送消息
	 *
	 * @param int $UserId        	
	 * @param string $Title        	
	 * @param string $Content        	
	 * @param int $SendId        	
	 *
	 * @return bool
	 */
	public static function sendNotice($UserId, $Title, $Content, $SendId = 0) {
		if ($UserId && $Title && $Content) {
			
			$queue = new \Core\Queue ();
			$res = $queue->add ( 'SendNotice', serialize ( [ 
					'UserId' => $UserId,
					'Title' => $Title,
					'Content' => $Content,
					'SendId' => $SendId 
			] ) );
			return true;
		}
		return false;
	}
	public static function xml2array($contents, $get_attributes = 1, $priority = 'tag') {
		if (! $contents)
			return array ();
		
		if (! function_exists ( 'xml_parser_create' )) {
			// print "'xml_parser_create()' function not found!";
			return array ();
		}
		
		// Get the XML parser of PHP - PHP must have this module for the parser to work
		$parser = xml_parser_create ( '' );
		xml_parser_set_option ( $parser, XML_OPTION_TARGET_ENCODING, "UTF-8" ); // http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
		xml_parser_set_option ( $parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option ( $parser, XML_OPTION_SKIP_WHITE, 1 );
		xml_parse_into_struct ( $parser, trim ( $contents ), $xml_values );
		xml_parser_free ( $parser );
		
		if (! $xml_values)
			return; // Hmm...
				                         
		// Initializations
		$xml_array = array ();
		$parents = array ();
		$opened_tags = array ();
		$arr = array ();
		
		$current = &$xml_array; // Refference
		                        
		// Go through the tags.
		$repeated_tag_index = array (); // Multiple tags with same name will be turned into an array
		foreach ( $xml_values as $data ) {
			unset ( $attributes, $value ); // Remove existing values, or there will be trouble
			                           
			// This command will extract these variables into the foreach scope
			                           // tag(string), type(string), level(int), attributes(array).
			extract ( $data ); // We could use the array by itself, but this cooler.
			
			$result = array ();
			$attributes_data = array ();
			
			if (isset ( $value )) {
				if ($priority == 'tag')
					$result = $value;
				else
					$result ['value'] = $value; // Put the value in a assoc array if we are in the 'Attribute' mode
			}
			
			// Set the attributes too.
			if (isset ( $attributes ) and $get_attributes) {
				foreach ( $attributes as $attr => $val ) {
					if ($priority == 'tag')
						$attributes_data [$attr] = $val;
					else
						$result ['attr'] [$attr] = $val; // Set all the attributes in a array called 'attr'
				}
			}
			
			// See tag status and do the needed.
			if ($type == "open") { // The starting of the tag '<tag>'
				$parent [$level - 1] = &$current;
				if (! is_array ( $current ) or (! in_array ( $tag, array_keys ( $current ) ))) { // Insert New tag
					$current [$tag] = $result;
					if ($attributes_data)
						$current [$tag . '_attr'] = $attributes_data;
					$repeated_tag_index [$tag . '_' . $level] = 1;
					
					$current = &$current [$tag];
				} else { // There was another element with the same tag name
					
					if (isset ( $current [$tag] [0] )) { // If there is a 0th element it is already an array
						$current [$tag] [$repeated_tag_index [$tag . '_' . $level]] = $result;
						$repeated_tag_index [$tag . '_' . $level] ++;
					} else { // This section will make the value an array if multiple tags with the same name appear together
						$current [$tag] = array (
								$current [$tag],
								$result 
						); // This will combine the existing item and the new item together to make an array
						$repeated_tag_index [$tag . '_' . $level] = 2;
						
						if (isset ( $current [$tag . '_attr'] )) { // The attribute of the last(0th) tag must be moved as well
							$current [$tag] ['0_attr'] = $current [$tag . '_attr'];
							unset ( $current [$tag . '_attr'] );
						}
					}
					$last_item_index = $repeated_tag_index [$tag . '_' . $level] - 1;
					$current = &$current [$tag] [$last_item_index];
				}
			} elseif ($type == "complete") { // Tags that ends in 1 line '<tag />'
			                                // See if the key is already taken.
				if (! isset ( $current [$tag] )) { // New Key
					$current [$tag] = $result;
					$repeated_tag_index [$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $attributes_data)
						$current [$tag . '_attr'] = $attributes_data;
				} else { // If taken, put all things inside a list(array)
					if (isset ( $current [$tag] [0] ) and is_array ( $current [$tag] )) { // If it is already an array...
					                                                            
						// ...push the new element into that array.
						$current [$tag] [$repeated_tag_index [$tag . '_' . $level]] = $result;
						
						if ($priority == 'tag' and $get_attributes and $attributes_data) {
							$current [$tag] [$repeated_tag_index [$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index [$tag . '_' . $level] ++;
					} else { // If it is not an array...
						$current [$tag] = array (
								$current [$tag],
								$result 
						); // ...Make it an array using using the existing value and the new value
						$repeated_tag_index [$tag . '_' . $level] = 1;
						if ($priority == 'tag' and $get_attributes) {
							if (isset ( $current [$tag . '_attr'] )) { // The attribute of the last(0th) tag must be moved as well
								
								$current [$tag] ['0_attr'] = $current [$tag . '_attr'];
								unset ( $current [$tag . '_attr'] );
							}
							
							if ($attributes_data) {
								$current [$tag] [$repeated_tag_index [$tag . '_' . $level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index [$tag . '_' . $level] ++; // 0 and 1 index is already taken
					}
				}
			} elseif ($type == 'close') { // End of tag '</tag>'
				$current = &$parent [$level - 1];
			}
		}
		
		return ($xml_array);
	}

    /**
     * POST请求(微信专用)
     * @param unknown $url
     * @param string $data
     * @return mixed  返回数组
     */
    public static function curlPostRequsetByWeixin($url,$data='')
    {

        $ch = curl_init($url);
        $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
        if ($ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//禁用后cURL将终止从服务端进行验证
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);// 以上两项为https使用
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");//自定义请求类型
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);//post方式发送数据
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        $tmpInfo = curl_exec($ch);

        if (curl_errno($ch)) {
            self::testLog('Errno'.curl_error($ch));
        }
        curl_close($ch);
        $tmpInfo = json_decode($tmpInfo, true);
        return $tmpInfo;
    }
}
