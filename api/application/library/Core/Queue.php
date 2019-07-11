<?php

/**
 * 队列控制类
 * 
 * @author 苏宁 <suning@haodou.com>
 * 
 * $Id: Queue.class.php 2 2014-02-19 09:52:07Z suning $
 */
namespace Core;

class Queue extends httpsqs {
	public function __construct($group = 'default') {
		parent::__construct ( \Yaf_Registry::get ( 'config' ) ['queue'] ['httpsqs'] [$group] ['host'], \Yaf_Registry::get ( 'config' ) ['queue'] ['httpsqs'] [$group] ['port'] );
	}
	
	/**
	 * 向队列中添加一条数据
	 *
	 * @param string $name
	 *        	队列名
	 * @param string|array $data
	 *        	队列数据
	 * @return boolean
	 */
	public function add($name, $data) {
		if (is_string ( $data ))
			$data = trim ( $data );
		else
			$data = serialize ( $data );
		
		$result = $this->put ( $name, $data );
		$logs = array (
				"status : false",
				"name: {$name}",
				"data: {$data}",
				"time: " . date ( "Y-m-d H:i:s" ) 
		);
		if ($result === false) {
			$log = new \Core\Logger ( "./data/log/HTTPSQS/{$name}_Fail", $logs );
			$log->write ();
		}
		if ($result === true) {
			$logs [0] = "status:true";
			$log = new \Core\Logger ( "HTTPSQS/{$name}_Succ", $logs );
			$log->write ();
		}
		if ($result === "HTTPSQS_PUT_END") {
			$log = new \Core\Logger ( "HTTPSQS/{$name}_Overflow", $logs );
			return false;
		}
		return $result;
	}
}

/**
 * httpsqs 模块类
 */
class httpsqs {
	public $httpsqs_host;
	public $httpsqs_port;
	public $httpsqs_auth;
	public $httpsqs_charset;
	public function __construct($host = '127.0.0.1', $port = 1218, $auth = '', $charset = 'utf-8') {
		$this->httpsqs_host = $host;
		$this->httpsqs_port = $port;
		$this->httpsqs_auth = $auth;
		$this->httpsqs_charset = $charset;
		return true;
	}
	public function http_get($query) {
		$socket = fsockopen ( $this->httpsqs_host, $this->httpsqs_port, $errno, $errstr, 5 );
		if (! $socket) {
			return false;
		}
		$host = $this->httpsqs_host;
		$out = "GET ${query} HTTP/1.1\r\n";
		$out .= "Host: ${host}\r\n";
		$out .= "Connection: close\r\n";
		$out .= "\r\n";
		fwrite ( $socket, $out );
		$line = trim ( fgets ( $socket ) );
		$header = "";
		$header .= $line;
		list ( $proto, $rcode, $result ) = explode ( " ", $line );
		$len = - 1;
		while ( ($line = trim ( fgets ( $socket ) )) != "" ) {
			$header .= $line;
			if (strstr ( $line, "Content-Length:" )) {
				list ( $cl, $len ) = explode ( " ", $line );
			}
			if (strstr ( $line, "Pos:" )) {
				list ( $pos_key, $pos_value ) = explode ( " ", $line );
			}
			if (strstr ( $line, "Connection: close" )) {
				$close = true;
			}
		}
		if ($len <= 0) {
			return false;
		}
		
		$body = fread ( $socket, $len );
		$fread_times = 0;
		while ( strlen ( $body ) < $len ) {
			$body1 = fread ( $socket, $len );
			$body .= $body1;
			unset ( $body1 );
			if ($fread_times > 100) {
				break;
			}
			$fread_times ++;
		}
		// if ($close) fclose($socket);
		fclose ( $socket );
		$result_array ["pos"] = ( int ) $pos_value;
		$result_array ["data"] = $body;
		return $result_array;
	}
	public function http_post($query, $body) {
		$socket = fsockopen ( $this->httpsqs_host, $this->httpsqs_port, $errno, $errstr, 5 );
		if (! $socket) {
			return false;
		}
		$host = $this->httpsqs_host;
		$out = "POST ${query} HTTP/1.1\r\n";
		$out .= "Host: ${host}\r\n";
		$out .= "Content-Length: " . strlen ( $body ) . "\r\n";
		$out .= "Connection: close\r\n";
		$out .= "\r\n";
		$out .= $body;
		fwrite ( $socket, $out );
		$line = trim ( fgets ( $socket ) );
		$header = "";
		$header .= $line;
		list ( $proto, $rcode, $result ) = explode ( " ", $line );
		$len = - 1;
		while ( ($line = trim ( fgets ( $socket ) )) != "" ) {
			$header .= $line;
			if (strstr ( $line, "Content-Length:" )) {
				list ( $cl, $len ) = explode ( " ", $line );
			}
			if (strstr ( $line, "Pos:" )) {
				list ( $pos_key, $pos_value ) = explode ( " ", $line );
			}
			if (strstr ( $line, "Connection: close" )) {
				$close = true;
			}
		}
		if ($len < 0) {
			return false;
		}
		$body = @fread ( $socket, $len );
		// if ($close) fclose($socket);
		fclose ( $socket );
		$result_array ["pos"] = ( int ) $pos_value;
		$result_array ["data"] = $body;
		return $result_array;
	}
	public function put($queue_name, $queue_data) {
		$result = $this->http_post ( "/?auth=" . $this->httpsqs_auth . "&charset=" . $this->httpsqs_charset . "&name=" . $queue_name . "&opt=put", $queue_data );
		if ($result ["data"] == "HTTPSQS_PUT_OK") {
			return true;
		} else if ($result ["data"] == "HTTPSQS_PUT_END") {
			return $result ["data"];
		}
		return false;
	}
	public function get($queue_name) {
		$result = $this->http_get ( "/?auth=" . $this->httpsqs_auth . "&charset=" . $this->httpsqs_charset . "&name=" . $queue_name . "&opt=get" );
		if ($result == false || $result ["data"] == "HTTPSQS_ERROR" || $result ["data"] == false) {
			return false;
		}
		return $result ["data"];
	}
	public function gets($queue_name) {
		$result = $this->http_get ( "/?auth=" . $this->httpsqs_auth . "&charset=" . $this->httpsqs_charset . "&name=" . $queue_name . "&opt=get" );
		if ($result == false || $result ["data"] == "HTTPSQS_ERROR" || $result ["data"] == false) {
			return false;
		}
		return $result;
	}
	public function status($queue_name) {
		$result = $this->http_get ( "/?auth=" . $this->httpsqs_auth . "&charset=" . $this->httpsqs_charset . "&name=" . $queue_name . "&opt=status" );
		if ($result == false || $result ["data"] == "HTTPSQS_ERROR" || $result ["data"] == false) {
			return false;
		}
		return $result ["data"];
	}
	public function view($queue_name, $queue_pos) {
		$result = $this->http_get ( "/?auth=" . $this->httpsqs_auth . "&charset=" . $this->httpsqs_charset . "&name=" . $queue_name . "&opt=view&pos=" . $queue_pos );
		if ($result == false || $result ["data"] == "HTTPSQS_ERROR" || $result ["data"] == false) {
			return false;
		}
		return $result ["data"];
	}
	public function reset($queue_name) {
		$result = $this->http_get ( "/?auth=" . $this->httpsqs_auth . "&charset=" . $this->httpsqs_charset . "&name=" . $queue_name . "&opt=reset" );
		if ($result ["data"] == "HTTPSQS_RESET_OK") {
			return true;
		}
		return false;
	}
	public function maxqueue($queue_name, $num) {
		$result = $this->http_get ( "/?auth=" . $this->httpsqs_auth . "&charset=" . $this->httpsqs_charset . "&name=" . $queue_name . "&opt=maxqueue&num=" . $num );
		if ($result ["data"] == "HTTPSQS_MAXQUEUE_OK") {
			return true;
		}
		return false;
	}
	public function status_json($queue_name) {
		$result = $this->http_get ( "/?auth=" . $this->httpsqs_auth . "&charset=" . $this->httpsqs_charset . "&name=" . $queue_name . "&opt=status_json" );
		if ($result == false || $result ["data"] == "HTTPSQS_ERROR" || $result ["data"] == false) {
			return false;
		}
		return $result ["data"];
	}
	public function synctime($num) {
		$result = $this->http_get ( "/?auth=" . $this->httpsqs_auth . "&charset=" . $this->httpsqs_charset . "&name=httpsqs_synctime&opt=synctime&num=" . $num );
		if ($result ["data"] == "HTTPSQS_SYNCTIME_OK") {
			return true;
		}
		return false;
	}
}
?>