<?php
// +----------------------------------------------------------------------
// | 采购类库存操作
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/29 Time:20:23
// +----------------------------------------------------------------------


namespace Services\Stock;

use Core\GoldPrice;
use Image\ImageModel;
use Product\ProductAttributeModel;
use Product\ProductModel;

class PurchaseStockService extends StockService
{
    protected $old_product = [];

    public function __construct(array $product)
    {
        parent::__construct($product);

        $this->old_product = $this->product;
    }

    /**
     * 重写入库方法
     * @return bool
     * @throws \Exception
     */
    public function in()
    {
        $this->action = 'in';
        try {
            if ($this->isProductExist()) {
                // 商户存在该商品，则更新库存
                $this->verify();
                $this->stock();
                $this->change();
                $this->updatePurchasePrice();
            } else {
                // 判断商户是否存在该商品，不存在则将商品初始化入库
                $this->copyProduct();
                $this->setType(self::LOG_TYPE_1);
                $this->change();
                $this->stock();
            }
            return true;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 更新采购价
     * @return bool
     * @throws \Exception
     */
    protected function updatePurchasePrice()
    {
        $result = ProductModel::updatePurchasePrice($this->product['id'], $this->product['purchase_price']);
        if (!$result) {
            throw new \Exception('更新采购商品采购价失败');
        }
        return true;
    }


    /**
     * 商品是否存在
     * @return bool
     */
    protected function isProductExist()
    {
        return ProductModel::isExist($this->product['self_code'], $this->loginInfo['supplier_id']);
    }

    /**
     * 复制采购商品到新商品
     */
    protected function copyProduct()
    {
        $product = $this->product;

        $new_product = [];
        $new_product['supplier_id'] = $this->loginInfo['supplier_id'];
        $new_product['name'] = $product['name'];
        $new_product['self_code'] = $product['self_code'];
        $new_product['custom_code'] = $product['custom_code'];
        $new_product['market_price'] = $product['market_price'];
        $new_product['sale_price'] = $product['channel_price'];
        $new_product['sale_up_price'] = 0;
        $new_product['sale_is_up'] = 1;
        $new_product['weight'] = $product['weight'];
        $new_product['channel_price'] = $product['channel_price'];
        $new_product['channel_up_price'] = 0;
        $new_product['channel_is_up'] = 1;
        $new_product['purchase_price'] = $product['channel_price'];
        $new_product['category_id'] = $product['category_id'];
        $new_product['category_name'] = $product['category_name'];
        $new_product['brand_id'] = $product['brand_id'];
        $new_product['introduction'] = $product['introduction'];
        $new_product['logo_url'] = $product['logo_url'];
        $new_product['channel_status'] = 1;
        //$new_product['channel_now_at'] = '';
        $new_product['channel_note'] = '';
        $new_product['stock'] = 0;
        $new_product['lock_stock'] = 0;
        $new_product['sale_num'] = 0;
        $new_product['admin_id'] = $this->loginInfo['id'];
        $new_product['admin_name'] = $this->loginInfo['fullname'];
        $new_product['on_status'] = 1;
        //$new_product['now_at'] = '';
        $new_product['type'] = $product['type'];
        $new_product['is_purchase'] = 2;
        $new_product['note'] = '';
        $new_product['describe'] = '';
        $new_product['collect_num'] = 0;
        $new_product['browse_num'] = 0;
        $new_product['is_return'] = $product['is_return'];
        $productModel = ProductModel::create($new_product);
        if ($productModel->save()){
            //置换为入库的新商品
            $this->product = $productModel->toArray();
            //复制原商品属性到新商品
            $this->copyAttributes($product['attribute_info'],$this->product['id']);
            //复制原商品图片到新商品
            $this->copyImages($product['img_info'],$this->product['id']);
            return true;
        }
        return false;
    }

    /**
     * 验证入库商品
     */
    protected function verify()
    {
        $id = ProductModel::getPrimaryId($this->product['self_code'], $this->loginInfo['supplier_id']);
        $productModel = ProductModel::find($id);
        $productModel->purchase_price = $this->product['channel_price'];
        $this->product = $productModel->toArray();
    }

    /**
     * 复制原商品属性到新商品中
     * @param $old_product_id array 原商品属性
     * @param $new_product_id int 新商品ID
     */
    protected function copyAttributes(array $old_attrs,$new_product_id)
    {
        foreach ($old_attrs as $attr){
            ProductAttributeModel::create([
                'supplier_id' => $this->loginInfo['supplier_id'],
                'product_id' => $new_product_id,
                'attribute_id' => $attr['attribute_id'],
                'attribute_name' => $attr['attribute_name'],
                'attribute_value_id' => $attr['attribute_value_id'],
                'attribute_value_name' => $attr['attribute_value_name'],
                'type' => $attr['type'],
            ]);
        }
    }

    /**
     * 复制原商品图片到新商品中
     * @param $old_product_id array 原商品图片
     * @param $new_product_id int 新商品ID
     */
    protected function copyImages(array $old_images,$new_product_id)
    {
        foreach ($old_images as $img){
            ImageModel::create([
                'supplier_id' => $this->loginInfo['supplier_id'],
                'obj_id' => $new_product_id,
                'type' => 'product',
                'img_url' => $img['img_url'],
                'img_type' => $img['img_type'],
            ]);
        }
    }

}