<?php
use Core\PDOQuery;
use Core\YDRedis;
use Core\Logger;
use Core\YDMemcache;

/**
* 
* @author zhaoyu 
* @time 2018-04-09 20:20:00
*/
class Bootstrap extends Yaf_Bootstrap_Abstract
{
    //controller模型白名单，用于外部接口访问
    const M_WHITE_LIST = ["Weixin"];
    //controller控制层白名单，用户外部控制接口访问
    const C_WHITE_LIST = ["Wechat"];

	/**
	 * 初始化配置
	 */
	public function _initConfig() 
	{
        $config = Yaf_Application::app()->getConfig();
		Yaf_Registry::set("config", $config);
		

        include APPLICATION_PATH . '/conf/database.cfg.php';
        include APPLICATION_PATH . '/conf/cdn.cfg.php';
        include APPLICATION_PATH . '/conf/corefuns.php';

        Yaf_Registry::set('DBConfig', $cfg);




    }
	
	public function _initDB() 
	{
        Yaf_Registry::set('PDO', function($dbname) {
            $params = Yaf_Registry::get('DBConfig')[$dbname];
            return new PDOQuery($params, null);
        });
        
        Yaf_Registry::set('Redis_r', function($dbname) {
            $params = Yaf_Registry::get('DBConfig')[$dbname];
            $redis = new YDRedis($params['r']['host'], $params['r']['port']);
           
            if (isset($params['r']['auth']) && $params['r']['auth']) {
            	
                $redis->authYD($params['r']['auth']);
            }
            
            return $redis;
        });
        
        Yaf_Registry::set('Redis_w', function($dbname) {
            $params = Yaf_Registry::get('DBConfig')[$dbname];
            $redis = new YDRedis($params['w']['host'], $params['w']['port']);    
			
            if (isset($params['w']['auth']) && $params['w']['auth']) {
                $redis->authYD($params['w']['auth']);
				
            }
            return $redis;
        });
        
        Yaf_Registry::set('Mem', function($dbname) {
            $params = Yaf_Registry::get('DBConfig')[$dbname];
			
			//$mem = new Memcache();
            //$mem->addServer($params[0]['host'],$params[0]['port']);
			$mem = new YDMemcache($params[0]['host'],$params[0]['port']);
            return $mem;
        });
    }
	 
	public function _initHandler() 
    {
        $loglevel = Yaf_Application::app()->getConfig()->loglevel;

        /** 错误捕获 */
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
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

            ob_start();
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $backtrace = ob_get_contents();
            ob_end_clean();

            $errtype = $_error_types[$errno];
            $info = [
                'URL: ' . (isset($_SERVER['REQUEST_URI'])  ? $_SERVER['REQUEST_URI'] : ''),
                'REF: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''),
                'DATE: ' . date('Y-m-d H:i:s'),
                'INFO: ' . "错误级别: {$errtype}({$errno}), 文件: {$errfile}, 行号: {$errline}",
                'MSG: ' . $errstr,
                'TRACE: ' . $backtrace,
            ];
			
            $log = new Logger('Debug/E_USER_ERROR', $info, PHP_EOL);
            $log->write();

            trigger_error("{$errstr} {$errtype}({$errno}) in {$errfile} on line {$errline}", (isset($_user_error_map[$errno]) ? $_user_error_map[$errno] : $errno));
        }, $loglevel);
		
	
        /** 异常捕获 */
        set_exception_handler(function( $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();
            $trace = $e->getTraceAsString();

            $info = [
                'URL: ' . (isset($_SERVER['REQUEST_URI'])  ? $_SERVER['REQUEST_URI'] : ''),
                'REF: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''),
                'DATE: ' . date('Y-m-d H:i:s'),
                'INFO: ' . "错误代码: {$code}, 文件: {$file}, 行号: {$line}",
                'MSG: ' . $msg,
                'TRACE: ' . $trace,
            ];
	
            $log = new Logger('Debug/E_USER_EXCEPTION', $info, PHP_EOL);
            $log->write();

            trigger_error("{$msg} ($code)in {$file} on line {$line}", E_USER_ERROR);
        });
		
    }

	/**
	 * 初始化路由
	 */
	public function _initRoute(Yaf_Dispatcher $dispatcher) 
	{
		//HMVC
        $router = Yaf_Dispatcher::getInstance()->getRouter();
        //$router->addConfig(Yaf_Registry::get("config")->routes);
        $route = new Yaf_Route_Simple("m", "c", "a");
  		$router->addRoute("name", $route);
    }
	
    public function _initDefaultName(Yaf_Dispatcher $dispatcher) 
    {
		$dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
    }
}
