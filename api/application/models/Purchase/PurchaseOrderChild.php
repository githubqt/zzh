<?php
// +----------------------------------------------------------------------
// | 采购子订单模型
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/20 Time:15:33
// +----------------------------------------------------------------------


namespace Purchase;

use Assemble\Builder;
use Purchase\PurchaseProductModel;


class PurchaseOrderChildModel extends \BaseModel
{
    /**
     * 查询需要显示的列
     * @var array
     */
    public static $showColumns = [
        'id', 'supplier_id', 'purchase_supplier_id', 'purchase_id', 'order_id', 'order_no',
        'child_order_no', 'child_order_original_amount', 'child_order_actual_amount','purchase_num', 'child_pay_type',
        'child_status', 'express_id', 'express_name', 'express_pinyin', 'express_no', 'delivery_time',
        'delivery_type', 'delivery_no', 'take_delivery_time','is_return', 'note', 'created_at',
    ];

    /**
     * 通过主订单号获取各子订单
     * @param $order_no
     * @return array
     */
    public static function getChildOrderByOrderNO($order_no)
    {
        $purchaseColumns = self::$showColumns;
        $builder = new Builder();
        $builder->select($purchaseColumns);
        $form = sprintf("`%s`", self::getFullTable());
        $builder->from($form);
        $builder->where('order_no', $order_no);
        $builder->where('is_del', self::DELETE_SUCCESS);
        $sql = $builder->showSql()['query'];
        $list = self::newWrite()->YDGetAll($sql);

        foreach ($list as $k => $item) {
            $list[$k]['order_status_text'] = PurchaseModel::showOrderStatusText($item['child_status']);
        }

        return $list;

    }

    /**
     * 通过主订单号获取各子订单的商品列表
     * @param $order_no
     * @return array
     */
    public static function getChildOrderProductByOrderNO($order_no)
    {
        $orders = self::getChildOrderByOrderNO($order_no);
        foreach ($orders as $k => $order) {
            $orders[$k]['products'] = PurchaseOrderChildProductModel::getProductByChildOrderNO($order['child_order_no']);
        }

        return $orders;
    }

    /**
     * 通过主订单号更新主订单下所有子订单状态
     * @param $order_no
     * @param $status
     * @return bool|\PDOStatement
     */
    public static function updateStatus($order_no, $status)
    {
        return self::newWrite()->update(self::table(),['child_status'=>$status],['order_no'=>$order_no]);
    }


    /**
     * 更新某一子订单状态
     * @param $child_order_no
     * @param $status
     * @return bool|\PDOStatement
     */
    public static function updateStatusByChildOrderNo($child_order_no, $status)
    {
        return self::newWrite()->update(self::table(),['child_status'=>$status],['child_order_no'=>$child_order_no]);
    }





    /**
     * 通过子订单号获取单条订单
     * @param $order_child_no
     * @return array
     */
    public static function getChildOrderByRow($order_child_no)
    {
    	$purchaseColumns = self::$showColumns;
    	$builder = new Builder();
    	$builder->select($purchaseColumns);
    	$form = sprintf("`%s`", self::getFullTable());
    	$builder->from($form);
    	$builder->where('child_order_no', $order_child_no);
    	$builder->where('is_del', self::DELETE_SUCCESS);
    	$sql = $builder->showSql()['query'];
    	$list = self::newRead()->YDGetRow($sql);

    	return $list;

    }

    /**
     * 通过主订单号获取单个子订单
     * @param $child_order_no
     * @return array
     */
    public static function getChildOrderByChildOrderNO($child_order_no)
    {
        $purchaseColumns = self::$showColumns;
        $builder = new Builder();
        $builder->select($purchaseColumns);
        $form = sprintf("`%s`", self::getFullTable());
        $builder->from($form);
        $builder->where('child_order_no', $child_order_no);
        $builder->where('is_del', self::DELETE_SUCCESS);
        $sql = $builder->showSql()['query'];
        $list = self::newRead()->YDGetRow($sql);
        if (!empty($list)){
            $list['order_status_text'] = PurchaseModel::showOrderStatusText($list['child_status']);
        }
        return $list;
    }



    /**
     * 根据子订单创建退货信息后更新采购数量
     * @param $child_order_no
     * @param $num
     * @return bool|\PDOStatement
     */
    public static function updateNumByChildOrder($child_order_no, $num)
    {
    	return self::newWrite()->update(self::table(),['purchase_num'=>$num],['child_order_no'=>$child_order_no]);
    }




    /**
     * 通过子订单号获取商品列表
     * @param $order_child_no
     * @return array
     */
    public static function getChildOrderReturnProduct($order_child_no)
    {
    	$orders = self::getChildOrderByRow($order_child_no);
    	if($orders){
    		$orders['products'] = PurchaseProductModel::getProductByChildOrderNO($orders['child_order_no']);
    	}
    	return $orders;
    }


        /**
         * 通过子订单号获取商品列表
        * @param $order_child_no
         * @return array
         */
        public static function getChildOrderProductBy($order_child_no)
       {
    	       $orders = self::getChildOrderByRow($order_child_no);
    	       if($orders){
    		      $orders['products'] = PurchaseOrderChildProductModel::getProductByChildOrderNO($orders['child_order_no']);
    		       }
    		      return $orders;

    	  }




    /**
     * 通过子订单号获取快递信息
     * @param $child_order_no
     * @return array
     */
    public static function getExpressByChildOrderNo($child_order_no)
    {
        $result = [];
        $row = self::getOrderInfoByChildOrderNo($child_order_no);
        if ($row) {
            $result ['express_id'] = $row['express_id'];
            $result ['express_name'] = $row['express_name'];
            $result ['express_pinyin'] = $row['express_pinyin'];
            $result ['express_no'] = $row['express_no'];
            $result ['child_status'] = $row['child_status'];
        }
        return $result;
    }

    /**
     * 通过子订单号获取订单信息
     * @param $child_order_no
     * @return array
     */
    public static function getOrderInfoByChildOrderNo($child_order_no)
    {
        $columns = self::$showColumns;
        $builder = new Builder();
        $builder->select($columns);
        $form = sprintf("`%s`", self::getFullTable());
        $builder->from($form);
        $builder->where('child_order_no', $child_order_no);
        $builder->where('is_del', self::DELETE_SUCCESS);
        $sql = $builder->showSql()['query'];
        return self::newRead()->YDGetRow($sql);
    }



    /**
     * 获取子订单数
     * @param $order_no
     * @param array $status
     * @return mixed
     */
    public static function getChildOrderCount($order_no,array $status = [])
    {
        $purchaseColumns = self::$showColumns;
        $builder = new Builder();
        $builder->select($purchaseColumns);
        $form = sprintf("`%s`", self::getFullTable());
        $builder->from($form);
        $builder->where('order_no', $order_no);
        $builder->where('is_del', self::DELETE_SUCCESS);

        if (!empty($status)){
            $builder->where('child_status', 'in',$status);
        }

        $sql = $builder->showSql()['count'];
        $row = self::newRead()->YDGetRow($sql);
        return $row['aggregate'];
    }



}