<?php
// +----------------------------------------------------------------------
// | 采购服务类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/21 Time:14:54
// +----------------------------------------------------------------------
namespace Services\Purchase;

use Admin\AdminModel;
use Custom\YDLib;
use Image\ImageModel;
use Product\ProductModel;
use Purchase\PurchaseOrderChildProductModel;
use Purchase\PurchaseProductModel;
use Services\BaseService;
use Common\SerialNumber;
use Purchase\PurchaseModel;
use Purchase\PurchaseOrderChildModel;
use Purchase\PurchaseOrderModel;
use Purchase\PurchaseReturnModel;
use Core\Express;
use Services\Stock\PurchaseStockService;

class PurchaseOrderService extends PurchaseService
{
    /**
     * 创建采购单
     * @return bool
     */
    public function createPurchase()
    {
        try {
            //验证库存
//            foreach ($this->request['product'] as $item) {
//                $product = ProductModel::find($item['id']);
//                if ($product->stock <= 0 || $product->stock < $item['num']) {
//                    throw new \InvalidArgumentException("编号为{$product->self_code}的商品库存不足，创建采购单失败");
//                }
//            }
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
                $this->purchaseType,
                $this->request['source_id']
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

            //YDLib::testlog('创建采购子订单成功');
            //处理采购单
            //商户审核
            $purchase = PurchaseModel::getPurchaseById($this->purchase->id);
            $this->handleAudit($purchase);
            //YDLib::testlog('商户审核成功');
            //商户付款
            $this->handlePay($purchase,'',[]);
            //YDLib::testlog('商户付款成功');
            //平台付款审核通过
            $PlatformPurchaseService = new PlatformPurchaseService();
            $PlatformPurchaseService->handlePayAudit($purchase,1);
            //YDLib::testlog('平台付款审核通过成功');
            return true;
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
    public function purchasePay($purchase_id, $account = '', array $images = [])
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
     * 支付汇款操作
     * @param array $purchase
     * @param $account
     * @param array $images
     * @return bool
     */
    protected function handlePay(array $purchase, $account, array $images)
    {
        //$this->newWrite()->beginTransaction();
        try {
            $result = $this->handleStatus($purchase['order_no'], self::PURCHASE_ORDER_STATUS_21);
            if (!$result) {
                throw new \Exception('支付汇款提交操作失败');
            }

            // 记录采购日志
            purchaseTrackingLog(
                $purchase['id'],
                '0',
                '采购单汇款',
                "采购单:{$purchase['id']} 提交汇款凭证"
            );

            //$this->newWrite()->commit();
            return $result;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            //$this->newWrite()->rollback();
            return false;
        }
    }
}