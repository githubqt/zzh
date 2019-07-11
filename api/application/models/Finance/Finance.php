<?php
// +----------------------------------------------------------------------
// | 财务收支流水表
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/9/6 Time:15:25
// +----------------------------------------------------------------------
namespace Finance;

use Assemble\Builder;
use Assemble\Support\Arr;
use Assemble\Support\Date;
use Services\Finance\FinanceBaseService;
use Supplier\SupplierModel;

class FinanceModel extends \BaseModel
{
    /* 收支明细列表*/
    public static function getList(array $search = [])
    {
        $builder = new Builder();

        // 选择列语句
        $fileds = " a.*,s.company supplier_name";
        $builder->select(explode(',', $fileds));

        // 表和表连接语句
        $form = self::getFullTable() . ' a 
		        LEFT JOIN
		            ' . SupplierModel::getFullTable() . ' s 
		        ON
		            a.supplier_id = s.id';
        $builder->from($form);

        // 必要查询条件
        $builder->where('a.is_del', self::DELETE_SUCCESS);

        // 搜索查询条件
        // 时间搜索
        $time_type = Arr::value($search, 'time_type');
        $start = Arr::value($search, 'start_time');
        $end = Arr::value($search, 'end_time');
        if ($time_type && $start && $end && $start <= $end) {
            $builder->where('a.'.$time_type, '>=', Date::startOfDay($start));
            $builder->where('a.'.$time_type, '<=', Date::endOfDay($end));
        }

        // 收支类型
        $type = Arr::value($search, 'type');
        if ($type) {
            $builder->where('a.type', '=', $type);
        }

        // 收支对象类型
        $obj_type = Arr::value($search, 'obj_type');
        if ($obj_type) {
            $builder->where('a.obj_type', $obj_type);
        }

        // 支付方式
        $pay_type = Arr::value($search, 'pay_type');
        if ($pay_type) {
            $builder->where('a.pay_type', $pay_type);
        }

        // 结算状态
        $settle_type = Arr::value($search, 'settle_type');
        if ($settle_type) {
            $builder->where('a.settle_type', $settle_type);
        }

        // 商户名称
        $supplier_name = Arr::value($search, 'supplier_name');
        if ($supplier_name) {
            $builder->where('s.company', $supplier_name);
        }

        // 收支对象名称
        $role_obj_name = Arr::value($search, 'role_obj_name');
        if ($role_obj_name) {
            $builder->where('a.role_obj_name', $role_obj_name);
        }

        // 流水号
        $payment_no = Arr::value($search, 'payment_no');
        if ($payment_no) {
            $builder->where('a.payment_no', $payment_no);
        }

        // 订单编号
        $obj_id = Arr::value($search, 'obj_id');
        if ($obj_id) {
            $builder->where('a.obj_id', $obj_id);
        }

        // 商户id
        $supplier_id = Arr::value($search, 'supplier_id');
        if ($supplier_id) {
            $builder->where('a.supplier_id', $supplier_id);
        }

        //结算类型
        $balance_type = Arr::value($search, 'balance_type');
        if ($balance_type) {
            $builder->where('a.obj_type', 'in', FinanceBaseService::BALANCE_TYPE_ARRAY[$balance_type]);
        }

        // 结算id
        $settle_id = Arr::value($search, 'settle_id');
        if ($settle_id) {
            $builder->where('a.settle_id', $settle_id);
        }

        // 修正多表连表时排序字段增加别名前缀
        $builder->orderAlias('a');

        return static::paginate($builder);
    }

}