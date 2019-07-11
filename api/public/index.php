<?php
define ( 'DS', DIRECTORY_SEPARATOR );
define ( "APPLICATION_PATH", dirname ( dirname ( __FILE__ ) ) );

// 开启session
@session_start ();
$app = new Yaf_Application ( APPLICATION_PATH . DS . "conf" . DS . "app.ini" );
$app->bootstrap ()-> // call bootstrap methods defined in Bootstrap.php
run ();