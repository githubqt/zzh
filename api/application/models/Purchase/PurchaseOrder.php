<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/20 Time:15:31
// +----------------------------------------------------------------------


namespace Purchase;


class PurchaseOrderModel extends \BaseModel
{
    /**
     * 查询需要显示的列
     * @var array
     */
    public static $showColumns = [
        'id', 'created_at', 'status', 'pay_type',
    ];

    /**
     * 通过采购单ID获取订单
     * @param $purchase_id
     * @return array
     */
    public static function getOrderByPurchaseId($purchase_id)
    {
        $sql = "SELECT * FROM `" . self::getFullTable() . "` WHERE purchase_id = '{$purchase_id}' AND is_del = '2'";
        return self::newRead()->YDGetRow($sql);
    }

    /**
     * 更新订单状态
     * @param $order_no
     * @param $status
     * @return bool|\PDOStatement
     */
    public static function updateStatus($order_no, $status)
    {
        return self::newWrite()->update(self::table(), [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['order_no' => $order_no]);
    }

    /**
     * 更新汇款账号
     * @param $order_no
     * @param $account
     * @return bool|int
     */
    public static function updateRemittanceAccount($order_no, $account)
    {
        return self::newWrite()->update(self::table(), [
            'remittance_account' => $account,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['order_no' => $order_no]);
    }
}