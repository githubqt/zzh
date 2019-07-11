<?php
// +----------------------------------------------------------------------
// | 虚拟库存操作
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：lqt
// +----------------------------------------------------------------------
// | Author: lqt  Date:2018/8/29 Time:20:23
// +----------------------------------------------------------------------


namespace Services\Stock;

use Product\ProductChannelModel;
use Product\ProductStockLogModel;

class VoidStockService extends StockService
{
    public function __construct(array $product)
    {
        parent::__construct($product);
    }

    /**
     * 虚拟库存日志变动
     * @return bool
     */
    protected function change()
    {
        $lockStockChange = $stockChange = 0;
        switch ($this->action) {
            case 'in':
                $stockChange = $this->stockNum;
                break;
            case 'out':
                $stockChange = -$this->stockNum;
                break;
            case 'lock'://供应订单下单，虚拟商品锁库存
                $stockChange = $this->stockNum ? -$this->stockNum : -$this->lockNum;
                $lockStockChange = $this->lockNum;
                break;
            case 'unlock':
                $lockStockChange = -$this->lockNum;
                break;
            case 'revert'://供应订单取消，虚拟商品解锁返还库存
                $stockChange = $this->stockNum ? $this->stockNum : $this->lockNum;
                $lockStockChange = -$this->lockNum;
                break;
        }
        $data = [];
        $data['supplier_id'] = $this->product['supplier_id'];
        $data['product_id'] = $this->product['product_id'];
        $data['product_name'] = $this->product['product_name'];
        $data['stock_old'] = $this->product['stock'];
        $data['stock_change'] = $stockChange;
        $data['stock_new'] = bcadd($this->product['stock'], $stockChange);
        $data['lock_stock_old'] = $this->product['lock_stock'];
        $data['lock_stock_change'] = $lockStockChange;
        $data['lock_stock_new'] = bcadd($this->product['lock_stock'], $lockStockChange);
        $data['type'] = $this->type;
        $data['note'] = self::LOG_TYPE[$this->type];
        $data['admin_id'] = $this->adminId;
        $data['admin_name'] = $this->adminName ?: '';
        $ProductStockLogModel = ProductStockLogModel::create($data);
        if (!$ProductStockLogModel->id) {
            throw new \InvalidArgumentException('库存变动写入失败');
        }
        return true;
    }

    /**
     * 更新商品库存
     * @return bool
     */
    protected function stock()
    {
        $ProductChannelModel = ProductChannelModel::find($this->product['id']);
        $ProductChannelModel->stock = bcadd($ProductChannelModel->stock, $this->stockNum);
        if (!$ProductChannelModel->save()) {
            throw new \InvalidArgumentException('更新商品库存失败');
        }
        return true;
    }

    /**
     * 更新商品解锁库存
     * @return bool
     */
    protected function unlockStock()
    {
        $ProductChannelModel = ProductChannelModel::find($this->product['id']);
        $ProductChannelModel->lock_stock = bcadd($ProductChannelModel->lock_stock, $this->lockNum);
        if (!$ProductChannelModel->save()) {
            throw new \InvalidArgumentException('更新商品解锁库存失败');
        }
        return true;
    }

    /**
     * 更新商品锁定库存
     * @return bool
     */
    protected function lockStock()
    {
        $ProductChannelModel = ProductChannelModel::find($this->product['id']);
        $ProductChannelModel->stock = bcsub($ProductChannelModel->stock, $this->lockNum);
        $ProductChannelModel->lock_stock = bcadd($ProductChannelModel->lock_stock, $this->lockNum);
        if (!$ProductChannelModel->save()) {
            throw new \InvalidArgumentException('更新商品锁定库存失败');
        }
        return true;
    }

}