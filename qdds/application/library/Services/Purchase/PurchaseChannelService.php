<?php
// +----------------------------------------------------------------------
// | 渠道采购服务类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/27 Time:11:24
// +----------------------------------------------------------------------


namespace Services\Purchase;


use Admin\AdminModel;
use Common\SerialNumber;
use Express\ExpressCompanyModel;
use Product\ProductModel;
use Purchase\PurchaseModel;
use Purchase\PurchaseOrderChildModel;
use Purchase\PurchaseOrderChildProductModel;
use Purchase\PurchaseOrderModel;
use Services\Recovery\RecoveryService;
use Services\Stock\PurchaseStockService;

class PurchaseChannelService extends PurchaseService
{
    public function orderDetail($child_order_no)
    {
        $order = PurchaseOrderChildModel::getChildOrderProductByChildOrderNO($child_order_no);
        foreach ($order['products'] as $k => $v) {
            $product = ProductModel::find($v['product_id'], ['stock', 'lock_stock']);
            $order['products'][$k]['stock'] = $product->stock;
            $order['products'][$k]['lock_stock'] = $product->lock_stock;
        }
        return $order;
    }


    /**
     * 商户对采购单进行查看操作
     * @param $purchase_id
     * @return array|bool
     */
    public function purchaseDetail($purchase_id)
    {
        try {
            $purchase = PurchaseModel::getPurchaseById($purchase_id);
            if (empty($purchase)) {
                return false;
            }
            $result = $purchase;

            // 子单
            $result['district_text'] = \AreaModel::getPca($result['province_id'], $result['city_id'], $result['area_id']);
            return $result;
        } catch (\Exception $exception) {
            $this->error = "参数错误";
            return false;
        }
    }

    /**
     * 采购发货操作
     * @param $purchase_id
     * @param $child_order_no
     * @param $express_id
     * @param $express_no
     * @return bool
     */
    public function purchaseDeliver($purchase_id, $child_order_no, $express_id, $express_no)
    {
        try {
            $purchase = PurchaseModel::getPurchaseById($purchase_id);
            if (empty($purchase)) {
                $this->error = "采购单不存在";
                return false;
            }
            if ($purchase['status'] != self::PURCHASE_ORDER_STATUS_40) {
                $this->error = '当前状态是:' . PurchaseModel::showOrderStatusText($purchase['status']) . ",不允许该操作";
                return false;
            }
            $childOrder = $this->orderDetail($child_order_no);
            if (empty($childOrder)) {
                $this->error = "子订单不存在";
                return false;
            }

            //处理子订单发货状态
            $this->handleDeliver($purchase, $childOrder, $express_id, $express_no);

            // 如果所有的子订单都发货完成，则处理主订单状态
            $this->deliverCompleted($purchase);
            return true;
        } catch (\Exception $exception) {
            $this->error = "参数错误";
            return false;
        }
    }

    /**
     * 采购发货操作 处理
     * @param $purchase
     * @param $childOrder
     * @param $express_id
     * @param $express_no
     * @return bool
     */
    protected function handleDeliver($purchase, $childOrder, $express_id, $express_no)
    {
        $express = ExpressCompanyModel::find($express_id, ['id', 'name', 'pinyin']);
        if (!$express) {
            throw new \InvalidArgumentException('快递公司不存在');
        }

        $this->newWrite()->beginTransaction();
        try {
            $expressInfo = [];
            $expressInfo['express_id'] = $express->id;
            $expressInfo['express_name'] = $express->name;
            $expressInfo['express_pinyin'] = $express->pinyin;
            $expressInfo['express_no'] = $express_no;
            $result = PurchaseOrderChildModel::updateExpressByChildOrderNo($childOrder['child_order_no'], $expressInfo);
            if (!$result) {
                throw new \Exception('更新快递信息失败');
            }

            $result = PurchaseOrderChildModel::updateStatusByChildOrderNo($childOrder['child_order_no'], self::PURCHASE_ORDER_STATUS_50);
            if (!$result) {
                throw new \Exception('更新发货状态失败');
            }
            // 库存解锁
            $this->unlockStock($childOrder['child_order_no'],$purchase);
            // 记录日志
            purchaseTrackingLog(
                $purchase['id'],
                $childOrder['child_order_no'],
                '发货',
                "子订单：{$childOrder['child_order_no']} 已发货"
            );
            $this->newWrite()->commit();
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }

    /**
     * 处理库存
     * @param $child_order_no
     * @param $purchase
     * @return bool
     * @throws \Exception
     */
    public function unlockStock($child_order_no,$purchase)
    {
        try {
            $admin = AdminModel::getCurrentLoginInfo();
            $products = PurchaseOrderChildProductModel::getProductByChildOrderNO($child_order_no);
            foreach ($products as $item) {
                $product = ProductModel::find($item['product_id'])->toArray();
                $PurchaseStockService = new PurchaseStockService($product);
                $PurchaseStockService->setAdminId($admin['id']);
                $PurchaseStockService->setAdminName($admin['fullname']);
                if ($purchase['purchase_type'] != PurchaseService::PURCHASE_TYPE_SALE) {
                    $PurchaseStockService->setType(PurchaseStockService::LOG_TYPE_17);
                } else {
                    $PurchaseStockService->setType(PurchaseStockService::LOG_TYPE_23);
                }
                $PurchaseStockService->setLockNum($item['num']);
                $PurchaseStockService->unlock();
            }
            return true;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 所有子单发货完成后，更新主单状态为已发货
     * @param array $purchase
     */
    protected function deliverCompleted(array $purchase)
    {
        // 总子订单数
        $childCount = PurchaseOrderChildModel::getChildOrderCount($purchase['order_no']);
        // 总已发货、已完成订单数
        $deliverChildCount = PurchaseOrderChildModel::getChildOrderCount($purchase['order_no'], [
            self::PURCHASE_ORDER_STATUS_50,
            self::PURCHASE_ORDER_STATUS_60,
        ]);

        if ($childCount == $deliverChildCount) {
            PurchaseOrderModel::updateStatus($purchase['order_no'], self::PURCHASE_ORDER_STATUS_50);
        }
    }

    public static function getStatus()
    {
        return [
            10 => ['name' => '待审核', 'status' => [10]],
            20 => ['name' => '待付款', 'status' => [20,21]],
            40 => ['name' => '待发货', 'status' => [40]],
            50 => ['name' => '待收货', 'status' => [50]],
            59 => ['name' => '已收货', 'status' => [59]],
            60 => ['name' => '已完成', 'status' => [60]],
            80 => ['name' => '取消', 'status' => [80,90]],
        ];
    }

    /**
     * 创建回收采购单
     * @return bool
     */
    public function createRecoveryPurchase()
    {
        // 开始事务
        $this->newWrite()->beginTransaction();
        try {
            //验证库存,处理供应价
            foreach ($this->request['product'] as &$item) {
                $product = ProductModel::find($item['id'], [
                    'stock','self_code','channel_price','max_channel_price'
                ]);
                if ($product->stock <= 0 || $product->stock < $item['num']) {
                    throw new \InvalidArgumentException("编号为{$product->self_code}的商品库存不足，创建采购单失败");
                }
                $item['channel_price'] = $product->channel_price;
                $item['max_channel_price'] = $product->max_channel_price;
            }
            //验证提交的参数
            $this->validRequestParams();
            list($this->purchase, $err) = PurchaseModel::createRecoveryPurchase(
                $this->request['title'],
                $this->request['name'],
                $this->request['mobile'],
                $this->request['province_id'],
                $this->request['city_id'],
                $this->request['area_id'],
                $this->request['address'],
                $this->request['product'],
                $this->request['pay_type'],
                $this->purchaseType,
                $this->request['source_id'],
                $this->request['purchase_supplier_id']
            );
            if ($err) {
                throw new \InvalidArgumentException('创建采购单失败');
            }
            /**
             * 创建采购商品
             */
            list($this->purchaseProducts, $err) = PurchaseModel::createRecoveryPurchaseProduct($this->purchase, $this->request['product']);
            if ($err) {
                throw new \InvalidArgumentException('创建采购单商品失败');
            }
            /**
             * 创建采购订单
             */
            list($this->purchaseOrder, $err) = $this->createRecoveryPurchaseOrder();
            if ($err) {
                throw new \InvalidArgumentException('创建采购订单失败');
            }
            /**
             * 创建采购子订单
             */
            $this->createRecoveryPurchaseOrderChild();
            /**
             * 锁库存
             */
            $auth = AdminModel::getCurrentLoginInfo();
            foreach ($this->request['product'] as $item) {
                $product = ProductModel::find($item['id']);
                $stock = new PurchaseStockService($product->toArray());
                $stock->setAdminId($auth['id']);
                $stock->setAdminName($auth['fullname']);
                $stock->setType(PurchaseStockService::LOG_TYPE_27);
                $stock->setLockNum($item['num']);
                $stock->lock();
            }
            /**
             * 回写回收单状态
             */
            RecoveryService::addPurchaseCallBack($this->request['source_id'],$this->purchase->id);

            //回收采购单直接商户审核审核通过
            $purchase = PurchaseModel::getPurchaseById($this->purchase->id);
            $this->handleAudit($purchase);
            //兜底售出，发送短信给竞拍成功供应商或渠道商@todo

            return $this->newWrite()->commit();

        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }

    /**
     * 创建回收采购订单
     * @return array [$ret,$err]  $ret 返回模型对象; $err 错误标识 0 成功   1 失败
     */
    protected function createRecoveryPurchaseOrder()
    {
        $result = PurchaseOrderModel::create([
            'order_no' => SerialNumber::createSN(SerialNumber::SN_ERP_PO),
            'order_original_amount' => $this->purchase->price,
            'order_actual_amount' => $this->purchase->price,
            'order_original_amount_buy' => $this->purchase->buy_price,
            'order_actual_amount_buy' => $this->purchase->buy_price,
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
    protected function createRecoveryPurchaseOrderChild()
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
                'child_order_original_amount_buy' => $this->totalChildOrderPriceBuy($products),
                'child_order_actual_amount_buy' => $this->totalChildOrderPriceBuy($products),
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
}