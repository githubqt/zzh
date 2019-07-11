<?php
// +----------------------------------------------------------------------
// | 回收操作类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：黄献国 
// +----------------------------------------------------------------------
// | Author: 黄献国  Date:2018/11/5 Time:19:01
// +----------------------------------------------------------------------


namespace Services\Recovery;


use Admin\AdminModel;
use Core\Sms;
use Recovery\RecoveryModel;
use Recovery\RecoverySetModel;
use Services\Purchase\PurchaseChannelService;
use Services\Purchase\PurchaseService;
use Supplier\SupplierModel;
use Category\CategoryModel;
use Brand\BrandModel;
use Product\ProductModel;
use Image\ImageModel;
use Recovery\RecoveryOfferModel;

class RecoveryService extends RecoveryBaseService
{

    /* 列表*/
    public static function getList(array $search = [])
    {
        $result = RecoveryModel::getList($search);

         foreach ($result['rows'] as $key => $value) {
            $result['rows'][$key]['status_txt'] = self::SETTLEMENT_VALUE[$value['recovery_status']];
                
            if ($value['product_id']) {
                $product = ProductModel::getInfoByID($value['product_id']);
                if ($product) {
                    $result['rows'][$key]['is_onstatus'] = $product['on_status'];
                    $result['rows'][$key]['sale_price'] = $product['sale_price']?$product['sale_price']:'';
                }
            }
            
            $result['rows'][$key]['last_day'] = '-';
            $result['rows'][$key]['last_time'] = '-';
            $result['rows'][$key]['over_last_time'] = '-';
            if ($value['examine_time']) {
                $time = strtotime('+'.$value['offer_hour'].' hour',strtotime($value['examine_time']));
                $now_time = strtotime('+'.$value['offer_minute'].' minute',$time);
                $surplus_time = bcdiv($now_time-time(),60,0);
                $result['rows'][$key]['over_last_time'] = date('Y-m-d H:i:s',$now_time);
                
                if ($surplus_time <= '0' && $value['recovery_status'] == self::RECOVERY_STATUS_TWENTY) {
                    $result['rows'][$key]['status_txt'] = self::SETTLEMENT_VALUE['30'].'/清算中';
                }
                
                $over_time = strtotime('+'.$value['over_hour'].' hour',strtotime($value['examine_time']));
                $over_now_time = strtotime('+'.$value['over_minute'].' minute',$over_time);
                $over_surplus_time = bcdiv($over_now_time-time(),60,0);
                $result['rows'][$key]['last_time'] = $over_surplus_time;
                
                if ($surplus_time <= '0' && $value['recovery_status'] >= self::RECOVERY_STATUS_THIRTY) {
                    $value['last_day'] = bcdiv(strtotime('+'.$value['recovery_day'].' day',strtotime($value['examine_time']))-time(),86400,0);
                    if ($value['last_day'] < 0) {
                        $result['rows'][$key]['last_day'] = '<span style="color:red">已结束</span>';
                    } else if ($value['last_day'] <= 5 && $value['last_day'] > 0){
                        $result['rows'][$key]['last_day'] = '<span style="color:red">还剩'.$value['last_day'].'天</span>';
                    } else {
                        $result['rows'][$key]['last_day'] = '<span>还剩'.$value['last_day'].'天</span>';
                    }
                }
                if ($value['recovery_status'] == self::RECOVERY_STATUS_FORTY) {
                    $result['rows'][$key]['last_day'] = '-';
                }
            }
            
            $result['rows'][$key]['category_name'] = $value['c1_name'].'|'.$value['c2_name'].'|'.$value['c3_name'];
            
            //是否可以补全信息
            $result['rows'][$key]['is_evaluation'] = '0';
            if ($value['recovery_status'] == self::RECOVERY_STATUS_THIRTY) {
                if ($value['offer_supplier_id'] && $value['offer_price'] > '0' && $value['is_completion'] == '1') {
                    $result['rows'][$key]['is_evaluation'] = '1';
                }
            }
        } 

        return $result;
    }
    
    /* 获取数据*/
    public static function getInfoByID($id)
    {
        $detail = RecoveryModel::find($id)->toArray();
        if ($detail) {
            $supplier = SupplierModel::getInfoByID($detail['supplier_id']);
            $detail['supplier_name'] = $supplier['company'];
            $detail['supplier_domain'] = $supplier['domain'];
            $c_name = CategoryModel::getInfoByID($detail['category_id']);
            $b_name = CategoryModel::getInfoByID($c_name['parent_id']);
            $a_name = CategoryModel::getInfoByID($b_name['parent_id']);
            $detail['category_name'] = $a_name['name'].'|'.$b_name['name'].'|'.$c_name['name'];
            $detail['brand_name'] = BrandModel::getInfoByID($detail['brand_id'])['name'];
            $detail['last_day'] = '-';
            if ($detail['examine_time'] && $detail['recovery_status'] >= self::RECOVERY_STATUS_THIRTY) {
                $detail['last_day'] = bcdiv(strtotime('+'.$detail['recovery_day'].' day',strtotime($detail['examine_time']))-time(),86400,0);
                if ($detail['last_day'] < 0) {
                    $detail['last_day'] = '<span style="color:red">已结束</span>';
                } else if ($detail['last_day'] <= 5){
                    $detail['last_day'] = '<span style="color:red">还剩'.$detail['last_day'].'天</span>';
                } else {
                    $detail['last_day'] = '<span>还剩'.$detail['last_day'].'天</span>';
                }
                if ($detail['recovery_status'] == self::RECOVERY_STATUS_FORTY) {
                    $detail['last_day'] = '-';
                }
            }
            $detail['status_txt'] = self::SETTLEMENT_VALUE[$detail['recovery_status']];
            if ($detail['product_id']) {
                $detail['product'] = ProductModel::getInfoByID($detail['product_id']);
            }
            $detail['imglist']  = ImageModel::getInfoByTypeOrID(intval($id),'recovery');
            
            if ($detail['examine_time']) {
                $time = strtotime('+'.$detail['offer_hour'].' hour',strtotime($detail['examine_time']));
                $now_time = strtotime('+'.$detail['offer_minute'].' minute',$time);
                $detail['surplus_time'] = $now_time-time();
                $over_time = strtotime('+'.$detail['over_hour'].' hour',strtotime($detail['examine_time']));
                $over_now_time = strtotime('+'.$detail['over_minute'].' minute',$time);
                
                if ($detail['surplus_time'] <= '0' && $detail['recovery_status'] == self::RECOVERY_STATUS_TWENTY) {
                    $detail['status_txt'] = self::SETTLEMENT_VALUE['30'].'/清算中';
                }
                
                
                
                $time_list ['time_m'] = "00";
                $time_list ['time_i'] = "00";
                $time_list ['time_s'] = "00";
                if ($detail['surplus_time'] > 0) {
                    $time_list ['time_m'] = floor ( $detail['surplus_time'] / 3600 );
                    if ($time_list ['time_m'] < 10)
                        $time_list ['time_m'] = "0" . $time_list ['time_m'];
                
                        $time_list ['time_i'] = floor ( $detail['surplus_time'] % 3600 / 60 );
                        if ($time_list ['time_i'] < 10)
                            $time_list ['time_i'] = "0" . $time_list ['time_i'];
                
                            $time_list ['time_s'] = $detail['surplus_time'] % 60;
                            if ($time_list ['time_s'] < 10)
                                $time_list ['time_s'] = "0" . $time_list ['time_s'];
                }
                
                $detail['time_list'] = $time_list;
            }
            
            //瑕疵
            if ($detail['recovery_flaw']) {
                $flaw = explode(',',$detail['recovery_flaw']);
                foreach ($flaw as $key=>$val) {
                    $flaw[$key] = self::RECOVERY_FLAW_STATUS[$val];
                }
                $detail['recovery_flaw_txt'] = implode(',', $flaw);
            }
            
            //附件
            if ($detail['recovery_enclosure']) {
                $flaw = explode(',',$detail['recovery_enclosure']);
                foreach ($flaw as $key=>$val) {
                    $flaw[$key] = self::RECOVERY_ENCLOSURE_STATUS[$val];
                }
                $detail['recovery_enclosure_txt'] = implode(',', $flaw);
            }
            
            //是否显示剩余时间信息
            $detail['is_evaluation'] = '0';
            if ($detail['recovery_status'] == self::RECOVERY_STATUS_THIRTY) {
                if (!$detail['offer_supplier_id'] && $detail['is_completion'] == '1') {
                    $detail['is_evaluation'] = '1';
                }
            }
            $detail['offer_price'] = $detail['offer_price'] > '0'?$detail['offer_price']:RecoveryOfferModel::getInfoByRecoveryID($id)['offer_price'];
        }
        
        return $detail;
    }

    /* 更新*/
    public static function updateByID($data, $id)
    {
        return RecoveryModel::update($data, ['id'=>$id]);
    }
    
    
    /* 获取设置*/
    public static function getInfoBySetID($id)
    {
        return RecoverySetModel::find($id)->toArray();
    }
    
    /* 更新设置*/
    public static function updateBySetID($data, $id)
    {
        return RecoverySetModel::update($data, ['id'=>$id]);
    }
    
    

    
    
    
    /* 列表*/
    public static function getOfferList(array $search = [])
    {
        $result = RecoveryOfferModel::getList($search);
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page']-1;
        $i = '0';
        foreach ($result['rows'] as $key => $value) {
            ++$i;
            $result['rows'][$key]['index'] = $limit*$page+$i;
        }
    
        return $result;
    }
    
    
    /* 获取最新一条最高价格*/
    public static function getNewHotPrice($id)
    {
        $result = RecoveryOfferModel::getInfoByRecoveryID($id);
        
        return $result;
    }

    /* 兜底售出*/
    public static function addpurchase($id)
    {
        $detail = RecoveryModel::find($id);
        if ($detail->recovery_status != self::RECOVERY_STATUS_THIRTY) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '兜底售出失败！';
            return $jsonData;
        }
        $supplier_data = SupplierModel::find($detail->over_supplier_id);
        $params = Array
        (
            'title' => '回收采购'.date("ymd"),
            'name' => $supplier_data->contact,
            'mobile' => $supplier_data->mobile,
            'province_id' => $supplier_data->province_id,
            'city_id' => $supplier_data->city_id,
            'area_id' => $supplier_data->area_id,
            'address' => $supplier_data->address,
            'pay_type' => 'offline',
            'source_id' => $id,
            'purchase_supplier_id' => $supplier_data->id,
            'product' => [
                [
                    'id' => $detail->product_id,
                    'num' => 1
                ]
            ]
        );
        //采购单参数
        $PurchaseChannelService = new PurchaseChannelService();
        $PurchaseChannelService->setRequest($params);
        //设置回收采购
        $PurchaseChannelService->setPurchaseType($PurchaseChannelService::PURCHASE_TYPE_RECOVERY);
        $res =  $PurchaseChannelService->createRecoveryPurchase();

        if (!$res) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '兜底售出失败：'.$PurchaseChannelService->getError();
            return $jsonData;
        }
        $jsonData['code'] = '200';
        $jsonData['msg'] = '兜底售出成功！';
        return $jsonData;
    }

    /**
     * 兜底售出更新回收状态
     * @param integer $recovery_id
     * @param integer $purchase_id
     * @return float
     */
    public static function addPurchaseCallBack($recovery_id,$purchase_id)
    {
        $auth = AdminModel::getCurrentLoginInfo();
        $Recovery = RecoveryModel::find($recovery_id);
        $Recovery->option_record = $Recovery->option_record."<br/><br/>".date("Y-m-d H:i:s")." &nbsp;&nbsp;&nbsp;&nbsp;".$auth['fullname']." &nbsp;&nbsp;&nbsp;&nbsp;兜底售出 采购单id:".$purchase_id;
        $Recovery->recovery_status = self::RECOVERY_STATUS_FIFTY;
        $Recovery->is_purchase = 2;
        $Recovery->purchase_id = $purchase_id;
        $Recovery->save();

        //商品已兜底售出，发送短信给竞拍成功供应商或渠道商
        self::SendSms($Recovery->id,4);

    }

    //发短信
    public static function SendSms($id,$type)
    {
        set_time_limit(0);
        $detail = self::getInfoByID($id);
        switch ($type) {
            case 1:/* 平台审核过后发送短信给有权限的供应商以及渠道商进行出价*/
                $supplier = SupplierModel::getBYwhere(['company','mobile'],['can_recovery'=>2]);
                if (is_array($supplier) && count($supplier) > 0) {
                    foreach ($supplier as $key => $value) {
                        if (!empty($value['mobile'])) {
                            Sms::SendSms($value['mobile'], 29,$detail['supplier_domain'],[$value['company'],$detail['product_name']]);
                        }
                    }
                }
                break;
            case 2:/* 商品竞拍成功，发送短信给竞拍成功供应商或渠道商*/
                $supplier = SupplierModel::getInfoByID($detail['over_supplier_id']);
                if (!empty($supplier['mobile'])) {
                    Sms::SendSms($supplier['mobile'], 30,$detail['supplier_domain'],[$supplier['company'],$detail['product_name']]);
                }
                break;
            case 3:/* 商品已被商家售出，发送短信给竞拍成功供应商或渠道商*/
                $supplier = SupplierModel::getInfoByID($detail['over_supplier_id']);
                if (!empty($supplier['mobile'])) {
                    Sms::SendSms($supplier['mobile'], 31,$detail['supplier_domain'],[$supplier['company'],$detail['product_name']]);
                }
                break;
            case 4:/* 商品已兜底售出，发送短信给竞拍成功供应商或渠道商*/
                $supplier = SupplierModel::getInfoByID($detail['over_supplier_id']);
                $where = $supplier['type'] == 3?'回收采购':'采购列表';
                if (!empty($supplier['mobile'])) {
                    Sms::SendSms($supplier['mobile'], 32,$detail['supplier_domain'],[$supplier['company'],$detail['product_name'],$where]);
                }
                break;
        }


    }
}