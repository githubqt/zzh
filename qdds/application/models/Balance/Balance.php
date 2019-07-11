<?php
/**
 * 财务结算管理
 * @version v0.01
 * @author laiqingtao
 * @time 2018-09-11
 */

namespace Balance;

use Assemble\Builder;
use Assemble\Support\Arr;
use Assemble\Support\Date;
use Services\Finance\BalanceService;
use Supplier\SupplierModel;

class BalanceModel extends \BaseModel
{
    /* 财务结算列表*/
    public static function getList(array $search = [])
    {
        $builder = new Builder();

        $auth = self::auth();

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
        $builder->where('a.supplier_id', '=', $auth['supplier_id']);
        $builder->where('a.status', 'in', BalanceService::BALANCE_STATUS_SP);

        // 搜索查询条件
        // 商户名称
        $supplier_name = Arr::value($search, 'supplier_name');
        if ($supplier_name) {
            $builder->where('s.company', $supplier_name);
        }

        // 结算单号
        $balance_no = Arr::value($search, 'balance_no');
        if ($balance_no) {
            $builder->where('a.balance_no', $balance_no);
        }

        // 结算类型
        $balance_type = Arr::value($search, 'balance_type');
        if ($balance_type) {
            $builder->where('a.balance_type', $balance_type);
        }

        // 结算状态
        $status = Arr::value($search, 'status');
        if ($status) {
            $builder->where('a.status', $status);
        }

        // 时间搜索
        $start = Arr::value($search, 'start_time');
        $end = Arr::value($search, 'end_time');
        if ($start) {
            $builder->where('a.created_at', '>=', Date::startOfDay($start));
        }
        if ($end) {
            $builder->where('a.created_at', '<=', Date::endOfDay($end));
        }

        // 修正多表连表时排序字段增加别名前缀
        $builder->orderAlias('a');

        return static::paginate($builder);
    }

}