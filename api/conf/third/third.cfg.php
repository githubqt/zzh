<?php
/**
 * third 第三方配置
 */

// 快递100
define ( 'CUSTOMER_ID_100', '45FDE5C6A812BB6BB04031EAFEDD7371' );
define ( 'KEY_100', 'iHHaFfyr8697' );

//创蓝短信
define ( 'CHUANGLAN_API_ACCOUNT', 'N7477060' ); // 创蓝API账号
define ( 'CHUANGLAN_API_PASSWORD', '5Kp1Un1Ai' ); // 创蓝API密码

// 小程序微信支付
defined ( 'MINI_WEIXIN_APPID' ) or define ( 'MINI_WEIXIN_APPID', 'wx942b3cc0357fd683' );
defined ( 'MINI_WEIXIN_APPSECRET' ) or define ( 'MINI_WEIXIN_APPSECRET', '69aab066830b91212df6132324105e99' );

// 支付宝
defined ( 'ALIPAY_APPID' ) or define ( 'ALIPAY_APPID', '2088031898979462' );
defined ( 'ALIPAY_MCHID' ) or define ( 'ALIPAY_MCHID', '892656703@qq.com' );
defined ( 'ALIPAY_KEY' ) or define ( 'ALIPAY_KEY', 'gnox18jfum8pv8ibzar31sro4s4yhpp5' );
defined ( 'ALIPAY_SSLKEY_PATH' ) or define ( 'ALIPAY_SSLKEY_PATH', APPLICATION_PATH . "/application/library/Pay/Alipay/Alipay/rsa_private_key.pem" );
defined ( 'ALIPAY_SSLCERT_PATH' ) or define ( 'ALIPAY_SSLCERT_PATH', APPLICATION_PATH . "/application/library/Pay/Alipay/Alipay/rsa_public_key.pem" );

defined ( 'ALIPAY_OPENID' ) or define ( 'ALIPAY_OPENID', '2018051060127325' );
defined ( 'ALIPAY_OPENKEY_PATH' ) or define ( 'ALIPAY_OPENKEY_PATH', APPLICATION_PATH . "/application/library/Pay/Alipay/Aop/rsa_private_key.pem" );
defined ( 'ALIPAY_OPENERT_PATH' ) or define ( 'ALIPAY_OPENERT_PATH', APPLICATION_PATH . "/application/library/Pay/Alipay/Aop/rsa_public_key.pem" );

// 微信退款
defined ( 'WEIXIN_SSLCERT_PATH' ) or define ( 'WEIXIN_SSLCERT_PATH', APPLICATION_PATH . "/application/library/Pay/Weixin/WxPay/cert/apiclient_cert.pem" );
defined ( 'WEIXIN_SSLKEY_PATH' ) or define ( 'WEIXIN_SSLKEY_PATH', APPLICATION_PATH . "/application/library/Pay/Weixin/WxPay/cert/apiclient_key.pem" );


//上海金价
define('GOLDPRICE_APPKEY', '10ae0159c2ade7712505b293e1f2325f');
