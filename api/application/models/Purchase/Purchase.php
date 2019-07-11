<?php
// +----------------------------------------------------------------------
// | 采购表
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少
// +----------------------------------------------------------------------
// | Author: 昌少 Date:2018/8/20 Time:15:29
// +----------------------------------------------------------------------
namespace Purchase;

use Assemble\Builder;
use Assemble\Support\Arr;
use Assemble\Support\Date;
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
        $builder->where('purchase.purchase_supplier_id', $auth ['supplier_id']);

        // 搜索查询条件
        // 采购编号搜索
        $purchaseId = Arr::value($search, 'code');
        if ($purchaseId) {
            $builder->where('purchase.id', $purchaseId);
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
        if ($deliverStart && $deliverEnd && $deliverStart <= $deliverEnd) {
            $builder->where('order_child.deliver_time', '>=', Date::startOfDay($deliverStart));
            $builder->where('order_child.deliver_time', '<=', Date::endOfDay($deliverEnd));
        }

        // 下单时间搜索
        $orderStart = Arr::value($search, 'order_start_time');
        $orderEnd = Arr::value($search, 'order_end_time');
        if ($orderStart && $orderEnd && $orderStart <= $orderEnd) {
            $builder->where('order_child.created_at', '>=', Date::startOfDay($deliverStart));
            $builder->where('order_child.created_at', '<=', Date::endOfDay($deliverEnd));
        }

        // 状态搜索
        $status = Arr::value($search, 'order_status');
        $pstatus = self::getStatus();
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
        }

        return $result;
    }

    public static function createPurchase($title, $name, $mobile, $province_id, $city_id, $area_id, $address, array $product, $pay_type = 'offline', $purchase_type = 1,$source_id=0)
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
            $product = ProductModel::find($item ['id'], [
                'channel_price'
            ]);
            if ($product->channel_price) {
                $total += bcmul($product->channel_price, $item ['num'], 2);
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
            list ($res, $err) = PurchaseProductModel::createPurchaseProduct($product ['id'], $product ['num'], $purchase->id, $product ['order_child_product_id']);
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
                        return PurchaseService::ORDER_COMPLETED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_61 :
                        return PurchaseService::RETURN_ORDER_REFUSED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_62 :
                        return PurchaseService::RETURN_ORDER_RECEIVED;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_70 :
                        return PurchaseService::RETURN_ORDER_COMPLETE;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_80 :
                        return PurchaseService::ORDER_CANCEL_MERCHANT;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_81 :
                        return PurchaseService::ORDER_CANCEL_MERCHANT;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_90 :
                        return PurchaseService::ORDER_CANCEL_MERCHANT;
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
                        return PurchaseService::PURCHASE_ORDER_STATUS_60;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_80 :
                        return PurchaseService::ORDER_CANCEL_MERCHANT;
                        break;
                    case PurchaseService::PURCHASE_ORDER_STATUS_90 :
                        return PurchaseService::ORDER_CANCEL_MERCHANT;
                        break;
                }
                break;
        }

        return '未知';
    }

    /**
     * 定义搜索订单状态
     * @return array
     */
    public static function getStatus()
    {
        return [
            10 => ['name' => '待审核', 'status' => [10]],
            20 => ['name' => '待付款', 'status' => [20]],
            21 => ['name' => '付款审核', 'status' => [21]],
            40 => ['name' => '待发货', 'status' => [40]],
            50 => ['name' => '待收货', 'status' => [50]],
            60 => ['name' => '已完成', 'status' => [60]],
            80 => ['name' => '取消', 'status' => [80, 90]],
        ];
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
}