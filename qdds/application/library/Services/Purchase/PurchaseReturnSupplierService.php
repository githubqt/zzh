<?php
// +----------------------------------------------------------------------
// | 平台退货操作服务类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/9/14 Time:15:51
// +----------------------------------------------------------------------


namespace Services\Purchase;


use Admin\AdminModel;
use Express\ExpressCompanyModel;
use Product\ProductChannelModel;
use Product\ProductModel;
use Purchase\PurchaseReturnModel;
use Services\Finance\FinanceService;
use Services\Stock\PurchaseStockService;
use Services\Stock\VoidStockService;
use Supplier\SupplierModel;

class PurchaseReturnSupplierService extends PurchaseReturnService
{
    /**
     * 确认收货
     * @param $id
     * @return bool
     */
    public function handleReceipt($id)
    {
        try {
            $detail = $this->showReturnDetail($id);
            if (!$detail) {
                throw new \Exception('退货单不存在');
            }

            $model = PurchaseReturnModel::find($id);
            if (!$model) {
                throw new \Exception('退货单号不存在');
            }

            $model->refund_to = 'merchant';
            $model->refund_merchant = $detail['return_money'];
            $model->refund_supplier = 0;
            $model->refund_note = '供应商确认收货，自动退款';
            $model->take_delivery_time = date('Y-m-d H:i:s');
            $model->order_status = self::PURCHASE_RETURN_ORDER_STATUS_70;
            if (!$model->save()) {
                throw new \Exception('确认收货失败');
            }

            $toMerchantMoney = $detail['return_money'];
            $toSupplierMoney = $detail['return_money'];
            // 退款给商户
            $finance = new FinanceService();
            $finance->setPayType($detail['child_pay_type']);
            $finance->setAmount($toMerchantMoney);
            $finance->setRoleType('supplier');
            $finance->setRoleObjId($detail['purchase_supplier_id']);
            $finance->setObjType(FinanceService::PURCHASE_CHILD_ORDER_RETURN);
            $finance->setObjId($detail['return_no']);
            $finance->setRoleObjName(SupplierModel::getCompanyBySupplierId($detail['purchase_supplier_id']));
            $finance->setSupplierId($detail['purchase_supplier_id']);
            $finance->in();

            // 供应商退款
            $finance = new FinanceService();
            $finance->setPayType($detail['child_pay_type']);
            $finance->setAmount($toSupplierMoney);
            $finance->setRoleType('supplier');
            $finance->setRoleObjId($detail['supplier_id']);
            $finance->setObjType(FinanceService::PURCHASE_CHILD_ORDER_RETURN);
            $finance->setObjId($detail['return_no']);
            $finance->setRoleObjName(SupplierModel::getCompanyBySupplierId($detail['supplier_id']));
            $finance->setSupplierId($detail['supplier_id']);
            $finance->out();

            //渠道退货不处理库存
            $purchase_supplier_info = SupplierModel::getInfoByID($detail['purchase_supplier_id']);
            if ($purchase_supplier_info['type'] != 4) {
                foreach ($detail['products'] as $k => $v) {
                    if ($v['channel_id'] > 0) {
                        /**
                         * 虚拟商品锁库存
                         */
                        $channel_product = ProductChannelModel::find($v['channel_id']);
                        $stock = new VoidStockService($channel_product->toArray());
                        $stock->setType(VoidStockService::LOG_TYPE_26);
                        $stock->setLockNum($v['return_num']);
                        $stock->revert();
                    } else {
                        $product = ProductModel::find($v['product_id']);
                        $stock = new PurchaseStockService($product->toArray());
                        $stock->setLockNum($v['return_num']);
                        $stock->setType(PurchaseStockService::LOG_TYPE_20);
                        $stock->revert();
                    }
                }
            }
            // 记录操作日志
            purchaseTrackingLog(
                $detail['purchase_id'],
                $detail['child_order_no'],
                '收货',
                "供应商已收货",
                2
            );

            // 记录操作日志
            purchaseTrackingLog(
                $detail['purchase_id'],
                $detail['child_order_no'],
                '退款',
                "退款完成：退给供应商：{$toSupplierMoney}元 ,退给商户：{$toMerchantMoney}元 ,",
                2
            );
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * 拒收
     * @param $id
     * @param $params
     * @return bool
     */
    public function handleReject($id, $params)
    {
        $this->newWrite()->beginTransaction();
        try {
            $detail = $this->showReturnDetail($id);
            if (!$detail) {
                throw new \Exception("退货单不存在");
            }

            $model = PurchaseReturnModel::find($id);
            $model->supplier_reject_reason = $params['supplier_reject_reason'];
            $model->order_status = self::PURCHASE_RETURN_ORDER_STATUS_61;
            $model->save();

            $logContent = "供应商拒收, 拒收原因：{$params['supplier_reject_reason']}";

            // 记录操作日志
            purchaseTrackingLog(
                $detail['purchase_id'],
                $detail['child_order_no'],
                '供应商拒收',
                $logContent,
                2
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
     *
     * 供应商填写物流
     * @param $id
     * @param $params
     * @return bool
     */
    public function handleExpress($id, $params)
    {
        $this->newWrite()->beginTransaction();
        try {
            $detail = $this->showReturnDetail($id);
            if (!$detail) {
                throw new \Exception("退货单不存在");
            }
            $model = PurchaseReturnModel::find($id);
            $content = [];
            $content['express_no'] = $params['express_no'];
            if ($params['express_id']) {
                $express = ExpressCompanyModel::find($params['express_id'], ['id', 'name', 'pinyin']);
                $content['express_id'] = $express->id;
                $content['express_name'] = $express->name;
                $content['express_pinyin'] = $express->pinyin;
            }
            foreach ($content as $field => $value){
                $model->{$field} = $value;
            }
            $model->order_status = self::PURCHASE_RETURN_ORDER_STATUS_64;
            $model->save();

            //更新物流地址
            $expressService = new PurchaseReturnExpressService($detail);
            $expressService->setType(PurchaseReturnExpressService::TYPE_SUPPLIER_RETURN);
            $expressService->saveExpress(
                $content['express_id'],
                $content['express_no']
            );

            $logContent = "供应商填写物流, 物流单号：{$params['express_no']}[{$content['express_name']}]";
            // 记录操作日志
            purchaseTrackingLog(
                $detail['purchase_id'],
                $detail['child_order_no'],
                '填写物流',
                $logContent,
                2
            );

            $this->newWrite()->commit();
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }


}