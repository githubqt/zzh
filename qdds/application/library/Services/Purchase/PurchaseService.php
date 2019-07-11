<?php
// +----------------------------------------------------------------------
// | 采购服务类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/21 Time:14:54
// +----------------------------------------------------------------------
namespace Services\Purchase;

use Admin\AdminModel;
use Core\GoldPrice;
use Image\ImageModel;
use Product\ProductModel;
use Purchase\PurchaseOrderChildProductModel;
use Purchase\PurchaseProductModel;
use Services\BaseService;
use Common\SerialNumber;
use Purchase\PurchaseModel;
use Purchase\PurchaseOrderChildModel;
use Purchase\PurchaseOrderModel;
use Core\Express;
use Services\Stock\PurchaseStockService;
use Services\Stock\VoidStockService;
use Product\ProductChannelModel;

class PurchaseService extends BaseService
{
    /**
     * 错误提示
     * @var string
     */
    protected $error = '';
    /**
     * 保存提交的数据
     * @var array
     */
    protected $request = [];

    /**
     * 采购单模型对象
     * @var
     */
    protected $purchase;

    /**
     * 采购类型
     * @var int
     */
    protected $purchaseType = 1;

    /**
     * 采购单订单模型对象
     * @var
     */
    protected $purchaseOrder;

    /**
     * 采购单商品模型对象集合
     * @var
     */
    protected $purchaseProducts;

    /**
     * 采购订单流转状态
     *
     * 商户端：
     * ['待审核'=>10,'待付款'=>20,'待收货'=>21|30|40,'商户发货'=>41,'确认收货'=>50,'拒收'=>51,'完成'=>60,'退货完成'=>70,'已取消'=>81'关闭'=>80|90]
     *
     * 供应端：
     * ['待审核'=>10,'待审核'=>12,'待付款'=>20|21,'待拣货'=>30,'待发货'=>40,'退货中'=>41,'拒收'=>52,'待收货'=>50,'完成'=>60,'审核拒绝'=>61,'退货完成'=>70,'商户取消'=>80,'供应取消'=>90]
     *
     * 平台端：
     * ['拒绝审核'=>5,'待审核'=>10,'已审核'=>11,'待付款'=>20,'付款审核'=>21,'待拣货'=>30,'待发货'=>40,'退货中'=>41,'已发货'=>50,'拒收'=>51,'完成'=>60,'退货完成'=>70,'商户取消'=>80,'供应取消'=>90]
     *
     */
    const PURCHASE_ORDER_STATUS_5 = 5;//审核拒接   平台端
    const PURCHASE_ORDER_STATUS_9 = 9;
    const PURCHASE_ORDER_STATUS_10 = 10;
    const PURCHASE_ORDER_STATUS_11 = 11;//已审核     平台端
    const PURCHASE_ORDER_STATUS_12 = 12;//已审核     供应端
    const PURCHASE_ORDER_STATUS_20 = 20;
    const PURCHASE_ORDER_STATUS_21 = 21;
    const PURCHASE_ORDER_STATUS_30 = 30;
    const PURCHASE_ORDER_STATUS_40 = 40;
    const PURCHASE_ORDER_STATUS_41 = 41;//商户发货
    const PURCHASE_ORDER_STATUS_50 = 50;
    const PURCHASE_ORDER_STATUS_51 = 51;//拒收平台
    const PURCHASE_ORDER_STATUS_52 = 52;//拒收供应
    const PURCHASE_ORDER_STATUS_60 = 60;//已收货
    const PURCHASE_ORDER_STATUS_61 = 61;//审核拒接   供应端
    const PURCHASE_ORDER_STATUS_62 = 62;//已收货
    const PURCHASE_ORDER_STATUS_70 = 70;//退货完成 、已完成
    const PURCHASE_ORDER_STATUS_80 = 80;
    const PURCHASE_ORDER_STATUS_81 = 81;//已取消
    const PURCHASE_ORDER_STATUS_90 = 90;

    /**
     * 订单状态描述
     */
    const ORDER_AUDIT_PENDING = '待审核';
    const ORDER_PAYMENT_PENDING = '待付款';
    const ORDER_PAYMENT_AUDIT_PENDING = '付款审核';
    const ORDER_PICK_PENDING = '待拣货';
    const ORDER_DELIVER_PENDING = '待发货';
    const ORDER_RECEIPT_PENDING = '待收货';
    const ORDER_DELIVER_COMPLETED = '已发货';
    const ORDER_RECEIPTED = '已收货';
    const ORDER_COMPLETED = '已完成';
    const ORDER_CANCEL_MERCHANT = '取消'; //商户取消
    const ORDER_CANCEL = '取消';  // 取消
    /**
     * 退货订单状态
     *
     */
    const RETURN_ORDER_REFUSED = '审核拒绝';
    const RETURN_ORDER_CAN = '已审核';
    const RETURN_ORDER_COMPLETE = '已完成';
    const RETURN_ORDER_RECEIVED = '已收货';
    const RETURN_ORDER_OK = '退货完成';
    const RETURN_ORDER_REFUND = '退款中';
    const RETURN_ORDER_CARGO = '退货中';
    const RETURN_ORDER_REJECTION = '已拒收';
    const RETURN_ORDER_PART = '部份退货';
    const RETURN_ORDER_ALL = '全部退货';
    const RETURN_ORDER_DEMAND = '需求采购单';
    const RETURN_ORDER_PURCHASE = '采购进货单';

    /**
     * 状态码
     */
    const PURCHASE_ORDER_STATUS_CODE = [
        'status_5' => self::PURCHASE_ORDER_STATUS_5,
        'status_9' => self::PURCHASE_ORDER_STATUS_9,
        'status_10' => self::PURCHASE_ORDER_STATUS_10,
        'status_11' => self::PURCHASE_ORDER_STATUS_11,
        'status_12' => self::PURCHASE_ORDER_STATUS_12,
        'status_20' => self::PURCHASE_ORDER_STATUS_20,
        'status_21' => self::PURCHASE_ORDER_STATUS_21,
        'status_30' => self::PURCHASE_ORDER_STATUS_30,
        'status_40' => self::PURCHASE_ORDER_STATUS_40,
        'status_41' => self::PURCHASE_ORDER_STATUS_41,
        'status_50' => self::PURCHASE_ORDER_STATUS_50,
        'status_51' => self::PURCHASE_ORDER_STATUS_51,
        'status_52' => self::PURCHASE_ORDER_STATUS_52,
        'status_60' => self::PURCHASE_ORDER_STATUS_60,
        'status_61' => self::PURCHASE_ORDER_STATUS_61,
        'status_70' => self::PURCHASE_ORDER_STATUS_70,
        'status_80' => self::PURCHASE_ORDER_STATUS_80,
        'status_81' => self::PURCHASE_ORDER_STATUS_81,
        'status_90' => self::PURCHASE_ORDER_STATUS_90,
    ];

    /**
     * 可查快递的状态码
     */
    const CAN_QUERY_EXPRESS_STATUS = [
        self::PURCHASE_ORDER_STATUS_50,
        self::PURCHASE_ORDER_STATUS_70,
        self::PURCHASE_ORDER_STATUS_60,
    ];

    /**
     * 采购类型
     */
    const PURCHASE_TYPE_NORMAL = 1;
    const PURCHASE_TYPE_SALE = 2;
    const PURCHASE_TYPE_NEED = 3;
    const PURCHASE_TYPE_RECOVERY = 4;

    /**
     * 采购类型状态码
     */
    const PURCHASE_TYPES = [
        self::PURCHASE_TYPE_NORMAL => '普通采购',
        self::PURCHASE_TYPE_SALE => '供应采购',
        self::PURCHASE_TYPE_NEED => '需求采购',
        self::PURCHASE_TYPE_RECOVERY => '回收采购',
    ];

    /**
     * 保存请求的数据
     * @param array $request
     * @return $this
     */
    public function setRequest(array $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * 设置采购类型
     * @param int $type
     */
    public function setPurchaseType(int $type)
    {
        $this->purchaseType = $type;
    }

    /**
     * 创建采购单
     * @return bool
     */
    public function createPurchase()
    {
        // 开始事务
        $this->newWrite()->beginTransaction();
        try {
            //验证库存,处理供应价
            //上海金价
            $gold_price = GoldPrice::getGoldPrice();
            foreach ($this->request['product'] as &$item) {
                $product = ProductModel::find($item['id'], [
                    'stock','self_code','channel_price','channel_is_up','channel_up_price','weight'
                ]);
                if ($product->stock <= 0 || $product->stock < $item['num']) {
                    throw new \InvalidArgumentException("编号为{$product->self_code}的商品库存不足，创建采购单失败");
                }
                //处理供应价
                if ($product->channel_is_up == ProductModel::IS_UP_2) {
                    $product->channel_price = bcmul(bcadd($gold_price,$product->channel_up_price,2),$product->weight,2);
                }
                $item['channel_price'] = $product->channel_price;
            }
            //验证提交的参数
            $this->validRequestParams();
            list($this->purchase, $err) = PurchaseModel::createPurchase(
                $this->request['title'],
                $this->request['name'],
                $this->request['mobile'],
                $this->request['province_id'],
                $this->request['city_id'],
                $this->request['area_id'],
                $this->request['address'],
                $this->request['product'],
                $this->request['pay_type'],
                $this->purchaseType
            );
            if ($err) {
                throw new \InvalidArgumentException('创建采购单失败');
            }
            /**
             * 创建采购商品
             */
            list($this->purchaseProducts, $err) = PurchaseModel::createPurchaseProduct($this->purchase, $this->request['product']);
            if ($err) {
                throw new \InvalidArgumentException('创建采购单商品失败');
            }
            /**
             * 创建采购订单
             */
            list($this->purchaseOrder, $err) = $this->createPurchaseOrder();
            if ($err) {
                throw new \InvalidArgumentException('创建采购订单失败');
            }
            /**
             * 创建采购子订单
             */
            $this->createPurchaseOrderChild();
            /**
             * 锁库存
             */
            $auth = AdminModel::getCurrentLoginInfo();
            foreach ($this->request['product'] as $item) {
                $product = ProductModel::find($item['id']);
                $stock = new PurchaseStockService($product->toArray());
                $stock->setAdminId($auth['id']);
                $stock->setAdminName($auth['fullname']);
                $stock->setType(PurchaseStockService::LOG_TYPE_15);
                $stock->setLockNum($item['num']);
                $stock->lock();
            }

            return $this->newWrite()->commit();

        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }

    /**
     * 创建采购订单
     * @return array [$ret,$err]  $ret 返回模型对象; $err 错误标识 0 成功   1 失败
     */
    protected function createPurchaseOrder()
    {
        $result = PurchaseOrderModel::create([
            'order_no' => SerialNumber::createSN(SerialNumber::SN_ERP_PO),
            'order_original_amount' => $this->purchase->price,
            'order_actual_amount' => $this->purchase->price,
            'purchase_num' => $this->purchase->amount,
            'pay_type' => $this->purchase->pay_type,
            'status' => self::PURCHASE_ORDER_STATUS_10,
            'note' => '',
            'purchase_id' => $this->purchase->id,
        ]);

        if ($result->id) {
            return [$result, 0];
        }
        return [$result, 1];
    }

    /**
     * 创建采购订单子订单
     * @return bool
     */
    protected function createPurchaseOrderChild()
    {
        //拆分供应商订单
        $purchaseProducts = $this->splitSupplierOrder();
        foreach ($purchaseProducts as $supplier_id => $products) {
           
            $PurchaseOrderChildModel = PurchaseOrderChildModel::create([
                'supplier_id' => $supplier_id,
                'purchase_supplier_id' => $this->purchase->purchase_supplier_id,
                'purchase_id' => $this->purchase->id,
                'order_id' => $this->purchaseOrder->id,
                'order_no' => $this->purchaseOrder->order_no,
                'child_order_no' => SerialNumber::createSN(SerialNumber::SN_ERP_PC),
                'child_order_original_amount' => $this->totalChildOrderPrice($products),
                'child_order_actual_amount' => $this->totalChildOrderPrice($products),
                'purchase_num' => $this->totalChildOrderProductNum($products),
                'can_return_num' => $this->totalChildOrderReturnProductNum($products),
                'child_pay_type' => $this->purchase->pay_type,
                'child_status' => '10',
            ]);

            if ($PurchaseOrderChildModel->id) {
                foreach ($products as $product) {
                    list($ret, $err) = $this->createPurchaseOrderChildProduct(
                        $this->purchaseOrder->id,
                        $this->purchaseOrder->order_no,
                        $PurchaseOrderChildModel->child_order_no,
                        $product['id']
                    );
                    if ($err) {
                        throw new \InvalidArgumentException('创建采购子订单商品失败');
                    }
                }
                // 记录采购子订单日志
                purchaseTrackingLog(
                    $this->purchase->id,
                    $PurchaseOrderChildModel->child_order_no,
                    '生成子订单',
                    "子订单：{$PurchaseOrderChildModel->child_order_no} 生成成功"
                );
            } else {
                throw new \InvalidArgumentException('创建采购子订单失败');
            }
        }
    }

    /**
     * 创建采购单子订单商品
     * @param $order_id
     * @param $order_no
     * @param $child_order_no
     * @param $purchase_product_id
     * @return array
     */
    protected function createPurchaseOrderChildProduct($order_id, $order_no, $child_order_no, $purchase_product_id)
    {
        $result = PurchaseOrderChildProductModel::create([
            'order_id' => $order_id,
            'order_no' => $order_no,
            'child_order_no' => $child_order_no,
            'purchase_product_id' => $purchase_product_id,
        ]);
        if ($result->id) {
            return [$result, 0];
        }
        return [$result, 1];
    }

    /**
     * 子订单总价
     * @param array $products
     * @return float
     */
    protected function totalChildOrderPrice(array $products)
    {
        $total = 0;
        foreach ($products as $product) {
            $total += $product['price'];
        }
        return floatval($total);
    }

    /**
     * 子订单总价
     * @param array $products
     * @return float
     */
    protected function totalChildOrderPriceBuy(array $products)
    {
        $total = 0;
        foreach ($products as $product) {
            $total += $product['buy_price'];
        }
        return floatval($total);
    }

    /**
     * 子订单商品总数
     * @param array $products
     * @return int
     */
    protected function totalChildOrderProductNum(array $products)
    {
        $total = 0;
        foreach ($products as $product) {
            $total += $product['num'];
        }
        return intval($total);
    }
    
    /**
     * 子订单可退货商品总数
     * @param array $products
     * @return int
     */
    protected function totalChildOrderReturnProductNum(array $products)
    {
        $total = 0;
        foreach ($products as $product) {
            if ($product['is_return'] == '2') {
                $total += $product['num'];
            }
        }
        return intval($total);
    }

    /**
     * 拆分供应商订单
     * @return array
     */
    protected function splitSupplierOrder()
    {
        $products = $this->purchaseProducts;
        $suppliers = [];
        foreach ($products as $product) {
            $suppliers [] = $product->supplier_id;
        }
        $suppliers = array_unique($suppliers);
        $result = [];
        foreach ($suppliers as $supplier) {
            $i = 0;
            foreach ($products as $product) {
                if ($supplier == $product->supplier_id) {
                    $result[$supplier][$i] = $product;
                    $i++;
                }
            }
        }
        return $result;
    }

    /**
     * 验证提交的参数
     * @return bool
     * @throws \Exception
     */
    protected function validRequestParams()
    {
        $params = [
            'title', 'name', 'title', 'province_id', 'city_id', 'area_id', 'address', 'product', 'pay_type'
        ];
        foreach ($params as $param) {
            if (!isset($this->request[$param])) {
                throw new \Exception(" 参数 $param 缺失 ");
            }

            if ($param == 'product') {
                if (!is_array($this->request[$param])) {
                    throw new \Exception(" 参数 $param 必须是一个数组 ");
                }
            }
        }
        return true;
    }

    /**
     * 获取错误提示
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 商户对采购单进行查看操作
     * @param $purchase_id
     * @return array|bool
     */
    public function purchaseDetail($purchase_id)
    {
        $purchase = PurchaseModel::getPurchaseById($purchase_id);
        if (empty($purchase)) {
            return false;
        }
        $result = $purchase;

        // 子单
        $result['district_text'] = \AreaModel::getPca($result['province_id'], $result['city_id'], $result['area_id']);
        $result['remittance_images'] = ImageModel::getPurchasePayImages($purchase_id);
        $result['child_order'] = PurchaseOrderChildModel::getChildOrderProductByOrderNO($purchase['order_no']);
        return $result;
    }

    /**
     * 商户对采购单进行审核操作
     * @param $purchase_id
     * @return bool|\PDOStatement
     */
    public function purchaseAudit($purchase_id)
    {
        $purchase = PurchaseModel::getPurchaseById($purchase_id);
        if (empty($purchase)) {
            return false;
        }

        if ($purchase['status'] != self::PURCHASE_ORDER_STATUS_10) {
            $this->error = '当前状态是:' . PurchaseModel::showOrderStatusText($purchase['status']) . ",不允许该操作";
            return false;
        }

        return $this->handleAudit($purchase);
    }

    /**
     * 商户对采购单进行取消操作
     * @param $purchase_id
     * @return bool|\PDOStatement
     */
    public function purchaseCancel($purchase_id)
    {
        $purchase = PurchaseModel::getPurchaseById($purchase_id);
        if (empty($purchase)) {
            return false;
        }

        if (!in_array($purchase['status'], [
            self::PURCHASE_ORDER_STATUS_10,
            self::PURCHASE_ORDER_STATUS_20,
        ])) {
            $this->error = '当前状态是:' . PurchaseModel::showOrderStatusText($purchase['status']) . ",不允许该操作";
            return false;
        }

        return $this->handleCancel($purchase);
    }

    /**
     * 商户对采购单进行确认收货操作
     */
    public function purchaseComplete()
    {

    }

    /**
     * 商户对采购单进行删除操作
     * @param $purchase_id
     * @return bool
     */
    public function purchaseDelete($purchase_id)
    {
        $purchase = PurchaseModel::getPurchaseById($purchase_id);
        if (empty($purchase)) {
            return false;
        }

        if ($purchase['status'] != self::PURCHASE_ORDER_STATUS_80) {
            $this->error = '当前状态是:' . PurchaseModel::showOrderStatusText($purchase['status']) . ",不允许该操作";
            return false;
        }

        try {
            $result = $this->handleDelete($purchase_id);
            if (!$result) {
                throw new \Exception('删除失败');
            }

            // 记录采购子订单日志
            purchaseTrackingLog(
                $purchase['id'],
                '0',
                '删除采购单',
                "采购单:{$purchase['id']} 删除成功"
            );

            return $result;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }

    }

    /**
     * 商户对采购单进行支付操作
     * @param $purchase_id
     * @param $account
     * @param array $images
     * @return bool|\PDOStatement
     */
    public function purchasePay($purchase_id, $account, array $images)
    {
        $purchase = PurchaseModel::getPurchaseById($purchase_id);
        if (empty($purchase)) {
            return false;
        }

        if ($purchase['status'] != self::PURCHASE_ORDER_STATUS_20) {
            $this->error = '当前状态是:' . PurchaseModel::showOrderStatusText($purchase['status']) . ",不允许该操作";
            return false;
        }
        return $this->handlePay($purchase, $account, $images);
    }

    /**
     * 处理审核
     * @param array $purchase
     * @return bool|\PDOStatement
     */
    protected function handleAudit(array $purchase)
    {
        try {
            $result = $this->handleStatus($purchase['order_no'], self::PURCHASE_ORDER_STATUS_20);
            if (!$result) {
                throw new \Exception('取消失败');
            }

            // 记录采购子订单日志
            purchaseTrackingLog(
                $purchase['id'],
                '0',
                '采购单审核',
                "采购单:{$purchase['id']} 审核成功"
            );

            return $result;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * 处理取消
     * @param array $purchase
     * @return bool|\PDOStatement
     */
    protected function handleCancel(array $purchase)
    {
        try {
            $result = $this->handleStatus($purchase['order_no'], self::PURCHASE_ORDER_STATUS_80);
            if (!$result) {
                throw new \Exception('取消失败');
            }

            //解锁库存
            $products = PurchaseProductModel::getProductByPurchaseId($purchase['id']);
            $auth = AdminModel::getCurrentLoginInfo();
            foreach ($products as $item) {
                $product = ProductModel::find($item['product_id']);
                $stock = new PurchaseStockService($product->toArray());
                $stock->setAdminId($auth['id']);
                $stock->setAdminName($auth['fullname']);
                $stock->setType(PurchaseStockService::LOG_TYPE_16);
                $stock->setLockNum($item['num']);
                $stock->revert();
            }

            // 记录采购子订单日志
            purchaseTrackingLog(
                $purchase['id'],
                '0',
                '采购单取消',
                "采购单:{$purchase['id']} 取消成功"
            );

            return $result;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * 更新主订单及所有子订单状态
     * @param $order_no
     * @param $status
     * @return bool|\PDOStatement
     */
    protected function handleStatus($order_no, $status)
    {
        $this->newWrite()->beginTransaction();
        try {
            // 更新主订单状态
            $result = PurchaseOrderModel::updateStatus($order_no, $status);
            if (!$result) {
                throw new \Exception('更新主单状态失败');
            }
            // 更新主单下所有子订单状态
            $result = PurchaseOrderChildModel::updateStatus($order_no, $status);
            if (!$result) {
                throw new \Exception('更新子订单状态');
            }
            // 其他操作
            $this->newWrite()->commit();
            return $result;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }

    /**
     * 更新某一子订单状态
     * @param $child_order_no
     * @param $status
     * @return bool|\PDOStatement
     */
    protected function handleChildStatus($child_order_no, $status)
    {
        $this->newWrite()->beginTransaction();
        try {
            // 更新某一子订单状态
            $result = PurchaseOrderChildModel::updateStatusByChildOrderNo($child_order_no, $status);
            if (!$result) {
                throw new \Exception('更新子订单状态');
            }
            // 其他操作
            $this->newWrite()->commit();
            return $result;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }

    /**
     * 支付汇款操作
     * @param array $purchase
     * @param $account
     * @param array $images
     * @return bool
     */
    protected function handlePay(array $purchase, $account, array $images)
    {
        $this->newWrite()->beginTransaction();
        try {
            // 更新汇款图片信息
            $result = ImageModel::add('purchase_pay', $purchase['id'], $images);
            if (!$result) {
                throw new \Exception('更新汇款图片信息失败');
            }
            // 更新汇款信息
            $result = PurchaseOrderModel::updateRemittanceAccount($purchase['order_no'], $account);
            if (!$result) {
                throw new \Exception('更新汇款信息失败');
            }
            $result = $this->handleStatus($purchase['order_no'], self::PURCHASE_ORDER_STATUS_21);
            if (!$result) {
                throw new \Exception('支付汇款提交操作失败');
            }

            // 记录采购日志
            purchaseTrackingLog(
                $purchase['id'],
                '0',
                '采购单汇款',
                "采购单:{$purchase['id']} 提交线下汇款凭证"
            );

            $this->newWrite()->commit();
            return $result;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }

    /**
     * 商户对子单进行收货操作
     * @param $purchase_id
     * @param $child_order_no
     * @return bool|\PDOStatement
     */
    public function purchaseReceipt($purchase_id, $child_order_no)
    {
        $this->purchase = PurchaseModel::getPurchaseById($purchase_id);
        if (empty($this->purchase)) {
            return false;
        }

        $child_order = PurchaseOrderChildModel::getChildOrderByChildOrderNO($child_order_no);

        if ($child_order['child_status'] != self::PURCHASE_ORDER_STATUS_50) {
            $this->error = '当前状态是:' . PurchaseModel::showOrderStatusText($child_order['child_status']) . ",不允许该操作";
            return false;
        }

        $return = $this->handleReceipt($child_order_no);

        $this->receivingCompleted($this->purchase);

        if ($return) {
            // 记录采购日志
            purchaseTrackingLog(
                $purchase_id,
                $child_order_no,
                '确认收货',
                "子订单：{$child_order_no} 确认收货完成"
            );
        }
        return $return;
    }

    /**
     * 处理确认收货
     * @param $child_order_no
     * @return bool|\PDOStatement
     */
    protected function handleReceipt($child_order_no)
    {
        try {

            $result = PurchaseOrderChildModel::updateStatusByChildOrderNo($child_order_no, self::PURCHASE_ORDER_STATUS_60);
            if (!$result) {
                throw new \Exception('确认收货失败');
            }

            $admin = AdminModel::getCurrentLoginInfo();
            // 库存处理
            switch ($this->purchase['purchase_type']) {
                case self::PURCHASE_TYPE_SALE: // 0库存订单采购
                    // 库存处理
                    $products = PurchaseOrderChildProductModel::getProductByChildOrderNO($child_order_no);
                    foreach ($products as $item) {
                        $channel_product = ProductChannelModel::findOneWhere(['supplier_id' => $this->purchase['purchase_supplier_id'],'product_id' => $item['product_id']]);
                        $stock = new VoidStockService($channel_product);
                        $stock->setAdminId($admin['id']);
                        $stock->setAdminName($admin['fullname']);
                        $stock->setType(PurchaseStockService::LOG_TYPE_24);
                        $stock->setStockNum($item['num']);
                        $stock->in();
                    }
                    break;
                case self::PURCHASE_TYPE_NEED: // 需求采购
                    // 库存处理
                    // TODO
                    break;
                case self::PURCHASE_TYPE_NORMAL:  // 普通采购
                default:
                    // 库存处理
                    $products = PurchaseOrderChildProductModel::getProductByChildOrderNO($child_order_no);
                    foreach ($products as $item) {
                        $product = json_decode($item['archives'],true);
                        $PurchaseStockService = new PurchaseStockService($product);
                        $PurchaseStockService->setStockNum($item['num']);
                        $PurchaseStockService->setAdminId($admin['id']);
                        $PurchaseStockService->setAdminName($admin['fullname']);
                        $PurchaseStockService->setSupplierId($admin['supplier_id']);
                        $PurchaseStockService->setType(PurchaseStockService::LOG_TYPE_18);
                        $PurchaseStockService->in();
                    }
                    break;
            }
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * 查询采购快递
     * @param $child_order_no
     * @return array|bool
     */
    public function queryPurchaseExpress($child_order_no)
    {
        $order = PurchaseOrderChildModel::getExpressByChildOrderNo($child_order_no);
        if (false == in_array($order['child_status'], self::CAN_QUERY_EXPRESS_STATUS)) {
            $this->error = '当前状态是:' . PurchaseModel::showOrderStatusText($order['child_status']) . ",不允许该操作";
            return false;
        }

        if (!$order['express_pinyin'] || !$order['express_no']) {
            $this->error = "尚未填写快递单号";
            return false;
        }

        // 查询快递
        $express = new Express();
        $data = $express->searchExpress($order['express_pinyin'], $order['express_no']);

        if (is_array($data) && isset($data['state']) && $data['state']) {
            $data['state_text'] = isset(Express::STATES[$data['state']]) ? Express::STATES[$data['state']] : '转单';
            return array_merge($data, $order);
        }
        $this->error = "物流单暂无结果或查询异常";
        return false;
    }


    /**
     * 所有子单收货完成后，更新主单状态为完成
     * @param array $purchase
     */
    protected function receivingCompleted(array $purchase)
    {
        // 总子订单数
        $childCount = PurchaseOrderChildModel::getChildOrderCount($purchase['order_no']);
        // 总已完成订单数
        $deliverChildCount = PurchaseOrderChildModel::getChildOrderCount($purchase['order_no'], [
            self::PURCHASE_ORDER_STATUS_60,
        ]);

        if ($childCount == $deliverChildCount) {
            PurchaseOrderModel::updateStatus($purchase['order_no'], self::PURCHASE_ORDER_STATUS_60);
        }
    }

    /**
     * 所有子单确认收货完成后，更新主单状态为完成
     * @param array $purchase
     */
    protected function orderCompleted(array $purchase)
    {
        // 总子订单数
        $childCount = PurchaseOrderChildModel::getChildOrderCount($purchase['order_no']);
        // 总已完成订单数
        $deliverChildCount = PurchaseOrderChildModel::getChildOrderCount($purchase['order_no'], [
            self::PURCHASE_ORDER_STATUS_60,
        ]);

        if ($childCount == $deliverChildCount) {
            PurchaseOrderModel::updateStatus($purchase['order_no'], self::PURCHASE_ORDER_STATUS_60);
        }
    }

    /**
     * 删除处理
     * @param $purchase_id
     * @return bool
     */
    protected function handleDelete($purchase_id)
    {
        $this->newWrite()->beginTransaction();
        try {
            $result = PurchaseModel::deleteRelated($purchase_id);
            if (!$result) {
                throw new \Exception('删除采购失败');
            }
            $this->newWrite()->commit();
            return true;
        } catch (\Exception $exception) {
            $this->newWrite()->rollback();
            return false;
        }

    }

    /**
     * 定义搜索订单状态
     * @return array
     */
    public static function getStatus()
    {
        return [
            PurchaseService::PURCHASE_ORDER_STATUS_10 =>
                [
                    'name' => PurchaseService::ORDER_AUDIT_PENDING,
                    'status' => [PurchaseService::PURCHASE_ORDER_STATUS_10]],
            PurchaseService::PURCHASE_ORDER_STATUS_20 =>
                [
                    'name' => PurchaseService::ORDER_PAYMENT_PENDING,
                    'status' => [PurchaseService::PURCHASE_ORDER_STATUS_20]],
            PurchaseService::PURCHASE_ORDER_STATUS_21 =>
                [
                    'name' => PurchaseService::ORDER_PAYMENT_AUDIT_PENDING,
                    'status' => [PurchaseService::PURCHASE_ORDER_STATUS_21]],
            PurchaseService::PURCHASE_ORDER_STATUS_40 =>
                [
                    'name' => PurchaseService::ORDER_DELIVER_PENDING,
                    'status' => [PurchaseService::PURCHASE_ORDER_STATUS_40]],
            PurchaseService::PURCHASE_ORDER_STATUS_50 =>
                [
                    'name' => PurchaseService::ORDER_RECEIPT_PENDING,
                    'status' => [PurchaseService::PURCHASE_ORDER_STATUS_50]],
            PurchaseService::PURCHASE_ORDER_STATUS_60 =>
                [
                    'name' => PurchaseService::ORDER_RECEIPTED,
                    'status' => [PurchaseService::PURCHASE_ORDER_STATUS_60]],
            PurchaseService::PURCHASE_ORDER_STATUS_70 =>
                [
                    'name' => PurchaseService::ORDER_COMPLETED,
                    'status' => [PurchaseService::PURCHASE_ORDER_STATUS_70]],
            PurchaseService::PURCHASE_ORDER_STATUS_80 =>
                [
                    'name' => PurchaseService::ORDER_CANCEL,
                    'status' => [
                        PurchaseService::PURCHASE_ORDER_STATUS_80,
                        PurchaseService::PURCHASE_ORDER_STATUS_90
                    ]
                ],
        ];
    }


}