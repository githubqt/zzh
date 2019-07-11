<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/11/28 Time:19:16
// +----------------------------------------------------------------------


class ChunkUpload
{
    private $filepath; //绝对文件目录
    private $uploadName = ''; //上传文件名
    private $basePath; //相对目录
    private $tmpPath; //PHP文件临时目录
    private $fileName; //文件名
    private $error = '';
    private $startOffset = 0;
    private $input = [];
    private $chunkSize = 524288; //分块大小
    private $blobNum = 1;

    //缓存
    private $cache = [];

    public function __construct()
    {
        $this->input();
    }

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * 设置切片位置
     * @param $offset
     */
    public function setStartOffset($offset)
    {
        $this->startOffset = (int)$offset;
    }

    /**
     * 设置切片块数
     * @param $num
     */
    public function setBlobNum($num)
    {
        $this->blobNum = (int)$num;
    }

    /**
     * 设置基础目录
     * @param $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * 设置文件名称
     * @param $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }


    /**
     * 上传参数
     * @return array|mixed
     */
    private function input()
    {
        $input = file_get_contents("php://input");
        if ($input) {
            $this->input = json_decode($input, true);
            return $this->input;
        }

        $post = [];
        if (isset($_POST['phase']) && $_POST['phase']) {
            $post['phase'] = $_POST['phase'];
        }
        if (isset($_POST['session_id']) && $_POST['session_id']) {
            $post['session_id'] = $_POST['session_id'];
        }
        if (isset($_POST['start_offset']) && $_POST['start_offset']) {
            $post['start_offset'] = $_POST['start_offset'];
        }

        $this->input = $post;
        if(!empty($_FILES)){
            $this->input['chunk'] =  $_FILES['chunk'];
        }

        return $this->input;
    }

    /**
     * 开始上传
     */
    public function start()
    {
        $session_id = $this->generateId();
        $data = [];
        $data['data'] = [
            'session_id' => $session_id,
            'end_offset' => $this->chunkSize,
        ];
        $data['status'] = 'success';
        return $data;
    }

    /**
     * 上传
     * @return bool
     */
    public function upload()
    {
        try {
            if (!$this->mkdir($this->tmpPath)) {
                throw  new Exception("创建目录失败");
            }
            //上传分片编号
            $num = ceil(($this->input['start_offset'] + $this->chunkSize) / $this->chunkSize);
            if (!@move_uploaded_file($this->input['chunk']['tmp_name'], "{$this->tmpPath}/_part_{$num}")) {
                throw  new Exception("移动文件出错");
            }
            $this->blobNum = $num;
            return true;
        } catch (Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * 上传完成
     * @return bool|string
     */
    public function finish()
    {
        $flag = true;
        $blob = '';
        for ($i = 1; $i <= $this->blobNum; $i++) {
            $blob .= file_get_contents("{$this->tmpPath}/_part_{$i}");
        }
        $name = $this->getRandName();
        if ($this->mkdir($this->filepath)) {
            $rs = file_put_contents($this->filepath . $name, $blob);
            if (!$rs) {
                $this->error = '文件写入失败';
                $flag = false;
            }
        } else {
            $this->error = '创建目录失败';
            $flag = false;
        }

        $this->uploadName = $this->basePath . $name;

        if ($flag) {
            $this->clear();
        }
        return $flag;
    }

    /**
     * 清空临时文件
     */
    public function clear()
    {
        for ($i = 1; $i <= $this->blobNum; $i++) {
            $file = "{$this->tmpPath}/_part_{$i}";
            if (file_exists($file)) {
                @unlink($file);
                @rmdir($this->tmpPath);
            }
        }
    }

    /**
     * 设置上传保存临时文件目录
     * @param $dir
     */
    public function setTmpDir($dir)
    {
        $this->tmpPath = $dir;
    }

    /*
     * 设置上传保存文件目录
     */
    public function setFilePath($dir)
    {
        $this->filepath = $dir;
    }

    /**
     * 生成分片ID标识
     * @return string
     */
    public function generateId()
    {
        return md5(json_encode($this->input));
    }

    /**
     * 创建目录
     * @param $dir
     * @return bool
     */
    public function mkdir($dir)
    {
        if (!file_exists($dir)) {
            return @mkdir($dir, 0777, true);
        }
        return true;
    }

    /**
     * 获取上传参数
     * @return array
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * 获取扩展
     * @return string
     */
    function getExt()
    {
        $tmp = explode('.', $this->fileName);
        return strtolower($tmp[count($tmp) - 1]);
    }

    /**
     * 获取md5 名称
     * @return string
     */
    function getRandName()
    {
        $fileName = md5($this->input['session_id']) . "_" . rand(100, 999);
        return $fileName . '.' . $this->getExt();
    }

    /**
     * 错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 获取上传目录
     * @return string
     */
    public function getUploadName()
    {
        return $this->uploadName;
    }

    /**
     *
     * 获取分片数
     * @return int
     */
    public function getBlobNum(){
        return $this->blobNum;
    }

}