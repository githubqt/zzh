<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/20 Time:15:30
// +----------------------------------------------------------------------


namespace Purchase;


use Brand\BrandModel;
use Product\ProductModel;

class PurchaseProductModel extends \BaseModel
{
    public static function createPurchaseProduct($product_id, $num, $purchase_id, $order_child_product_id=0)
    {
        $product = ProductModel::find($product_id, [
            'id', 'name', 'supplier_id', 'self_code', 'custom_code',
            'market_price', 'channel_price', 'category_name', 'category_id',
            'logo_url', 'brand_id', 'introduction','is_return'
        ],
            [
                'channel_status' => 3    // 上架到渠道：1未上架 2上架
            ]);

        if (!$product->id) {
            return false;
        }
        //获取品牌
        $brand = BrandModel::find($product->brand_id, ['name']);

        $PurchaseProduct = new self();
        $PurchaseProduct->purchase_id = $purchase_id;
        $PurchaseProduct->product_id = $product_id;
        $PurchaseProduct->self_code = $product->self_code;
        $PurchaseProduct->custom_code = $product->custom_code;
        $PurchaseProduct->product_name = $product->name;
        $PurchaseProduct->product_cover = $product->logo_url;
        $PurchaseProduct->category_name = $product->category_name;
        $PurchaseProduct->brand_name = $brand->name ?: '';
        $PurchaseProduct->introduction = $product->introduction;
        $PurchaseProduct->num = $num;
        $PurchaseProduct->price = self::totalPrice($product->channel_price, $num);
        $PurchaseProduct->market_price = $product->market_price;
        $PurchaseProduct->channel_price = $product->channel_price;
        $PurchaseProduct->supplier_id = $product->supplier_id;
        $PurchaseProduct->is_return = $product->is_return;
        $PurchaseProduct->archives = json_encode(ProductModel::getArchivesByID($product_id));
        $PurchaseProduct->order_child_product_id = $order_child_product_id;
        $return = $PurchaseProduct->save();
        if ($return) {
            return [$PurchaseProduct, 0];
        } else {
            return [null, 1];
        }
    }

    protected static function totalPrice($price, $num)
    {
        return bcmul($price, $num, 2);
    }

    public static function getProductGroupBySupplierByPurchaseId($purchase_id)
    {
        $sql = "SELECT `supplier_id`,`purchase_id` FROM `" . static::getFullTable() . "` WHERE purchase_id = '{$purchase_id}' GROUP BY supplier_id";

        $list = self::newRead()->YDGetAll($sql);
        dc($list);
        $data = [];
        foreach ((array)$list as $item) {
            $data[$item['supplier_id']] = $item;
        }
        dc($data);
        return $data;
    }

    public static function getProductByPurchaseIdAndSupplierId($purchase_id, $supplier_id)
    {
        $sql = "SELECT * FROM `" . static::getFullTable() . "` WHERE purchase_id = '{$purchase_id}' AND supplier_id = '{$supplier_id}'";
        return self::newRead()->YDGetAll($sql);
    }

    public static function getProductByPurchaseId($purchase_id)
    {
        $sql = "SELECT * FROM `" . static::getFullTable() . "` WHERE purchase_id = '{$purchase_id}'";
        return self::newRead()->YDGetAll($sql);
    }
    
    
    
    /**
     * 根据子订单创建退货信息后更新采购数量
     * @param $child_order_no
     * @param $num
     * @param $price
     * @return bool|\PDOStatement
     */
    public static function updateNumByChildOrder($id, $num,$price)
    {
    	return self::newWrite()->update(self::table(),['num'=>$num ,'price' => $price],['id'=>$id]);
    }
    
}