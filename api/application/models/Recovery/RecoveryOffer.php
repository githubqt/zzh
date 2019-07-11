<?php
// +----------------------------------------------------------------------
// | 商品回收出价表
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：黄献国 
// +----------------------------------------------------------------------
// | Author: 黄献国   Date:2018/11/6 Time:10:00
// +----------------------------------------------------------------------
namespace Recovery;

use Assemble\Builder;
use Assemble\Support\Arr;
use Supplier\SupplierModel;

class RecoveryOfferModel extends \BaseModel
{
    
    /**
     * 定义表名后缀
     */
    protected static $_tableName = 'recovery_offer';
    
    /* 收支明细列表*/
    public static function getList(array $search = [])
    {
        $builder = new Builder();
    
        // 选择列语句
        $fileds = " a.*,s.company";
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
        $builder->where('a.recovery_id','=', Arr::value($search, 'recovery_id'));
    
        // 搜索查询条件
        // 时间搜索
        $start = Arr::value($search, 'start_time');
        $end = Arr::value($search, 'end_time');
        if ($start) {
            $builder->where('a.created_at', '>=', Date::startOfDay($start));
        }
        if ($end) {
            $builder->where('a.created_at', '<=', Date::endOfDay($end));
        }
    
        
        // 商户id
        $supplier_id = Arr::value($search, 'supplier_id');
        if ($supplier_id) {
            $builder->where('a.supplier_id', '=', $supplier_id);
        }
    
        // 商户名称
        $supplier_name = Arr::value($search, 'supplier_name');
        if ($supplier_name) {
            $builder->where('s.company', $supplier_name);
        }
    
        // 状态
        $recovery_status = Arr::value($search, 'status');
        if ($recovery_status) {
            $builder->where('a.status', $recovery_status);
        }
    
        $builder->orderBy(' offer_price desc');
    
        // 修正多表连表时排序字段增加别名前缀
        $builder->orderAlias('a');
    
        return static::paginate($builder);
    }

    
    /**
     * 根据表回收ID获取最高价格记录信息
     * @param int $id 表自增ID
     */
    public static function getInfoByRecoveryID($recovery_id)
    {
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['recovery_id'] = intval($recovery_id);
        $where['status'] = '1';
        $pdo = self::_pdo('db_r');
        return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
    }
    
    
    
}