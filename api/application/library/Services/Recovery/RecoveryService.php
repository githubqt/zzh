<?php
// +----------------------------------------------------------------------
// | 回收操作类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：黄献国 
// +----------------------------------------------------------------------
// | Author: 黄献国  Date:2018/11/5 Time:19:01
// +----------------------------------------------------------------------


namespace Services\Recovery;


use Admin\AdminModel;
use Common\CommonBase;
use Custom\YDLib;
use Recovery\RecoveryModel;
use Recovery\RecoverySetModel;
use Sms\SmsModel;
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
            $result['rows'][$key]['category_name'] = $value['c1_name'].'|'.$value['c2_name'].'|'.$value['c3_name'];

            if ($value['examine_time']) {
                if ($value['last_day'] < 0) {
                    $result['rows'][$key]['last_day'] = '<span style="color:red">已结束</span>';
                } else if ($value['last_day'] <= 5){
                    $result['rows'][$key]['last_day'] = '<span style="color:red">还剩'.$value['last_day'].'天</span>';
                } else {
                    $result['rows'][$key]['last_day'] = '<span>还剩'.$value['last_day'].'天</span>';
                }

                $time = $value['offer_expire_time'] ? strtotime($value['offer_expire_time']):0;

                $result['rows'][$key]['remaining_time'] = $time> time()?$time - time():0;
            } else {
                $result['rows'][$key]['last_day'] = '-';
                $result['rows'][$key]['last_time'] = '-';
                $result['rows'][$key]['remaining_time'] = 0;
            }


            $rtime  = $result['rows'][$key]['remaining_time'];

            //原状态
            $result['rows'][$key]['real_recovery_status'] = $value['recovery_status'];
            //如果已估计中且估计时间到期，则跳入下个状态
            if($value['recovery_status'] == self::RECOVERY_STATUS_TWENTY && $rtime === 0){
                $result['rows'][$key]['recovery_status'] = self::RECOVERY_STATUS_THIRTY;
                $result['rows'][$key]['status_txt'] = self::SETTLEMENT_VALUE[self::RECOVERY_STATUS_THIRTY];
            }



            // 查询主图
            $imgs = ImageModel::getInfoByTypeOrID($value['id'],'recovery');
            $cover = '';
            if (isset($imgs[0])){
                $cover = $imgs[0]['img_url'];
            }
            $result['rows'][$key]['cover']  = $cover;
        }

        return $result;
    }

    /* 获取数据*/
    public static function getInfoByID($id)
    {
        $detail = RecoveryModel::find($id);
        if (!is_null($detail)) {
            $detail = $detail->toArray();
            $detail['supplier_name'] = SupplierModel::getInfoByID($detail['supplier_id'])['company'];
            $c_name = CategoryModel::getInfoByID($detail['category_id']);
            $b_name = CategoryModel::getInfoByID($c_name['parent_id']);
            $a_name = CategoryModel::getInfoByID($b_name['parent_id']);
            $detail['category_name'] = $a_name['name'].'|'.$b_name['name'].'|'.$c_name['name'];
            $detail['brand_name'] = BrandModel::getInfoByID($detail['brand_id'])['name'];
            $detail['imglist']  = ImageModel::getInfoByTypeOrID($id,'recovery');
            $detail['status_txt'] = self::SETTLEMENT_VALUE[$detail['recovery_status']];
            $detail['last_day'] = bcdiv(strtotime('+'.$detail['recovery_day'].' day',strtotime($detail['examine_time']))-time(),86400,0);
            $detail['last_time'] = date("Y-m-d H:i:s",strtotime('+'.$detail['recovery_day'].' day',strtotime($detail['examine_time'])));
            if ($detail['last_day'] < 0) {
                $detail['last_day'] = '<span style="color:red">已结束</span>';
            } else if ($detail['last_day'] <= 5){
                $detail['last_day'] = '<span style="color:red">还剩'.$detail['last_day'].'天</span>';
            } else {
                $detail['last_day'] = '<span>还剩'.$detail['last_day'].'天</span>';
            }

            //出价倒计时
            $time = strtotime('+'.$detail['over_hour'].' hour',strtotime($detail['examine_time']));
            $now_time = strtotime('+'.$detail['over_minute'].' minute',$time);
            $detail['surplus_time'] = $now_time-time();//总秒数

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

    /* 获取出价信息*/
    public static function getOfferByID($id)
    {
        $detail = RecoveryModel::find($id)->toArray();
        $detail['our_price'] = '-';
        $detail['our_price_num'] = 0;
        $auth = AdminModel::getCurrentLoginInfo();
        $offerList = RecoveryOfferModel::getList(['recovery_id'=>$detail['id'],'supplier_id'=>$auth['supplier_id']]);
        if ($offerList['total'] > 0) {
            $detail['our_price'] = $offerList['rows'][0]['offer_price'];
            $detail['our_price_num'] = $offerList['total'];
        }
        //出价倒计时
        $time = strtotime('+'.$detail['over_hour'].' hour',strtotime($detail['examine_time']));
        $now_time = strtotime('+'.$detail['over_minute'].' minute',$time);
        $detail['surplus_time'] = $now_time-time();//总秒数
        return $detail;
    }

    /* 加价*/
    public static function setOfferByID($offer_price,$id)
    {
        // 开始事务
        $pdo = YDLib::getPDO('db_w');
        $pdo->beginTransaction();
        try {
            $detail = RecoveryModel::find($id)->toArray();
            //第一次出价倒计时
            $time = strtotime('+'.$detail['offer_hour'].' hour',strtotime($detail['examine_time']));
            $now_time = strtotime('+'.$detail['offer_minute'].' minute',$time);
            $surplus_time = $now_time-time();//总秒数
            $auth = AdminModel::getCurrentLoginInfo();
            $updata_offer = [
                'offer_price'=>$offer_price,
                'offer_supplier_id'=>$auth['supplier_id'],
                'offer_time'=>date('Y-m-d H:i:s')
            ];
            $updata = [
                'over_price'=>$offer_price,
                'over_supplier_id'=>$auth['supplier_id'],
                'over_time'=>date('Y-m-d H:i:s')
            ];
            if ($surplus_time > 0) {
                $updata = array_merge($updata_offer,$updata);
            }
            RecoveryModel::update($updata,['id'=>$id]);

            RecoveryOfferModel::update(['status'=>2],['recovery_id'=>$id]);

            RecoveryOfferModel::create([
                'recovery_id'=>$id,
                'status'=>1,
                'offer_price'=>$offer_price,
                'option_admin_id'=>$auth['id'],
                'option_admin_name'=>$auth['fullname'],
                'supplier_id'=>$auth['supplier_id'],
                'is_del'=>2,
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s')
            ]);

            return $pdo->commit();
        } catch (\Exception $exception) {
            $pdo->rollback();
            return false;
        }
    }

    /**
     * 下单更新更新回收状态
     * @param integer $product_id
     * @return float
     */
    public static function addOrderCallBack($product_id)
    {

        //更新商品类型为非回收商品
        $Product = ProductModel::find($product_id);
        $Product->is_purchase = 1;
        $Product->save();

        $Recovery = RecoveryModel::findOneWhere(['product_id'=>$product_id]);
        $auth = AdminModel::getCurrentLoginInfo();
        $Recovery = RecoveryModel::find($Recovery['id']);
        $Recovery->option_record = $Recovery->option_record."<br/><br/>".date("Y-m-d H:i:s")." &nbsp;&nbsp;&nbsp;&nbsp;".$auth['fullname']." &nbsp;&nbsp;&nbsp;&nbsp;订单支付成功";
        $Recovery->recovery_status = self::RECOVERY_STATUS_SEVENTY;
        $Recovery->sellout_price = $Product->sale_price;
        $Recovery->save();

        //商品已被商家售出，发送短信给竞拍成功供应商或渠道商
        self::SendSms($Recovery->id,3);

    }

    //发短信
    public static function SendSms($id,$type)
    {
        $detail = self::getInfoByID($id);
        switch ($type) {
            case 1:/* 平台审核过后发送短信给有权限的供应商以及渠道商进行出价*/
                $supplier = SupplierModel::getBYwhere(['company','mobile'],['can_recovery'=>2]);
                if (is_array($supplier) && count($supplier) > 0) {
                    foreach ($supplier as $key => $value) {
                        if (!empty($value['mobile'])) {
                            $data = [];
                            $data ['mobile'] = $value['mobile'];
                            $data ['model_id'] = 29;
                            $data ['params'] = [$value['company'],$detail['product_name']];
                            SmsModel::SendSmsJustFire($data);
                        }
                    }
                }
                break;
            case 2:/* 商品竞拍成功，发送短信给竞拍成功供应商或渠道商*/
                $supplier = SupplierModel::getInfoByID($detail['over_supplier_id']);
                if (!empty($supplier['mobile'])) {
                    $data = [];
                    $data ['mobile'] = $supplier['mobile'];
                    $data ['model_id'] = 30;
                    $data ['params'] = [$supplier['company'],$detail['product_name']];
                    SmsModel::SendSmsJustFire($data);
                }
                break;
            case 3:/* 商品已被商家售出，发送短信给竞拍成功供应商或渠道商*/
                $supplier = SupplierModel::getInfoByID($detail['over_supplier_id']);
                if (!empty($supplier['mobile'])) {
                    $data = [];
                    $data ['mobile'] = $supplier['mobile'];
                    $data ['model_id'] = 31;
                    $data ['params'] = [$supplier['company'],$detail['product_name']];
                    SmsModel::SendSmsJustFire($data);
                }
                break;
            case 4:/* 商品已兜底售出，发送短信给竞拍成功供应商或渠道商*/
                $supplier = SupplierModel::getInfoByID($detail['over_supplier_id']);
                $where = $supplier['type'] == 3?'回收采购':'采购列表';
                if (!empty($supplier['mobile'])) {
                    $data = [];
                    $data ['mobile'] = $supplier['mobile'];
                    $data ['model_id'] = 32;
                    $data ['params'] = [$supplier['company'],$detail['product_name'],$where];
                    SmsModel::SendSmsJustFire($data);
                }
                break;
        }


    }
}