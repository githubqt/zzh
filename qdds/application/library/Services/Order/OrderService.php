<?php
// +----------------------------------------------------------------------
// | 订单操作抽象类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：lqt
// +----------------------------------------------------------------------
// | Author: lqt  Date:2018/10/8 Time:11:20
// +----------------------------------------------------------------------


namespace Services\Order;


use Admin\AdminModel;
use Common\CommonBase;
use Common\SerialNumber;
use Custom\YDLib;
use Freight\FreightSetModel;
use Order\OrderChildModel;
use Order\OrderChildProductModel;
use Order\OrderModel;
use Product\ProductChannelModel;
use Product\ProductModel;
use Product\ProductStockLogModel;
use Services\BaseService;
use Services\Stock\OrderStockService;
use Services\Stock\VoidStockService;
use User\UserSupplierModel;

class OrderService extends BaseService
{
    /**
     * 订单模型对象
     * @var OrderModel|null
     */
    protected $Order = null;

    /**
     * 初始化订单模型
     * OrderService constructor.
     */
    public function __construct()
    {
        $this->Order = new OrderModel();
    }

    /**
     * 订单商品模型对象集合
     * @var array
     */
    protected $orderProducts;

    /**
     * 订单商品原始总金额
     * @var array
     */
    protected $product_original_amount_total = 0;

    /**
     * 订单商品总数量
     * @var array
     */
    protected $sale_num_total = 0;

    /**
     * 错误提示
     * @var string
     */
    protected $error = '';

    /**
     * 获取错误提示
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 订单拆分
     * @return array
     */
    protected function splitOrder()
    {
        $products = [];
        $product_original_amount_total = 0;
        $sale_num_total = 0;
        foreach ($this->orderProducts as $key => $value) {
            //获取商品信息
            $productInfo = ProductModel::getInfoByIDUseAddOrder($key,true);
            if (!$productInfo) {
                throw new \InvalidArgumentException('商品不存在！');
            }
            if ($productInfo['stock'] < $value) {
                throw new \InvalidArgumentException('商品库存不足！');
            }
            $productInfo['num'] = $value;
            $productInfo['actual_amount'] = bcmul($productInfo['sale_price'],$value,2);
            $products[] = $productInfo;

            $product_original_amount_total = bcadd($product_original_amount_total,$productInfo['actual_amount'],2);
            $sale_num_total = bcadd($sale_num_total,$value);
        }

        $this->orderProducts = $products;
        $this->product_original_amount_total = $product_original_amount_total;
        $this->sale_num_total = $sale_num_total;
    }

    /**
     * 生成订单
     * @return num
     */
    public function addOrder($data)
    {
        //YDLib::testLog($data);
        $this->newWrite()->beginTransaction();
        try {
            //订单拆分
            $this->orderProducts = $data['product'];
            $this->splitOrder();

            $auth = AdminModel::getCurrentLoginInfo();

            //获取运费
            if ($data['delivery_type'] != '1') {
                $charge = FreightSetModel::getFreightBYProvinceID($data['province_id']);
                $data['province_name'] = \AreaModel::getInfoByID($data['province_id'])['area_name'];
                $data['city_name'] = \AreaModel::getInfoByID($data['city_id'])['area_name'];
                $data['area_name'] = \AreaModel::getInfoByID($data['area_id'])['area_name'];
                $data['street_name'] = \AreaModel::getInfoByID($data['street_id'])['area_name'];
            } else {
                //门店自提订单，收货地址默认为空
                $charge = '0.00';
                $data['province_id'] = 0;
                $data['province_name'] = '';
                $data['city_id'] = 0;
                $data['city_name'] = '';
                $data['area_id'] = 0;
                $data['area_name'] = '';
                $data['street_id'] = 0;
                $data['street_name'] = '';
                $data['address'] = '';
                $data['accept_name'] = '';
                $data['accept_mobile'] = '';
            }
            //生成主单
            $orderData = [];
            $orderData['supplier_id'] = $auth['supplier_id'];
            $orderData['order_no'] = SerialNumber::createSN(SerialNumber::SN_ORDER_MAIN);
            $orderData['user_id'] = $data['user_id'];
            $orderData['freight_charge_original_amount'] = $charge;//原始支付运费金额
            $orderData['freight_charge_actual_amount'] = $charge;//实际支付运费金额
            $orderData['freight_charge_discount_amount'] = 0;//优惠运费金额
            $orderData['coupan_discount_amount'] = 0;//卡券抵扣金额
            $orderData['product_original_amount'] = $this->product_original_amount_total;//商品原始金额
            $orderData['product_actual_amount'] = $this->product_original_amount_total;//商品实际支付金额
            $orderData['order_original_amount'] = bcadd($orderData['product_original_amount'],$orderData['freight_charge_original_amount'],2);//订单原始价格金额(订单总额+总邮费)
            $orderData['order_discount_amount'] = 0;//订单优惠金额(商品优惠+运费优惠+卡券抵扣)
            $orderData['order_actual_amount'] = bcadd($orderData['order_original_amount'], $orderData['order_discount_amount'],2);//订单实际支付金额(订单实际总额+实际总邮费)
            $orderData['product_discount_amount'] = 0;//商品优惠金额
            $orderData['sale_num'] = $this->sale_num_total;//销售数量
            $orderData['delivery_type'] = $data['delivery_type'];//选择收货方式：0 快递 ，1门店自提
            $orderData['pay_type'] = CommonBase::ORDER_PAY_TYPE_ONLINE;//支付类型 默认1 1在线支付 2货到付款
            $orderData['status'] = CommonBase::STATUS_PENDING_PAYMENT;//待付款状态
            $public = new \Publicb();
            $orderData['ip'] = $public->GetIP();
            $orderData['province_id'] = $data['province_id'];
            $orderData['province_name'] = $data['province_name'];
            $orderData['city_id'] = $data['city_id'];
            $orderData['city_name'] = $data['city_name'];
            $orderData['area_id'] = $data['area_id'];
            $orderData['area_name'] = $data['area_name'];
            $orderData['street_id'] = $data['street_id'];
            $orderData['street_name'] = $data['street_name'];
            $orderData['address'] = $data['address'];
            $orderData['accept_name'] = $data['accept_name'];
            $orderData['accept_mobile'] = $data['accept_mobile'];
            $orderData['order_from'] = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
            $orderData['express_id'] = 0;
            $orderData['express_name'] = '';
            $orderData['express_no'] = '';
            $orderData['note'] = '后台下单';
            //YDLib::testLog('生成主单');
            //YDLib::testLog($orderData);
            $order_id = OrderModel::addData($orderData);
            if (!$order_id) {
                throw new \InvalidArgumentException('生成主单失败！');
            }
            //生成子单
            $orderChildData = [];
            $orderChildData['supplier_id'] = $orderData['supplier_id'];
            $orderChildData['user_id'] = $data['user_id'];
            $orderChildData['order_id'] = $order_id;
            $orderChildData['order_no'] = $orderData['order_no'];
            $orderChildData['child_order_no'] = SerialNumber::createSN(SerialNumber::SN_ORDER_CHILD);
            $orderChildData['child_order_original_amount'] = $orderData['order_original_amount'];
            $orderChildData['child_order_actual_amount'] = $orderData['order_actual_amount'];
            $orderChildData['child_order_discount_amount'] = $orderData['order_discount_amount'];
            $orderChildData['child_product_original_amount'] = $orderData['product_original_amount'];
            $orderChildData['child_product_actual_amount'] = $orderData['product_actual_amount'];
            $orderChildData['child_product_discount_amount'] = $orderData['product_discount_amount'];
            $orderChildData['child_freight_charge_original_amount'] = $orderData['freight_charge_original_amount'];
            $orderChildData['child_freight_charge_actual_amount'] = $orderData['freight_charge_actual_amount'];
            $orderChildData['child_freight_charge_discount_amount'] = $orderData['freight_charge_discount_amount'];
            $orderChildData['coupan_discount_amount'] = $orderData['coupan_discount_amount'];
            $orderChildData['sale_num'] = $orderData['sale_num'];
            $orderChildData['child_pay_type'] = $orderData['pay_type'];
            $orderChildData['child_status'] = $orderData['status'];
            $orderChildData['is_comment'] = CommonBase::COMMENT_NONE;
            $orderChildData['ip'] = $orderData['ip'];
            $orderChildData['province_id'] = $orderData['province_id'];
            $orderChildData['province_name'] = $orderData['province_name'];
            $orderChildData['city_id'] = $orderData['city_id'];
            $orderChildData['city_name'] = $orderData['city_name'];
            $orderChildData['area_id'] = $orderData['area_id'];
            $orderChildData['area_name'] = $orderData['area_name'];
            $orderChildData['street_id'] = $orderData['street_id'];
            $orderChildData['street_name'] = $orderData['street_name'];
            $orderChildData['address'] = $orderData['address'];
            $orderChildData['accept_name'] = $orderData['accept_name'];
            $orderChildData['accept_mobile'] = $orderData['accept_mobile'];
            $orderChildData['order_from'] = $orderData['order_from'];
            $orderChildData['express_id'] = $orderData['express_id'];
            $orderChildData['express_name'] = $orderData['express_name'];
            $orderChildData['express_no'] = $orderData['express_no'];
            $orderChildData['delivery_type'] = $orderData['delivery_type'];
            $orderChildData['note'] = '';
            $orderChildData['is_after_sales'] = CommonBase::SERVICE_NONE;
            $order_child_id = OrderChildModel::addData($orderChildData);
            //YDLib::testLog('生成子单');
            //YDLib::testLog($orderChildData);
            if (!$order_child_id) {
                throw new \InvalidArgumentException('生成子单失败！');
            }

            //生成商品详情
            foreach ($this->orderProducts as $k => $v) {
                $orderChildProductData = [];
                $orderChildProductData['supplier_id'] = $orderData['supplier_id'];
                $orderChildProductData['user_id'] = $data['user_id'];
                $orderChildProductData['order_id'] = $order_id;
                $orderChildProductData['order_no'] = $orderData['order_no'];
                $orderChildProductData['child_order_id'] = $order_child_id;
                $orderChildProductData['child_order_no'] = $orderChildData['child_order_no'];
                $orderChildProductData['brand_id'] = $v['brand_id'];
                $orderChildProductData['brand_name'] = $v['brand_name'];
                $orderChildProductData['category_id'] = $v['category_id'];
                $orderChildProductData['category_name'] = $v['category_name'];
                $orderChildProductData['product_id'] = $v['id'];
                $orderChildProductData['self_code'] = $v['self_code'];
                $orderChildProductData['product_name'] = $v['name'];
                $orderChildProductData['market_price'] = $v['market_price'];
                $orderChildProductData['sale_price'] = $v['sale_price'];
                $orderChildProductData['channel_price'] = $v['channel_price'];
                $orderChildProductData['introduction'] = $v['introduction'];
                $orderChildProductData['logo_url'] = $v['logo_url'];
                $orderChildProductData['now_at'] = $v['now_at'];
                $img_ids = array_column($v['imglist'], 'id');
                $img_ids = implode(',', $img_ids);
                $orderChildProductData['imgs'] = $img_ids;
                $attributes_ids = array_column($v['attribute'], 'id');
                $attributes_ids = implode(',', $attributes_ids);
                $orderChildProductData['attributes'] = $attributes_ids;
                $orderChildProductData['sale_num'] = $v['num'];
                $orderChildProductData['actual_amount'] = $v['actual_amount'];
                $orderChildProductData['discount_amount'] = 0;
                //0库存订单
                $orderChildProductData['is_channel'] = OrderModel::IS_CHANNEL_1;
                if (!empty($v['is_id'])) {
                    $orderChildProductData['is_channel'] = OrderModel::IS_CHANNEL_2;
                    $orderChildProductData['channel_id'] = $v['is_id'];
                    $orderChildProductData['purchase_price'] = $v['channel_price'];
                } else {
                    $orderChildProductData['purchase_price'] = $v['purchase_price'];
                }
                $orderChildProductData['note'] = '';
                $orderChildProductData['is_after_sales'] = CommonBase::SERVICE_NONE;
                $orderChildProductData['return_order_id'] = 0;
                $orderChildProductData['is_return'] = $v['is_return'];
                $orderChildProductData['product_supplier_id'] = $v['product_supplier_id'];
                $order_child_product_id = OrderChildProductModel::addData($orderChildProductData);
                //YDLib::testLog('生成商品详情');
                //YDLib::testLog($orderChildProductData);
                if (!$order_child_product_id) {
                    throw new \InvalidArgumentException('生成商品详情失败！');
                }

                if (!empty($v['is_id'])) {//0库存订单
                    /**
                     * 虚拟商品锁库存
                     */
                    $channel_product = ProductChannelModel::find( $v['is_id'] );
                    $stock = new VoidStockService($channel_product->toArray());
                    $stock->setType(OrderStockService::LOG_TYPE_11);
                    $stock->setLockNum($v['num']);
                    $stock->lock();
                    /**
                     * 供应商品锁库存
                     */
                    $product = ProductModel::find($v['id']);
                    $stock = new OrderStockService($product->toArray());
                    $stock->setType(OrderStockService::LOG_TYPE_11);
                    $stock->setLockNum($v['num']);
                    $stock->lock();
                } else {
                    //库存变动日志
                    $log_id = ProductStockLogModel::addLog($v['id'], 4, -$v['num'], $v['num'], $auth['id'], $auth['name']);
                    if (!$log_id) {
                        throw new \InvalidArgumentException('生成库存日志失败！');
                    }
                    //变动库存
                    $upData['stock'] = bcadd($v['stock'], -$v['num']);
                    $upData['lock_stock'] = bcadd($v['lock_stock'], $v['num']);
                    $res = ProductModel::updateByID($upData, $v['id']);
                    if (!$res) {
                        throw new \InvalidArgumentException('变动库存失败！');
                    }
                }
            }

            //更新首次消费时间
            $res = UserSupplierModel::updateInfo($data['user_id'],$orderData['order_actual_amount']);
            if (!$res) {
                throw new \InvalidArgumentException('更新首次消费时间失败！');
            }
            $this->newWrite()->commit();
            $jsonData['code'] = '200';
            $jsonData['msg'] = '添加订单成功！';
        } catch (\Exception $e) {
            $this->newWrite()->rollback();
            $jsonData['code'] = '500';
            $jsonData['msg'] = $e->getMessage();
        }
        return $jsonData;
    }

}