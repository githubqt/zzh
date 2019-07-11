<?php
// +----------------------------------------------------------------------
// | 平台采购管理
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/24 Time:11:49
// +----------------------------------------------------------------------


namespace Services\Purchase;


use Custom\YDLib;
use Purchase\PurchaseModel;
use Purchase\PurchaseOrderChildModel;
use Purchase\PurchaseOrderModel;
use Services\Finance\FinanceService;
use Supplier\SupplierModel;
use Sms\SmsModel;

class PlatformPurchaseService extends PurchaseService
{

    /**
     * 商户对采购单进行支付审核操作
     * @param $purchase_id
     * @param $audit_status
     * @return bool|\PDOStatement
     */
    public function purchasePayAudit($purchase_id, $audit_status)
    {
        $purchase = PurchaseModel::getPurchaseById($purchase_id);
        if (empty($purchase)) {
            return false;
        }

        if ($purchase['status'] != self::PURCHASE_ORDER_STATUS_21) {
            $this->error = '当前状态是:' . PurchaseModel::showOrderStatusText($purchase['status']) . ",不允许该操作";
            return false;
        }
        return $this->handlePayAudit($purchase, $audit_status);
    }

    /**
     * 处理支付审核
     * @param array $purchase
     * @param $audit_status
     * @return bool|\PDOStatement
     */
    public function handlePayAudit(array $purchase, $audit_status)
    {
        //$this->newWrite()->beginTransaction();
        try {
            // 通过
            if ($audit_status == '1'){
                $result = $this->handleStatus($purchase['order_no'], self::PURCHASE_ORDER_STATUS_40);
                if (!$result) {
                    throw new \Exception('支付汇款提交通过操作失败');
                }
                /**
                 * 创建采购方商户财务支出流水
                 */
                $finance = new FinanceService();
                $finance->setObjSummary('供应采购主订单支付');
                $finance->setAmount($purchase['order_actual_amount']);
                $finance->setObjType(FinanceService::PURCHASE_ORDER_PAYMENT);
                $finance->setObjId($purchase['order_no']);
                $finance->setRoleObjId($purchase['purchase_supplier_id']);
                $finance->setRoleType(FinanceService::ROLE_SUPPLIER);
                $finance->setRoleObjName(SupplierModel::getCompanyBySupplierId($purchase['purchase_supplier_id']));
                $finance->setSupplierId($purchase['purchase_supplier_id']);
                $finance->setPayType($purchase['pay_type']);
                $finance->setPaymentNo($purchase['remittance_account']);
                $finance->out();
                /**
                 * 创建供应方供应商财务收入流水
                 */
                $childOrders = PurchaseOrderChildModel::getChildOrderByOrderNO($purchase['order_no']);
                foreach ($childOrders as $order){
                    $supplier_info = SupplierModel::getInfoByID($order['supplier_id']);

                    $finance = new FinanceService();
                    $finance->setObjSummary('供应采购子订单支付');
                    $finance->setAmount($order['child_order_actual_amount']);
                    $finance->setObjType(FinanceService::PURCHASE_CHILD_ORDER_PAYMENT);
                    $finance->setObjId($order['child_order_no']);
                    $finance->setRoleObjId($order['supplier_id']);
                    $finance->setRoleType(FinanceService::ROLE_SUPPLIER);
                    $finance->setRoleObjName($supplier_info['company']);
                    $finance->setSupplierId($order['supplier_id']);
                    $finance->setPayType($purchase['pay_type']);
                    $finance->setPaymentNo($purchase['remittance_account']);
                    $finance->in();

                    //发送短信
                    $smsdata ['model_id'] = '10';
                    $smsdata ['mobile'] = $supplier_info ['mobile'];
                    SmsModel::SendSmsJustFire ( $smsdata );

                }

                // 记录采购日志
                purchaseTrackingLog(
                    $purchase['id'],
                    '0',
                    '付款审核',
                    "采购单:{$purchase['id']} 付款审核通过"
                );
            }

            // 不通过
            else if ($audit_status == '2'){
                $result = $this->handleStatus($purchase['order_no'], self::PURCHASE_ORDER_STATUS_20);
                if (!$result) {
                    throw new \Exception('支付汇款提交通过操作失败');
                }
                // 记录采购日志
                purchaseTrackingLog(
                    $purchase['id'],
                    '0',
                    '付款审核',
                    "采购单:{$purchase['id']} 付款审核未通过"
                );
            }

            else {
                throw new \Exception('操作状态不存在');
            }
            // 其他操作
            //$this->newWrite()->commit();
            return $result;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            //$this->newWrite()->rollback();
            return false;
        }
    }

    public static function getStatus()
    {
        return [
            10 => ['name' => '待审核', 'status' => [10]],
            20 => ['name' => '待付款', 'status' => [20]],
            21 => ['name' => '付款审核', 'status' => [21]],
            40 => ['name' => '待发货', 'status' => [40]],
            50 => ['name' => '已发货', 'status' => [50]],
            59 => ['name' => '已收货', 'status' => [59]],
            60 => ['name' => '已完成', 'status' => [60]],
            80 => ['name' => '取消', 'status' => [80,90]],
        ];
    }
}