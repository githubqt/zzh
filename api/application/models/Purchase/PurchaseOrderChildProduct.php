<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/20 Time:15:33
// +----------------------------------------------------------------------


namespace Purchase;


use Assemble\Builder;

class PurchaseOrderChildProductModel extends \BaseModel
{
    /**
     * 查询需要显示的列
     * @var array
     */
    public static $showColumns = [
        'id', 'child_order_no', 'child_status',
        'delivery_time', 'created_at', 'express_no',
    ];

    public static function getProductByChildOrderNO($child_order_no)
    {
        $columns1 = [];
        $columns2 = [
            'b.product_id', 'b.self_code', 'b.custom_code', 'b.product_name', 'b.product_cover', 'b.category_name',
            'b.category_name', 'b.brand_name', 'b.introduction', 'b.num','b.market_price', 'b.channel_price', 'b.price',
        ];
        $builder = new Builder();
        $builder->select(array_merge($columns1, $columns2));
        $form = sprintf("`%s` a LEFT JOIN `%s` b ON a.purchase_product_id = b.id", self::getFullTable(), PurchaseProductModel::getFullTable());
        $builder->from($form);
        $builder->where('a.child_order_no', $child_order_no);
        $builder->where('b.is_del', self::DELETE_SUCCESS);
        $sql = $builder->showSql()['query'];
        $lists = self::newRead()->YDGetAll($sql);

        foreach ($lists as $k => $v) {
            $lists[$k]['img_url'] = HOST_FILE . ltrim($v['product_cover'], '/');
        }
        return $lists;
    }
}