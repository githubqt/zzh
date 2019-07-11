<?php

/**
 *  扎呵呵基础配置文件
 *
 * @author zhaoyu <zhaoyu@zhahehe.com>
 *
 * $Id$
 */

// 项目标识
define ( "PROJECT_NAME", "qdds" );

// 项目类型
define ( "PROJECT_TYPE", "5" );//2扎呵呵平台5设备管理系统

//时间设置
@date_default_timezone_set('Asia/Shanghai');

//加载环境变量
$envConfig = new Yaf_Config_Ini(APPLICATION_PATH . '/conf/env.ini');
$env = isset($_SERVER['APP_ENV'])?$_SERVER['APP_ENV']:$envConfig->environment->curr_env;

//IP
$_ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
$_hostname = empty($_ip) ? php_uname('u') : '';

//HTTP_TYPE
$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';  

//域名设置
$http_url = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
defined('SITEURL') or define('SITEURL', 'http://'.$http_url.'/');
 
//定义JS版本号
defined('TT_JS_VER') or define('TT_JS_VER', '1');

//定义CSS版本号
defined('TT_CSS_VER') or define('TT_CSS_VER', '1');

//定义实时日志开关
defined('TT_REALTIME_LOG') or define('TT_REALTIME_LOG', true);

//设置接口输出格式
define('APP_API_OUT_TYPE', 'json');

//静态资源
define("RESOURCE_STATIC", APPLICATION_PATH."/../resource/static/");	
define("RESOURCE_FILE", APPLICATION_PATH."/../resource/upload/");

//强制推出KEY
define("ADMIN_FORCED_RETURN", "ADMIN_FORCED_RETURN");

//根据当前环境设置对应配置文件
if (file_exists(APPLICATION_PATH . '/conf/env/'.$env.'.inc.php')){
    require(APPLICATION_PATH . '/conf/env/'.$env.'.inc.php');
}else{
    require(APPLICATION_PATH . '/conf/env/'.$envConfig->environment->curr_env.'.inc.php');
}

//加载第三方配置
require(APPLICATION_PATH . '/conf/third/third.cfg.php');

//加载平台收款银行账号配置
require(APPLICATION_PATH . '/conf/account/bank.php');

return $__CFG__[__ENV__];

