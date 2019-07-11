<?php
/**
 * CDN配置
 */

// @todo refactor
$__publicPath = dirname ( APPLICATION_PATH );

if (defined ( '__ENV__' ) && __ENV__ == 'DEV') {
	defined ( 'CDN_URL' ) or define ( 'CDN_URL', 'http://' . str_replace ( 'api.', '', $_SERVER ['HTTP_HOST'] ) . '/public/upload/' );
	defined ( 'CDN_PATH' ) or define ( 'CDN_PATH', $__publicPath . '/tetedev/public/upload/' );
	
	defined ( 'CDN_URL_TMP' ) or define ( 'CDN_URL_TMP', 'http://' . str_replace ( 'api.', '', $_SERVER ['HTTP_HOST'] ) . '/public/upload/tmp/' );
	defined ( 'CDN_PATH_TMP' ) or define ( 'CDN_PATH_TMP', $__publicPath . '/tetedev/public/upload/tmp/' );
} else if (defined ( '__ENV__' ) && __ENV__ == 'TEST') {
	defined ( 'CDN_URL' ) or define ( 'CDN_URL', 'http://' . str_replace ( 'api.', '', $_SERVER ['HTTP_HOST'] ) . '/public/upload/' );
	defined ( 'CDN_PATH' ) or define ( 'CDN_PATH', $__publicPath . '/tetetest/public/upload/' );
	
	defined ( 'CDN_URL_TMP' ) or define ( 'CDN_URL_TMP', 'http://' . str_replace ( 'api.', '', $_SERVER ['HTTP_HOST'] ) . '/public/upload/tmp/' );
	defined ( 'CDN_PATH_TMP' ) or define ( 'CDN_PATH_TMP', $__publicPath . '/tetetest/public/upload/tmp/' );
} else {
	// defined('CDN_URL') or define('CDN_URL', 'http://img'.rand(1, 2).'.tete.hoto.cn/');
	defined ( 'CDN_URL' ) or define ( 'CDN_URL', 'http://img1.tete.hoto.cn/' );
	defined ( 'CDN_PATH' ) or define ( 'CDN_PATH', '/data/mfsmnt/img_tete/' );
	
	// defined('CDN_URL_TMP') or define('CDN_URL_TMP', 'http://img'.rand(1, 2).'.tete.hoto.cn/tmp/');
	defined ( 'CDN_URL_TMP' ) or define ( 'CDN_URL_TMP', 'http://img1.tete.hoto.cn/tmp/' );
	defined ( 'CDN_PATH_TMP' ) or define ( 'CDN_PATH_TMP', '/data/mfsmnt/img_tete/tmp/' );
}
