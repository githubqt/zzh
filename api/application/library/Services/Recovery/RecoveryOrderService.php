<?php
// +----------------------------------------------------------------------
// | 回收我要卖
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/11/13 Time:16:35
// +----------------------------------------------------------------------


namespace Services\Recovery;


use Brand\BrandModel;
use Category\CategoryModel;
use Common\CommonBase;
use Custom\YDLib;
use Image\ImageModel;
use Recovery\RecoveryModel;
use Supplier\AdminModel;

class RecoveryOrderService extends RecoveryService
{
    protected $error = \ErrnoStatus::STATUS_PARAMS_ERROR;

    protected $user = [];

    protected $params = [];

    protected $default_value = [
        'recovery_num' => 1,
    ];

    protected $config = [];

    public function __construct()
    {
        $this->initRecoveryConfig();
    }

    public function setParam(array $params)
    {
        $this->params = $params;
    }

    public function getParam()
    {
        return $this->params;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getError()
    {
        return $this->error;
    }

    public function create()
    {
        self::newWrite()->beginTransaction();
        try {

            $recovery = $this->createRecovery();
            if (!$recovery) {
                throw new \Exception('创建回收单失败');
            }

            $img = $this->createRecoveryImg($recovery);
            if (!$img) {
                throw new \Exception('保存回收商品图片失败');
            }
            self::newWrite()->commit();
            return true;
        } catch (\Exception $exception) {
            self::newWrite()->rollback();
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * 生存回收商品名称
     * @return string
     */
    protected function generateProductName()
    {
        $brand = BrandModel::getInfoByID($this->params['brand_id']);
        $category = CategoryModel::getInfoByID($this->params['category_id']);
        return "{$brand['name']}-{$category['name']}";
    }

    /**
     * 创建回收单
     * @return bool|RecoveryModel
     */
    protected function createRecovery()
    {
        if ($this->params['id']){
            //编辑
            $recovery = RecoveryModel::find($this->params['id']);

        }else{
            //新建
            $recovery = new RecoveryModel();
        }

        //提交参数
        $recovery->product_name = $this->generateProductName();

        //记录
        //编辑字段
        if ($this->params['id']){
            $record = $recovery->option_record ."</br>".date('Y-m-d H:i:s') . " {$this->user['name']} 重新发布了该商品：{$recovery->product_name} </br>";
            $recovery->option_record = $record;  //附件

            //清空失败原因
            $recovery->fail_reason = '';
            //清空审核通过时间
            $recovery->examine_time = NULL;
        }else{
            $record = date('Y-m-d H:i:s') . " {$this->user['name']} 发布了回收商品：{$recovery->product_name} </br>";
            $recovery->option_record = $record;  //附件
        }

        $recovery->brand_id = $this->params['brand_id'];
        $recovery->category_id = $this->params['category_id'];
        $recovery->recovery_material = $this->params['material'];
        $recovery->recovery_size = $this->params['size'];
        $recovery->recovery_note = $this->params['note'];
        $recovery->recovery_flaw = $this->params['flaw_ids'];  //瑕疵
        $recovery->recovery_enclosure = $this->params['enclosure_ids'];  //附件
        $recovery->use_time_note = $this->params['use_time_note']; //使用时间

        //商户信息
        $recovery->option_admin_id = $this->user['id'];  //商户 admin_id
        $recovery->supplier_id = $this->user['supplier_id'];  //商户ID

        //默认字段
        $recovery->recovery_num = $this->default_value['recovery_num']; //默认值 1

        //配置参数
        $recovery->recovery_day = $this->config['recovery_day'];  //回收天数
        $recovery->offer_hour = $this->config['offer_hour'];  //出价小时
        $recovery->offer_minute = $this->config['offer_minute'];  //出价分钟
        $recovery->over_hour = $this->config['over_hour'];  //结束小时
        $recovery->over_minute = $this->config['over_minute']; //结束分钟
        // 状态
        $recovery->recovery_status = static::RECOVERY_STATUS_TEN;  //回收状态

        if ($recovery->save()) {
            return $recovery;
        }
        return false;
    }

    /**
     * 获取回收配置
     */
    public function getRecoveryConfig()
    {
        return $this->config;
    }

    /**
     * 初始化回收配置
     */
    protected function initRecoveryConfig()
    {
        $config = self::getInfoBySetID(1);
        $this->config ['recovery_day'] = $config['recovery_day'];
        $this->config ['offer_hour'] = $config['offer_hour'];
        $this->config ['offer_minute'] = $config['offer_minute'];
        $this->config ['over_hour'] = $config['over_hour'];
        $this->config ['over_minute'] = $config['over_minute'];
    }

    /**
     * 创建图片
     * @param $recovery
     * @return bool
     */
    protected function createRecoveryImg($recovery)
    {
        ImageModel::deleteRecoveryItem($recovery->id);

        foreach ((array)$this->params['img_m'] as $k => $v){
            ImageModel::addData([
                'supplier_id' =>$this->user['supplier_id'], //商户ID
                'obj_id' => $recovery->id,
                'type' => 'recovery',
                'img_url' => $v['url'],
                'img_type' => pathinfo($v['url'], PATHINFO_EXTENSION),
                'img_note' => isset($v['id'])?$v['id']:$k,
            ]);
        }

        foreach ((array)$this->params['img_s'] as $k => $v){
            ImageModel::addData([
                'supplier_id' =>$this->user['supplier_id'], //商户ID
                'obj_id' => $recovery->id,
                'type' => 'recovery',
                'img_url' => $v['url'],
                'img_type' => pathinfo($v['url'], PATHINFO_EXTENSION),
                'img_note' => 0,
            ]);
        }
        return true;
    }


    /**
     * 删除回收订单
     * @param $id
     * @return bool
     */
    public static function deleteRecovery($id){
        try{
            $r = RecoveryModel::find($id);
            if ($r->delete()){
                ImageModel::deleteRecoveryItem($id);
                return true;
            }
            throw  new \Exception('删除失败');
        }catch (\Exception $exception){
            return false;
        }

    }

    /**
     * 获取父级分类
     * @param $category_id
     * @return bool|mixed
     */
    public static function getCategoryPid($category_id){
        $category = CategoryModel::find($category_id,['id','parent_id']);
        if (!$category){
            return false;
        }
        if ($category->parent_id == 0){
            return $category->id;
        }
        return self::getCategoryPid($category->parent_id);
    }

}