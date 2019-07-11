<?php
/**
 * 结算管理
 * @version v0.01
 * @author laiqingtao
 * @time 2018-09-11
 */

namespace Validate;

class Balance
{
    /**
     * 定义数据验证规则
     * @var array
     */
    static $rule = [
        'supplier_id' => 'require',
        'balance_type' => 'require',
        'hdn_finance' => 'require|array',
        'starttime' => 'require|date',
        'endtime' => 'require|date',
        'actual_amount' => 'require|float'
    ];

    /**
     * 定义数据验证错误提示
     * @var array
     */
    static $message = [
        'supplier_id.require' => '请选择商户',
        'balance_type.require' => '请选择结算类型',
        'hdn_finance.require' => '请选择收支单',
        'hdn_finance.array' => '收支单信息格式出错',
        'starttime.require' => '结算周期开始时间必填',
        'starttime.date' => '结算周期开始时间格式出错',
        'endtime.require' => '结算周期结束时间必填',
        'endtime.date' => '结算周期结束时间格式出错',
        'actual_amount.require' => '结算金额必填',
        'actual_amount.float' => '结算金额格式出错',
    ];
    /**
     * 定义数据验证场景
     * @var array
     */
    static $scene = [
        'add' => ['supplier_id,balance_type,hdn_finance,starttime,endtime,actual_amount'],
        'check' => ['actual_amount'],
    ];
}