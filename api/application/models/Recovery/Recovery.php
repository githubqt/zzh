<?php
// +----------------------------------------------------------------------
// | 回收表
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
use Services\Recovery\RecoveryOrderService;
use Supplier\SupplierModel;
use Custom\YDLib;
use Common\CommonBase;
use Category\CategoryModel;
use Brand\BrandModel;
use Admin\AdminModel;

class RecoveryModel extends \BaseModel
{
    protected static $_tableName = 'recovery';
    
    /* 收支明细列表*/
    public static function getList(array $search = [])
    {
        $builder = new Builder();

        // 选择列语句
        $fileds = " a.*,c3.name c3_name,c2.name c2_name,c1.name c1_name,b.name as brand_name,
            datediff(DATE_ADD(a.examine_time,INTERVAL a.recovery_day DAY),NOW()) as last_day,
            DATE_ADD(a.examine_time,INTERVAL a.recovery_day DAY) as last_time,
            s.company";
        $builder->select(explode(',', $fileds));

        // 表和表连接语句
        $form = self::getFullTable() . ' a 
		        LEFT JOIN
		            ' . SupplierModel::getFullTable() . ' s 
		        ON
		            a.supplier_id = s.id 
		        LEFT JOIN
		            ' . CommonBase::$_tablePrefix . 'category c3 
		        ON
		            a.category_id = c3.id 
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c2
		       	ON
		       		c2.id = c3.parent_id    
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c1
		       	ON
		       		c1.id = c2.parent_id 		   
		        LEFT JOIN
		            ' . BrandModel::$_tablePrefix . 'brand b 
		        ON
		            a.brand_id = b.id';
        $builder->from($form);

        // 必要查询条件
        $builder->where('a.is_del', self::DELETE_SUCCESS);
        
//        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());

//        $builder->where('a.supplier_id', '=', $adminInfo['supplier_id']);
        
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
        
        // 有效天数
        $recovery_start_day = Arr::value($search, 'recovery_start_day');
        $recovery_end_day = Arr::value($search, 'recovery_end_day');
        if ($recovery_start_day) {
            $builder->where('datediff(DATE_ADD(a.examine_time,INTERVAL a.recovery_day DAY),NOW())', '>=', $recovery_start_day);
        }
        if ($recovery_end_day) {
            $builder->where('datediff(DATE_ADD(a.examine_time,INTERVAL a.recovery_day DAY),NOW())', '<=', $recovery_end_day);
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
        $recovery_status = Arr::value($search, 'recovery_status');
        if ($recovery_status) {
            ////如果已估计中且估计时间到期，则跳入下个状态
            switch ($recovery_status){
                case RecoveryOrderService::RECOVERY_STATUS_TWENTY:
                    $twenty = RecoveryOrderService::RECOVERY_STATUS_TWENTY;
                    $d = date('Y-m-d H:i:s');
                    $builder->whereRaw(" and ( a.recovery_status = {$twenty} and a.offer_expire_time >= '{$d}' ) ");
                    break;
                case RecoveryOrderService::RECOVERY_STATUS_THIRTY:
                    $thirty = RecoveryOrderService::RECOVERY_STATUS_THIRTY;
                    $twenty = RecoveryOrderService::RECOVERY_STATUS_TWENTY;
                    $d = date('Y-m-d H:i:s');
                    $builder->whereRaw(" and ( a.recovery_status = {$thirty} or ( a.recovery_status = {$twenty} and a.offer_expire_time < '{$d}'  )) ");
                    break;
                default:
                    $builder->where('a.recovery_status', $recovery_status);
                    break;

            }
        }

        // 商品名称
        $product_name = Arr::value($search, 'product_name');
        if ($product_name) {
            $builder->where('a.product_name', $product_name);
        }

        // 品牌
        $brand_id = Arr::value($search, 'brand_id');
        if ($brand_id) {
            $builder->where('a.brand_id','in', $brand_id);
        }
        
        // 分类
        $category_id = Arr::value($search, 'category_id');
        if ($category_id) {
            $builder->where('a.category_id','in', $category_id);
        }

        // 修正多表连表时排序字段增加别名前缀
        $builder->orderAlias('a');

        return static::paginate($builder);
    }
    
    
    /* 获取列表*/
    public static function getCategoryAndBandIDs($attribute = array())
    {
       
        if (!empty($attribute) && is_array($attribute) && count($attribute) > 0) {
            extract($attribute);
        }
    
        $pdo = YDLib::getPDO('db_r');
        
        $fileds = " a.*";
        
        $sql = 'SELECT
        		   [*]
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a
		        WHERE
        		    a.is_del = 2
        		';
        if ($type == 'category') {
            $fileds = " a.category_id as id ";
            $sql .= " GROUP BY a.category_id ";
        } else if ($type == 'brand') {
            $fileds = " a.brand_id as id ";
            $sql .= " GROUP BY a.brand_id ";
        }
        $result = $pdo ->YDGetAll(str_replace("[*]", $fileds, $sql));
        
        $ids = '';
        if ($result) {
            $id = [];
            foreach ($result as $key => $value) {
                $id[$key] = $value['id'];
            }
            $ids = implode(',', $id);
        }
        return $ids;
    }

    /**
     * 条件查询
     */
    public static function findOneWhere($where)
    {
        $pdo = self::_pdo('db_r');
        return $pdo->clear()->select('*')->from(self::table())->where($where)->getRow();
    }
}