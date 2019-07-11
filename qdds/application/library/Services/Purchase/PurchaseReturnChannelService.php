<?php
// +----------------------------------------------------------------------
// | 采购退货服务类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/9/13 Time:11:55
// +----------------------------------------------------------------------


namespace Services\Purchase;


use Admin\AdminModel;
use Assemble\Source;
use Common\SerialNumber;
use Core\Express;
use Express\ExpressCompanyModel;
use Image\ImageModel;
use Product\ProductChannelModel;
use Product\ProductModel;
use Purchase\PurchaseModel;
use Purchase\PurchaseOrderChildModel;
use Purchase\PurchaseOrderChildProductModel;
use Purchase\PurchaseReturnModel;
use Purchase\PurchaseReturnProductModel;
use Purchase\PurchaseTrackingLogModel;
use Services\Stock\PurchaseStockService;
use Services\Stock\VoidStockService;

class PurchaseReturnChannelService extends PurchaseReturnService
{

    /**
     * 退货渠道商品
     * @var
     */
    protected $channel;

    /**
     * 生成退货单
     * @param $data
     * @param $images
     * @return array|bool
     */
    public function purchaseReturn($data, $images)
    {
        $this->childOrderNo = $data['child_order_no'];
        $this->returnNum = $data['return_num'];
        $this->channel = $data['channel'];
        $this->returnType = $data['role_id'];
        $this->returnNote = $data['note'];
        $this->returnImages = $images;
        // 子单查询购买中的商品
        $this->returnOrder = PurchaseOrderChildModel::getChildOrderProductBy($this->childOrderNo);
        $this->purchase = PurchaseModel::getPurchaseById($this->returnOrder['purchase_id']);
        if (empty($this->returnOrder)) {
            $this->error = '当前退货商品订单不存在';
            return false;
        }
        $this->newWrite()->beginTransaction();
        try {
            // 写入退货表
            if (!$this->createPurchaseReturn()) {
                throw new \Exception('写入退货表失败');
            }

            // 写入退货商品表
            if (!$this->createPurchaseReturnProduct()) {
                throw new \Exception('写入退货商品表失败');
            }
            // 写入退货图片
            $this->createReturnImage();

            // 退货日志
            purchaseTrackingLog(
                $this->returnOrder ['purchase_id'],
                $this->returnOrder ['child_order_no'],
                '创建',
                '创建退货单',
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
     * 写入退货商品表
     * @return bool
     */
    protected function createPurchaseReturnProduct()
    {
        try {
            foreach ($this->returnNum as $purchase_product_id => $num) {
                if ($num > 0) {
                    $purchaseProduct = PurchaseOrderChildProductModel::getProductByPurchaseProductId($purchase_product_id);
                    if ($num > $purchaseProduct['num']) {
                        throw new \Exception('退货数量超出了采购数量，操作被终止');
                    }
                    if ($purchaseProduct['is_return'] == '1') {
                        throw new \Exception('商品'.$purchase_product_id.'不允许退货');
                    }

                    //供应商品退货
                    $this->returnProductModels[] = PurchaseReturnProductModel::create([
                        'product_id' => $purchaseProduct['product_id'],
                        'channel_id' => $this->channel[$purchase_product_id],
                        'product_name' => $purchaseProduct['product_name'],
                        'self_code' => $purchaseProduct['self_code'],
                        'custom_code' => $purchaseProduct['custom_code'],
                        'product_cover' => $purchaseProduct['product_cover'],
                        'category_name' => $purchaseProduct['category_name'],
                        'brand_name' => $purchaseProduct['brand_name'],
                        'introduction' => $purchaseProduct['introduction'],
                        'num' => $purchaseProduct['num'],
                        'return_num' => $num,
                        'price' => $purchaseProduct['price'],
                        'return_price' => bcmul($num, $purchaseProduct['channel_price']),
                        'return_no' => $this->returnPurchase['return_no'],
                        'note' => '',
                        'type' => Source::MERCHANT_ID,
                        'purchase_status' => $this->purchase['purchase_type'],
                        'market_price' => $purchaseProduct['market_price'],
                        'channel_price' => $purchaseProduct['channel_price'],
                        'supplier_id' => $this->returnOrder['supplier_id'],
                        'purchase_product_id' => $purchaseProduct['purchase_product_id'],
                    ]);

                    /**
                     * 虚拟商品锁库存
                     */
                    $channel_product = ProductChannelModel::find( $this->channel[$purchase_product_id] );
                    $stock = new VoidStockService($channel_product->toArray());
                    $stock->setType(VoidStockService::LOG_TYPE_25);
                    $stock->setLockNum($num);
                    $stock->lock();

                }
            }
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }
}