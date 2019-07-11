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

class PurchaseReturnService extends PurchaseService
{

    /**
     * 退货订单
     * @var
     */
    protected $returnOrder;
    /**
     * 退货子订单号
     * @var
     */
    protected $childOrderNo;

    /**
     * 退货商品ID及退货数量
     * @var array
     */
    protected $returnNum = [];

    /**
     * 退货商品模型对象
     * @var array
     */
    protected $returnProductModels = [];

    /**
     * 退货订单对象
     * @var
     */
    protected $returnPurchase;

    /**
     * 退货类型
     * @var int
     */
    protected $returnType = 1;

    /**
     * 退货描述
     * @var string
     */
    protected $returnNote = '';

    /**
     * 退货图片
     * @var array
     */
    protected $returnImages = [];

    const PURCHASE_RETURN_ORDER_STATUS_5 = 5;  //平台拒收
    const PURCHASE_RETURN_ORDER_STATUS_9 = 9;  //平台审核中
    const PURCHASE_RETURN_ORDER_STATUS_11 = 11;  //商户发货中
    const PURCHASE_RETURN_ORDER_STATUS_15 = 15;  //平台审核拒绝
    const PURCHASE_RETURN_ORDER_STATUS_41 = 41;  //平台收货中
    const PURCHASE_RETURN_ORDER_STATUS_61 = 61;  //供应商拒收
    const PURCHASE_RETURN_ORDER_STATUS_62 = 62;  //平台已收货
    const PURCHASE_RETURN_ORDER_STATUS_63 = 63;  //供应商退回中
    const PURCHASE_RETURN_ORDER_STATUS_64 = 64;  //供应商已发货
    const PURCHASE_RETURN_ORDER_STATUS_70 = 70;  //退货完成
    const PURCHASE_RETURN_ORDER_STATUS_81 = 81;  //已取消

    /**
     * 采购退货状态数组，用于视图页面
     */
    const PURCHASE_RETURN_ORDER_STATUS_TO_VIEWS = [
        'status_5' => self::PURCHASE_RETURN_ORDER_STATUS_5,
        'status_9' => self::PURCHASE_RETURN_ORDER_STATUS_9,
        'status_11' => self::PURCHASE_RETURN_ORDER_STATUS_11,
        'status_15' => self::PURCHASE_RETURN_ORDER_STATUS_15,
        'status_41' => self::PURCHASE_RETURN_ORDER_STATUS_41,
        'status_61' => self::PURCHASE_RETURN_ORDER_STATUS_61,
        'status_62' => self::PURCHASE_RETURN_ORDER_STATUS_62,
        'status_63' => self::PURCHASE_RETURN_ORDER_STATUS_63,
        'status_64' => self::PURCHASE_RETURN_ORDER_STATUS_64,
        'status_70' => self::PURCHASE_RETURN_ORDER_STATUS_70,
        'status_81' => self::PURCHASE_RETURN_ORDER_STATUS_81,
    ];

    /**
     * 采购退货状态定义
     */
    const PURCHASE_RETURN_ORDER_STATUS_CODES = [
        /**
         *  平台状态数组
         */
        Source::PLATFORM_ID => [
            self::PURCHASE_RETURN_ORDER_STATUS_5 => '平台拒收',
            self::PURCHASE_RETURN_ORDER_STATUS_9 => '待审核',
            self::PURCHASE_RETURN_ORDER_STATUS_11 => '已审核',
            self::PURCHASE_RETURN_ORDER_STATUS_15 => '审核拒绝',
            self::PURCHASE_RETURN_ORDER_STATUS_41 => '商户发货',
            self::PURCHASE_RETURN_ORDER_STATUS_61 => '供应商拒收',
            self::PURCHASE_RETURN_ORDER_STATUS_62 => '已收货',
            self::PURCHASE_RETURN_ORDER_STATUS_63 => '退货中',
            self::PURCHASE_RETURN_ORDER_STATUS_64 => '拒收寄回',
            self::PURCHASE_RETURN_ORDER_STATUS_70 => '退货完成',
            self::PURCHASE_RETURN_ORDER_STATUS_81 => '已取消',
        ],

        /**
         * 商户状态数组
         */
        Source::MERCHANT_ID => [
            self::PURCHASE_RETURN_ORDER_STATUS_5 => '已拒收',
            self::PURCHASE_RETURN_ORDER_STATUS_9 => '待审核',
            self::PURCHASE_RETURN_ORDER_STATUS_11 => '已审核',
            self::PURCHASE_RETURN_ORDER_STATUS_15 => '审核拒绝',
            self::PURCHASE_RETURN_ORDER_STATUS_41 => '退货中',
            self::PURCHASE_RETURN_ORDER_STATUS_61 => '退货中',
            self::PURCHASE_RETURN_ORDER_STATUS_62 => '退货中',
            self::PURCHASE_RETURN_ORDER_STATUS_63 => '退货中',
            self::PURCHASE_RETURN_ORDER_STATUS_64 => '退货中',
            self::PURCHASE_RETURN_ORDER_STATUS_70 => '退货完成',
            self::PURCHASE_RETURN_ORDER_STATUS_81 => '已取消',
        ],
        /**
         * 供应商状态数组
         */
        Source::PROVIDER_ID => [
            self::PURCHASE_RETURN_ORDER_STATUS_5 => '已拒收',
            self::PURCHASE_RETURN_ORDER_STATUS_9 => '待审核',
            self::PURCHASE_RETURN_ORDER_STATUS_11 => '已审核',
            self::PURCHASE_RETURN_ORDER_STATUS_15 => '审核拒绝',
            self::PURCHASE_RETURN_ORDER_STATUS_41 => '商品寄回',
            self::PURCHASE_RETURN_ORDER_STATUS_61 => '已拒收',
            self::PURCHASE_RETURN_ORDER_STATUS_62 => '退货中',
            self::PURCHASE_RETURN_ORDER_STATUS_63 => '已拒收',
            self::PURCHASE_RETURN_ORDER_STATUS_64 => '商品寄回',
            self::PURCHASE_RETURN_ORDER_STATUS_70 => '退货完成',
            self::PURCHASE_RETURN_ORDER_STATUS_81 => '已取消',
        ],
        /**
         * 渠道商状态数组
         */
        Source::CHANNEL_ID => [

        ]
    ];

    /**
     * 定义退货原因数组
     */
    const PURCHASE_RETURN_REASONS = [
        '1' => '商品破损',
        '2' => '颜色/尺寸/参数不合',
        '3' => '商品瑕疵',
        '4' => '质量问题',
        '5' => '假冒品牌',
    ];

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
     * 编辑退货
     * @param $id
     * @param $data
     * @param $images
     * @return bool
     */
    public function purchaseReturnEdit($id, $data, $images)
    {
        $this->childOrderNo = $data['child_order_no'];
        $this->returnNum = $data['return_num'];
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
        $returnOrder = $this->showReturnDetail($id);

        $this->newWrite()->beginTransaction();
        try {

            // 总退货数
            $returnNum = 0;
            // 总退货金额
            $returnPrice = 0;
            foreach ($this->returnNum as $purchase_product_id => $num) {
                if ($num > 0) {
                    $purchaseProduct = PurchaseOrderChildProductModel::getProductByPurchaseProductId($purchase_product_id);
                    $returnNum += $num;
                    $returnPrice += $num * $purchaseProduct['channel_price'];
                }
            }

            // 更新退货表
            $model = PurchaseReturnModel::find($returnOrder['id']);
            $model->return_number = $returnNum;
            $model->return_money = $returnPrice;
            $model->return_status = $returnNum == $this->returnOrder['purchase_num'] ? 2 : 1;
            $model->return_type = $this->returnType;
            $model->return_reason = $this->returnNote;
            $model->order_status = self::PURCHASE_ORDER_STATUS_9;
            if (!$model->save()) {
                throw new \Exception('更新退货表失败');
            }

            // 写入退货商品表
            foreach ($this->returnNum as $purchase_product_id => $num) {
                // 有退货
                if ($num > 0) {
                    $purchaseProduct = PurchaseOrderChildProductModel::getProductByPurchaseProductId($purchase_product_id);
                    if ($num > $purchaseProduct['num']) {
                        throw new \Exception('退货数量超出了采购数量，操作被终止');
                    }

                    // 更新已存在的退货商品
                    $product = PurchaseReturnProductModel::product($returnOrder['return_no'], $purchase_product_id);
                    if ($product) {
                        $model = PurchaseReturnProductModel::find($product['id']);
                        $model->return_num = $num;
                        $model->return_price = bcmul($num, $purchaseProduct['channel_price']);
                        if (!$model->save()) {
                            throw new \Exception('更新已存在的退货商品失败');
                        }
                        $this->returnProductModels[] = $model;
                    } else {
                        // 新建不存在的退货商品
                        $this->returnProductModels[] = PurchaseReturnProductModel::create([
                            'product_id' => $purchaseProduct['product_id'],
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
                    }
                } else {
                    // 无退货
                    // 删除已存在的退货商品
                    $product = PurchaseReturnProductModel::product($returnOrder['return_no'], $purchase_product_id);
                    if ($product) {
                        $model = PurchaseReturnProductModel::find($product['id']);
                        $model->delete();
                    }
                }
            }
            // 写入退货图片
            $this->createReturnImage();

            // 退货日志
            purchaseTrackingLog(
                $this->returnOrder ['purchase_id'],
                $this->returnOrder ['child_order_no'],
                '编辑',
                '重新退货',
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
     * 获取退货订单信息
     * @param $child_order_no
     * @return array|bool
     */
    public function showChildProducts($child_order_no)
    {
        // 子单查询购买中的商品
        $result = PurchaseOrderChildModel::getChildOrderProductBy($child_order_no);

        foreach ($result['products'] as $k => $product) {
            $return_num = PurchaseReturnModel::getSumReturnNumByChildOrderNo($child_order_no, $product['purchase_product_id']);
            $result['products'][$k]['return_num'] = $return_num;
            $result['products'][$k]['can_return_num'] = $product['num'] - $return_num;
        }
        if (empty($result)) {
            return false;
        }
        return $result;
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

                    $id = ProductModel::getPrimaryId($purchaseProduct['self_code'], $this->returnOrder['purchase_supplier_id']);
                    if (!$id){
                        throw new \Exception('当前库存不存在退货商品');
                    }

                    $this->returnProductModels[] = PurchaseReturnProductModel::create([
                        'product_id' => $id,
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

                    // 锁定库存
                    $product = ProductModel::find($id);
                    $stock = new PurchaseStockService($product->toArray());
                    $stock->setLockNum($num);
                    $stock->setType(PurchaseStockService::LOG_TYPE_19);
                    $stock->lock();

                }
            }
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * 写入退货表
     * @return bool
     */
    protected function createPurchaseReturn()
    {
        try {
            // 总退货数
            $returnNum = 0;
            // 总退货金额
            $returnPrice = 0;
            foreach ($this->returnNum as $purchase_product_id => $num) {
                if ($num > 0) {
                    $purchaseProduct = PurchaseOrderChildProductModel::getProductByPurchaseProductId($purchase_product_id);
                    $returnNum += $num;
                    $returnPrice += $num * $purchaseProduct['channel_price'];
                }
            }
            $auth = AdminModel::getCurrentLoginInfo();
            $this->returnPurchase = PurchaseReturnModel::create([
                'supplier_id' => $this->returnOrder['supplier_id'],
                'purchase_id' => $this->returnOrder['purchase_id'],
                'purchase_supplier_id' => $this->returnOrder['purchase_supplier_id'],
                'purchase_order_id' => $this->returnOrder['order_id'],
                'purchase_child_id' => $this->returnOrder['id'],
                'order_no' => $this->returnOrder['order_no'],
                'child_order_no' => $this->returnOrder['child_order_no'],
                'return_no' => SerialNumber::createSN(SerialNumber::SN_ORDER_RETURN),
                'return_number' => $returnNum,
                'return_money' => $returnPrice,
                'order_return_charge_money' => 0,
                'return_status' => $returnNum == $this->returnOrder['purchase_num'] ? 2 : 1,
                'return_type' => $this->returnType,
                'order_status' => self::PURCHASE_ORDER_STATUS_9,
                'purchase_status' => $this->purchase['purchase_type'],
                'child_pay_type' => $this->purchase['pay_type'],
                'province_id' => 0,
                'city_id' => 0,
                'area_id' => 0,
                'street_id' => 0,
                'address' => '',
                'express_id' => 0,
                'express_name' => '',
                'express_pinyin' => '',
                'express_no' => '',
                'delivery_time' => '',
                'take_delivery_time' => '',
                'note' => '',
                'mobile' => '',
                'name' => '',
                'type' => Source::MERCHANT_ID,
                'admin_id' => $auth['id'],
                'admin_name' => $auth['fullname'],
                'order_created_at' => $this->returnOrder['created_at'],
                'return_reason' => $this->returnNote,
            ]);

            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * 创建图片
     * @return bool|int
     */
    protected function createReturnImage()
    {
        if (count($this->returnImages)) {
            $this->deleteReturnImage();
            return ImageModel::add('salesreturn', $this->returnPurchase->id, $this->returnImages);
        }
        return false;
    }

    /**
     * 删除图片
     * @return bool|int
     */
    protected function deleteReturnImage()
    {
        return $this->newWrite()->update('img', [
            'is_del' => '1',
            'deleted_at' => date('Y-m-d h:i:s')
        ], [
            'obj_id' => $this->returnPurchase->id,
            'type' => 'salesreturn',
            'is_del' => '2'
        ]);
    }

    /**
     * 定义采购退货搜索订单状态
     * 角色类型  平台 1 ；商户 2 ；供应商  3 ；渠道商 4
     * @param int $type
     * @return array
     */
    public static function getStatus($type = 0)
    {
        $returnStatus = [
            /**
             * 平台状态
             */
            Source::PLATFORM_ID => [
                // 待审核
                self::PURCHASE_RETURN_ORDER_STATUS_9 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PLATFORM_ID][self::PURCHASE_RETURN_ORDER_STATUS_9],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_9
                    ]
                ],

                // 已审核
                self::PURCHASE_RETURN_ORDER_STATUS_11 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PLATFORM_ID][self::PURCHASE_RETURN_ORDER_STATUS_11],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_11
                    ]
                ],
                // 审核拒绝
                self::PURCHASE_RETURN_ORDER_STATUS_15 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PLATFORM_ID][self::PURCHASE_RETURN_ORDER_STATUS_15],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_15
                    ]
                ],

                // 商户发货
                self::PURCHASE_RETURN_ORDER_STATUS_41 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PLATFORM_ID][self::PURCHASE_RETURN_ORDER_STATUS_41],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_41
                    ]
                ],
                // 供应商拒收
                self::PURCHASE_RETURN_ORDER_STATUS_61 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PLATFORM_ID][self::PURCHASE_RETURN_ORDER_STATUS_61],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_61
                    ]
                ],
                self::PURCHASE_RETURN_ORDER_STATUS_62 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PLATFORM_ID][self::PURCHASE_RETURN_ORDER_STATUS_62],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_62
                    ]
                ],
                self::PURCHASE_RETURN_ORDER_STATUS_63 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PLATFORM_ID][self::PURCHASE_RETURN_ORDER_STATUS_63],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_63
                    ]
                ],
                self::PURCHASE_RETURN_ORDER_STATUS_64 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PLATFORM_ID][self::PURCHASE_RETURN_ORDER_STATUS_64],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_64
                    ]
                ],
                // 退货完成
                self::PURCHASE_RETURN_ORDER_STATUS_70 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PLATFORM_ID][self::PURCHASE_RETURN_ORDER_STATUS_70],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_70
                    ]
                ],
                // 平台拒收
                self::PURCHASE_RETURN_ORDER_STATUS_5 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PLATFORM_ID][self::PURCHASE_RETURN_ORDER_STATUS_5],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_5
                    ]
                ],

                // 已取消
                self::PURCHASE_RETURN_ORDER_STATUS_81 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PLATFORM_ID][self::PURCHASE_RETURN_ORDER_STATUS_81],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_81
                    ]
                ],
            ],
            /**
             * 商户状态
             */
            Source::MERCHANT_ID => [
                // 待审核
                self::PURCHASE_RETURN_ORDER_STATUS_9 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::MERCHANT_ID][self::PURCHASE_RETURN_ORDER_STATUS_9],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_9
                    ]
                ],

                // 已审核
                self::PURCHASE_RETURN_ORDER_STATUS_11 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::MERCHANT_ID][self::PURCHASE_RETURN_ORDER_STATUS_11],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_11
                    ]
                ],
                // 审核拒绝
                self::PURCHASE_RETURN_ORDER_STATUS_15 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::MERCHANT_ID][self::PURCHASE_RETURN_ORDER_STATUS_15],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_15
                    ]
                ],

                // 退货中
                self::PURCHASE_RETURN_ORDER_STATUS_41 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::MERCHANT_ID][self::PURCHASE_RETURN_ORDER_STATUS_41],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_41,
                        self::PURCHASE_RETURN_ORDER_STATUS_61,
                        self::PURCHASE_RETURN_ORDER_STATUS_62,
                        self::PURCHASE_RETURN_ORDER_STATUS_63,
                        self::PURCHASE_RETURN_ORDER_STATUS_64,
                    ]
                ],
                // 退货完成
                self::PURCHASE_RETURN_ORDER_STATUS_70 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::MERCHANT_ID][self::PURCHASE_RETURN_ORDER_STATUS_70],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_70
                    ]
                ],
                // 拒绝
                self::PURCHASE_RETURN_ORDER_STATUS_5 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::MERCHANT_ID][self::PURCHASE_RETURN_ORDER_STATUS_5],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_5
                    ]
                ],
                // 取消
                self::PURCHASE_RETURN_ORDER_STATUS_81 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::MERCHANT_ID][self::PURCHASE_RETURN_ORDER_STATUS_81],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_81
                    ]
                ],
                // GO ON
            ],

            /**
             * 供应商状态
             */
            Source::PROVIDER_ID => [
                // 待审核
                self::PURCHASE_RETURN_ORDER_STATUS_9 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PROVIDER_ID][self::PURCHASE_RETURN_ORDER_STATUS_9],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_9
                    ]
                ],

                // 已审核
                self::PURCHASE_RETURN_ORDER_STATUS_11 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PROVIDER_ID][self::PURCHASE_RETURN_ORDER_STATUS_11],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_11
                    ]
                ],
                // 审核拒绝
                self::PURCHASE_RETURN_ORDER_STATUS_15 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PROVIDER_ID][self::PURCHASE_RETURN_ORDER_STATUS_15],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_15
                    ]
                ],

                // 退货中
                self::PURCHASE_RETURN_ORDER_STATUS_41 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PROVIDER_ID][self::PURCHASE_RETURN_ORDER_STATUS_41],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_41
                    ]
                ],
                // 商品寄回
                self::PURCHASE_RETURN_ORDER_STATUS_61 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PROVIDER_ID][self::PURCHASE_RETURN_ORDER_STATUS_61],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_61,
                        self::PURCHASE_RETURN_ORDER_STATUS_63,
                    ]
                ],
                self::PURCHASE_RETURN_ORDER_STATUS_62 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PROVIDER_ID][self::PURCHASE_RETURN_ORDER_STATUS_62],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_62
                    ]
                ],
                self::PURCHASE_RETURN_ORDER_STATUS_64 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PROVIDER_ID][self::PURCHASE_RETURN_ORDER_STATUS_64],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_64
                    ]
                ],
                // 退货完成
                self::PURCHASE_RETURN_ORDER_STATUS_70 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PROVIDER_ID][self::PURCHASE_RETURN_ORDER_STATUS_70],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_70
                    ]
                ],
                // 已拒收
                self::PURCHASE_RETURN_ORDER_STATUS_5 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PROVIDER_ID][self::PURCHASE_RETURN_ORDER_STATUS_5],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_5
                    ]
                ],
                self::PURCHASE_RETURN_ORDER_STATUS_81 => [
                    'name' => self::PURCHASE_RETURN_ORDER_STATUS_CODES[Source::PROVIDER_ID][self::PURCHASE_RETURN_ORDER_STATUS_81],
                    'status' => [
                        self::PURCHASE_RETURN_ORDER_STATUS_81
                    ]
                ],
                // GO ON
            ],

            /**
             * 渠道状态
             */
            Source::CHANNEL_ID => [
                // @TODO
            ],


        ];

        /**
         * 如果指定了类型，则返回指定类型的状态数组，否则返回全部类型的
         */
        $source = Source::all();
        if (isset($source[$type])) {
            return $returnStatus[$type];
        }
        return $returnStatus;
    }

    /**
     * 显示退货单详细
     * @param $id
     * @return array|bool
     */
    public function showReturnDetail($id)
    {
        try {
            $detail = PurchaseReturnModel::detailWithProducts($id);
            if (is_null($detail) || empty($detail)) {
                throw new \Exception('退货单不存在');
            }
            return $detail;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * 退货日志
     * @param $child_order_no
     * @return array
     */
    public function showReturnLog($child_order_no)
    {
        return PurchaseTrackingLogModel::getProductByLogId($child_order_no);
    }


    /**
     * 查询退货快递
     * @param $id
     * @return array|bool
     */
    public function queryReturnExpress($id)
    {
        $detail = PurchaseReturnModel::detail($id);
        if (!$detail['express_pinyin'] || !$detail['express_no']) {
            $this->error = "未查到对应物流信息";
            return false;
        }
        // 查询快递
        $express = new Express();
        $data = $express->searchExpress($detail['express_pinyin'], $detail['express_no']);

        if (is_array($data) && isset($data['state']) && $data['state']) {
            $data['state_text'] = isset(Express::STATES[$data['state']]) ? Express::STATES[$data['state']] : '转单';
            return array_merge($data, $detail);
        }
        $this->error = "物流单暂无结果或查询异常";
        return false;
    }


    /**
     * 更新退货订单状态
     * @param int $id 退货订单主键ID
     * @param int $status 订单状态
     * @return bool|\PDOStatement
     * @throws \Exception
     */
    public function handleStatus($id, $status)
    {
        $model = PurchaseReturnModel::find($id);
        if (!$model) {
            throw new \Exception('退货订单不存在');
        }
        $model->order_status = $status;
        return $model->save();
    }

    /**
     * 删除退货单操作
     * @param $id
     * @return bool
     */
    public function handleDeleteReturn($id)
    {
        $this->newWrite()->beginTransaction();
        try {
            $detail = $this->showReturnDetail($id);
            if (!$detail) {
                throw new \Exception('退货单号不存在');
            }
            if ($detail['order_status'] != self::PURCHASE_RETURN_ORDER_STATUS_15) {
                throw new \Exception("{$detail['order_status_text']} 状态下禁止该操作");
            }
            // 删除退货单
            $model = PurchaseReturnModel::find($id);
            $model->order_status = self::PURCHASE_RETURN_ORDER_STATUS_81;
            $model->delete();
            // 删除退货商品
            if ($detail['products']) {
                foreach ((array)$detail['products'] as $k => $v) {
                    $model = PurchaseReturnProductModel::find($v['id']);
                    $model->delete();
                }
            }
            // 删除退货图片
            if ($detail['return_img']) {
                foreach ((array)$detail['return_img'] as $k => $v) {
                    $model = ImageModel::find($v['id']);
                    $model->delete();
                }
            }

            purchaseTrackingLog(
                $detail['purchase_id'],
                $detail['child_order_no'],
                '删除退货',
                '删除退货单',
                2
            );

            // 撤销锁定库存
            foreach ($detail['products'] as $k=> $v){
                if ($v['channel_id'] > 0) {
                    /**
                     * 虚拟商品锁库存
                     */
                    $channel_product = ProductChannelModel::find( $v['channel_id'] );
                    $stock = new VoidStockService($channel_product->toArray());
                    $stock->setType(VoidStockService::LOG_TYPE_26);
                    $stock->setLockNum($v['return_num']);
                    $stock->revert();
                } else {
                    $product = ProductModel::find($v['product_id']);
                    $stock = new PurchaseStockService($product->toArray());
                    $stock->setLockNum($v['return_num']);
                    $stock->setType(PurchaseStockService::LOG_TYPE_20);
                    $stock->revert();
                }
            }

            $this->newWrite()->commit();
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }

    /**
     * 取消退货操作
     * @param $id
     * @return bool
     */
    public function handleCancelReturn($id)
    {
        $this->newWrite()->beginTransaction();
        try {
            $detail = $this->showReturnDetail($id);
            if (!$detail) {
                throw new \Exception('退货单号不存在');
            }

            if (!in_array($detail['order_status'], [
                self::PURCHASE_RETURN_ORDER_STATUS_9,
                self::PURCHASE_RETURN_ORDER_STATUS_15,
            ])) {
                throw new \Exception("{$detail['order_status_text']} 状态下禁止该操作");
            }

            // 更新采购退货单状态
            $this->handleStatus($id, self::PURCHASE_RETURN_ORDER_STATUS_81);

            // 记录操作日志
            purchaseTrackingLog(
                $detail['purchase_id'],
                $detail['child_order_no'],
                '取消退货',
                '取消退货',
                2
            );

            // 撤销锁定库存
            foreach ($detail['products'] as $k=> $v){
                if ($v['channel_id'] > 0) {
                    /**
                     * 虚拟商品锁库存
                     */
                    $channel_product = ProductChannelModel::find( $v['channel_id'] );
                    $stock = new VoidStockService($channel_product->toArray());
                    $stock->setType(VoidStockService::LOG_TYPE_26);
                    $stock->setLockNum($v['return_num']);
                    $stock->revert();
                } else {
                    $product = ProductModel::find($v['product_id']);
                    $stock = new PurchaseStockService($product->toArray());
                    $stock->setLockNum($v['return_num']);
                    $stock->setType(PurchaseStockService::LOG_TYPE_20);
                    $stock->revert();
                }
            }

            $this->newWrite()->commit();
            return true;
        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }

    /**
     * 填写物流信息
     * @param $id
     * @param $params
     * @return bool
     */
    public function handleExpressReturn($id, $params)
    {
        $this->newWrite()->beginTransaction();
        try {
            if (!$params['express_no']){
                throw new \InvalidArgumentException('请输入物流单号');
            }

            $detail = $this->showReturnDetail($id);
            if (!$detail) {
                throw new \Exception('退货单号不存在');
            }

            $express = ExpressCompanyModel::find($params['express_id'], ['id', 'name', 'pinyin']);
            if (!$express) {
                throw new \InvalidArgumentException('快递公司不存在');
            }


            // 写入物流信息
            $model = PurchaseReturnModel::find($id);
            $model->express_id = $express->id;
            $model->express_name = $express->name;
            $model->express_pinyin = $express->pinyin;
            $model->express_no = $params['express_no'];
            $model->delivery_time = date('Y-m-d H:i:s');
            $model->order_status = self::PURCHASE_RETURN_ORDER_STATUS_41;
            $model->save();

            // 记录物流信息
            $expressService = new PurchaseReturnExpressService($detail);
            $expressService->setType(PurchaseReturnExpressService::TYPE_MERCHANT_SEND);
            $expressService->setLog("商户填写物流，物流单号为：{$params['express_no']}[{$express->name}]");
            $expressService->saveExpress($express->id, $params['express_no']);

            // 记录操作日志
            purchaseTrackingLog(
                $detail['purchase_id'],
                $detail['child_order_no'],
                '退货',
                "退货商品已发出，物流单号为：{$params['express_no']}[{$express->name}]",
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

}