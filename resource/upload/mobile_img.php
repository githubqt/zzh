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
	ini_set('post_max_size','20M');
	ini_set('upload_max_filesize','20M');
	error_reporting(0);
	define("UPLOAD_PATH",  dirname(__FILE__));
	$_maxsize = 20971520;
	$_allowtype = array("gif", "png", "jpg","jpeg");
	$_allowtypeFile = array('txt','doc','docx',"gif", "png", "jpg","jpeg","xls",'xlsx','ppt','pdf','csv');
	$_israndname = TRUE;
	$_CLASSIFY = array(1=>'head',2=>'tosu',3=>'diandang',4=>'other',5=>'recycling');
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
	$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'; 
	
	$type = $_REQUEST['filetype'] ?$_REQUEST['filetype']:4;
	if (empty($type)) {
		$jsonData = [];
        $jsonData['errno'] = 40001;
		$jsonData['errmsg'] = 'filetype参数不能为空!';
		echo apiOut($jsonData);
		exit;	
    }
	
	$classify = isset($_CLASSIFY[$type]) ? $_CLASSIFY[$type] : "";
	$jsonData = [];
	if (empty($classify)) {
		$jsonData['errno'] = 50001;
		$jsonData['errmsg'] = '请选择要上传位置!';
		echo apiOut($jsonData);
		exit;	
	}
	$path = $classify."/".date("Y/m/d")."/";
	$basePath = "/upload/".$path;
	$path = UPLOAD_PATH.$basePath;
	
	if (empty($_REQUEST['base'])) {
		 //设置属性(上传的位置， 大小， 类型， 名是是否要随机生成)
	    $upload = new FileUpload;
	    $upload->set("path", $path);
	    $upload->set("maxsize", $_maxsize);
	    $upload->set("allowtype", $_allowtype);
	    $upload->set("israndname", $_israndname);
	  	
	    /*使用对象中的upload方法， 就可以上传文件， 方法需要传一个上传表单的名子 pic, 如果成功返回true, 失败返回false*/
	    if ($upload->upload("file")) {
	        /*获取上传后文件名子*/
	  	   $jsonData['errno'] = 0;
		   $jsonData['errmsg'] = '上传成功';
		   $jsonData['data']['auth_url'] =  $http_type.$_SERVER['HTTP_HOST'].$basePath.$upload->getFileName();
		   $jsonData['data']['url'] =   $basePath.$upload->getFileName();
		   echo apiOut($jsonData);
	    } else {
	    	
	        $jsonData['errno'] = 50002;
			$jsonData['errmsg'] = $upload->getErrorMsg();
			echo apiOut($jsonData);
	    }
	} else {
		$file =  base64_image_content($_POST['file'],$path,$basePath);
		if ($file != FALSE) {
			 /*获取上传后文件名子*/
	  	   $jsonData['errno'] = 0;
		   $jsonData['errmsg'] = '上传成功';
		   $jsonData['data']['auth_url'] =   $http_type.$_SERVER['HTTP_HOST'].$file;
		   $jsonData['data']['url'] =   $file;
		   echo apiOut($jsonData);
		} else {
			$jsonData['errno'] = 50002;
			$jsonData['errmsg'] = '图片上传失败';
			echo apiOut($jsonData);
		}
	}

   
   
   /**
 * [将Base64图片转换为本地图片并保存]
 * @E-mial wuliqiang_aa@163.com
 * @TIME   2017-04-07
 * @WEB    http://blog.iinu.com.cn
 * @param  [Base64] $base64_image_content [要保存的Base64]
 * @param  [目录] $path [要保存的路径]
 */
function base64_image_content($base64_image_content,$path,$basePath)
{
    //匹配出图片的格式
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
        $type = $result[2];

        if(!file_exists($path)){
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($path, 0700,TRUE);
        }
        $new_file = $path.md5($base64_image_content).".{$type}";
        $web_file = $basePath.md5($base64_image_content).".{$type}";
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
            return $web_file;
        }else{
            return FALSE;
        }
    }else{
        return FALSE;
    }
}