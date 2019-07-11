<?php
define('DS', DIRECTORY_SEPARATOR);
define("APPLICATION_PATH",  dirname(dirname(__FILE__)));
header("Access-Control-Allow-Origin: *");

$app  = new Yaf_Application(APPLICATION_PATH . DS . "conf" . DS . "app.ini");
$app->bootstrap() //call bootstrap methods defined in Bootstrap.php
    ->run();
   
	 
