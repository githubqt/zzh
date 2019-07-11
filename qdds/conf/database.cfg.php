<?php

/**
 * 配置文件－－数据库配置文件
 * 
 * @package main
 * @subpackage configure
 * @author 苏宁 <snsnsky@gmail.com>
 * 
 * $Id$
 */

// 加载全站基本配置
$base_cfg = require('base.inc.php');

// Memcache 默认通用
$cfg['memcache'] = [
    ['host' => $base_cfg['memcache']['default'][0]['host'] ,'port' => $base_cfg['memcache']['default'][0]['port']],
];

// Memcache 手机专用
$cfg['memcache_mobile'] =[
    ['host' => $base_cfg['memcache']['mobile'][0]['host'] ,'port' => $base_cfg['memcache']['mobile'][0]['port']],
];

// Queue
$cfg['httpsqs'] = $base_cfg['httpsqs'];

// MongoDb
//$cfg['mongo_mobile'] = $base_cfg['mongo']['mobile'];

// Redis
$cfg['redis'] = $base_cfg['redis'];
// $cfg['redis_cart'] = $base_cfg['redis']['cart'];
// $cfg['redis_notice'] = $base_cfg['redis']['notice'];
// $cfg['redis_feed'] = $base_cfg['redis']['feed'];
// $cfg['redis_mobile'] = $base_cfg['redis']['mobile'];
// $cfg['redis_other'] = $base_cfg['redis']['other'];

$_dbPrefix = [
    'LOCAL' => '_dev',
    'DEV' => '_dev',
    'TEST' => '_test',
    'PRIV' => '',
    'ONLINE' => '',
] [__ENV__];

// 后台管理 - 写
$cfg['db_w'] = array(
    'driver'=> 'mysql',
    'host'=> $base_cfg['mysql']['main']['host'],
    'name'=> 'zzh'.$_dbPrefix ,
    'user'=> $base_cfg['mysql']['main']['user'],
    'password'=> $base_cfg['mysql']['main']['password']
);

// 后台管理 - 读
$cfg['db_r'] = array(
    'driver'=> 'mysql',
    'host'=> $base_cfg['mysql']['main']['host'],
    'name'=> 'zzh'.$_dbPrefix ,
    'user'=> $base_cfg['mysql']['main']['user'],
    'password'=> $base_cfg['mysql']['main']['password']
);
