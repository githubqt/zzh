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
include 'ChunkUpload.php';
error_reporting(0);
define("UPLOAD_PATH", dirname(__FILE__));
$_maxsize = 20971520;
$_allowtype = array("gif", "png", "jpg", "jpeg");
$_allowtypeFile = array('txt', 'doc', 'docx', "gif", "png", "jpg", "jpeg", "xls", 'xlsx', 'ppt', 'pdf', 'csv');
$_israndname = TRUE;
$_CLASSIFY = array(1 => 'head', 2 => 'tosu', 3 => 'diandang', 4 => 'other', 5 => 'recycling');
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
header("Content-Type:application/json;charset=utf-8");
$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

$type = $_REQUEST['filetype'] ? $_REQUEST['filetype'] : 4;

$classify = isset($_CLASSIFY[$type]) ? $_CLASSIFY[$type] : "";
$path = $classify . "/" . date("Y/m/d") . "/";
$basePath = "/upload/" . $path;
$path = UPLOAD_PATH . $basePath;
// 分片临时文件目录
$tmp_path = UPLOAD_PATH . '/tmp/';

session_start();
$chunk = new  ChunkUpload();
$input = $chunk->getInput();
$chunk->setTmpDir($tmp_path . "{$input['session_id']}");
try {
    switch ($input['phase']) {
        case "start":
            $ext = getExt($input['name']);
            if (!in_array($ext, $_allowtypeFile)) {
                throw  new  Exception("禁止上传{$ext}类型文件");
            }
            //上传开始处理
            $result = $chunk->start();
            $session_id = $result['data']['session_id'];
            //记录开始上传文件信息 到 session中
            $_SESSION[$session_id] = [
                'session_id' => $session_id,
                'file' => $input,
                'chunk_num' => 0, //
            ];
            break;
        case "upload":
            $session = $_SESSION[$input['session_id']];
            $filetype = $session['file']['filetype'];
            if (isset($_CLASSIFY[$filetype])) {
                $path = str_replace('/other/', "/{$_CLASSIFY[$filetype]}/", $path);
                $basePath = str_replace('/other/', "/{$_CLASSIFY[$filetype]}/", $basePath);
            }
            $chunk->setFilePath($path);
            $chunk->setBasePath($basePath);
            if (!$chunk->upload()) {
                throw new Exception($chunk->getError());
            }
            //记录分片数
            $_SESSION[$input['session_id']]['chunk_num'] = $chunk->getBlobNum();
            $result['status'] = 'success';
            break;
        case "finish":
            //读取缓存的文件信息
            $session = $_SESSION[$input['session_id']];
            $filetype = $session['file']['filetype'];
            if (isset($_CLASSIFY[$filetype])) {
                $path = str_replace('/other/', "/{$_CLASSIFY[$filetype]}/", $path);
                $basePath = str_replace('/other/', "/{$_CLASSIFY[$filetype]}/", $basePath);
            }
            $chunk->setFilePath($path);
            $chunk->setBasePath($basePath);
            $chunk->setFileName($session['file']['name']);
            $chunk->setBlobNum($session['chunk_num']);
            //合成文件
            if (!$chunk->finish()) {
                throw new Exception($chunk->getError());
            }

            $result['data'] = [
                'auth_url' => $http_type . $_SERVER['HTTP_HOST'] . $chunk->getUploadName(),
                'url' => $chunk->getUploadName(),
            ];
            $result['status'] = 'success';
            break;
        default:
            throw new Exception('参数错误');
            break;
    }

    echo json_encode(array_merge((array)$result, [
        "errno" => 0,
        "errmsg" => '',
    ]));
    die();

} catch (Exception $exception) {
    echo json_encode([
        "data" => [],
        "errno" => -1,
        "errmsg" => $exception->getMessage(),
        "status" => 'error'
    ]);
    die();
}


/**
 * 识别文件扩展名
 * @param $file_name
 * @return mixed
 */
function getExt($file_name)
{
    $t = explode('.', $file_name);
    return $t[count($t) - 1];
}







