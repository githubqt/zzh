<?php
	/**
	 * upload.php
	 *
	 * Copyright 2013, Moxiecode Systems AB
	 * Released under GPL License.
	 *
	 * License: http://www.plupload.com/license
	 * Contributing: http://www.plupload.com/contributing
	 */
	include 'fileupload.class.php';
	
	define("UPLOAD_PATH",  dirname(__FILE__));
	$_maxsize = 2000000;
	$_allowtype = array("gif", "png", "jpg","jpeg");
	$_allowtypeFile = array('txt','doc','docx','gif', 'png', 'jpg','jpeg','xls','xlsx','ppt','pdf','csv');
    $_allowtype = array_merge($_allowtype,$_allowtypeFile);
	$_israndname = TRUE;
	#!! IMPORTANT:
	#!! this file is just an example, it doesn't incorporate any security checks and
	#!! is not recommended to be used in production environment as it is. Be sure to
	#!! revise it and customize to your needs.
	
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Method: GET,POST,OPTIONS");
	// Make sure file is not cached (as it happens for example on iOS devices)
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	$classify = $_GET['classify'];
	$jsonData = [];
	if (empty($classify)) {
		$jsonData['code'] = 500;
		$jsonData['msg'] = '请选择要上传位置!';
		echo apiOut($jsonData);
		exit;	
	}
	$path = $classify."/".date("Y/m/d")."/";

    //设置属性(上传的位置， 大小， 类型， 名是是否要随机生成)
    $upload = new FileUpload;
    $upload->set("path", UPLOAD_PATH."/upload/".$path);
    $upload->set("maxsize", $_maxsize);
    $upload->set("allowtype", $_allowtype);
    $upload->set("israndname", $_israndname);
  	
    /*使用对象中的upload方法， 就可以上传文件， 方法需要传一个上传表单的名子 pic, 如果成功返回true, 失败返回false*/
    if ($upload->upload("file")) {
        /*获取上传后文件名子*/
  	   $jsonData['code'] = 200;
	   $jsonData['msg'] = '上传成功';
	   $jsonData['data'] =  "/upload/".$path.$upload->getFileName();
	   echo apiOut($jsonData);
    } else {
    	
        $jsonData['code'] = 500;
		$jsonData['msg'] = $upload->getErrorMsg();
		echo apiOut($jsonData);
    }