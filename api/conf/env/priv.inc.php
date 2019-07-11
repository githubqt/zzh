<?php
/**
 * priv 预发布环境
 */
error_reporting ( E_ALL ^ E_NOTICE );
defined ( '__ENV__' ) or define ( '__ENV__', 'PRIV' );
defined ( '__KEY__' ) or define ( '__KEY__', '6b9c8a5d1bbbefea414ad6b32dd89a67' );
defined ( '__IP__' ) or define ( '__IP__', '47.95.255.57' );
defined ( '__REDIS_AUTH__' ) or define ( '__REDIS_AUTH__', 'zhahehe0522' );
defined ( '__MEMCACHE_KEY__' ) or define ( '__MEMCACHE_KEY__', 'PRIV_' );
define ( "HOST_STATIC", $http_type . "static.zhahehe.com/" );
define ( "HOST_FILE", $http_type . "file.zhahehe.com/" );
define ( 'HOST_API', $http_type . 'api.zhahehe.com/' );
define ( "SHOM_URL", $http_type . 'testm.zhahehe.com/' );
define ( "M_URL", $http_type . $identif . ".testm.zhahehe.com/" );
define ( "SHOM_URL_MINI", 'https://shopm.zhahehe.com/' );
define ( "SHOM_URL_HTTP", 'http://shopm.zhahehe.com/' );
define ( "M_URL_HTTP", "http://" . $identif . ".testm.zhahehe.com/" );
define ( "COOKIE_DOMAIN" , "zhahehe.com" );
define("JD_URL", $http_type."jd.zhahehe.com");

//微信授权
defined ( 'WEIXIN_APPID' ) or define ( 'WEIXIN_APPID', 'wxcbd431186cb7addd' );
defined ( 'WEIXIN_APPSECRET' ) or define ( 'WEIXIN_APPSECRET', '314e8df7ce48f3e8aecd32e0397fe231' );

//微信支付
defined ( 'WEIXIN_MCHID' ) or define ( 'WEIXIN_MCHID', '1502230391' );
defined ( 'WEIXIN_TOKEN' ) or define ( 'WEIXIN_TOKEN', 'zhahehe' );
defined ( 'WEIXIN_KEY' ) or define ( 'WEIXIN_KEY', '314e8df7ce48f3e8aecd32e0397fe232' );
defined ( 'WEIXIN_AES' ) or define ( 'WEIXIN_AES', 'LAWBiEY25FPKy1iEa3WVtj1oLOk3UNWsfnsvp8OsKT9' );

// 第三方平台认证（正式）
defined ( 'WEIXIN_OPEN_APPID' ) or define ( 'WEIXIN_OPEN_APPID', 'wxdef36dbb3b1dbb76' );
defined ( 'WEIXIN_OPEN_TOKEN' ) or define ( 'WEIXIN_OPEN_TOKEN', 'zhahehe' );
defined ( 'WEIXIN_OPEN_KEY' ) or define ( 'WEIXIN_OPEN_KEY', 'LAWBiEY25FPKy1iEa3WVtj1oLOk3UNWsfnsvp8OsKT9' );
defined ( 'WEIXIN_OPEN_APPSECRET' ) or define ( 'WEIXIN_OPEN_APPSECRET', 'd51be32696ba4ce5bfa875b8ab8e3dcf' );

//小程序
defined('MINI_APPID') or define('MINI_APPID', 'wx942b3cc0357fd683');

$__CFG__ = [ 
		__ENV__ => [
            'mysql' => [
                'main' => ['host'=> __IP__, 'user'=> 'adduser', 'password'=> 'Zyytest12!'],
            ],
            'mysql_r' => [
                'main' => ['host'=> __IP__, 'user'=> 'adduser', 'password'=> 'Zyytest12!'],
            ],
				'memcache' => [ 
						'default' => [ 
								[ 
										'host' => __IP__,
										'port' => 11211 
								] 
						],
						'mobile' => [ 
								[ 
										'host' => __IP__,
										'port' => 11212 
								] 
						] 
				],
				'redis' => [ 
						'w' => [ 
								'host' => __IP__,
								'port' => 6379,
								'auth' => __REDIS_AUTH__ 
						],
						'r' => [ 
								'host' => __IP__,
								'port' => 6379,
								'auth' => __REDIS_AUTH__ 
						] 
				],
				'httpsqs' => [ 
						'default' => [ 
								'host' => __IP__,
								'port' => 1218 
						] 
				],
				// 'mongo' => [
				// 'mobile' => ['host' => __IP__, 'port' => 27017]
				// ]
				'swoole' => [ 
						'default' => [ 
								'host' => __IP__,
								'port' => 9503 
						] 
				] ,
                'elasticsearch' => [
                    'http://47.92.215.79:9200'
                ]
		] 
];	