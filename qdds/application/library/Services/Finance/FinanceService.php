<?php
// +----------------------------------------------------------------------
// | 财务操作类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/9/6 Time:11:54
// +----------------------------------------------------------------------


namespace Services\Finance;


use Finance\FinanceModel;

class FinanceService extends FinanceBaseService
{
    /* 收支明细列表*/
    public static function getList(array $search = [])
    {
        $result = FinanceModel::getList($search);

        foreach ($result['rows'] as $key => $value) {
            $result['rows'][$key]['type_txt'] = self::TYPE_VALUE[$value['type']];
            $result['rows'][$key]['pay_type_txt'] = self::PAY_TYPE_VALUE[$value['pay_type']];
            $result['rows'][$key]['settle_type_txt'] = self::SETTLEMENT_VALUE[$value['settle_type']];
        }

        return $result;
    }

    /* 更新收支明细*/
    public static function updateByID($data, $id)
    {
        return FinanceModel::update($data, ['id'=>$id]);
    }

}