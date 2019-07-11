<?php
/**
 * test 测试环境
 */

error_reporting(E_ALL ^ E_NOTICE);
defined('__ENV__') or define('__ENV__', 'TEST');
defined('__KEY__') or define('__KEY__', '6b9c8a5d1bbbefea414ad6b32dd89a67');
defined('__IP__') or define('__IP__', '47.104.212.73');
defined('__REDIS_AUTH__') or define('__REDIS_AUTH__', 'zhahehe0522');
defined('__MEMCACHE_KEY__') or define('__MEMCACHE_KEY__', 'TEST_');
define("HOST_STATIC", $http_type."teststatic.zhahehe.com/");
define("HOST_FILE", $http_type."testfile.zhahehe.com/");
define("M_URL", $http_type."%s.testm.zhahehe.com/");	
define("API_URL", $http_type."testapi.zhahehe.com/");	
define("SP_URL", $http_type."testsp.zhahehe.com/");
define ( "COOKIE_DOMAIN" , "zhahehe.com" );
define ( "SHOM_URL_MINI", 'https://shopm.zhahehe.com/' );
define("JD_URL", $http_type."testjd.zhahehe.com");

defined('WEIXIN_APPID') or define('WEIXIN_APPID', 'wx0109b8c7c419b2d2');
defined('WEIXIN_APPSECRET') or define('WEIXIN_APPSECRET', '5c57b9c38570baa14229b7bb29a12300');

defined('MINI_APPID') or define('MINI_APPID', 'wx942b3cc0357fd683');

//第三方平台认证（测试）
defined('WEIXIN_OPEN_APPID') or define('WEIXIN_OPEN_APPID', 'wx61f7cb4853bdaf98');
defined('WEIXIN_OPEN_TOKEN') or define('WEIXIN_OPEN_TOKEN', 'zhahehe');
defined('WEIXIN_OPEN_KEY') or define('WEIXIN_OPEN_KEY', 'LAWBiEY25FPKy1iEa3WVtj1oLOk3UNWsfnsvp8OsKT9');
defined('WEIXIN_OPEN_APPSECRET') or define('WEIXIN_OPEN_APPSECRET', '8e3de75af0e1c8adcc83e1ac5a2295ef');

$__CFG__ = [
    __ENV__ => [
        'mysql' => [
            'main' => ['host'=> __IP__, 'user'=> 'qdd_dev', 'password'=> '6cqWn7Z^Yk6@TcXD'],
        ],
        'mysql_r' => [
            'main' => ['host'=> __IP__, 'user'=> 'qdd_dev', 'password'=> '6cqWn7Z^Yk6@TcXD'],
        ],
        'memcache' => [
            'default' => [
                ['host' => __IP__, 'port' => 11211],
                ['host' => __IP__, 'port' => 11212],
                ['host' => __IP__, 'port' => 11213],
            ],
            'mobile' => [
                ['host' => __IP__, 'port' => 11212],
                ['host' => __IP__, 'port' => 11213],
                ['host' => __IP__, 'port' => 11214],
            ]
        ],
        'redis' => [
            'w'   => [
            		'host' => __IP__, 
            		'port' => 6379,
            		'auth' => __REDIS_AUTH__
            		],
            'r'   =>  [
            		'host' => __IP__, 
            		'port' => 6379,
            		'auth' => __REDIS_AUTH__
            		],
        ],
        'httpsqs' => [
            'default' => ['host' => __IP__, 'port' => 1218],
        ],
//        'mongo' => [
//            'mobile' => ['host' => __IP__, 'port' => 27017]
//        ]
		 'swoole' => [
           'default' => ['host' => __IP__, 'port' => 9503]
       	 ]
    ]
];