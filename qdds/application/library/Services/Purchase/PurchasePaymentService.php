<?php
// +----------------------------------------------------------------------
// | 支付流水服务类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/28 Time:11:23
// +----------------------------------------------------------------------


namespace Services\Purchase;


use Admin\AdminModel;
use Payment\PaymentTransactionModel;
use Purchase\PurchaseModel;
use Purchase\PurchaseOrderModel;
use Services\BaseService;

class PurchasePaymentService extends BaseService
{
    /**
     * 采购单模型对象
     * @var
     */
    protected $purchase;

    /**
     * 采购单订单模型对象
     * @var
     */
    protected $purchaseOrder;

    protected $payType = 'offline';

    public function __construct(PurchaseModel $purchase, PurchaseOrderModel $purchaseOrder)
    {
        $this->purchase = $purchase;
        $this->purchaseOrder = $purchaseOrder;
    }

    public function setPayType($type = 'offline')
    {
        $this->payType = $type;
        return $this;
    }

    public function getPayType()
    {
        return $this->payType;
    }

    /**
     * 创建采购主单支付流水
     * @return \BaseModel
     */
    public function paymentCreate()
    {
        $auth = AdminModel::getCurrentLoginInfo();
        $data = [];
        $data['supplier_id'] = $auth['supplier_id'];
        $data['user_id'] = 0;
        $data['user_name'] = '';
        $data['biz_type'] = 'purchase';
        $data['biz_no'] = $this->purchaseOrder->order_no;
        $data['pay_type'] = $this->payType;
        $data['pay_amount'] = $this->purchaseOrder->order_original_amount;
        $data['status'] = PaymentTransactionModel::PAYMENT_STATUS_1;
        $data['return_payment_no'] = '';
        $data['return_payment_data'] = '';
        $data['ip'] = (new \Publicb())->GetIP();
        $data['payment_no'] = 0;
//        $data['pay_time'] = '';
        $data['type'] = 1;
        $data['is_return'] = 1;
        $data['payment_id'] = 0;
        return PaymentTransactionModel::create($data);
    }

    /**
     * 更新采购主单支付流水支付状态
     * @return bool
     */
    public function paymentFinished()
    {
        $payment = PaymentTransactionModel::getInfo(
            $this->purchase->purchase_supplier_id,
            'purchase',
            $this->purchaseOrder->order_no
        );

        if (isset($payment['id']) && $payment['id']) {
            $PaymentModel = PaymentTransactionModel::find($payment['id']);
            $PaymentModel->status = PaymentTransactionModel::PAYMENT_STATUS_2;
            $PaymentModel->pay_time = date('Y-m-d H:i:s');
            return $PaymentModel->save();
        }
        throw new \InvalidArgumentException("未查到采购订单号：{$this->purchaseOrder->order_no} 流水");
    }

    /**
     * 取消采购主单支付流水支付状态
     * @return bool
     */
    public function paymentCancel()
    {
        $payment = PaymentTransactionModel::getInfo(
            $this->purchase->purchase_supplier_id,
            'purchase',
            $this->purchaseOrder->order_no
        );

        if (isset($payment['id']) && $payment['id']) {
            $PaymentModel = PaymentTransactionModel::find($payment['id']);
            $PaymentModel->status = PaymentTransactionModel::PAYMENT_STATUS_1;
            $PaymentModel->pay_time = null;
            return $PaymentModel->save();
        }
        throw new \InvalidArgumentException("未查到采购订单号：{$this->purchaseOrder->order_no} 流水");
    }


}