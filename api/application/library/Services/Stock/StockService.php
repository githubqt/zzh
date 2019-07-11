<?php
// +----------------------------------------------------------------------
// | 库存操作基类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/29 Time:17:31
// +----------------------------------------------------------------------


namespace Services\Stock;


use Admin\AdminModel;
use Assemble\Builder;
use Product\ProductModel;
use Product\ProductStockLogModel;
use Services\BaseService;

abstract class StockService extends BaseService implements StockInterface
{
    const LOG_TYPE_0 = '0';//系统操作
    const LOG_TYPE_1 = '1';//库存初始化
    const LOG_TYPE_2 = '2';//后台库存增加
    const LOG_TYPE_3 = '3';//后台库存减少
    const LOG_TYPE_4 = '4';//普通订单下单锁定
    const LOG_TYPE_5 = '5';//普通订单发货出库
    const LOG_TYPE_6 = '6';//普通订单取消解锁
    const LOG_TYPE_7 = '7';//普通订单退货入库
    const LOG_TYPE_8 = '8';//活动启动锁定
    const LOG_TYPE_9 = '9';//活动领取发货
    const LOG_TYPE_10 = '10';//活动取消解锁
    const LOG_TYPE_11 = '11';//供应订单下单锁定
    const LOG_TYPE_12 = '12';//供应订单取消解锁
    const LOG_TYPE_13 = '13';//供应订单发货出库
    const LOG_TYPE_14 = '14';//供应订单退货入库
    const LOG_TYPE_15 = '15';//普通采购下单锁定
    const LOG_TYPE_16 = '16';//普通采购取消解锁
    const LOG_TYPE_17 = '17';//普通采购出库
    const LOG_TYPE_18 = '18';//普通采购入库
    const LOG_TYPE_19 = '19';//普通采购退货出库
    const LOG_TYPE_20 = '20';//普通采购退货入库
    const LOG_TYPE_21 = '21';//供应采购下单锁定
    const LOG_TYPE_22 = '22';//供应采购取消解锁
    const LOG_TYPE_23 = '23';//供应采购出库
    const LOG_TYPE_24 = '24';//供应采购入库
    const LOG_TYPE_25 = '25';//供应采购退货锁定
    const LOG_TYPE_26 = '26';//供应采购退货解锁
    const LOG_TYPE_27 = '27';//回收采购下单锁定
    const LOG_TYPE_28 = '28';//回收采购取消解锁
    const LOG_TYPE_29 = '29';//回收采购出库
    const LOG_TYPE_30 = '30';//回收采购入库
    const LOG_TYPE_31 = '31';//回收采购退货锁定
    const LOG_TYPE_32 = '32';//回收采购退货解锁

    // 变动类型
    const LOG_TYPE = array(
        0 => '系统操作',
        1 => '库存初始化',
        2 => '后台库存增加',
        3 => '后台库存减少',
        4 => '普通订单下单锁定',
        5 => '普通订单发货出库',
        6 => '普通订单取消解锁',
        7 => '普通订单退货入库',
        8 => '活动启动锁定',
        9 => '活动领取发货',
        10 => '活动取消解锁',
        11 => '供应订单下单锁定',
        12 => '供应订单取消解锁',
        13 => '供应订单发货出库',
        14 => '供应订单退货入库',
        15 => '普通采购下单锁定',
        16 => '普通采购取消解锁',
        17 => '普通采购出库',
        18 => '普通采购入库',
        19 => '普通采购退货出库',
        20 => '普通采购退货入库',
        21 => '供应采购下单锁定',
        22 => '供应采购取消解锁',
        23 => '供应采购出库',
        24 => '供应采购入库',
        25 => '供应采购退货锁定',
        26 => '供应采购退货解锁',
        27 => '回收采购下单锁定',
        28 => '回收采购取消解锁',
        29 => '回收采购出库',
        30 => '回收采购入库',
        31 => '回收采购退货锁定',
        32 => '回收采购退货解锁',
    );

    /**
     * 商品信息
     * @var array
     */
    protected $product;
    /**
     * 修改商品库存数
     * @var int
     */
    protected $stockNum = 0;

    /**
     * 修改商品锁定数
     * @var int
     */
    protected $lockNum = 0;

    /**
     * 库存变动类型
     * @var int
     */
    protected $type = 0;

    /**
     * 当前系统登录信息
     * @var array
     */
    protected $loginInfo;

    /**
     * 操作人ID
     * @var int
     */
    protected $adminId = 0;

    /**
     * 操作人名称
     * @var string
     */
    protected $adminName = '系统';

    /**
     * 操作动作 可选值： in|out|lock|unlock|revert
     * @var null
     */
    protected $action = null;


    public function __construct(array $product)
    {
        $this->product = $product;
        $this->loginInfo = AdminModel::getCurrentLoginInfo();
    }

    /**
     * 设置库存数量
     * @param int $num
     */
    public function setStockNum(int $num)
    {
        $this->stockNum = $num;
    }

    /**
     * 获取库存数量
     * @return int
     */
    public function getStockNum()
    {
        return abs($this->stockNum);
    }

    /**
     * 设置锁定数量
     * @param int $num
     */
    public function setLockNum(int $num)
    {
        $this->lockNum = $num;
    }

    /**
     * 获取锁定数量
     * @return int
     */
    public function getLockNum()
    {
        return abs($this->lockNum);
    }

    /**
     * 设置操作人ID，默认是 0 既系统
     * @param int $admin_id
     */
    public function setAdminId(int $admin_id)
    {
        $this->adminId = $admin_id;
    }

    /**
     * 获取操作人ID
     * @return int
     */
    public function getAdminId()
    {
        return $this->adminId;
    }

    /**
     * 设置操作人，默认是 系统
     * @param string $name
     */
    public function setAdminName($name)
    {
        $this->adminName = $name;
    }

    /**
     * 获取操作人
     * @return string
     */
    public function getAdminName()
    {
        return $this->adminName;
    }

    /**
     * 设置出入库类型
     * @param int $type
     */
    public function setType(int $type)
    {
        $this->type = $type;
    }

    /**
     * 获取出入口类型
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 锁定商品库存
     * @return bool
     * @throws \Exception
     */
    public function lock()
    {
        $this->action = 'lock';
        //$this->newWrite()->beginTransaction();
        try {
            $this->lockStock();
            $this->change();
            //$this->newWrite()->commit();
            return true;
        } catch (\Exception $exception) {
            //$this->newWrite()->rollback();
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 解锁商品库存
     * @return bool
     * @throws \Exception
     */
    public function unlock()
    {
        $this->action = 'unlock';
        //$this->newWrite()->beginTransaction();
        try {
            $this->change();
            $this->lockNum = -$this->lockNum;
            $this->unlockStock();
            //$this->newWrite()->commit();
            return true;
        } catch (\Exception $exception) {
            //$this->newWrite()->rollback();
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 商品出库
     * @return bool
     * @throws \Exception
     */
    public function out()
    {
        $this->action = 'out';
        //$this->newWrite()->beginTransaction();
        try {
            $this->stock();
            $this->change();
            //$this->newWrite()->commit();
            return true;
        } catch (\Exception $exception) {
            //$this->newWrite()->rollback();
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 商品入库
     * @return bool
     * @throws \Exception
     */
    public function in()
    {
        $this->action = 'in';
        //$this->newWrite()->beginTransaction();
        try {
            if (!$this->isProductExist()) {
                $this->initProduct();
            } else {
                $this->verify();
                $this->stock();
                $this->change();
            }
            //$this->newWrite()->commit();
            return true;
        } catch (\Exception $exception) {
            //$this->newWrite()->rollback();
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 库存日志变动
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
            case 'lock':
                $stockChange = $this->stockNum ? -$this->stockNum : -$this->lockNum;
                $lockStockChange = $this->lockNum;
                break;
            case 'unlock':
                $lockStockChange = -$this->lockNum;
                break;
            case 'revert':
                $stockChange = $this->stockNum ? $this->stockNum : $this->lockNum;
                $lockStockChange = -$this->lockNum;
                break;
        }
        $data = [];
        $data['supplier_id'] = $this->product['supplier_id'];//$this->loginInfo['supplier_id'];
        $data['product_id'] = $this->product['id'];
        $data['product_name'] = $this->product['name'];
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
        $ProductModel = ProductModel::find($this->product['id']);
        $ProductModel->stock = bcadd($ProductModel->stock, $this->stockNum);
        if (!$ProductModel->save()) {
            throw new \InvalidArgumentException('更新商品库存失败');
        }
        return true;
    }

    /**
     * 更新商品锁定库存
     * @return bool
     */
    protected function lockStock()
    {
        $ProductModel = ProductModel::find($this->product['id']);
        $ProductModel->stock = bcsub($ProductModel->stock, $this->lockNum);
        $ProductModel->lock_stock = bcadd($ProductModel->lock_stock, $this->lockNum);
        if (!$ProductModel->save()) {
            throw new \InvalidArgumentException('更新商品锁定库存失败');
        }
        return true;
    }

    /**
     * 更新商品解锁库存
     * @return bool
     */
    protected function unlockStock()
    {
        $ProductModel = ProductModel::find($this->product['id']);
        $ProductModel->lock_stock = bcadd($ProductModel->lock_stock, $this->lockNum);
        if (!$ProductModel->save()) {
            throw new \InvalidArgumentException('更新商品解锁库存失败');
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
     * 初始化商品
     */
    protected function initProduct()
    {
        $product = $this->product;
        unset($product['id']);
        unset($product['is_del']);
        unset($product['created_at']);
        unset($product['updated_at']);
        unset($product['deleted_at']);
        $product['supplier_id'] = $this->loginInfo['supplier_id'];
        $product['channel_status'] = 1;
        $product['on_status'] = 1;
        $product['stock'] = 0;
        $product['lock_stock'] = 0;
        $product['sale_num'] = 0;
        $product['purchase_price'] = $product['channel_price'];
        $productModel = ProductModel::create($product);
        $this->product = $productModel->toArray();
        $this->setType(self::LOG_TYPE_1);
        $this->change();
        $productModel->stock = bcadd($productModel->stock, $this->stockNum);
        $productModel->save();
    }

    /**
     * 验证入库商品
     */
    protected function verify()
    {
        $id = ProductModel::getPrimaryId($this->product['self_code'], $this->loginInfo['supplier_id']);
        $productModel = ProductModel::find($id);
        $this->product = $productModel->toArray();
    }

    /**
     * 退货 用于取消订单，解锁商品库存
     * @return bool
     * @throws \Exception
     */
    public function revert()
    {
        $this->action = 'revert';
        //$this->newWrite()->beginTransaction();
        try {
            $this->change();
            if (!$this->stockNum) {
                $this->stockNum = $this->lockNum;
            }
            $this->stock();
            $this->lockNum = -$this->lockNum;
            $this->unlockStock();
            //$this->newWrite()->commit();
            return true;
        } catch (\Exception $exception) {
            //$this->newWrite()->rollback();
            throw new \Exception($exception->getMessage());
        }
    }

}