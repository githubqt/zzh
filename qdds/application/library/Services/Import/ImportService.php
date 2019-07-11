<?php
/**
 * 数据导入
 * @version v0.01
 * @author laiqingtao
 * @time 2018-11-15
 */
namespace Services\Import;

use Admin\AdminModel;
use Common\CommonBase;
use Core\Csv;
use Custom\YDLib;
use Services\BaseService;
use User\UserModel;
use AreaModel;
use User\UserSupplierModel;

class ImportService extends BaseService
{
    private $error;

    //会员导入任务
    const USER_IMPORT_TASK_MK = 'user_import_task_mk';

    const IMPORT_TASK_KEY = [
        'step',             //导入步骤:0准备导入1导入数据校验中2导入数据校验完毕3数据导入中4数据导入完毕
        'total',            //总计数量
        'valid_fail',       //校验失败数量
        'valid_success',    //校验成功数量
        'valid_progress',   //校验进度
        'fail',             //导入失败数量
        'success',          //导入成功数量
        'progress',         //导入进度
        'uploadPath',       //上传目录
        'valid_download',   //校验结果下载地址
        'fail_download'     //导入失败结果
    ];

    const USER_CSV_FIELD = ['mobile', 'name', 'sex', 'status', 'birthday', 'email', 'qq', 'wchat', 'check_info'];

    public function getError()
    {
        return $this->error;
    }

    /**
     * 下载模板
     * @param string $type
     * @param string $name
     * @return array
     */
    public function getImportExample($type,$name)
    {
        $file = realpath(RESOURCE_STATIC.'common/lib/model/' . $type . '.csv');
        $this->downloadFile($file,$name);
    }

    // 获取key
    public function getImportValidMark($mark)
    {
        $auth = AdminModel::getCurrentLoginInfo();
        return $mark . "-" . $auth['supplier_id'] . "-" . $auth['id'];
    }

    // 获取任务进度
    public function getImportState($mark)
    {
        try {
            $handleMark = $this->getImportValidMark($mark);
            $redis = YDLib::getRedis('redis','w');
            //$redis->delete($handleMark);
            $res = $redis->exists($handleMark);
            if ($res) {
                $importState = $redis->hmGet($handleMark,self::IMPORT_TASK_KEY);
                if ($importState['total'] == 0) {
                    $progress = [
                        'valid_progress_100'=>0,
                        'progress_100'=>0,
                    ];
                } else {
                    $progress = [
                        'valid_progress_100' => $importState['valid_progress'] ? bcmul(bcdiv($importState['valid_progress'], $importState['total'], 4) , 100,2) : 0,
                        'progress_100' => $importState['progress'] ? bcmul(bcdiv($importState['progress'], $importState['total'], 4) , 100,2) : 0,
                    ];
                }
                $importState = array_merge($importState,$progress);
            } else {
                $importState = [
                    'step'=>0,
                    'valid_progress_100'=>0,
                    'progress_100'=>0,
                ];//待上传
            }
            return $importState;
        } catch (\Exception $e) {
            return false;
        }
    }

    // 导入验证队列
    public function pushImportValidateTask($handleFunc, $file_url, $mark)
    {
        try {
            $uploadPath = RESOURCE_FILE.$file_url;
            $total = count(file($uploadPath));
            $total = $total>1?$total-1:0;
            if ($total > 500) {
                $this->error='一次最多上传500条';
                return false;
            }
            $handleMark = $this->getImportValidMark($mark);
            $redis = YDLib::getRedis('redis','w');
            $res = $redis->exists($handleMark);
            if ($res) {
                $redis->delete($handleMark);
            }
            $redis->hMset($handleMark, [
                'step'              =>  1,//导入步骤:校验中
                'total'             =>  $total,//总计数量
                'valid_fail'        =>  0,//校验失败数量
                'valid_success'     =>  0,//校验成功数量
                'valid_progress'    =>  0,//校验进度
                'fail'              =>  0,//导入失败数量
                'success'           =>  0,//导入成功数量
                'progress'          =>  0,//导入进度
                'uploadPath'        =>  $uploadPath,//上传目录
                'valid_download'    =>  '',//校验结果下载地址
                'fail_download'     =>  ''//导入失败结果
            ]);
            //启动校验任务
            $handleFunc = 'valid'.$handleFunc;
            $this->$handleFunc($handleMark);
        } catch (\Exception $e) {
            $this->error=$e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 会员校对数据
     * @param string $handleMark
     * @return array
     */
    public function validUser($handleMark)
    {

        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $redis = YDLib::getRedis('redis','w');
        $uploadPath = $redis->hGet($handleMark,'uploadPath');
        //上传的文件
        $fileHandle = fopen($uploadPath, 'r');

        //获取文件的编码方式
        $contents = file_get_contents($uploadPath);
        $encoding = mb_detect_encoding($contents, array('GB2312','GBK','UTF-16','UCS-2','UTF-8','BIG5','ASCII'));

        //生成的校验文件
        list($filename, $handle) = Csv::genCSVFile('user', date('dHis'));
        $redis->hSet($handleMark, 'valid_download', $filename);//校验结果下载地址
        //表头

        Csv::putDataCSV([
            '手机号（必填）',
            '姓名（必填）',
            '性别（必填）',
            '状态（必填）',
            '生日',
            '邮箱',
            'QQ',
            '微信',
            '校验结果'
        ], false, $handle);

        $i = 1;
        $lineArrSuccess = [];
        $lineArrFail = [];
        while ($row = fgetcsv($fileHandle)) {
            if ($i == 1) {
                $i++;
                continue;
            }
            try {
                $hasError = false;
                $lineArr = [];
                $lineArr[self::USER_CSV_FIELD[0]] = trim(iconv($encoding, 'UTF-8', $row[0]));
                $lineArr[self::USER_CSV_FIELD[1]] = trim(iconv($encoding, 'UTF-8', $row[1]));
                $lineArr[self::USER_CSV_FIELD[2]] = trim(iconv($encoding, 'UTF-8', $row[2]));//性别0保密1男2女'
                $lineArr[self::USER_CSV_FIELD[3]] = trim(iconv($encoding, 'UTF-8', $row[3]));//状态1禁用2启用
                $lineArr[self::USER_CSV_FIELD[4]] = trim(iconv($encoding, 'UTF-8', $row[4]));
                $lineArr[self::USER_CSV_FIELD[5]] = trim(iconv($encoding, 'UTF-8', $row[5]));
                $lineArr[self::USER_CSV_FIELD[6]] = trim(iconv($encoding, 'UTF-8', $row[6]));
                $lineArr[self::USER_CSV_FIELD[7]] = trim(iconv($encoding, 'UTF-8', $row[7]));
                $lineArr[self::USER_CSV_FIELD[8]] = '';
                if (!$lineArr['mobile']) {
                    $hasError = true;
                    $lineArr['check_info'] .= '/手机号必填';
                } else {
                    if (YDLib::validMobile($lineArr['mobile'])) {
                        $hasError = true;
                        $lineArr['check_info'] .= '/手机号格式错误';
                    } else {
                        //检测文件里边有没有重复的手机号
                        if (count($lineArrSuccess) > 0) {
                            $mobileList = array_column($lineArrSuccess,'mobile');
                            if (count($mobileList) > 0) {
                                if (in_array($lineArr['mobile'],$mobileList)) {
                                    $hasError = true;
                                    $lineArr['check_info'] .= '/导入手机号重复';
                                }
                            }
                        }
                        if ($hasError == false) {
                            $infoByMobile = UserModel::getInfoByWhere(['mobile'=>$lineArr['mobile']]);
                            if ($infoByMobile) {
                                $hasError = true;
                                $lineArr['check_info'] .= '/手机号已存在';
                            }
                        }
                    }
                }
                if (!$lineArr['name']) {
                    $hasError = true;
                    $lineArr['check_info'] .= '/姓名必填';
                }
                if (!$lineArr['sex']) {
                    $hasError = true;
                    $lineArr['check_info'] .= '/性别必填';
                } else {
                    if (!in_array($lineArr['sex'],['男','女'])) {
                        $hasError = true;
                        $lineArr['check_info'] .= '/性别需是男或女';
                    }
                }
                if (!$lineArr['status']) {
                    $hasError = true;
                    $lineArr['check_info'] .= '/状态必填';
                } else {
                    if (!in_array($lineArr['status'],['启用','禁用'])) {
                        $hasError = true;
                        $lineArr['check_info'] .= '/状态需是启用或禁用';
                    }
                }
                if ($lineArr['birthday'] && !strtotime($lineArr['birthday'])) {
                    $hasError = true;
                    $lineArr['check_info'] .= '/生日格式错误';
                }
                if ($lineArr['email'] && !YDLib::validMobile($lineArr['email'])) {
                    $hasError = true;
                    $lineArr['check_info'] .= '/邮箱格式错误';
                }
                if ($lineArr['qq'] && !is_numeric($lineArr['qq'])) {
                    $hasError = true;
                    $lineArr['check_info'] .= '/QQ格式错误';
                }
                $lineArr['check_info'] = ltrim($lineArr['check_info'],'/');
                if ($hasError) {
                    $redis->hIncrByFloat($handleMark, 'valid_fail', 1);
                    $lineArr['check_info'] = '校验失败：'.$lineArr['check_info'];
                    $lineArrFail[] = $lineArr;
                } else {
                    $redis->hIncrByFloat($handleMark, 'valid_success', 1);
                    $lineArr['check_info'] = '校验成功';
                    $lineArrSuccess[] = $lineArr;
                }
                $redis->hIncrByFloat($handleMark, 'valid_progress', 1);
                $importState = $redis->hmGet($handleMark,['total','valid_progress']);
                if ($importState['valid_progress'] == $importState['total']) {
                    $redis->hSet($handleMark,'step',2);//导入状态：校验完毕
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        //存储csv文件
        $lineArrAll = array_merge($lineArrFail,$lineArrSuccess);
        foreach ($lineArrAll as $value){
            Csv::putDataCSV(array_values($value), false, $handle);
        }
        return $filename;
    }

    /**
     * 会员校对数据
     * @param string $mark
     * @param integer $page
     * @param integer $rows
     * @return array
     */
    public function getValidList($mark, $page, $rows)
    {
        ignore_user_abort();// run script in background
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $handleMark = $this->getImportValidMark($mark);
        $redis = YDLib::getRedis('redis','w');
        $valid_download = $redis->hGet($handleMark,'valid_download');
        $total = $redis->hGet($handleMark,'total');

        //获取文件的编码方式
        $contents = file_get_contents($valid_download);
        $encoding = mb_detect_encoding($contents, array('GB2312','GBK','UTF-16','UCS-2','UTF-8','BIG5','ASCII'));

        $line_star = ($page-1)*$rows + 2;
        $line_end = $page*$rows + 2;
        if ($line_star > $total+1) {
            $line_star = $total+1;
        }
        if ($line_end > $total+1) {
            $line_end = $total+1;
        }
        $list['total'] = $total;
        $list['rows'] = [];
        $rows = Csv::getCSVLine($valid_download, $line_star, $line_end);
        foreach ($rows as $key => $value) {
            $row = [];
            $value = explode(',',$value);
            foreach ($value as $k => $v) {
                $v = iconv($encoding, 'UTF-8', $v);
                $v = trim($v);
                $v = trim($v,'"');
                $row[self::USER_CSV_FIELD[$k]] = $v;
            }
            $list['rows'][] = $row;
        }
        return $list;
    }

    /**
     * 下载校验结果
     * @param string $type
     * @param string $name
     * @return array
     */
    public function downloadValid($mark,$name)
    {
        $handleMark = $this->getImportValidMark($mark);
        $redis = YDLib::getRedis('redis','w');
        $valid_download = $redis->hGet($handleMark,'valid_download');
        $this->downloadFile($valid_download,$name);
    }

    /**
     * 下载导入失败结果
     * @param string $type
     * @param string $name
     * @return array
     */
    public function downloadImport($mark,$name)
    {
        $handleMark = $this->getImportValidMark($mark);
        $redis = YDLib::getRedis('redis','w');
        $fail_download = $redis->hGet($handleMark,'fail_download');
        $this->downloadFile($fail_download,$name);
    }

    public function downloadFile($file,$name)
    {
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Accept-Length: " . filesize($file));
        header("Content-Disposition:  attachment;  filename= " . $name . date('Ymd') . ".csv");
        readfile($file);
    }

    // 重新导入
    public function modify($mark)
    {
        $handleMark = $this->getImportValidMark($mark);
        $redis = YDLib::getRedis('redis','w');
        $res = $redis->exists($handleMark);
        if ($res) {
            $redis->delete($handleMark);
        }
    }

    // 导入会员数据
    public function import($handleFunc, $mark)
    {
        try {
            $handleMark = $this->getImportValidMark($mark);
            $redis = YDLib::getRedis('redis','w');
            $redis->hset($handleMark, 'step',3);//导入步骤:导入中
            //启动导入任务
            $handleFunc = 'import'.$handleFunc;
            $this->$handleFunc($handleMark);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 导入会员数据
     * @param string $handleMark
     * @return array
     */
    public function importUser($handleMark)
    {
        ignore_user_abort();// run script in background
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $redis = YDLib::getRedis('redis','w');
        $valid_download = $redis->hGet($handleMark,'valid_download');
        //校验的文件
        $fileHandle = fopen($valid_download, 'r');

        //获取文件的编码方式
        $contents = file_get_contents($valid_download);
        $encoding = mb_detect_encoding($contents, array('GB2312','GBK','UTF-16','UCS-2','UTF-8','BIG5','ASCII'));

        //生成导入失败的文件
        list($filename, $handle) = Csv::genCSVFile('user', date('dHis'));
        $redis->hSet($handleMark, 'fail_download', $filename);//导入失败下载地址
        //表头
        Csv::putDataCSV([
            '手机号（必填）',
            '姓名（必填）',
            '性别（必填）',
            '状态（必填）',
            '生日',
            '邮箱',
            'QQ',
            '微信',
            '校验结果',
            '导入结果',
        ], false, $handle);

        $i = 1;
        $lineArrSuccess = [];
        $lineArrFail = [];
        while ($row = fgetcsv($fileHandle)) {
            if ($i == 1) {
                $i++;
                continue;
            }
            try {
                $hasError = false;
                $lineArr = [];
                $lineArr[self::USER_CSV_FIELD[0]] = trim(iconv($encoding, 'UTF-8', $row[0]));
                $lineArr[self::USER_CSV_FIELD[1]] = trim(iconv($encoding, 'UTF-8', $row[1]));
                $lineArr[self::USER_CSV_FIELD[2]] = trim(iconv($encoding, 'UTF-8', $row[2]));//性别0保密1男2女'
                $lineArr[self::USER_CSV_FIELD[3]] = trim(iconv($encoding, 'UTF-8', $row[3]));//状态1禁用2启用
                $lineArr[self::USER_CSV_FIELD[4]] = trim(iconv($encoding, 'UTF-8', $row[4]));
                $lineArr[self::USER_CSV_FIELD[5]] = trim(iconv($encoding, 'UTF-8', $row[5]));
                $lineArr[self::USER_CSV_FIELD[6]] = trim(iconv($encoding, 'UTF-8', $row[6]));
                $lineArr[self::USER_CSV_FIELD[7]] = trim(iconv($encoding, 'UTF-8', $row[7]));
                $lineArr[self::USER_CSV_FIELD[8]] = trim(iconv($encoding, 'UTF-8', $row[8]));
                $lineArr['import_info'] = '';
                if ($lineArr['check_info'] != '校验成功') {
                    $hasError = true;
                    $lineArr['import_info'] .= '/校验失败';
                } else {
                    $res = $this->importUserOne($lineArr);
                    if (!$res) {
                        $hasError = true;
                        $lineArr['import_info'] .= '/'.$this->error;
                    }
                }

                $lineArr['import_info'] = ltrim($lineArr['import_info'],'/');
                if ($hasError) {
                    $redis->hIncrByFloat($handleMark, 'fail', 1);
                    $lineArr['import_info'] = '导入失败：'.$lineArr['import_info'];
                    $lineArrFail[] = $lineArr;
                } else {
                    $redis->hIncrByFloat($handleMark, 'success', 1);
                    $lineArr['import_info'] = '导入成功';
                    $lineArrSuccess[] = $lineArr;
                }
                $redis->hIncrByFloat($handleMark, 'progress', 1);
                $importState = $redis->hmGet($handleMark,['total','progress']);
                if ($importState['progress'] == $importState['total']) {
                    $redis->hSet($handleMark,'step',4);//导入状态：导入完毕
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        //存储csv文件
        if (count($lineArrFail)>0) {
            foreach ($lineArrFail as $value){
                Csv::putDataCSV(array_values($value), false, $handle);
            }
        }

        return $filename;
    }

    /**
     * 导入会员数据
     * @param array $array
     * @return boolean
     */
    public function importUserOne($lineArr)
    {
        try {
            $user = UserModel::getInfoByMobile($lineArr['mobile']);
            if (!$user) {
                $user = new UserModel();
                $user->mobile=$lineArr['mobile'];
                $user->name=$lineArr['name'];
                $user->sex=CommonBase::sexToNum($lineArr['sex']);
                $user->birthday=date('Y-m-d',strtotime($lineArr['birthday']));
                $user->email=$lineArr['email'];
                $user->qq=$lineArr['qq'];
                $user->wchat=$lineArr['wchat'];
                $user->is_del=CommonBase::DELETE_SUCCESS;
                $user->created_at=date('Y-m-d H:i:s');
                $user->save();
                $user_id = $user->id;
            } else {
                $user_id = $user['id'];
            }

            $auth = AdminModel::getCurrentLoginInfo();
            $userSupplier = new UserSupplierModel();
            $userSupplier->supplier_id=$auth['supplier_id'];

            $userSupplier->user_id=$user_id;
            $userSupplier->status=$lineArr['status']=='启用'?2:1;
            $userSupplier->is_del=CommonBase::DELETE_SUCCESS;
            $userSupplier->created_at=date('Y-m-d H:i:s');
            $userSupplier->save();

        } catch (\Exception $e){
            $this->error=$e->getMessage();
            return false;
        }

        return true;
    }

}

