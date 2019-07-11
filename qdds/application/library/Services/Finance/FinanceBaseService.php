<?php
// +----------------------------------------------------------------------
// | 财务操作抽象类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/9/6 Time:11:20
// +----------------------------------------------------------------------


namespace Services\Finance;


use Finance\FinanceModel;

abstract class FinanceBaseService
{
    /**
     * 财务模型对象
     * @var FinanceModel|null
     */
    protected $finance = null;

    /**
     * 财务收支对象类型
     * @var string
     */
    protected $objType = '';

    /**
     * 财务收支对象ID
     * @var int
     */
    protected $objId = 0;

    /**
     * 财务收支对象描述
     * @var string
     */
    protected $objSummary = '';

    /**
     * 支付金额
     * @var float
     */
    protected $amount = 0.00;

    /**
     * 结算单号
     * @var string
     */
    protected $settleNo = '';

    /**
     * 财务收支对象角色类型
     * user:商户会员，supplier:商户|供应商|渠道商，system:系统，other:其它'
     * @var string
     */
    protected $roleType = 'system';

    /**
     * 财务收支对象角色类型对应编号
     * user：user表, supplier:supplier表'
     * @var int
     */
    protected $roleObjId = 0;

    /**
     * 商户id
     * @var int
     */
    protected $supplierId = 0;

    /**
     * 财务收支对象角色类型对应名称
     * user：user表name, supplier:supplier表company'
     * @var int
     */
    protected $roleObjName = 0;

    /**
     * 支付方式  weixin | alipay | unionpay | offline
     * @var string
     */
    protected $payType = '';

    /**
     * 支付流水号  三方订单流水号或者是线下支付汇款单号
     * @var string
     */
    protected $paymentNo = '';

    /**
     * 结算状态  财务收支是否需要结算
     * @var string
     */
    protected $settleType = self::SETTLEMENT_NO;

    /**
     * 交易流水id
     * @var string
     */
    protected $paymentId = 0;

    /**
     * 财务收支类型：
     * 收入
     */
    const IN = 'in';
    /**
     * 财务收支类型:
     * 支出
     */
    const OUT = 'out';

    /**
     * 财务收支类型描述
     */
    const TYPE_VALUE = [
        self::IN        => '收入',
        self::OUT       => '支出'
    ];

    /**
     * 财务支付方式：
     * 微信支付
     */
    const PAY_TYPE_WEIXIN = 'weixin';
    /**
     * 财务支付方式:
     * 支付宝支付
     */
    const PAY_TYPE_ALIPAY = 'alipay';
    /**
     * 财务支付方式:
     * 银联支付
     */
    const PAY_TYPE_UNIONPAY = 'unionpay';
    /**
     * 财务支付方式:
     * 线下支付
     */
    const PAY_TYPE_OFFLINE = 'offline';
    /**
     * 财务支付方式描述
     */
    const PAY_TYPE_VALUE = [
        self::PAY_TYPE_WEIXIN       => '微信支付',
        self::PAY_TYPE_ALIPAY       => '支付宝支付',
        self::PAY_TYPE_UNIONPAY     => '银联支付',
        self::PAY_TYPE_OFFLINE      => '线下支付'
    ];

    /**
     * 结算类型:未结算
     */
    const SETTLEMENT_NO = 1;
    /**
     * 结算类型:结算中
     */
    const SETTLEMENT_ING = 2;
    /**
     * 结算类型:已结算
     */
    const SETTLEMENT_YES = 3;
    /**
     * 结算类型:不需结算
     */
    const SETTLEMENT_IGNORE = 4;
    /**
     * 财务结算类型描述
     */
    const SETTLEMENT_VALUE = [
        self::SETTLEMENT_NO        => '未结算',
        self::SETTLEMENT_ING       => '结算中',
        self::SETTLEMENT_YES       => '已结算',
        self::SETTLEMENT_IGNORE    => '不需结算'
    ];

    /**
     * 财务收支对象角色类型：商户用户
     */
    const ROLE_USER = 'user';
    /**
     * 财务收支对象角色类型：商户、供应商、渠道
     */
    const ROLE_SUPPLIER = 'supplier';
    /**
     * 财务收支对象角色类型：系统
     */
    const ROLE_SYSTEM = 'system';
    /**
     * 财务收支对象角色类型：其它
     */
    const ROLE_OTHER = 'other';


    // +----------------------------------------------------------------------
    // | 用户订单支出收入类型
    // +----------------------------------------------------------------------
    /**
     * 会员订单支付
     */
    const USER_ORDER_PAYMENT = 'user_order_payment';
    /**
     * 会员订单退款
     */
    const USER_ORDER_RETURN = 'user_order_return';
    /**
     * 会员保证金支付
     */
    const USER_MARGIN_PAYMENT = 'user_margin_payment';
    /**
     * 会员保证金退款
     */
    const USER_MARGIN_RETURN = 'user_margin_return';
    /**
     * 会员充值
     */
    const USER_RECHARGE = 'user_recharge';
    /**
     * 会员提现
     */
    const USER_WITHDRAW = 'user_withdraw';


    // +----------------------------------------------------------------------
    // | 商户、供应商、渠道支出收入类型
    // +----------------------------------------------------------------------

    /**
     * 商户采购主订单支付
     */
    const PURCHASE_ORDER_PAYMENT = 'purchase_order_payment';

    /**
     * 商户采购子订单支付
     */
    const PURCHASE_CHILD_ORDER_PAYMENT = 'purchase_child_order_payment';

    /**
     * 商户采购主订单退款
     */
    const PURCHASE_ORDER_RETURN = 'purchase_order_return';

    /**
     * 商户采购子订单退款
     */
    const PURCHASE_CHILD_ORDER_RETURN = 'purchase_child_order_return';

    /**
     * 商品鉴定
     */
    const APPRAISAL_PRICE = 'appraisal_price';

    /**
     * 短信充值
     */
    const SMS_RECHARGE = 'sms_recharge';

    /**
     * 支出收入类型描述
     */
    const TYPE_SUMMARY = [
        self::USER_ORDER_PAYMENT => '订单支付',
        self::USER_ORDER_RETURN => '订单退款',
        self::USER_MARGIN_PAYMENT => '保证金支付',
        self::USER_MARGIN_RETURN => '保证金退款',
        self::USER_RECHARGE => '会员充值',
        self::USER_WITHDRAW => '会员提现',
        self::PURCHASE_ORDER_PAYMENT => '采购主订单支付',
        self::PURCHASE_CHILD_ORDER_PAYMENT => '采购子订单支付',
        self::PURCHASE_ORDER_RETURN => '采购主订单退款',
        self::PURCHASE_CHILD_ORDER_RETURN => '采购子订单退款',
        self::SMS_RECHARGE => '短信充值',
        self::APPRAISAL_PRICE => '商品鉴定',
    ];

    const BALANCE_TYPE_ARRAY = [
        BalanceService::BALANCE_TYPE_1 => [
            self::PURCHASE_ORDER_PAYMENT,
            self::PURCHASE_CHILD_ORDER_PAYMENT,
            self::PURCHASE_ORDER_RETURN,
            self::PURCHASE_CHILD_ORDER_RETURN,
        ],
        BalanceService::BALANCE_TYPE_2 => [
            self::USER_ORDER_PAYMENT,
            self::USER_ORDER_RETURN,
            self::USER_MARGIN_PAYMENT,
            self::USER_MARGIN_RETURN,
        ],
        BalanceService::BALANCE_TYPE_3 => [
            'express_search'
        ],
        BalanceService::BALANCE_TYPE_4 => [
            self::USER_RECHARGE,
            self::USER_WITHDRAW,
        ],
    ];


    /**
     * 初始化财务模型
     * FinanceService constructor.
     */
    public function __construct()
    {
        $this->finance = new FinanceModel();
    }

    /**
     * 设置财务收支对象类型
     * @param $obj_type
     */
    public function setObjType($obj_type)
    {
        $this->objType = $obj_type;
    }

    /**
     * 获取财务收支对象类型
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * 设置财务收支对象ID
     * @param $obj_id
     */
    public function setObjId($obj_id)
    {
        $this->objId = $obj_id;
    }

    /**
     * 获取财务收支对象ID
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * 设置财务收支对象描述
     * @param $obj_summary
     */
    public function setObjSummary($obj_summary)
    {
        $this->objSummary = $obj_summary;
    }

    /**
     * 获取财务收支对象描述
     * @return string
     */
    public function getObjSummary()
    {
        return $this->objSummary;
    }

    /**
     * 设置财务收支对象角色类型
     * @param $role_type
     */
    public function setRoleType($role_type)
    {
        $this->roleType = $role_type;
    }

    /**
     * 获取财务收支对象角色类型
     * @return string
     */
    public function getRoleType()
    {
        return $this->roleType;
    }

    /**
     * 设置财务收支对象角色类型对应编号
     * @param $role_obj_id
     */
    public function setRoleObjId($role_obj_id)
    {
        $this->roleObjId = $role_obj_id;
    }

    /**
     * 获取财务收支对象角色类型对应编号
     * @return int
     */
    public function getRoleObjId()
    {
        return $this->roleObjId;
    }

    /**
     * 设置商户id
     * @param $supplier_id
     */
    public function setSupplierId($supplier_id)
    {
        $this->supplierId = $supplier_id;
    }

    /**
     * 获取商户id
     * @return int
     */
    public function getSupplierId()
    {
        return $this->supplierId;
    }

    /**
     * 设置财务收支对象角色类型对应名称
     * @param $role_obj_id
     */
    public function setRoleObjName($role_obj_name)
    {
        $this->roleObjName = $role_obj_name;
    }

    /**
     * 获取财务收支对象角色类型对应名称
     * @return string
     */
    public function getRoleObjName()
    {
        return $this->roleObjName;
    }

    /**
     * 设置支付金额
     * @param $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * 获取支付金额
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }
    /**
     * 设置结算状态
     * @param $settleType
     */
    public function setSettleType($settleType)
    {
        $this->settleType = $settleType;
    }

    /**
     * 获取结算状态
     * @return int
     */
    public function getSettleType()
    {
        return $this->settleType;
    }
    /**
     * 设置交流流水id
     * @param $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * 获取交流流水id
     * @return int
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * 获取财务模型对象
     * @return FinanceModel|null
     */
    public function getFinance()
    {
        return $this->finance;
    }

    /**
     * 设置结算单号
     * @param $settle_no
     */
    public function setSettleNo($settle_no)
    {
        $this->settleNo = $settle_no;
    }

    /**
     * 获取结算单号
     * @return string
     */
    public function getSettleNo()
    {
        return $this->settleNo;
    }

    /**
     * 设置支付方式
     * 可选项值： weixin | alipay | unionpay | offline
     * @param $pay_type
     */
    public function setPayType($pay_type)
    {
        $this->payType = $pay_type;
    }

    /**
     * 获取支付方式
     * @return string
     */
    public function getPayType()
    {
        return $this->payType;
    }

    /**
     * 设置支付流水号
     * @param $payment_no
     */
    public function setPaymentNo($payment_no)
    {
        $this->paymentNo = $payment_no;
    }

    /**
     * 获取支付流水号
     * @return string
     */
    public function getPaymentNo()
    {
        return $this->paymentNo;
    }

    /**
     * 财务收入
     * @return mixed
     */
    final public function in()
    {
        $this->finance->type = static::IN;
        $this->finance->obj_type = $this->objType;
        $this->finance->obj_id = $this->objId;
        $this->finance->obj_summary = !empty($this->objSummary)?$this->objSummary:static::TYPE_SUMMARY[$this->objType];
        $this->finance->amount = $this->amount;
        $this->finance->settle_type = $this->settleType;
        $this->finance->pay_time = date('Y-m-d H:i:s');
        $this->finance->pay_type = $this->payType;
        $this->finance->payment_no = $this->paymentNo;
        $this->finance->payment_id = $this->paymentId;
        $this->finance->role_type = $this->roleType;
        $this->finance->role_obj_id = $this->roleObjId;
        $this->finance->role_obj_name = $this->roleObjName;
        $this->finance->supplier_id = $this->supplierId;
        return $this->finance->save();
    }

    /**
     * 财务支出
     * @return mixed
     */
    final public function out()
    {
        $this->finance->type = static::OUT;
        $this->finance->obj_type = $this->objType;
        $this->finance->obj_id = $this->objId;
        $this->finance->obj_summary = !empty($this->objSummary)?$this->objSummary:static::TYPE_SUMMARY[$this->objType];
        $this->finance->amount = -$this->amount;
        $this->finance->settle_type = $this->settleType;
        $this->finance->pay_time = date('Y-m-d H:i:s');
        $this->finance->pay_type = $this->payType;
        $this->finance->payment_no = $this->paymentNo;
        $this->finance->payment_id = $this->paymentId;
        $this->finance->role_type = $this->roleType;
        $this->finance->role_obj_id = $this->roleObjId;
        $this->finance->role_obj_name = $this->roleObjName;
        $this->finance->supplier_id = $this->supplierId;
        return $this->finance->save();
    }

    /**
     * 更新财务结算状态
     * @param $finance_id
     * @return bool
     */
    final public function settle($finance_id)
    {
        $finance = FinanceModel::find($finance_id);
        $finance->settle_type = self::SETTLEMENT_YES;
        $finance->settle_time = date('Y-m-d H:i:s');
        $finance->settle_no = $this->settleNo;
        return $finance->save();
    }

}