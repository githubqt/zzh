<?php
// +----------------------------------------------------------------------
// | 退货物流服务类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/9/19 Time:11:06
// +----------------------------------------------------------------------


namespace Services\Purchase;


use Admin\AdminModel;
use Assemble\Source;
use Express\ExpressCompanyModel;
use Purchase\PurchaseReturnExpressModel;
use Supplier\SupplierModel;

class PurchaseReturnExpressService
{
    const TYPE_MERCHANT_SEND = 1; // 商户发货
    const TYPE_PLATFORM_RETURN = 2;  // 平台退回
    const TYPE_SUPPLIER_RETURN = 3;  // 供应商退回

    protected $error = '';

    protected $type = self::TYPE_MERCHANT_SEND;
    protected $log = '';

    protected $purchaseReturnDetail = [];


    public function __construct(array $detail)
    {
        $this->purchaseReturnDetail = $detail;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setLog($content)
    {
        $this->log = $content;
    }

    /**
     * 保存收货地址
     * @param $name
     * @param $mobile
     * @param $province_id
     * @param $city_id
     * @param $area_id
     * @param $address
     * @return \BaseModel|PurchaseReturnExpressModel
     */
    public function saveAddress($name, $mobile, $province_id, $city_id, $area_id, $address)
    {
        $model = $this->model();
        //获取log列表
        $log = $model->log ? json_decode($model->log) : [];
        // 带省市区的地址
        $pca_address = \AreaModel::getPca($province_id, $city_id, $area_id) . "{$address}";

        $model->name = $name;
        $model->mobile = $mobile;
        $model->province_id = $province_id;
        $model->city_id = $city_id;
        $model->area_id = $area_id;
        $model->address = $address;
        array_push($log, $this->formatLog($this->log ?: "更新物流地址为：{$name} {$mobile} {$pca_address}"));
        $model->log = json_encode($log);
        $model->type = $this->type;
        $model->return_no = $this->purchaseReturnDetail['return_no'];
        $model->pca_address = $pca_address;
        $model->save();
        return $model;
    }

    /**
     * 保存快递信息
     * @param $express_id
     * @param $express_no
     * @return \BaseModel|bool|PurchaseReturnExpressModel
     */
    public function saveExpress($express_id, $express_no)
    {
        $express = ExpressCompanyModel::find($express_id, [
            'id', 'name', 'pinyin'
        ]);
        if (!$express) {
            return false;
        }
        $model = $this->model();
        //获取log列表
        $log = $model->log ? json_decode($model->log) : [];

        $model->express_id = $express_id;
        $model->express_no = $express_no;
        $model->express_name = $express->name;
        $model->express_pinyin = $express->pinyin;
        array_push($log, $this->formatLog($this->log ?: "更新物流单号为：{$express_no} ({$express->name})"));
        $model->log = json_encode($log);
        $model->save();
        return $model;
    }

    /**
     * 格式化日志内容
     * @param $content
     * @return array
     */
    protected function formatLog($content)
    {
        $auth = AdminModel::getCurrentLoginInfo();
        $roleName = SupplierModel::getCompanyBySupplierId($auth['supplier_id']) ?? Source::getSourceName(Source::PLATFORM_ID);
        return [
            'admin_id' => $auth['id'],
            'admin_name' => $auth['fullname'],
            'role_type' => Source::all()[$auth['type']],
            'role_name' => $roleName,
            'content' => $content,
            'date' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * 返回express 模型对象
     * @return \BaseModel|PurchaseReturnExpressModel
     */
    protected function model()
    {
        if (isset($this->purchaseReturnDetail['return_no'])) {
            $row = PurchaseReturnExpressModel::findByReturnNoWithType($this->purchaseReturnDetail['return_no'], $this->type);
            if ($row) {
                return PurchaseReturnExpressModel::find($row['id']);
            }
            return new PurchaseReturnExpressModel();
        }
        return new PurchaseReturnExpressModel();
    }

}