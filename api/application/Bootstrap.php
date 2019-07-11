<?php
/**
 * 
 *  加载核心类
 * @author zhaoyu 
 * @time 2018-05-10 20:20:00
 */
use Core\PDOQuery;
use Core\Logger;
use Core\YDRedis;
use Core\YDMemcache;
use Core\QddElasticsearch;

class Bootstrap extends Yaf_Bootstrap_Abstract {
	public function _initConfig() {
		Yaf_Registry::set ( 'config', Yaf_Application::app ()->getConfig () );
		
		include APPLICATION_PATH . '/conf/database.cfg.php';
		include APPLICATION_PATH . '/conf/cdn.cfg.php';
        include APPLICATION_PATH . '/conf/corefuns.php';
		
		$filterName = require APPLICATION_PATH . '/conf/filterName.php';
		$filterErrno = require APPLICATION_PATH . '/conf/filterErrno.php';
		
		Yaf_Registry::set ( 'filterErrno', $filterErrno );
		Yaf_Registry::set ( 'filterName', $filterName );
		Yaf_Registry::set ( 'DBConfig', $cfg );
	}
	public function _initAutoload() {
		$localLibDirs = scandir ( APPLICATION_PATH . '/application/library' );
		foreach ( $localLibDirs as $k => $d ) {
			if ($d == '.' || $d == '..') {
				unset ( $localLibDirs [$k] );
			}
		}
		
		if ($localLibDirs) {
			Yaf_Loader::getInstance ()->registerLocalNamespace ( array_values ( $localLibDirs ) );
		}
	}
	public function _initHandler() {
		$loglevel = Yaf_Application::app ()->getConfig ()->loglevel;
		
		/**
		 * 错误捕获
		 */
		set_error_handler ( function ($errno, $errstr, $errfile, $errline) {
			static $_error_types = [ 
					E_ERROR => 'E_ERROR',
					E_WARNING => 'E_WARNING',
					E_STRICT => 'E_STRICT',
					E_USER_ERROR => 'E_USER_ERROR',
					E_USER_WARNING => 'E_USER_WARNING',
					E_USER_NOTICE => 'E_USER_NOTICE' 
			];
			static $_user_error_map = [ 
					E_ERROR => E_USER_ERROR,
					E_WARNING => E_USER_WARNING,
					E_STRICT => E_USER_NOTICE 
			];
			
			ob_start ();
			debug_print_backtrace ( DEBUG_BACKTRACE_IGNORE_ARGS );
			$backtrace = ob_get_contents ();
			ob_end_clean ();
			
			$errtype = $_error_types [$errno];
			$info = [ 
					'URL: ' . (isset ( $_SERVER ['REQUEST_URI'] ) ? $_SERVER ['REQUEST_URI'] : ''),
					'REF: ' . (isset ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_REFERER'] : ''),
					'DATE: ' . date ( 'Y-m-d H:i:s' ),
					'INFO: ' . "错误级别: {$errtype}({$errno}), 文件: {$errfile}, 行号: {$errline}",
					'MSG: ' . $errstr,
					'TRACE: ' . $backtrace 
			];
			
			$log = new Logger ( 'Debug/E_USER_ERROR', $info, PHP_EOL );
			$log->write ();
			
			trigger_error ( "{$errstr} {$errtype}({$errno}) in {$errfile} on line {$errline}", (isset ( $_user_error_map [$errno] ) ? $_user_error_map [$errno] : $errno) );
		}, $loglevel );
		
		/**
		 * 异常捕获
		 */
		set_exception_handler ( function ($e) {
			$code = $e->getCode ();
			$msg = $e->getMessage ();
			$file = $e->getFile ();
			$line = $e->getLine ();
			$trace = $e->getTraceAsString ();
			
			$info = [ 
					'URL: ' . (isset ( $_SERVER ['REQUEST_URI'] ) ? $_SERVER ['REQUEST_URI'] : ''),
					'REF: ' . (isset ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_REFERER'] : ''),
					'DATE: ' . date ( 'Y-m-d H:i:s' ),
					'INFO: ' . "错误代码: {$code}, 文件: {$file}, 行号: {$line}",
					'MSG: ' . $msg,
					'TRACE: ' . $trace 
			];
			
			$log = new Logger ( 'Debug/E_USER_EXCEPTION', $info, PHP_EOL );
			$log->write ();
			
			trigger_error ( "{$msg} ($code)in {$file} on line {$line}", E_USER_ERROR );
		} );
	}
	public function _initCoreLoad() {
		// if (file_exists(APPLICATION_PATH . '/vendor/autoload.php')) {
		// require APPLICATION_PATH . '/vendor/autoload.php';
		// }
		// require APPLICATION_PATH . '/conf/corefuns.php';
	}
	public function _initDB() {
		Yaf_Registry::set ( 'PDO', function ($dbname) {
			$params = Yaf_Registry::get ( 'DBConfig' ) [$dbname];
			return new PDOQuery ( $params, null );
		} );
		
		Yaf_Registry::set ( 'Redis_r', function ($dbname) {
			$params = Yaf_Registry::get ( 'DBConfig' ) [$dbname];
			$redis = new YDRedis ( $params ['r'] ['host'], $params ['r'] ['port'] );
			
			if (isset ( $params ['r'] ['auth'] ) && $params ['r'] ['auth']) {
				$redis->authYD ( $params ['r'] ['auth'] );
			}
			
			return $redis;
		} );
		
		Yaf_Registry::set ( 'Redis_w', function ($dbname) {
			$params = Yaf_Registry::get ( 'DBConfig' ) [$dbname];
			$redis = new YDRedis ( $params ['w'] ['host'], $params ['w'] ['port'] );
			
			if (isset ( $params ['w'] ['auth'] ) && $params ['w'] ['auth']) {
				$redis->authYD ( $params ['w'] ['auth'] );
			}
			
			return $redis;
		} );
		
		Yaf_Registry::set ( 'Mem', function ($dbname) {
			$params = Yaf_Registry::get ( 'DBConfig' ) [$dbname];
			
			// $mem = new Memcache();
			// $mem->addServer($params[0]['host'],$params[0]['port']);
			$mem = new YDMemcache ( $params [0] ['host'], $params [0] ['port'] );
			return $mem;
		} );

        Yaf_Registry::set ( 'ES', function ($dbname) {
            $params = Yaf_Registry::get ( 'DBConfig' ) [$dbname];

            // $mem = new Memcache();
            // $mem->addServer($params[0]['host'],$params[0]['port']);
            $mem = new QddElasticsearch ($params);
            return $mem;
        } );



	}
	public function _initPlugin(Yaf_Dispatcher $Yaf_Dispatcher) {
		/* register a plugin */
		$Yaf_Dispatcher->registerPlugin ( new PretreatmentPlugin () );
	}
}
