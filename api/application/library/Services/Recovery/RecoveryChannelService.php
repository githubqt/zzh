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
use Product\ProductModel;
use Recovery\RecoveryModel;
use Recovery\RecoveryOfferModel;

class RecoveryChannelService extends RecoveryService
{

    /**
     * 渠道回收所有状态
     */
    const CHANNEL_RECOVERY_STATUS_ING = [
        self::RECOVERY_STATUS_TWENTY,
        self::RECOVERY_STATUS_THIRTY,
        self::RECOVERY_STATUS_FORTY,
        self::RECOVERY_STATUS_FIFTY,
        self::RECOVERY_STATUS_SIXTY,
        self::RECOVERY_STATUS_SEVENTY,
        self::RECOVERY_STATUS_EIGHTY
    ];

    /**
     * 渠道回收拍中状态
     */
    const CHANNEL_RECOVERY_STATUS_OVER = [
        self::RECOVERY_STATUS_THIRTY,
        self::RECOVERY_STATUS_FIFTY,
        self::RECOVERY_STATUS_SIXTY,
        self::RECOVERY_STATUS_SEVENTY,
        self::RECOVERY_STATUS_EIGHTY
    ];
    /**
     * 渠道回收所有状态描述
     */
    const CHANNEL_RECOVERY_STATUS_ING_VALUE = [
        self::RECOVERY_STATUS_TWENTY            => '出价中',
        self::RECOVERY_STATUS_THIRTY            => '已估价',
        self::RECOVERY_STATUS_FORTY             => '无人估价',
        self::RECOVERY_STATUS_FIFTY             => '回收中',
        self::RECOVERY_STATUS_SIXTY             => '已回收',
        self::RECOVERY_STATUS_SEVENTY           => '已售出',
        self::RECOVERY_STATUS_EIGHTY            => '取消'
    ];

    /**
     * 渠道回收拍中状态描述
     */
    const CHANNEL_RECOVERY_STATUS_OVER_VALUE = [
        self::RECOVERY_STATUS_THIRTY            => '已估价',
        self::RECOVERY_STATUS_FIFTY             => '回收中',
        self::RECOVERY_STATUS_SIXTY             => '已回收',
        self::RECOVERY_STATUS_SEVENTY           => '已售出',
        self::RECOVERY_STATUS_EIGHTY            => '取消'
    ];

    /* 列表*/
    public static function getList(array $search = [])
    {
         $result = RecoveryModel::getList($search);
         //是否需要书信本页
         foreach ($result['rows'] as $key => $value) {
             $result['rows'][$key]['status_txt'] = self::CHANNEL_RECOVERY_STATUS_ING_VALUE[$result['rows'][$key]['recovery_status']];
             if ($value['recovery_status'] == self::RECOVERY_STATUS_TWENTY) {
                 //出价倒计时
                 $time = strtotime('+'.$value['over_hour'].' hour',strtotime($value['examine_time']));
                 $now_time = strtotime('+'.$value['over_minute'].' minute',$time);
                 $surplus_time = $now_time-time();//总秒数
                 if ($surplus_time <= 0) {
                     $result['rows'][$key]['recovery_status'] = self::RECOVERY_STATUS_THIRTY;
                     $result['rows'][$key]['status_txt'] = '已估价结算中';
                 }
             }


            $result['rows'][$key]['category_name'] = $value['c1_name'].'|'.$value['c2_name'].'|'.$value['c3_name'];
            if ($value['recovery_status'] >= self::RECOVERY_STATUS_THIRTY) {
                if ($value['last_day'] < 0) {
                    $result['rows'][$key]['last_day'] = '<span style="color:red">已结束</span>';
                } else if ($value['last_day'] <= 5){
                    $result['rows'][$key]['last_day'] = '<span style="color:red">还剩'.$value['last_day'].'天</span>';
                } else {
                    $result['rows'][$key]['last_day'] = '<span>还剩'.$value['last_day'].'天</span>';
                }
            } else {
                $result['rows'][$key]['last_day'] = '-';
            }

            //我的出价
             $result['rows'][$key]['our_price'] = '-';
             $result['rows'][$key]['our_price_num'] = 0;
             $auth = AdminModel::getCurrentLoginInfo();
             $offerList = RecoveryOfferModel::getList(['recovery_id'=>$value['id'],'supplier_id'=>$auth['supplier_id']]);
             if ($offerList['total'] > 0) {
                 $result['rows'][$key]['our_price'] = $offerList['rows'][0]['offer_price'];
                 $result['rows'][$key]['our_price_num'] = $offerList['total'];
             }
        } 

        return $result;
    }

    /* 列表*/
    public static function getOfferList(array $search = [])
    {
        $auth = AdminModel::getCurrentLoginInfo();
        $search['supplier_id'] = $auth['supplier_id'];
        $result = RecoveryOfferModel::getList($search);
        $limit = $_REQUEST['rows'];
        $page = $_REQUEST['page']-1;
        $i = '0';
        foreach ($result['rows'] as $key => $value) {
            ++$i;
            $result['rows'][$key]['index'] = $limit*$page+$i;
        }
        //获取最新价格
        $result['over_price'] = RecoveryModel::find($search['recovery_id'],['over_price'])->over_price;
        return $result;
    }

    /**
     * 回收采购单确认收货更新回收状态
     * @param integer $recovery_id
     * @return float
     */
    public static function purchaseReceiptCallBack($recovery_id)
    {
        $auth = AdminModel::getCurrentLoginInfo();
        $Recovery = RecoveryModel::find($recovery_id);
        $Recovery->option_record = $Recovery->option_record."<br/><br/>".date("Y-m-d H:i:s")." &nbsp;&nbsp;&nbsp;&nbsp;".$auth['fullname']." &nbsp;&nbsp;&nbsp;&nbsp;回收采购单确认收货";
        $Recovery->recovery_status = self::RECOVERY_STATUS_SEVENTY;
        $Recovery->save();

        //更新商品类型为非回收商品
        $Product = ProductModel::find($Recovery->product_id);
        $Product->is_purchase = 1;
        $Product->save();
    }
}