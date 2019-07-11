<?php
// +----------------------------------------------------------------------
// | 采购表
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少
// +----------------------------------------------------------------------
// | Author: 昌少 Date:2018/8/20 Time:15:29
// +----------------------------------------------------------------------
namespace Purchase;

use Assemble\Builder;
use Assemble\Support\Arr;
use Assemble\Support\Date;
use Core\GoldPrice;
use Product\ProductModel;
use Services\Purchase\PurchaseService;

class PurchaseModel extends \BaseModel
{

    /**
     * 线下支付
     */
    const PAY_OFFLINE = 'offline';

    /**
     * 微信支付
     */
    const PAY_WEIXIN = 'weixin';

    /**
     * 支付宝支付
     */
    const PAY_ALIPAY = 'alipay';

    /**
     * 银联支付
     */
    const PAY_UNINOPAY = 'unionpay';


    /**
     * 支付类型
     */
    const PAY_TYPES = [
        self::PAY_OFFLINE => '线下支付',
        self::PAY_WEIXIN => '微信支付',
        self::PAY_ALIPAY => '支付宝支付',
        self::PAY_UNINOPAY => '银联支付',
    ];

    /**
     * 待审核
     */
    const ORDER_STATUS_PENDING = 10;

    /**
     * 查询需要显示的列
     *
     * @var array
     */
    public static $showColumns = [
        'id',
        'title',
        'pay_type',
        'purchase_type',
        'name',
        'mobile',
        'province_id',
        'city_id',
        'area_id',
        'created_at'
    ];

    public static function getList(array $search = [])
    {
        $builder = new Builder ();

        $auth = self::auth();
        // 采购单子订单表
        $purchaseOrderChildColumns = PurchaseOrderChildModel::aliasColumn('order_child');
        // 采购单订单表
        $purchaseOrderColumns = PurchaseOrderModel::aliasColumn('main', true);
        // 采购单表
        $purchaseColumns = self::aliasColumn('purchase', true);

        $builder->select(array_merge($purchaseOrderChildColumns, $purchaseOrderColumns, $purchaseColumns));

        // 表和表连接语句
        $form = sprintf(" %s order_child 
                LEFT JOIN %s main ON order_child.order_id = main.id 
                LEFT JOIN %s purchase ON purchase.id = main.purchase_id  ",
            PurchaseOrderChildModel::getFullTable(),
            PurchaseOrderModel::getFullTable(), self::getFullTable());

        $builder->from($form);
        // 必要查询条件
        $builder->where('order_child.is_del', self::DELETE_SUCCESS);

        //采购单类型
        $is_channel = Arr::value($search, 'is_channel');
        if (!$is_channel) {
            $builder->where('purchase.purchase_supplier_id', $auth ['supplier_id']);
        } else {
            $builder->where('order_child.supplier_id', $auth['supplier_id']);
        }

        // 搜索查询条件
        // 采购编号搜索
        $purchaseId = Arr::value($search, 'code');
        if ($purchaseId) {
            $builder->where('main.order_no', $purchaseId);
        }

        // 采购名称搜索
        $title = Arr::value($search, 'title');
        if ($title) {
            $builder->where('purchase.title', 'like', "%{$title}%");
        }

        // 姓名搜索
        $name = Arr::value($search, 'name');
        $name and $builder->where('purchase.name', 'like', "%$name%");

        // 手机号搜索
        $mobile = Arr::value($search, 'mobile');
        $mobile and $builder->where('purchase.mobile', $mobile);

        // 省市区街道搜索
        $provinceId = Arr::value($search, 'province_id');
        $provinceId and $builder->where('purchase.province_id', $provinceId);
        $cityId = Arr::value($search, 'city_id');
        $cityId and $builder->where('purchase.city_id', $cityId);
        $areaId = Arr::value($search, 'area_id');
        $areaId and $builder->where('purchase.area_id', $areaId);
        // $streetId = Arr::value($search, 'street_id');
        // $streetId and $builder->where('purchase.street_id', $streetId);

        // 发货时间搜索
        $deliverStart = Arr::value($search, 'deliver_start_time');
        $deliverEnd = Arr::value($search, 'deliver_end_time');
        if ($deliverStart) {
            $builder->where('order_child.delivery_time', '>=', Date::startOfDay($deliverStart));
        }
        if ($deliverEnd) {
            $builder->where('order_child.delivery_time', '<=', Date::endOfDay($deliverEnd));
        }

        // 下单时间搜索
        $orderStart = Arr::value($search, 'order_start_time');
        $orderEnd = Arr::value($search, 'order_end_time');
        if ($orderStart) {
            $builder->where('order_child.created_at', '>=', Date::startOfDay($orderStart));
        }
        if ($orderEnd) {
            $builder->where('order_child.created_at', '<=', Date::endOfDay($orderEnd));
        }
        
        // 状态搜索
        $status = Arr::value($search, 'order_status');
        $pstatus = PurchaseService::getStatus();
        if (isset($pstatus[$status])) {
            $status and $builder->where('order_child.child_status', 'in', $pstatus[$status]['status']);
        }

        // 状态搜索
        $payType = Arr::value($search, 'pay_type');
        $payType and $builder->where('purchase.pay_type', $payType);

        // 快递号搜索
        $expressNo = Arr::value($search, 'express_no');
        $expressNo and $builder->where('order_child.express_no', $expressNo);

        // 修正多表连表时排序字段增加别名前缀，单表不需要此操作
        $builder->orderAlias('order_child');
        $result = static::paginate($builder);
        foreach ($result ['rows'] as $key => $item) {
            $result ['rows'] [$key] ['main_order_status_text'] = self::showOrderStatusText($item ['main_status']);
            $result ['rows'] [$key] ['child_order_status_text'] = self::showOrderStatusText($item ['child_status']);
            $result ['rows'] [$key] ['pay_type_text'] = self::PAY_TYPES [$item ['main_pay_type']];
            $is_channel = Arr::value($search, 'is_channel');
            if (!$is_channel) {
                // 已退货总数: 已退+正在退的
                $result ['rows'] [$key] ['total_return_num'] = 0;
                // 是否有正在退货中的退货单
                $result ['rows'] [$key] ['has_return_order'] = 0;
                // 是否还有可退商品
                $result ['rows'] [$key] ['is_can_return'] = 0;
                //普通采购单判断是否可退货
                if ($item['purchase_purchase_type'] == PurchaseService::PURCHASE_TYPE_NORMAL) {
                    // 已退货总数: 已退+正在退的
                    $total_return_num = PurchaseReturnModel::getTotalReturnNumByChildOrderNO($item ['child_order_no']);
                    $result ['rows'] [$key] ['total_return_num'] = $total_return_num;
                    // 是否有正在退货中的退货单
                    $result ['rows'] [$key] ['has_return_order'] = count(PurchaseReturnModel::getReturnOrderByChildOrderNO($item ['child_order_no']));
                    // 是否还有可退商品
                    $is_can_return = isset($item ['can_return_num']) ? $item ['can_return_num'] - $total_return_num : $item ['purchase_num'] - $total_return_num;
                    $result ['rows'] [$key] ['is_can_return'] = $is_can_return > 0 ? 1 : 0;
                }
            }
        }
        return $result;
    }

    public static function createPurchase($title, $name, $mobile, $province_id, $city_id, $area_id, $address, array $product, $pay_type = 'offline', $purchase_type = 1, $source_id = 0)
    {
        $auth = self::auth();
        $Purchase = new self ();
        $Purchase->title = $title;
        $Purchase->name = $name;
        $Purchase->mobile = $mobile;
        $Purchase->province_id = $province_id;
        $Purchase->city_id = $city_id;
        $Purchase->area_id = $area_id;
        $Purchase->address = ( string )$address;
        $Purchase->pay_type = $pay_type;
        $Purchase->purchase_type = $purchase_type;
        $Purchase->purchase_supplier_id = $auth ['supplier_id'];
        $Purchase->admin_id = $auth ['id'];
        $Purchase->admin_name = $auth ['fullname'];
        $Purchase->amount = self::getTotalAmount($product);
        $Purchase->price = self::getTotalPrice($product);
        $Purchase->source_id = $source_id;
        $result = $Purchase->save();
        if ($result) {
            return [
                $Purchase,
                0
            ];
        } else {
            return [
                $Purchase,
                1
            ];
        }
    }

    public static function createRecoveryPurchase($title, $name, $mobile, $province_id, $city_id, $area_id, $address, array $product, $pay_type = 'offline', $purchase_type = 1, $source_id = 0, $purchase_supplier_id = '0')
    {
        $auth = self::auth();
        $Purchase = new self ();
        $Purchase->title = $title;
        $Purchase->name = $name;
        $Purchase->mobile = $mobile;
        $Purchase->province_id = $province_id;
        $Purchase->city_id = $city_id;
        $Purchase->area_id = $area_id;
        $Purchase->address = ( string )$address;
        $Purchase->pay_type = $pay_type;
        $Purchase->purchase_type = $purchase_type;
        $Purchase->purchase_supplier_id = $purchase_supplier_id;//回收采购渠道商id
        $Purchase->admin_id = $auth ['id'];
        $Purchase->admin_name = $auth ['fullname'];
        $Purchase->amount = self::getTotalAmount($product);
        $Purchase->price = self::getTotalPrice($product);
        $Purchase->buy_price = self::getTotalSalePrice($product);//回收采购渠道展示价格
        $Purchase->source_id = $source_id;
        $result = $Purchase->save();
        if ($result) {
            return [
                $Purchase,
                0
            ];
        } else {
            return [
                $Purchase,
                1
            ];
        }
    }

    /**
     * 总数量计算
     *
     * @param array $products
     * @return int
     */
    public static function getTotalAmount(array $products)
    {
        $total = 0;
        foreach ($products as $product) {
            $total += ( int )$product ['num'];
        }
        return $total;
    }

    /**
     * 总价计算
     *
     * @param array $products
     * @return float
     */
    public static function getTotalPrice(array $products)
    {
        $total = 0;
        foreach ($products as $item) {
            if ($item['channel_price']) {
                $total += bcmul($item['channel_price'], $item ['num'], 2);
            }
        }
        return floatval($total);
    }

    /**
     * 总价计算
     *
     * @param array $products
     * @return float
     */
    public static function getTotalSalePrice(array $products)
    {
        $total = 0;
        foreach ($products as $item) {
            if ($item['max_channel_price']) {
                $total += bcmul($item['max_channel_price'], $item ['num'], 2);
            }
        }
        return floatval($total);
    }

    /**
     * 创建采购商品
     *
     * @param
     *            $purchase
     * @param array $products
     * @return array
     */
    public static function createPurchaseProduct($purchase, array $products)
    {
        $count = 0;
        $return = [];
        foreach ($products as $product) {
            list ($res, $err) = PurchaseProductModel::createPurchaseProduct($product ['id'], $product ['num'], $product ['channel_price'], $purchase->id);
            if ($err === 0) {
                $return [] = $res;
            }
            $count++;
        }
        if ($count === count($return)) {
            return [
                $return,
                0
            ];
        } else {
            return [
                $return,
                1
            ];
        }
    }

    /**
     * 创建回收采购商品
     *
     * @param
     *            $purchase
     * @param array $products
     * @return array
     */
    public static function createRecoveryPurchaseProduct($purchase, array $products)
    {
        $count = 0;
        $return = [];
        foreach ($products as $product) {
            list ($res, $err) = PurchaseProductModel::createRecoveryPurchaseProduct($product ['id'], $product ['num'], $purchase->id);
            if ($err === 0) {
                $return [] = $res;
            }
            $count++;
        }
        if ($count === count($return)) {
            return [
                $return,
                0
            ];
        } else {
            return [
                $return,
                1
            ];
        }
    }

    public static function createPurchaseOrder()
    {
    }

    public static function getPurchaseById($purchase_id)
    {
        $purchaseColumns = [
            'p.id',
            'p.purchase_supplier_id',
            'p.admin_id',
            'p.admin_name',
            'p.title',
            'p.`name`',
            'p.mobile',
            'p.province_id',
            'p.city_id',
            'p.area_id',
            'p.address',
            'p.pay_type',
            'p.purchase_type',
            'p.amount',
            'p.price',
            'p.created_at'
        ];
        $orderColumns = [
            'o.id as order_id ',
            'o.order_no',
            ' o.order_original_amount',
            'o.order_actual_amount',
            'o.purchase_num',
            ' o.`status`',
            ' o.note',
            'o.remittance_account'
        ];
        $builder = new Builder ();
        $builder->select(array_merge($purchaseColumns, $orderColumns));
        $form = sprintf("`%s` p LEFT JOIN `%s` o ON p.id = o.purchase_id", self::getFullTable(), PurchaseOrderModel::getFullTable());
        $builder->from($form);
        $builder->where('p.id', $purchase_id);
        $builder->where('p.is_del', self::DELETE_SUCCESS);
        $sql = $builder->showSql() ['query'];
        $row = self::newWrite()->YDGetRow($sql);
        if (!empty($row)) {
            $row ['pay_type_text'] = self::PAY_TYPES [$row ['pay_type']];
            $row ['order_status_text'] = self::showOrderStatusText($row ['status']);
        }
        return $row;
    }

    /**
     * 返回订单状态
     *
     * @param
     *            $order_status
     * @return int|string
     */
    public static function showOrderStatusText($order_status)
    {
        $auth = self::auth();
        switch ($auth ['type']) {
            case 1 : // 平台
                switch ($order_status) {
                    case PurchaseService::PURCHASE_ORDER_STATUS_10 :
                        return PurchaseService::ORDER_AUDIT_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_20 :
                        return PurchaseService::ORDER_PAYMENT_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_21 :
                        return PurchaseService::ORDER_PAYMENT_AUDIT_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_30 :
                        return PurchaseService::ORDER_PICK_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_40 :
                        return PurchaseService::ORDER_DELIVER_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_50 :
                        return PurchaseService::ORDER_DELIVER_COMPLETED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_60 :
                        return PurchaseService::ORDER_RECEIPTED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_70 :
                        return PurchaseService::ORDER_COMPLETED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_80 :
                        return PurchaseService::ORDER_DELIVER_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_90 :
                        return PurchaseService::ORDER_DELIVER_PENDING;
                        break;
                }
                break;
            case 2 : // 商户
                switch ($order_status) {
                    case PurchaseService::PURCHASE_ORDER_STATUS_5 :
                        return PurchaseService::RETURN_ORDER_REFUSED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_9 :
                        return PurchaseService::RETURN_ORDER_CARGO;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_10 :
                        return PurchaseService::ORDER_AUDIT_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_11 :
                        return PurchaseService::RETURN_ORDER_CAN;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_12 :
                        return PurchaseService::RETURN_ORDER_CAN;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_20 :
                        return PurchaseService::ORDER_PAYMENT_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_21 :
                        return PurchaseService::ORDER_PAYMENT_AUDIT_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_30 :
                        return PurchaseService::ORDER_DELIVER_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_40 :
                        return PurchaseService::ORDER_DELIVER_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_41 :
                        return PurchaseService::ORDER_DELIVER_COMPLETED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_50 :
                        return PurchaseService::ORDER_RECEIPT_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_60 :
                        return PurchaseService::ORDER_RECEIPTED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_70 : // 采购完成
                        return PurchaseService::ORDER_COMPLETED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_61 :
                        return PurchaseService::RETURN_ORDER_REFUSED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_62 :
                        return PurchaseService::RETURN_ORDER_RECEIVED;
                        break;
//                    case PurchaseService::PURCHASE_ORDER_STATUS_70 : // 退货完成
//                        return PurchaseService::RETURN_ORDER_COMPLETE;
//                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_80 :
                        return PurchaseService::ORDER_CANCEL;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_81 :
                        return PurchaseService::ORDER_CANCEL;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_90 :
                        return PurchaseService::ORDER_CANCEL;
                        break;
                }
                break;
            case 3 : // 供应商
                switch ($order_status) {
                    case PurchaseService::PURCHASE_ORDER_STATUS_10 :
                        return PurchaseService::ORDER_AUDIT_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_20 :
                        return PurchaseService::ORDER_PAYMENT_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_21 :
                        return PurchaseService::ORDER_PAYMENT_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_30 :
                        return PurchaseService::ORDER_PICK_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_40 :
                        return PurchaseService::ORDER_DELIVER_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_50 :
                        return PurchaseService::ORDER_RECEIPT_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_60 :
                        return PurchaseService::ORDER_RECEIPTED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_70 :
                        return PurchaseService::ORDER_COMPLETED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_80 :
                        return PurchaseService::ORDER_DELIVER_PENDING;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_90 :
                        return PurchaseService::ORDER_DELIVER_PENDING;
                        break;
                }
                break;
            case 4 : // 渠道
                switch ($order_status) {
                    case PurchaseService::PURCHASE_ORDER_STATUS_10 :
                        return PurchaseService::PURCHASE_ORDER_STATUS_10;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_20 :
                        return PurchaseService::PURCHASE_ORDER_STATUS_20;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_21 :
                        return PurchaseService::PURCHASE_ORDER_STATUS_21;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_30 :
                        return PurchaseService::PURCHASE_ORDER_STATUS_30;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_40 :
                        return PurchaseService::PURCHASE_ORDER_STATUS_40;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_50 :
                        return PurchaseService::PURCHASE_ORDER_STATUS_50;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_60 :
                        return PurchaseService::ORDER_RECEIPTED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_70 :
                        return PurchaseService::ORDER_COMPLETED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_80 :
                        return PurchaseService::ORDER_CANCEL;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_90 :
                        return PurchaseService::ORDER_CANCEL;
                        break;
                }
                break;
        }

        return '未知';
    }


    /**
     * 删除采购单相关
     * @param $purchaseId
     * @return bool
     */
    public static function deleteRelated($purchaseId)
    {
        self::newWrite()->beginTransaction();
        try {

            $update = "`is_del`='1',`deleted_at`='" . date('Y-m-d H:i:s') . "'";

            $purchase = self::getPurchaseById($purchaseId);

            if (empty($purchase)) {
                throw new \Exception('采购单不存在');
            }

            $childOrders = PurchaseOrderChildModel::getChildOrderByOrderNO($purchase['order_no']);

            foreach ($childOrders as $order) {
                $sql = "UPDATE `" . PurchaseOrderChildProductModel::getFullTable() . "` SET {$update} WHERE (`child_order_no`='{$order['child_order_no']}')";
                self::newWrite()->YDExecute($sql);
            }

            $sqls = [
                [
                    'sql' => "UPDATE `" . self::getFullTable() . "` SET {$update} WHERE (`id`='{$purchaseId}')",
                    'msg' => '删除采购单失败'
                ],
                [
                    'sql' => "UPDATE `" . PurchaseOrderModel::getFullTable() . "` SET {$update} WHERE (`purchase_id`='{$purchaseId}')",
                    'msg' => '删除采购单主单失败'
                ],
                [
                    'sql' => "UPDATE `" . PurchaseOrderChildModel::getFullTable() . "` SET {$update} WHERE (`purchase_id`='{$purchaseId}')",
                    'msg' => '删除采购单子单失败'
                ],
                [
                    'sql' => "UPDATE `" . PurchaseProductModel::getFullTable() . "` SET {$update} WHERE (`purchase_id`='{$purchaseId}')",
                    'msg' => '删除采购单商品失败'
                ],
            ];

            foreach ($sqls as $sql) {
                $result = self::newWrite()->YDExecute($sql['sql']);
                if (!$result) {
                    throw new \Exception($sql['msg']);
                }
            }

            self::newWrite()->commit();
            return true;
        } catch (\Exception $exception) {
            self::newWrite()->rollback();
            return false;
        }
    }

    /**
     * 获取信息
     */
    public static function getInfoBYSourceID($source_id, $purchase_type = '2')
    {
        $sql = "SELECT * FROM `" . self::getFullTable() . "` WHERE is_del = '2' AND source_id = '{$source_id}' AND purchase_type = '{$purchase_type}'";
        return self::newRead()->YDGetRow($sql);
    }

}