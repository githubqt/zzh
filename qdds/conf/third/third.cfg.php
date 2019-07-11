<?php
/**
 * third 第三方配置
 */

//快递100
define('CUSTOMER_ID_100', '45FDE5C6A812BB6BB04031EAFEDD7371');
define('KEY_100', 'iHHaFfyr8697');

//小程序设置
define('MINI_WEIXIN_OPENID', 'wx942b3cc0357fd683');
define('MINI_WEIXIN_APPSECRET', '69aab066830b91212df6132324105e99');

//微信支付
defined('WEIXIN_MCHID') or define('WEIXIN_MCHID', '1502230391');
defined('WEIXIN_TOKEN') or define('WEIXIN_TOKEN', 'zhahehe');
defined('WEIXIN_KEY') or define('WEIXIN_KEY', '314e8df7ce48f3e8aecd32e0397fe232');
defined('WEIXIN_AES') or define('WEIXIN_AES', 'LAWBiEY25FPKy1iEa3WVtj1oLOk3UNWsfnsvp8OsKT9');

//微信通用支付
defined('WEIXIN_PAY_APPID') or define('WEIXIN_PAY_APPID', 'wxcbd431186cb7addd');
defined('WEIXIN_PAY_APPSECRET') or define('WEIXIN_PAY_APPSECRET', '314e8df7ce48f3e8aecd32e0397fe231');

//小程序微信支付
defined('MINI_WEIXIN_APPID') or define('MINI_WEIXIN_APPID', 'wx942b3cc0357fd683');
defined('MINI_WEIXIN_APPSECRET') or define('MINI_WEIXIN_APPSECRET', '69aab066830b91212df6132324105e99');

/* //第三方平台认证（正式）
//defined('WEIXIN_OPEN_APPID') or define('WEIXIN_OPEN_APPID', 'wxdef36dbb3b1dbb76');
//defined('WEIXIN_OPEN_TOKEN') or define('WEIXIN_OPEN_TOKEN', 'zhahehe');
//defined('WEIXIN_OPEN_KEY') or define('WEIXIN_OPEN_KEY', 'LAWBiEY25FPKy1iEa3WVtj1oLOk3UNWsfnsvp8OsKT9');
//defined('WEIXIN_OPEN_APPSECRET') or define('WEIXIN_OPEN_APPSECRET', 'd51be32696ba4ce5bfa875b8ab8e3dcf');
//第三方平台认证（测试）
defined('WEIXIN_OPEN_APPID') or define('WEIXIN_OPEN_APPID', 'wx61f7cb4853bdaf98');
defined('WEIXIN_OPEN_TOKEN') or define('WEIXIN_OPEN_TOKEN', 'zhahehe');
defined('WEIXIN_OPEN_KEY') or define('WEIXIN_OPEN_KEY', 'LAWBiEY25FPKy1iEa3WVtj1oLOk3UNWsfnsvp8OsKT9');
defined('WEIXIN_OPEN_APPSECRET') or define('WEIXIN_OPEN_APPSECRET', '8e3de75af0e1c8adcc83e1ac5a2295ef'); */

//支付宝
defined('ALIPAY_APPID') or define('ALIPAY_APPID', '2088031898979462');
defined('ALIPAY_MCHID') or define('ALIPAY_MCHID', '892656703@qq.com');
defined('ALIPAY_KEY') or define('ALIPAY_KEY', 'gnox18jfum8pv8ibzar31sro4s4yhpp5');
defined('ALIPAY_SSLKEY_PATH') or define('ALIPAY_SSLKEY_PATH', APPLICATION_PATH."/application/library/Pay/Alipay/Alipay/rsa_private_key.pem");
defined('ALIPAY_SSLCERT_PATH') or define('ALIPAY_SSLCERT_PATH',APPLICATION_PATH."/application/library/Pay/Alipay/Alipay/rsa_public_key.pem");

defined('ALIPAY_OPENID') or define('ALIPAY_OPENID', '2018051060127325');
defined('ALIPAY_OPENKEY_PATH') or define('ALIPAY_OPENKEY_PATH', APPLICATION_PATH."/application/library/Pay/Alipay/Aop/rsa_private_key.pem");
defined('ALIPAY_OPENERT_PATH') or define('ALIPAY_OPENERT_PATH',APPLICATION_PATH."/application/library/Pay/Alipay/Aop/rsa_public_key.pem");

//上海金价
define('GOLDPRICE_APPKEY', '10ae0159c2ade7712505b293e1f2325f');