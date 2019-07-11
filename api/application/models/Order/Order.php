<?php

/**
 * 订单model
 * @version v0.01
 * @author laiqingtao
 * @time 2018-05-08
 */
namespace Order;

use Custom\YDLib;
use Common\CommonBase;
use Common\SerialNumber;
use User\UserAddressModel;
use User\UserSupplierModel;
use Product\ProductModel;
use Product\ProductStockLogModel;
use Order\OrderChildModel;
use Order\OrderChildProductModel;
use Order\OrderCoupanModel;
use ErrnoStatus;
use Cart\CartModel;
use Coupan\UserCoupanModel;
use Coupan\CoupanModel;
use Seckill\SeckillModel;
use Freight\FreightSetModel;
use Publicb;
use Seckill\SeckillLogModel;
use Seckill\SeckillOrderModel;
use Product\ProductChannelModel;
use Services\Purchase\PurchaseService;
use Services\Stock\PurchaseStockService;
use Services\Stock\OrderStockService;
use Services\Stock\VoidStockService;
use Services\Msg\MsgService;
use User\UserModel;

class OrderModel extends \Common\CommonBase
{
	 /** 待审核 */
    const  STATUS_AUDIT = '10';
    /** 已审核 */
    const  STATUS_AUDITED = '11';
    /** 待付款 */
    const  STATUS_PENDING_PAYMENT = '20';
    /** 已付款 */
    const  STATUS_ALREADY_PAID = '21';

    /** 异常拣货单**/
    const  STATUS_PICKING_ABNORMAL = '22';
    /** 待拣货 */
    const  STATUS_PICKING_GOODS = '30';
    /** 拣货中 */
    const  STATUS_PICKING_UP_GOODS = '31';

    /** 待发货 */
    const  STATUS_BE_SHIPPED = '40';
    /** 已录入快递号 */
    const  STATUS_ENTERED_NUMBER = '41';
    /** 录入订单重量 */
    const  STATUS_ENTERED_WEIGHT = '42';
    /** 已发货 */
    const  STATUS_ALREADY_SHIPPED = '50';
    /** 交易成功 */
    const  STATUS_SUCCESSFUL_TRADE = '60';
    /** 交易关闭 */
    const  STATUS_CLOSED = '70';
    /** 用户取消 */
    const  STATUS_USER_CANCEL = '80';
    /** 客服取消 */
    const  STATUS_CUSTOM_CANCEL = '90';


    /** 支付类型货到付款 */
    const  ORDER_PAY_TYPE_DELIVERY = 2;
    /** 支付类型在线 */
    const  ORDER_PAY_TYPE_ONLINE = 1;

    const  ORDER_FINANCE_STATUS_SUCCESS = 2;
    const  ORDER_FINANCE_STATUS_NONE = 1;
    const  ORDER_FINANCE_STATUS_NULL = 0;

    /** 待审核  */
    const ORDER_RETURN_STATUS_AUDIT = 10;

    /** 驳回(备注) */
    const ORDER_RETURN_STATUS_AUDIT_REJECT = 11;

    /** 用户取消售后 */
    const ORDER_RETURN_STATUS_AUDIT_REJECT_USER = 12;

    /** 售后发货(取件) */
    const ORDER_RETURN_STATUS_AUDIT_PICKUP = 20;

    /** 待收货 */
    const ORDER_RETURN_STATUS_RECEIPT_GOODS = 30;

    /** 待质检 */
    const ORDER_RETURN_STATUS_QUALITY_CONTROL = 31;

    /** 待入库  */
    const ORDER_RETURN_STATUS_STORAGE = 32;

    /** 待退款 */
    const ORDER_RETURN_STATUS_RETURN_GOODS = 40;

    /** 售后完成 */
    const ORDER_RETURN_STATUS_SUCCESS = 50;

    /** 退款驳回 */
    const ORDER_RETURN_STATUS_REFUND_REJECT = 60;


    /** 是否售后 */
    const  SERVICE_CUSTOMER = 2;
    const  SERVICE_NONE = 1;


    /** 是否供应订单（0库存） */
    const  IS_CHANNEL_2 = 2;//供应订单
    const  IS_CHANNEL_1 = 1;//普通订单

    const  IS_CHANNEL_VALUE = [self::IS_CHANNEL_2=>'供应订单',self::IS_CHANNEL_1=>'普通订单'];
    /**
     * 定义表名后缀
     */
    protected static $_tableName = 'order';

    /**
     * 获取表名
     */
    public static function getTb()
    {
        return self::$_tablePrefix . self::$_tableName;
    }

	 /**
     * 记录入库
     * @param array $data 表字段名作为key的数组
     * @return int 入库成功则返回入库记录的自增ID，否则返回FALSE
     */
    public static function addData($data)
    {
        $data['is_del'] = self::DELETE_SUCCESS;
		$data['created_at'] = date("Y-m-d H:i:s");
		$data['updated_at'] = date("Y-m-d H:i:s");

		$pdo = self::_pdo('db_w');
		return $pdo->insert(self::$_tableName, $data);
    }




    /**
     * 根据表自增ID获取该条记录信息
     *
     * @param int $id
     *        	表自增ID
     */
    public static function getUserOrderByID($id) {
    	$where ['is_del'] = self::DELETE_SUCCESS;
    	$where ['id'] = intval ( $id );

    	$pdo = self::_pdo ( 'db_r' );
    	return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
    }




    /**
     * 根据表自增ID获取该条记录信息
     * @param int $id 表自增ID
     */
    public static function getInfoByID($id)
    {

		$sql  = "SELECT
                     o.*
                 FROM
                     ".self::$_tablePrefix."order_child c
                 LEFT JOIN 
        			".self::$_tablePrefix."order o	
        		 ON	
        			o.id = c.order_id
                 WHERE
                     o.is_del=".self::DELETE_SUCCESS." 
                     AND o.id=".intval($id)." ";

		$pdo = self::_pdo('db_r');
		return $pdo->YDGetRow($sql);
    }

    /**
     * 获取秒杀信息
     * @param int $id 表自增ID
     */
    public static function getSeckillInfoByOrderID($id)
    {

		$sql  = "SELECT
                     c.discount_type,c.discount_id,o.order_del
                 FROM
                     ".self::$_tablePrefix."order_child_product c
                 LEFT JOIN 
        			".self::$_tablePrefix."seckill o	
        		 ON	
        			o.id = c.discount_id
                 WHERE
                     c.is_del=".self::DELETE_SUCCESS." 
                     AND c.order_id=".intval($id)." ";

		$pdo = self::_pdo('db_r');
		return $pdo->YDGetRow($sql);
    }

    /**
     * 根据一条自增ID更新表记录
     * @param array $data 更新字段作为key的数组
     * @param array $id 表自增id
     * @return boolean 更新结果
     */
    public static function updateByID($data, $id)
    {
    	$data['updated_at'] = date("Y-m-d H:i:s");

		$where['id'] =  intval($id);
		$where['supplier_id'] = SUPPLIER_ID;

		$pdo = self::_pdo('db_w');
        $info = $pdo->update(self::$_tableName, $data, $where);
        return $info;
    }




    /**
     * 根据表自增 ID删除记录
     * @param int $id 表自增 ID
     * @return boolean 删除是否成功
     */
    public static function deleteByID($id)
    {
        $data['is_del'] = self::DELETE_FAIL;
		$data['updated_at'] = date("Y-m-d H:i:s");
		$data['deleted_at'] = date("Y-m-d H:i:s");

		$pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
    }

	/**
	 * 获取对应的list列表
	 * @param array  $attribute 获取对应的参数
	 * @param integer $page 对应的页
	 * @param integer $rows 取出的行数
	 * @return array
	 */
    public static function getList($attribute = array(),$page = 1,$rows = 10)
    {
        $limit = ($page-1) * $rows;

		if (is_array($attribute) && count($attribute) > 0) {
			extract($attribute);
		}

        $fields = "id,company,province_id,city_id,area_id,address,contact,mobile,created_at";

		$sql = "SELECT 
        		    [*] 
        		FROM
		            ".self::getTb()."
		        WHERE
        		    is_del=".self::DELETE_SUCCESS."
		        AND
        		    status=".self::STATUS_SUCCESS;

		if (isset($id) && !empty(intval($id))) {
		    $sql .= " AND id = '".intval($id)."'";
		}
		if (isset($company) && !empty(trim($company))) {
		    $sql .= " AND company LIKE '%".trim($company)."%'";
		}
		if (isset($province_id) && !empty(intval($province_id))) {
		    $sql .= " AND province_id = '".intval($province_id)."'";
		}
		if (isset($city_id) && !empty(intval($city_id))) {
		    $sql .= " AND city_id = '".intval($city_id)."'";
		}
		if (isset($area_id) && !empty(intval($area_id))) {
		    $sql .= " AND area_id = '".intval($area_id)."'";
		}
		if (isset($contact) && !empty(trim($contact))) {
		    $sql .= " AND contact LIKE '%".trim($contact)."%'";
		}
		if (isset($mobile) && !empty(trim($mobile))) {
		    $sql .= " AND mobile LIKE '%".trim($mobile)."%'";
		}
		if (isset($start_at) && !empty(trim($start_at))) {
		    $sql .= " AND created_at >= '".trim($start_at)."'";
		}
		if (isset($end_at) && !empty(trim($end_at))) {
		    $sql .= " AND created_at <= '".trim($end_at)."'";
		}

		$pdo = self::_pdo('db_r');
		$resInfo = array();
		$resInfo['total'] = $pdo->YDGetOne(str_replace('[*]', 'COUNT(1) num', $sql));

		$sort = isset($sort)?$sort:'id';
		$order = isset($order)?$order:'DESC';

		$sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
		$resInfo['rows'] = $pdo->YDGetAll(str_replace('[*]', $fields, $sql));
		return $resInfo;

    }

    /**
     * 查询订单数
     * @return num
     */
    public static function count()
    {
    	$sql = "
    			SELECT 
    				COUNT(id) num
        		FROM
		            ".self::getTb()."		        	
		        WHERE
        		    supplier_id = ".SUPPLIER_ID."
				AND 
					to_days(created_at) = to_days(now()) ";

    	$pdo = self::_pdo('db_r');
        return $pdo ->YDGetOne($sql);
    }

	/**
	 * 获取订单可用的优惠券列表
	 */
	public static function getOrderCoupan($data)
	{
		//获取用户未使用优惠券
		$search['user_id'] = $data['user_id'];
		$search['status'] = 1;
		$coupanList = UserCoupanModel::getList($search,1,500);
		if (!is_array($coupanList['rows']) || count($coupanList['rows']) == 0) {
			YDLib::output(ErrnoStatus::STATUS_60519);
		}

		$actual_amount_total = 0;
		foreach ($data['product'] as $key => $value) {
			//获取商品信息（检测是否是本商户商品）
			$productInfo = ProductModel::getInfoByIDUseAddOrder($value['product_id']);
			if (!$productInfo) {
				YDLib::output(ErrnoStatus::STATUS_60025,array('product_id' => $value['product_id']));
			}

			if ($productInfo['stock'] < $value['num']) {
				YDLib::output(ErrnoStatus::STATUS_50014,
				array(
					'product_id' => $value['product_id'],
					'name' => $productInfo['name'],
					'stock' => $productInfo['stock'])
				);
			}
			$data['product'][$key]['sale_price'] = $productInfo['sale_price'];
			$data['product'][$key]['actual_amount'] = bcmul($productInfo['sale_price'],$value['num'],2);

			$actual_amount_total = bcadd($actual_amount_total,$data['product'][$key]['actual_amount'],2);
		}

		//检测优惠券对于本单的有效性
  		//`use_type` tinyint(4) DEFAULT '1' COMMENT '使用类型：1店铺优惠券2商品优惠券',
  		//`use_product_ids` text COMMENT '适用商品id，逗号隔开',
  		//`sill_type` tinyint(4) DEFAULT '1' COMMENT '门槛类型：1无门槛2有门槛',
  		//`sill_price` decimal(10,2) DEFAULT '0.00' COMMENT '适用门槛：满多少元可用',

		//`time_type` tinyint(4) DEFAULT '1' COMMENT '有效时间：1时间段2不限',
		//`start_time` timestamp NULL DEFAULT NULL COMMENT '开始时间',
		//`end_time` timestamp NULL DEFAULT NULL COMMENT '结束时间',

		//`is_more` tinyint(4) DEFAULT '1' COMMENT '是否限时优惠券仅原价购买商品时可用：1不限2限制',@todo
		$product_price = 0;
		foreach ($coupanList['rows'] as $key => $value) {
			$coupanList['rows'][$key]['can_use'] = 1;
			$price = '0';
			if ($value['time_type'] == 1 && $value['start_time'] > date("Y-m-d H:i:s")) {
				$coupanList['rows'][$key]['can_use'] = 2;
			} else {
				if ($value['use_type'] == 2) {
					$product_id_list = explode(',', $value['use_product_ids']);
					$use = FALSE;
					$use_amount = 0;
					foreach ($data['product'] as $k => $v) {
						if (in_array($v['product_id'], $product_id_list)) {
							$use_amount = bcadd($use_amount, $v['actual_amount']);
							$use = TRUE;
						}
					}
					if (!$use) {
						$coupanList['rows'][$key]['can_use'] = 2;
					} else {
						if ($value['sill_type'] == 2 && $use_amount < $value['sill_price']) {
							$coupanList['rows'][$key]['can_use'] = 2;
						}
					}

					//找最优惠的优惠券
					if ($coupanList['rows'][$key]['can_use'] == 1) {
					    if ($value['pre_type'] == '1') {//减多少元
					        $price = $value['pre_value'];
					    } else {
					        $price = bcsub($actual_amount_total,bcmul($use_amount, bcdiv($value['pre_value'],100,4),2));
					    }
					}

				} else {
					if ($value['sill_type'] == 2 && $actual_amount_total < $value['sill_price']) {
						$coupanList['rows'][$key]['can_use'] = 2;
					}


					//找最优惠的优惠券
					if ($coupanList['rows'][$key]['can_use'] == 1) {
					    if ($value['pre_type'] == '1') {//减多少元
					        $price = $value['pre_value'];
					    } else {
					        $price = bcsub($actual_amount_total,bcmul($actual_amount_total, bcdiv($value['pre_value'],100,4),2));
					    }
					}
				}
				if ($price > $product_price) {
				    $product_price = $price;
				}
			}
			$coupanList['rows'][$key]['use_coupan_price'] = $price;
		}

		foreach ($coupanList['rows'] as $key => $value) {
		    $coupanList['rows'][$key]['is_more_price'] = '0';
		    if ($value['can_use'] == '1') {
		        if ($value['use_coupan_price'] >= $product_price) {
		            $coupanList['rows'][$key]['is_more_price'] = '1';
		        }
		    }
		}

		if (!is_array($coupanList['rows']) || count($coupanList['rows']) == 0) {
			YDLib::output(ErrnoStatus::STATUS_60519);
		}
		YDLib::output(ErrnoStatus::STATUS_SUCCESS,$coupanList['rows'],FALSE);
	}

    /**
     * 生成订单
     * @return num
     */
	public static function addOrder($data)
	{
		$pdo = self::_pdo('db_w');
		$pdo->beginTransaction();
		try {
			$product = [];
			$product_original_amount_total = 0;
			$sale_num_total = 0;self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
			foreach ($data['product'] as $key => $value) {
				//删除购物车
				$res = CartModel::deleteByID(array('product_id'=>$value['product_id'],'user_id'=>$data['user_id']));
				if (!$res) {
					$pdo->rollback();
					YDLib::output(ErrnoStatus::STATUS_50008);
				}
				//删除购物车缓存
				$mem = YDLib::getMem('memcache');
				$key = __CLASS__."::getInfoByUserID::".SUPPLIER_ID."::".$data['user_id'];
				$mem->delete($key);

				//获取商品信息（检测是否是本商户商品）
				$productInfo = ProductModel::getInfoByIDUseAddOrder($value['product_id'],TRUE);
				if (!$productInfo) {
					$pdo->rollback();
					YDLib::output(ErrnoStatus::STATUS_60025,array('product_id' => $value['product_id']));
				}

				$seckill_price = 0;
				//限购活动
				if ($data['seckill_id']) {
				    $redis = YDLib::getRedis('redis','r');
				    $stock_num = $redis->llen("storenum_".SUPPLIER_ID."_id_".$data['seckill_id']);
				    if (empty($stock_num) || $stock_num <= 0) {//没有库存了
				        $pdo->rollback();
				        YDLib::output(ErrnoStatus::STATUS_60560);
				    }
				    $seckill = $redis->get("seckill_".SUPPLIER_ID."_id_".$data['seckill_id']);
				    if (empty($seckill)) {//redis不存在
				        $pdo->rollback();
				        YDLib::output(ErrnoStatus::STATUS_60560);
				    }
				    $seckill = json_decode($seckill,true);

				    if (strtotime($seckill['starttime']) >= time() && strtotime($seckill['endtime']) >= time()) {
				        $pdo->rollback();
				        YDLib::output(ErrnoStatus::STATUS_60562);//未开始
				    } else if (strtotime($seckill['starttime']) <= time() && strtotime($seckill['endtime']) <= time()) {
				        $pdo->rollback();
				        YDLib::output(ErrnoStatus::STATUS_60561);//已结束
				    }

				    if ($seckill['is_restrictions'] == '2') {//限购
				        //查询是否已经购买过
				        $user_order_num = OrderChildProductModel::getInfoByTypeIDAndUserId($data['seckill_id'], $data['user_id']);
				        if ($user_order_num >= $seckill['restrictions_num']) {
				            $pdo->rollback();
				            YDLib::output(ErrnoStatus::STATUS_60563);//已达到购买限额
				        }

				        if ($value['num'] > $seckill['restrictions_num']) {
				            $pdo->rollback();
				            YDLib::output(ErrnoStatus::STATUS_60563);//已达到购买限额
				        }
				    }
				    //减redis库存数
				    $redis = YDLib::getRedis('redis','w');
				    for ($i=0;$i<$value['num'];$i++) {
				        $redis->lpop("storenum_".SUPPLIER_ID."_id_".$data['seckill_id']);
				    }
				    $seckill_price = bcadd($seckill_price, bcmul($seckill['seckill_price'], $value['num'],2),2);
				}

				if ($productInfo['stock'] < $value['num']) {
					$pdo->rollback();
					//回滚限购库存数
					if ($data['seckill_id']) {
					    self::editSeckillNum($data['seckill_id'], $value['num']);
					}
					YDLib::output(ErrnoStatus::STATUS_50014,
					array(
						'product_id' => $value['product_id'],
						'name' => $productInfo['name'],
						'stock' => $productInfo['stock'])
					);
				}
				$productInfo['num'] = $value['num'];
				$productInfo['discount_amount'] = '0';
				//限购情况下计算商品优惠价
				$actual_amount = bcmul($productInfo['sale_price'],$value['num'],2);
				if ($seckill) {
				    $productInfo['actual_amount'] = bcmul($seckill['seckill_price'],$value['num'],2);
				    $productInfo['discount_amount'] = bcsub($actual_amount, $productInfo['actual_amount'],2);
				    $actual_amount = $productInfo['actual_amount'];
				} else {
				    //计算优惠金额
				    $productInfo['actual_amount'] = bcmul($productInfo['sale_price'],$value['num'],2);
				}

				$product_original_amount_total = bcadd($product_original_amount_total,$actual_amount,2);
				$sale_num_total = bcadd($sale_num_total,$value['num']);
				$product[] = $productInfo;
			}

			//获取收货地址信息(检测是否是本人收货地址)
			if ($data['delivery_type'] != '1') {
    			$addressInfo = UserAddressModel::getInfoByID($data['address_id'],$data['user_id']);
    			if (!$addressInfo) {
    				$pdo->rollback();
    				//回滚限购库存数
    				if ($data['seckill_id']) {
    				    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
    				}
    				YDLib::output(ErrnoStatus::STATUS_60507);
    			}

    			//获取运费
    			$charge = FreightSetModel::getFreightBYProvinceID($addressInfo['province']);
			} else {
				//门店自提订单，收货地址默认为空
			    $charge = '0.00';
				$addressInfo = [];
				$addressInfo['province'] = 0;
				$addressInfo['province_txt'] = '';
				$addressInfo['city'] = 0;
				$addressInfo['city_txt'] = '';
				$addressInfo['area'] = 0;
				$addressInfo['area_txt'] = '';
				$addressInfo['street'] = 0;
				$addressInfo['street_txt'] = '';
				$addressInfo['address'] = '';
				$addressInfo['name'] = '';
				$addressInfo['mobile'] = '';
			}

			//获取卡券信息与添加卡券信息
			$coupan = [];//优惠券信息
			$coupan_discount_amount = 0;//优惠券抵扣金额
			$product_use_coupan_id = [];
			if (isset($data['user_coupan_id']) && !empty($data['user_coupan_id']) && is_numeric($data['user_coupan_id'])) {

				$search['user_id'] = $data['user_id'];
				$search['user_coupan_id'] = $data['user_coupan_id'];
				$search['status'] = 1;
				$coupanList = UserCoupanModel::getList($search,1,100);
				if (!is_array($coupanList['rows']) || count($coupanList['rows']) == 0) {
    				$pdo->rollback();
    				//回滚限购库存数
    				if ($data['seckill_id']) {
    				    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
    				}
					YDLib::output(ErrnoStatus::STATUS_60519);
				}

				$coupanInfo = $coupanList['rows'][0];

				//检测优惠券对于本单的有效性
		  		//`use_type` tinyint(4) DEFAULT '1' COMMENT '使用类型：1店铺优惠券2商品优惠券',
		  		//`use_product_ids` text COMMENT '适用商品id，逗号隔开',
		  		//`sill_type` tinyint(4) DEFAULT '1' COMMENT '门槛类型：1无门槛2有门槛',
		  		//`sill_price` decimal(10,2) DEFAULT '0.00' COMMENT '适用门槛：满多少元可用',
		  		//`pre_type` tinyint(4) DEFAULT '1' COMMENT '优惠类型：1减免N元2打M折',
  				//`pre_value` decimal(10,2) DEFAULT '0.00' COMMENT '优惠值',
				if ($coupanInfo['time_type'] == 1 && $coupanInfo['start_time'] > date("Y-m-d H:i:s")) {
    				$pdo->rollback();
    				//回滚限购库存数
    				if ($data['seckill_id']) {
    				    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
    				}
					YDLib::output(ErrnoStatus::STATUS_60520);
				} else {
					if ($coupanInfo['use_type'] == 2) {
						$product_id_list = explode(',', $coupanInfo['use_product_ids']);
						$use = FALSE;
						$use_amount = 0;
						foreach ($product as $k => $v) {
							if (in_array($v['id'], $product_id_list)) {
							    $product_use_coupan_id[] = $v['id'];
								$use_amount = bcadd($use_amount, $v['actual_amount']);
								$use = TRUE;
							}
						}
						if (!$use) {
		    				$pdo->rollback();
		    				//回滚限购库存数
		    				if ($data['seckill_id']) {
		    				    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
		    				}
							YDLib::output(ErrnoStatus::STATUS_60520);
						} else {
							if ($coupanInfo['sill_type'] == 2 && $use_amount < $coupanInfo['sill_price']) {
                				$pdo->rollback();
                				//回滚限购库存数
                				if ($data['seckill_id']) {
                				    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
                				}
								YDLib::output(ErrnoStatus::STATUS_60520);
							}
						}

						if ($coupanInfo['pre_type'] == 1) {
							$coupan_discount_amount = $coupanInfo['pre_value'];
						} else if ($coupanInfo['pre_type'] == 2) {
	                        $coupan_discount_amount = bcsub($use_amount,bcmul($use_amount,bcdiv($coupanInfo['pre_value'],100,4),2),2);
						}

						if ($coupan_discount_amount > $use_amount) {
							$coupan_discount_amount = $use_amount;
						}
					} else {
						if ($coupanInfo['sill_type'] == 2 && $product_original_amount_total < $coupanInfo['sill_price']) {
            				$pdo->rollback();
            				//回滚限购库存数
            				if ($data['seckill_id']) {
            				    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
            				}
							YDLib::output(ErrnoStatus::STATUS_60520);
						}
						if ($coupanInfo['pre_type'] == 1) {
							$coupan_discount_amount = $coupanInfo['pre_value'];
						} else if ($coupanInfo['pre_type'] == 2) {
							$coupan_discount_amount = bcsub($product_original_amount_total,bcmul($product_original_amount_total,bcdiv($coupanInfo['pre_value'],100,4),2),2);
						}

						if ($coupan_discount_amount > $product_original_amount_total) {
							$coupan_discount_amount = $product_original_amount_total;
						}
					}
		        }

				$coupan['supplier_id'] = SUPPLIER_ID;
				$coupan['order_id'] = 0;
				$coupan['coupan_id'] = $coupanInfo['coupan_id'];
				$coupan['user_id'] = $data['user_id'];
				$coupan['user_coupan_id'] = $coupanInfo['user_coupan_id'];
				$coupan['coupan_discount_amount'] = $coupan_discount_amount;

	        }

			$orderData = [];
			$orderData['supplier_id'] = SUPPLIER_ID;
			$orderData['order_no'] = SerialNumber::createSN(SerialNumber::SN_ORDER_MAIN);
			$orderData['user_id'] = $data['user_id'];

			$orderData['coupan_discount_amount'] = $coupan_discount_amount;//卡券抵扣金额
			$orderData['freight_charge_original_amount'] = $charge;//原始支付运费金额
			$orderData['freight_charge_actual_amount'] = $charge;//实际支付运费金额
			$orderData['freight_charge_discount_amount'] = 0;//优惠运费金额

			$orderData['product_original_amount'] = $product_original_amount_total;//商品原始金额
			if ($seckill) {
			    $orderData['product_actual_amount'] = $seckill_price;//商品实际支付金额 = 限购价
			    $orderData['product_discount_amount'] = bcsub($product_original_amount_total, $seckill_price,2);//商品优惠金额 = 原始金额-限购总金额
			} else {
			    $orderData['product_actual_amount'] = $product_original_amount_total;//商品实际支付金额
			    $orderData['product_discount_amount'] = 0;//商品优惠金额
			}

			$orderData['order_original_amount'] = bcadd($orderData['product_original_amount'],$orderData['freight_charge_original_amount'],2);//订单原始价格金额(订单总额+总邮费)
			$orderData['order_discount_amount'] = bcadd(bcadd($orderData['coupan_discount_amount'], $orderData['product_discount_amount'],2),$orderData['freight_charge_discount_amount'],2);//订单优惠金额(商品优惠+运费优惠+卡券抵扣)
			$orderData['order_actual_amount'] = bcsub($orderData['order_original_amount'], $orderData['order_discount_amount'],2);//订单实际支付金额(订单实际总额+实际总邮费)

			$orderData['sale_num'] = $sale_num_total;//销售数量
			$orderData['delivery_type'] = $data['delivery_type'];//选择收货方式：0 快递 ，1门店自提
			$orderData['pay_type'] = CommonBase::ORDER_PAY_TYPE_ONLINE;//支付类型 默认1 1在线支付 2货到付款
			$orderData['status'] = CommonBase::STATUS_PENDING_PAYMENT;//待付款状态
			$public = new Publicb();
			$orderData['ip'] = $public->GetIP();
			$orderData['province_id'] = $addressInfo['province'];
			$orderData['province_name'] = $addressInfo['province_txt'];
			$orderData['city_id'] = $addressInfo['city'];
			$orderData['city_name'] = $addressInfo['city_txt'];
			$orderData['area_id'] = $addressInfo['area'];
			$orderData['area_name'] = $addressInfo['area_txt'];
			$orderData['street_id'] = $addressInfo['street'];
			$orderData['street_name'] = $addressInfo['street_txt'];
			$orderData['address'] = $addressInfo['address'];
			$orderData['accept_name'] = $addressInfo['name'];
			$orderData['accept_mobile'] = $addressInfo['mobile'];
			$orderData['order_from'] = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
			$orderData['express_id'] = 0;
			$orderData['express_name'] = '';
			$orderData['express_no'] = '';
			$orderData['note'] = '';
			$order_id = OrderModel::addData($orderData);
			if (!$order_id) {
				$pdo->rollback();
				//回滚限购库存数
				if ($data['seckill_id']) {
				    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
				}
				YDLib::output(ErrnoStatus::STATUS_60508);
			}

			//生成订单优惠券信息
			if (is_array($coupan) && count($coupan) > 0) {
				$coupan['order_id'] = $order_id;
				$res = OrderCoupanModel::addData($coupan);
				if (!$res) {
					$pdo->rollback();
					YDLib::output(ErrnoStatus::STATUS_60508);
				}
				$upCoupanData['use_num'] = 1;
				//更新优惠券信息
				$res = CoupanModel::autoUpdateByID($upCoupanData,$coupan['coupan_id']);
				if (!$res) {
    				$pdo->rollback();
    				//回滚限购库存数
    				if ($data['seckill_id']) {
    				    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
    				}
					YDLib::output(ErrnoStatus::STATUS_60508);
				}
				//更新会员优惠券信息
				$upUserCoupanData['status'] = 2;
				$upUserCoupanData['use_at'] = date("Y-m-d H:i:s");
				$upUserCoupanData['order_id'] = $order_id;
				$upUserCoupanData['order_price'] = $orderData['order_actual_amount'];
				$upUserCoupanData['discount_price'] = $coupan['coupan_discount_amount'];
				$res = UserCoupanModel::updateByID($upUserCoupanData,$coupan['user_coupan_id']);
				if (!$res) {
    				$pdo->rollback();
    				//回滚限购库存数
    				if ($data['seckill_id']) {
    				    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
    				}
					YDLib::output(ErrnoStatus::STATUS_60508);
				}
			}

			//生成子单
			$orderChildData = [];
			$orderChildData['supplier_id'] = SUPPLIER_ID;
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
			//$orderChildData['delivery_time'] = '';
			$orderChildData['delivery_type'] = $orderData['delivery_type'];
			//$orderChildData['take_delivery_time'] = '';
			$orderChildData['note'] = '';
			$orderChildData['is_after_sales'] = CommonBase::SERVICE_NONE;
			$order_child_id = OrderChildModel::addData($orderChildData);
			if (!$order_child_id) {
				$pdo->rollback();
				//回滚限购库存数
				if ($data['seckill_id']) {
				    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
				}
				YDLib::output(ErrnoStatus::STATUS_60508);
			}

			//如果有优惠券计算优惠金额
	        //店铺券进行商品平均
			$product_coupan_money = '0';
	        if ($coupan_discount_amount > 0 && !$product_use_coupan_id) {


	            $product_coupan_money = bcdiv($coupan_discount_amount, count($product),2);

	        } else if ($coupan_discount_amount > 0 && $product_use_coupan_id) {//商品券判断本商品是否能使用

                $product_coupan_money = bcdiv($coupan_discount_amount, count($product_use_coupan_id),2);

            }
            array_multisort(array_column($product,'actual_amount'),SORT_ASC,$product);


			//生成商品详情
			$price_coupan = '0';
			$now_price = '0';
			foreach ($product as $key => $value) {
			    //分摊卡券优惠金额
			    if ($product_use_coupan_id && in_array($value['id'], $product_use_coupan_id)) {
    			    $now_price = bcadd($product_coupan_money,$price_coupan,2);

    			    if($now_price > $value['actual_amount']) {

    			        $now_price = $value['actual_amount'];
    			        $price_coupan = bcsub($now_price, $value['actual_amount'],2);

    			    } else {

    			        $new_product_coupan_money = $now_price;

    			    }
			    } else if ($product_use_coupan_id && !in_array($value['id'], $product_use_coupan_id)) {
			        $new_product_coupan_money = 0;
			    } else if (!$product_use_coupan_id) {

			        $now_price = bcadd($product_coupan_money,$price_coupan,2);

			        if($now_price > $value['actual_amount']) {

			            $new_product_coupan_money = $value['actual_amount'];
			            $price_coupan = bcsub($now_price, $value['actual_amount'],2);

			        } else {

			            $new_product_coupan_money = $now_price;
			        }
			    }

				$orderChildProductData = [];
				$orderChildProductData['supplier_id'] = SUPPLIER_ID;
				$orderChildProductData['user_id'] = $data['user_id'];
				$orderChildProductData['order_id'] = $order_id;
				$orderChildProductData['order_no'] = $orderData['order_no'];
				$orderChildProductData['child_order_id'] = $order_child_id;
				$orderChildProductData['child_order_no'] = $orderChildData['child_order_no'];
				$orderChildProductData['brand_id'] = $value['brand_id'];
				$orderChildProductData['brand_name'] = $value['brand_name'];
				$orderChildProductData['category_id'] = $value['category_id'];
				$orderChildProductData['category_name'] = $value['category_name'];
				$orderChildProductData['product_id'] = $value['id'];
				$orderChildProductData['self_code'] = $value['self_code'];
				$orderChildProductData['product_name'] = $value['name'];
				$orderChildProductData['market_price'] = $value['market_price'];
				$orderChildProductData['sale_price'] = $value['sale_price'];
				$orderChildProductData['channel_price'] = $value['channel_price'];
				$orderChildProductData['introduction'] = $value['introduction'];
				$orderChildProductData['logo_url'] = $value['logo_url_old'];
				$orderChildProductData['now_at'] = $value['now_at'];
				$img_ids = array_column($value['imglist'], 'id');
				$img_ids = implode(',', $img_ids);
				$orderChildProductData['imgs'] = $img_ids;
				$attributes_ids = array_column($value['attribute'], 'id');
				$attributes_ids = implode(',', $attributes_ids);
				$orderChildProductData['attributes'] = $attributes_ids;
				$orderChildProductData['sale_num'] = $value['num'];
				$orderChildProductData['actual_amount'] = $value['actual_amount'];

				$orderChildProductData['discount_amount'] = bcadd($value['discount_amount'],$new_product_coupan_money,2);//优惠金额=优惠金额+优惠券抵扣金额
				$orderChildProductData['coupan_discount_amount'] = $new_product_coupan_money;
    			if ($data['seckill_id']) {//限时抢购
    			    $orderChildProductData['discount_type'] = '1';//限时抢购
    			    $orderChildProductData['discount_id'] = $data['seckill_id'];
    			}
                //0库存订单
                $orderChildProductData['is_channel'] = self::IS_CHANNEL_1;
    			if (!empty($value['is_id'])) {
                    $orderChildProductData['is_channel'] = self::IS_CHANNEL_2;
                    $orderChildProductData['channel_id'] = $value['is_id'];
                    $orderChildProductData['purchase_price'] = $value['channel_price'];
                } else {
                    $orderChildProductData['purchase_price'] = $value['purchase_price'];
                }

				$orderChildProductData['note'] = '';
				$orderChildProductData['is_after_sales'] = CommonBase::SERVICE_NONE;
				$orderChildProductData['return_order_id'] = 0;
				$orderChildProductData['is_return'] = $value['is_return'];
				$orderChildProductData['product_supplier_id'] = $value['product_supplier_id'];

                $order_child_product_id = OrderChildProductModel::addData($orderChildProductData);
				if (!$order_child_product_id) {
    				$pdo->rollback();
    				//回滚限购库存数
    				if ($data['seckill_id']) {
    				    self::editSeckillNum($data['seckill_id'], $value['num']);
    				}
					YDLib::output(ErrnoStatus::STATUS_60508);
				}

                if (!empty($value['is_id'])) {//0库存订单
                    /**
                     * 虚拟商品锁库存
                     */
                    $channel_product = ProductChannelModel::find( $value['is_id'] );
                    $stock = new VoidStockService($channel_product->toArray());
                    $stock->setType(OrderStockService::LOG_TYPE_11);
                    $stock->setLockNum($value['num']);
                    $stock->lock();
                    /**
                     * 供应商品锁库存
                     */
                    $product = ProductModel::find($value['id']);
                    $stock = new OrderStockService($product->toArray());
                    $stock->setType(OrderStockService::LOG_TYPE_11);
                    $stock->setLockNum($value['num']);
                    $stock->lock();

                } else {
                    //库存变动日志
                    $log_id = ProductStockLogModel::addLog($value['id'], 4, -$value['num'], $value['num']);
                    if (!$log_id) {
                        $pdo->rollback();
                        //回滚限购库存数
                        if ($data['seckill_id']) {
                            self::editSeckillNum($data['seckill_id'], $value['num']);
                        }
                        YDLib::output(ErrnoStatus::STATUS_60508);
                    }
                    //变动库存
                    $upData['stock'] = bcadd($value['stock'], -$value['num']);
                    $upData['lock_stock'] = bcadd($value['lock_stock'], $value['num']);
                    $res = ProductModel::updateByID($upData, $value['id']);
                    if (!$res) {
                        $pdo->rollback();
                        //回滚限购库存数
                        if ($data['seckill_id']) {
                            self::editSeckillNum($data['seckill_id'], $value['num']);
                        }
                        YDLib::output(ErrnoStatus::STATUS_60508);
                    }
                }
			}
			//更新首次消费时间
			$res = UserSupplierModel::updateInfo($data['user_id'],$orderData['order_actual_amount']);
			if (!$res) {
				$pdo->rollback();
				//回滚限购库存数
				if ($data['seckill_id']) {
				    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
				}
				YDLib::output(ErrnoStatus::STATUS_60508);
			}

			$pdo->commit();
        } catch (\Exception $e) {
			$pdo->rollback();
			//回滚限购库存数
			if ($data['seckill_id']) {
			    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
			}
            YDLib::output(ErrnoStatus::STATUS_60508);
        }
		$resData['order_id'] = $order_id;//主单ID
		$resData['payurl'] = 'https://'.$_SERVER['HTTP_HOST'].'/v1/payment/pay?identif='.SUPPLIER_DOMAIN.'&orderId='.$order_id;//跳转支付页面
		YDLib::output(ErrnoStatus::STATUS_SUCCESS,$resData);
	}

    /**
     * 生成拼团订单
     * @return num
     */
	public static function addGroupOrder($data)
	{
		$pdo = self::_pdo('db_w');
		$pdo->beginTransaction();
		try {

			//获取商品信息
			$productInfo = ProductModel::getInfoByIDUseAddOrder($data['seckill']['product_id'],TRUE);
			if (!$productInfo) {
				$pdo->rollback();
				YDLib::output(ErrnoStatus::STATUS_60025);
			}

		    if (strtotime($data['seckill']['starttime']) >= time() && strtotime($data['seckill']['endtime']) >= time()) {
		        $pdo->rollback();
		        YDLib::output(ErrnoStatus::STATUS_60562);//未开始
		    } else if (strtotime($data['seckill']['starttime']) <= time() && strtotime($data['seckill']['endtime']) <= time()) {
		        $pdo->rollback();
		        YDLib::output(ErrnoStatus::STATUS_60561);//已结束
		    }

		    //查询剩余参团数
		    if ($data['tuan_id']>0) {
				$tuanInfo = SeckillLogModel::getInfoByID($data['tuan_id']);
				if ($tuanInfo['dump_num'] <= 0 ) {
					$pdo->rollback();
					YDLib::output(ErrnoStatus::STATUS_60586);
				}

				if ($tuanInfo['user_id'] == $data['user_id']) {
					$pdo->rollback();
					YDLib::output(ErrnoStatus::STATUS_50024);
				}

				//判断是否是已经参与过的拼团
				$canyuInfo  = SeckillLogModel::getCanyuInfo($data['user_id'],$data['tuan_id']);
				if ($canyuInfo) {
					$pdo->rollback();
					YDLib::output(ErrnoStatus::STATUS_50025);
				}

		    }

		    if ($data['seckill']['is_restrictions'] == '2') {//限购
		        if ($data['num'] > $data['seckill']['restrictions_num']) {
		            $pdo->rollback();
		            YDLib::output(ErrnoStatus::STATUS_60585);//超过限额
		        }
		    }

			if ($data['seckill']['stock'] < $data['num']) {
				$pdo->rollback();
				YDLib::output(ErrnoStatus::STATUS_50014);
			}

			$productInfo['num'] = $data['num'];
			$productInfo['original_amount'] = bcmul($data['seckill']['sale_price'],$data['num'],2);//原价
			$productInfo['actual_amount'] = bcmul($data['seckill']['group_price'],$data['num'],2);//实际价
			$productInfo['discount_amount'] = bcsub($productInfo['original_amount'], $productInfo['actual_amount'],2);//优惠价

			//获取收货地址信息(检测是否是本人收货地址)
			if ($data['delivery_type'] != '1') {
    			$addressInfo = UserAddressModel::getInfoByID($data['address_id'],$data['user_id']);
    			if (!$addressInfo) {
    				$pdo->rollback();
    				YDLib::output(ErrnoStatus::STATUS_60507);
    			}
    			//获取运费
    			$charge = FreightSetModel::getFreightBYProvinceID($addressInfo['province']);
			} else {
				//门店自提订单，收货地址默认为空
			    $charge = '0.00';
				$addressInfo = [];
				$addressInfo['province'] = 0;
				$addressInfo['province_txt'] = '';
				$addressInfo['city'] = 0;
				$addressInfo['city_txt'] = '';
				$addressInfo['area'] = 0;
				$addressInfo['area_txt'] = '';
				$addressInfo['street'] = 0;
				$addressInfo['street_txt'] = '';
				$addressInfo['address'] = '';
				$addressInfo['name'] = '';
				$addressInfo['mobile'] = '';
			}

			//优惠券抵扣金额
			$coupan_discount_amount = 0;

			$orderData = [];
			$orderData['supplier_id'] = SUPPLIER_ID;
			$orderData['order_no'] = SerialNumber::createSN(SerialNumber::SN_ORDER_MAIN);
			$orderData['user_id'] = $data['user_id'];
			$orderData['coupan_discount_amount'] = $coupan_discount_amount;//卡券抵扣金额
			$orderData['freight_charge_original_amount'] = $charge;//原始支付运费金额
			$orderData['freight_charge_actual_amount'] = $charge;//实际支付运费金额
			$orderData['freight_charge_discount_amount'] = 0;//优惠运费金额
			$orderData['product_original_amount'] = $productInfo['original_amount'];//商品原始金额
		    $orderData['product_actual_amount'] = $productInfo['actual_amount'];//商品实际支付金额
		    $orderData['product_discount_amount'] = $productInfo['discount_amount'];//商品优惠金额
			$orderData['order_original_amount'] = bcadd($orderData['product_original_amount'],$orderData['freight_charge_original_amount'],2);//订单原始价格金额(订单总额+总邮费)
			$orderData['order_discount_amount'] = bcadd(bcadd($orderData['coupan_discount_amount'], $orderData['product_discount_amount'],2),$orderData['freight_charge_discount_amount'],2);//订单优惠金额(商品优惠+运费优惠+卡券抵扣)
			$orderData['order_actual_amount'] = bcsub($orderData['order_original_amount'], $orderData['order_discount_amount'],2);//订单实际支付金额(订单实际总额+实际总邮费)
			$orderData['sale_num'] = $productInfo['num'];//销售数量
			$orderData['delivery_type'] = $data['delivery_type'];//选择收货方式：0 快递 ，1门店自提
			$orderData['pay_type'] = CommonBase::ORDER_PAY_TYPE_ONLINE;//支付类型 默认1 1在线支付 2货到付款
			$orderData['status'] = CommonBase::STATUS_PENDING_PAYMENT;//待付款状态
			$public = new Publicb();
			$orderData['ip'] = $public->GetIP();
			$orderData['province_id'] = $addressInfo['province'];
			$orderData['province_name'] = $addressInfo['province_txt'];
			$orderData['city_id'] = $addressInfo['city'];
			$orderData['city_name'] = $addressInfo['city_txt'];
			$orderData['area_id'] = $addressInfo['area'];
			$orderData['area_name'] = $addressInfo['area_txt'];
			$orderData['street_id'] = $addressInfo['street'];
			$orderData['street_name'] = $addressInfo['street_txt'];
			$orderData['address'] = $addressInfo['address'];
			$orderData['accept_name'] = $addressInfo['name'];
			$orderData['accept_mobile'] = $addressInfo['mobile'];
			$orderData['order_from'] = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
			$orderData['express_id'] = 0;
			$orderData['express_name'] = '';
			$orderData['express_no'] = '';
			$orderData['note'] = '';
			$order_id = OrderModel::addData($orderData);
			if (!$order_id) {
				$pdo->rollback();
				YDLib::output(ErrnoStatus::STATUS_60508);
			}

			//生成子单
			$orderChildData = [];
			$orderChildData['supplier_id'] = SUPPLIER_ID;
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
			//$orderChildData['delivery_time'] = '';
			$orderChildData['delivery_type'] = $orderData['delivery_type'];
			//$orderChildData['take_delivery_time'] = '';
			$orderChildData['note'] = '';
			$orderChildData['is_after_sales'] = CommonBase::SERVICE_NONE;
			$order_child_id = OrderChildModel::addData($orderChildData);
			if (!$order_child_id) {
				$pdo->rollback();
				YDLib::output(ErrnoStatus::STATUS_60508);
			}
			
			//生成商品详情
			$orderChildProductData = [];
			$orderChildProductData['supplier_id'] = SUPPLIER_ID;
			$orderChildProductData['user_id'] = $data['user_id'];
			$orderChildProductData['order_id'] = $order_id;
			$orderChildProductData['order_no'] = $orderData['order_no'];
			$orderChildProductData['child_order_id'] = $order_child_id;
			$orderChildProductData['child_order_no'] = $orderChildData['child_order_no'];
			$orderChildProductData['brand_id'] = $productInfo['brand_id'];
			$orderChildProductData['brand_name'] = $productInfo['brand_name'];
			$orderChildProductData['category_id'] = $productInfo['category_id'];
			$orderChildProductData['category_name'] = $productInfo['category_name'];
			$orderChildProductData['product_id'] = $productInfo['id'];
			$orderChildProductData['self_code'] = $productInfo['self_code'];
			$orderChildProductData['product_name'] = $productInfo['name'];
			$orderChildProductData['market_price'] = $productInfo['market_price'];
			$orderChildProductData['sale_price'] = $productInfo['sale_price'];
			$orderChildProductData['channel_price'] = $productInfo['channel_price'];
			$orderChildProductData['introduction'] = $productInfo['introduction'];
			$orderChildProductData['logo_url'] = $productInfo['logo_url_old'];
			$orderChildProductData['now_at'] = $productInfo['now_at'];
			$img_ids = array_column($productInfo['imglist'], 'id');
			$img_ids = implode(',', $img_ids);
			$orderChildProductData['imgs'] = $img_ids;
			$attributes_ids = array_column($productInfo['attribute'], 'id');
			$attributes_ids = implode(',', $attributes_ids);
			$orderChildProductData['attributes'] = $attributes_ids;
			$orderChildProductData['sale_num'] = $productInfo['num'];
			$orderChildProductData['actual_amount'] = $productInfo['actual_amount'];
			$orderChildProductData['discount_amount'] = $productInfo['discount_amount'];
		    $orderChildProductData['discount_type'] = $data['seckill']['type'];
		    $orderChildProductData['discount_id'] = $data['seckill']['seckill_id'];
		    $orderChildProductData['discount_product_id'] = $data['id'];
            //0库存订单
            $orderChildProductData['is_channel'] = self::IS_CHANNEL_1;
            if (!empty($productInfo['is_id'])) {
                $orderChildProductData['is_channel'] = self::IS_CHANNEL_2;
                $orderChildProductData['channel_id'] = $productInfo['is_id'];
                $orderChildProductData['purchase_price'] = $productInfo['channel_price'];
            } else {
                $orderChildProductData['purchase_price'] = $productInfo['purchase_price'];
            }
			$orderChildProductData['note'] = '';
			$orderChildProductData['is_after_sales'] = CommonBase::SERVICE_NONE;
			$orderChildProductData['return_order_id'] = 0;
            $orderChildProductData['is_return'] = $productInfo['is_return'];
            $orderChildProductData['product_supplier_id'] = $productInfo['product_supplier_id'];
			$order_child_product_id = OrderChildProductModel::addData($orderChildProductData);
            
			if (!$order_child_product_id) {
				$pdo->rollback();
				YDLib::output(ErrnoStatus::STATUS_60508);
			}

            if (!empty($productInfo['is_id'])) {//0库存订单
                /**
                 * 虚拟商品锁库存
                 */
                $channel_product = ProductChannelModel::find( $productInfo['is_id'] );
                $stock = new VoidStockService($channel_product->toArray());
                $stock->setType(OrderStockService::LOG_TYPE_11);
                $stock->setLockNum($productInfo['num']);
                $stock->lock();
                /**
                 * 供应商品锁库存
                 */
                $product = ProductModel::find($productInfo['id']);
                $stock = new OrderStockService($product->toArray());
                $stock->setType(OrderStockService::LOG_TYPE_11);
                $stock->setLockNum($productInfo['num']);
                $stock->lock();

            } else {
                //库存变动日志
                $log_id = ProductStockLogModel::addLog($productInfo['id'], 4, -$productInfo['num'], $productInfo['num']);
                if (!$log_id) {
                    $pdo->rollback();
                    YDLib::output(ErrnoStatus::STATUS_60508);
                }
                //变动库存
                $upData['stock'] = bcadd($productInfo['stock'], -$productInfo['num']);
                $upData['lock_stock'] = bcadd($productInfo['lock_stock'], $productInfo['num']);
                $res = ProductModel::updateByID($upData, $productInfo['id']);
                if (!$res) {
                    $pdo->rollback();
                    YDLib::output(ErrnoStatus::STATUS_60508);
                }
            }
			//更新首次消费时间
			$res = UserSupplierModel::updateInfo($data['user_id'],$orderData['order_actual_amount']);
			if (!$res) {
				$pdo->rollback();
				YDLib::output(ErrnoStatus::STATUS_60508);
			}

			//生成拼团订单
			$seckillLogData = [];
			$seckillLogData['seckill_id'] = $data['seckill']['seckill_id'];
			$seckillLogData['seckill_type'] = $data['seckill']['type'];
			$seckillLogData['seckill_product_id'] = $data['id'];
			$seckillLogData['status'] = '1';//未成团
			$seckillLogData['user_id'] = $data['user_id'];
			$seckillLogData['money'] = $orderData['order_actual_amount'];
			$seckillLogData['product_id'] = $productInfo['id'];
			$seckillLogData['order_id'] = $order_id;
			$seckillLogData['order_status'] = '1';//1待支付2已支付3已退款
			$seckillLogData['tuan_type'] = $data['tuan_id']>0?'2':'1';//1团长2团员
			$seckillLogData['tuan_id'] = $data['tuan_id'];
			$seckillLogData['dump_num'] = $data['seckill']['number'];
  			$seckillLogID = SeckillLogModel::addData($seckillLogData);
			if (!$res) {
				$pdo->rollback();
				YDLib::output(ErrnoStatus::STATUS_60508);
			}

			//更新团长ID
			if ($seckillLogData['tuan_type'] == '1') {
				$updata= [];
				$updata['tuan_id']= $seckillLogID;
				$res = SeckillLogModel::updateByID($updata,$seckillLogID);
				
				if (!$res) {
					$pdo->rollback();
					YDLib::output(ErrnoStatus::STATUS_60508);
				}
			}

		    //更新剩余参与数量
		    if ($data['tuan_id']>0) {
				$upsdata= [];
				$upsdata['dump_num']= $tuanInfo['dump_num'];
				$res = SeckillLogModel::updateByTuanID($upsdata,$data['tuan_id']);
				if (!$res) {
					$pdo->rollback();
					YDLib::output(ErrnoStatus::STATUS_60508);
				}
		    }


				$pdo->commit();
			
			
			
        } catch (\Exception $e) {
			$pdo->rollback();
            YDLib::output(ErrnoStatus::STATUS_60508);
        }
		$resData['order_id'] = $order_id;//主单ID
		$resData['payurl'] = 'https://'.$_SERVER['HTTP_HOST'].'/v1/payment/pay?identif='.SUPPLIER_DOMAIN.'&orderId='.$order_id;//跳转支付页面
		YDLib::output(ErrnoStatus::STATUS_SUCCESS,$resData);
	}

    /**
     * 确认收货
     * @param array $child_order_id 子单ID
     * @param array $order_id 主单ID
     * @return boolean 更新结果
     */
    public static function deliveryBYChildID($child_order_id,$order_id)
    {
    	$pdo = self::_pdo('db_w');
		//更新子单状态
    	$data['take_delivery_time'] = date("Y-m-d H:i:s");
		$data['child_status'] = CommonBase::STATUS_SUCCESSFUL_TRADE;
        $res = $pdo->update('order_child', $data, array('id' => $child_order_id));

		//更新主单状态
		$updata['status'] = CommonBase::STATUS_SUCCESSFUL_TRADE;
        $res = $pdo->update(self::$_tableName, $updata, array('id' => $order_id));

		return $res;
    }

    /**
     * 确认提货
     * @param array $child_order_id 子单ID
     * @param array $order_id 主单ID
     * @return boolean 更新结果
     */
    public static function codedeliveryBYChildID($child_order_id,$order_id)
    {
    	$pdo = self::_pdo('db_w');
		$pdo->beginTransaction();
		try {
			//更新子单状态
			$data = [];
			$data['child_status'] = CommonBase::STATUS_SUCCESSFUL_TRADE;
			$data['take_delivery_time'] = date("Y-m-d H:i:s");
	        $res = $pdo->update('order_child', $data, array('id' => $child_order_id));
			if (!$res) {
				$pdo->rollback();
				return FALSE;
			}
			//更新主单状态
			$updata = [];
			$updata['status'] = CommonBase::STATUS_SUCCESSFUL_TRADE;
	        $res = $pdo->update(self::$_tableName, $updata, array('id' => $order_id));
			if (!$res) {
				$pdo->rollback();
				return FALSE;
			}
			//发货出库商品解锁
			$product = OrderChildProductModel::getInfoByChildID($child_order_id);
			foreach ($product as $key => $value) {
                if ($value['is_channel'] != OrderModel::IS_CHANNEL_2 ) {//普通订单
                    //商品解锁
                    $log_id = ProductStockLogModel::addLog($value['product_id'],5,0,-$value['sale_num']);
                    if (!$log_id) {
                        $pdo->rollback();
                        YDLib::output(ErrnoStatus::STATUS_60510);
                    }
                    //变动库存
                    $upStock = [];
                    $upStock['lock_stock'] = -$value['sale_num'];
                    $res = ProductModel::autoUpdateByID($upStock,$value['product_id']);
                    if (!$res) {
                        $pdo->rollback();
                        return FALSE;
                    }
                } else {//供应订单
                    /**
                     * 虚拟商品解锁出库
                     */
                    $channel_product = ProductChannelModel::find( $value['channel_id'] );
                    $stock = new VoidStockService($channel_product->toArray());
                    $stock->setType(OrderStockService::LOG_TYPE_13);
                    $stock->setLockNum($value['sale_num']);
                    $stock->unlock();
                }
			}
			$pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollback();
            return FALSE;
        }
		return TRUE;



		return $res;
    }



    /**
     * 限购活动是否正常(未使用)
     * @param array $seckill_id 限购ID
     * @param array $user_id 会员ID
     * @param array $product_stock 商品库存
     * @return boolean 更新结果
     */
    public static function orderSeckillIsOk($seckill_id, $user_id, $product_stock) {

        $redis = YDLib::getRedis('redis','r');
        $stock_num = $redis->llen("storenum_".SUPPLIER_ID."_id_".$seckill_id);
        if (!$stock_num || $stock_num <= 0) {//没有库存了
            YDLib::output(ErrnoStatus::STATUS_60560);
        }
        $seckill = $redis->llen("seckill_".SUPPLIER_ID."_id_".$seckill_id);
        if (empty($seckill)) {//redis不存在
            YDLib::output(ErrnoStatus::STATUS_60560);
        }
        $seckill = json_decode($seckill,true);

        if (strtotime($seckill['starttime']) >= time() && strtotime($seckill['endtime']) >= time()) {
            YDLib::output(ErrnoStatus::STATUS_60562);//未开始
        } else if (strtotime($seckill['starttime']) <= time() && strtotime($seckill['endtime']) <= time()) {
            YDLib::output(ErrnoStatus::STATUS_60561);//已结束
        }

        if ($seckill['is_restrictions'] == '2') {//限购
            //查询是否已经购买过
            $user_order_num = OrderChildModel::getInfoByTypeIDAndUserId($seckill_id, $user_id);
            if ($user_order_num >= $seckill['restrictions_num']) {
                YDLib::output(ErrnoStatus::STATUS_60563);//已达到购买限额
            }
        }
        //判断数据库库存数
        if ($product_stock <= '0') {
            YDLib::output(ErrnoStatus::STATUS_60560);
        }
        return true;
    }


    /**
     * 修改限购活动数量
     * @param array $seckill_id 限购ID
     * @param array $user_id 会员ID
     * @param array $product_stock 商品库存
     * @return boolean 更新结果
     */
    public static function editSeckillNum($seckill_id,$num) {
        $redis = YDLib::getRedis('redis','w');
        //回滚redis库存数量
        for ($i=0;$i<$num;$i++) {
            $redis->lpush("storenum_".SUPPLIER_ID."_id_".$seckill_id,1);//加回库存队列
        }



       /*  //更新限购信息(支付后修改，暂时放这)
        if ($data['seckill_id']) {
            $seckill = SeckillModel::getInfoByID($data['seckill_id']);
            if (!$seckill) {
                $pdo->rollback();
                //回滚限购库存数
                if ($data['seckill_id']) {
                    self::editSeckillNum($data['seckill_id'], $data['product']['0']['num']);
                }
                YDLib::output(ErrnoStatus::STATUS_60560);
            }

            $s_info = [];
            $s_info['order_sale_price'] = bcadd($seckill['order_sale_price'], $orderChildData['']);
            $s_info['order_sale_price'] = bcadd($seckill['order_sale_price'], $orderChildData['']);
            $s_info['order_sale_price'] = bcadd($seckill['order_sale_price'], $orderChildData['']);

        } */

    }


    /**
     * 订单取消
     * @param array $child_order_id 子单ID
     * @param array $order_id 主单ID
     * @return boolean 更新结果
     */
    public static function cancelBYChildID($child_order_id,$order_id)
    {
    	$pdo = self::_pdo('db_w');
		$pdo->beginTransaction();
		try {
			//更新子单状态
			$data['child_status'] = CommonBase::STATUS_USER_CANCEL;
	        $res = $pdo->update('order_child', $data, array('id' => intval($child_order_id)));
			if (!$res) {
				$pdo->rollback();
				YDLib::output(ErrnoStatus::STATUS_60510);
			}
			//更新主单状态
			$updata['status'] = CommonBase::STATUS_USER_CANCEL;
	        $res = $pdo->update(self::$_tableName, $updata, array('id' => $order_id));
			if (!$res) {
				$pdo->rollback();
				YDLib::output(ErrnoStatus::STATUS_60510);
			}
			$child_order = OrderCoupanModel::getInfoByOrderID($order_id);
			//退还优惠券
			if ($child_order) {
			    $up                      = [];
			    $up['status']            = '1';
			    $up['use_at']            = NULL;
			    $up['order_id']          = NULL;
			    $up['order_price']       = '0';
			    $up['discount_price']    = '0';
			    $updata = UserCoupanModel::updateByID($up, $child_order['user_coupan_id']);
			    if (!$updata) {
			        $pdo->rollback();
			        YDLib::output(ErrnoStatus::STATUS_60591);
			    }

			    $upCoupanData['use_num'] = '-1';
			    //更新优惠券信息
			    $res = CoupanModel::autoUpdateByID($upCoupanData,$child_order['coupan_id']);

			}


			$product = OrderChildProductModel::getInfoByChildID($child_order_id);
			foreach ($product as $key => $value) {
                //如果是限时抢购
                if ($value['discount_type'] == 1) {
                    $redis = YDLib::getRedis('redis', 'w');
                    //回滚redis库存数量
                    for ($i = 0; $i < $value['sale_num']; $i++) {
                        $redis->lpush("storenum_" . $value['supplier_id'] . "_id_" . $value['discount_id'], 1);//加回库存队列
                    }
                }

                //如果是竞价拍
                if ($value['discount_type'] == 3) {
                    $skData['seckill_id']= $value['discount_id'];
                    $skData['user_id']= $value['user_id'];
                    $skData['product_id']= $value['product_id'];
                    $skData['supplier_id']= $value['supplier_id'];
                    $skOrder = SeckillOrderModel::getOrderByID($skData);
                    if($skOrder){
                        $upDatae['status'] = intval(6);
                        $upDatae['order_status'] = intval(80);//订单取消状态
                        $res = SeckillOrderModel::updateByID($upDatae,$skOrder['id']);
                        if (!$res) {
                            $pdo->rollback();
                            YDLib::output(ErrnoStatus::STATUS_60531);
                            //$jsonData['code'] = '500';
                            //$jsonData['msg'] = '竞价拍状态修改失败！';
                            //return $jsonData;
                        }
                    }
                }

                //如果是拼团
                if ($value['discount_type'] == 4) {
                    $group = SeckillLogModel::findWhereOne(['order_id'=>$value['order_id']]);
                    $res = SeckillLogModel::updateByID(['status'=>3],$group['id']);//更新为拼团失败
                    if (!$res) {
                        $pdo->rollback();
                        YDLib::output(ErrnoStatus::STATUS_60532);
                        //$jsonData['code'] = '500';
                        //$jsonData['msg'] = '更新为拼团失败失败！';
                        //return $jsonData;
                    }
                }

			    if ($value['is_channel'] != OrderModel::IS_CHANNEL_2 ) {//普通订单
                    //库存返还
                    $log_id = ProductStockLogModel::addLog($value['product_id'], 6, $value['sale_num'], -$value['sale_num']);
                    if (!$log_id) {
                        $pdo->rollback();
                        YDLib::output(ErrnoStatus::STATUS_60510);
                    }
                    //变动库存
                    $productInfo = ProductModel::getInfoByIDAllStatus($value['product_id']);
                    $upData['stock'] = bcadd($productInfo['stock'], $value['sale_num']);
                    $upData['lock_stock'] = bcadd($productInfo['lock_stock'], -$value['sale_num']);
                    $upData['lock_stock'] = $upData['lock_stock'] < '0' ? '1' : $upData['lock_stock'];
                    $res = ProductModel::updateByID($upData, $value['product_id']);
                    if (!$res) {
                        $pdo->rollback();
                        YDLib::output(ErrnoStatus::STATUS_60510);
                    }
                } else {//供应订单
                    /**
                     * 虚拟商品解锁返还库存
                     */
                    $channel_product = ProductChannelModel::find( $value['channel_id'] );
                    $stock = new VoidStockService($channel_product->toArray());
                    $stock->setType(OrderStockService::LOG_TYPE_12);
                    $stock->setLockNum($value['sale_num']);
                    $stock->revert();
                    /**
                     * 供应商品解锁返还库存
                     */
                    $product = ProductModel::find($value['product_id']);
                    $stock = new OrderStockService($product->toArray());
                    $stock->setType(OrderStockService::LOG_TYPE_12);
                    $stock->setLockNum($value['sale_num']);
                    $stock->revert();
                }
			}
			$pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollback();
            YDLib::output(ErrnoStatus::STATUS_60508);
        }
		YDLib::output(ErrnoStatus::STATUS_SUCCESS);
    }

    /**
     * 删除订单
     * @param array $child_order_id 子单ID
     * @param array $order_id 主单ID
     * @return boolean 更新结果
     */
    public static function deleteBYChildID($child_order_id,$order_id)
    {
    	$pdo = self::_pdo('db_w');
		//更新子单状态
    	$data['is_del'] = self::DELETE_FAIL;
		$data['deleted_at'] = date("Y-m-d H:i:s");
        $res = $pdo->update('order_child', $data, array('id' => $child_order_id));

		//更新主单状态
    	$updata['is_del'] = self::DELETE_FAIL;
		$updata['deleted_at'] = date("Y-m-d H:i:s");
        $res = $pdo->update(self::$_tableName, $updata, array('id' => $order_id));

		return $res;
    }

    /**
     * 评价订单
     * @param array $child_order_id 子单ID
     * @param array $order_id 主单ID
     * @return boolean 更新结果
     */
    public static function commentBYChildID($child_order_id,$order_id)
    {
    	$pdo = self::_pdo('db_w');
		//更新子单状态
		$data['is_comment'] = CommonBase::COMMENT_CUSTOMER;
        $res = $pdo->update('order_child', $data, array('id' => $child_order_id));

		return $res;
    }

	/**
	 * 获得秒杀活动对应的数量
	 * @param integer  $id 活动ID
	 * return
	 */
	public static function selectMiaoShaNumByType($id,$type = 'order')
	{
		$sql = "SELECT 
					[*]
				FROM 
					".self::$_tablePrefix."seckill s
				LEFT JOIN
					".self::$_tablePrefix."order_child_product p
				ON
					p.discount_id = s.id AND p.discount_type = 1	
				LEFT JOIN
					".self::getTb()." o
				ON
					o.id = p.order_id
				WHERE
					s.supplier_id = " . SUPPLIER_ID . "
							
		";

		$field = "COUNT(DISTINCT o.id) as num";
		if ($type == 'order') {
			$field = "COUNT(DISTINCT o.id) as num";
		} elseif ($type == 'user') {
			$field = "COUNT(DISTINCT o.user_id) as num";
		} elseif ($type == 'saleprice') {
			$field = "DISTINCT o.id ,SUM(o.order_actual_amount) as num";
		}

		$pdo = self::_pdo('db_r');
		$res = $pdo->YDGetRow(str_replace('[*]', $field, $sql));
		return $res['num'];
	}



	/**
	 * 获取订单优惠金额
	 * @return num
	 */
	public static function getMoney($data)
	{
	    $pdo = self::_pdo('db_w');
	    try {
	        $product = [];
	        $product_original_amount_total = 0;
	        $sale_num_total = 0;
	        foreach ($data['product'] as $key => $value) {

	            //获取商品信息（检测是否是本商户商品）
	            $productInfo = ProductModel::getInfoByIDUseAddOrder($value['product_id']);

	            if (!$productInfo) {
	                YDLib::output(ErrnoStatus::STATUS_60025,array('product_id' => $value['product_id']));
	            }

	            $seckill_price = 0;
	            //限购活动
	            if ($data['seckill_id']) {
	                $redis = YDLib::getRedis('redis','r');
	                $stock_num = $redis->llen("storenum_".SUPPLIER_ID."_id_".$data['seckill_id']);
	                if (empty($stock_num) || $stock_num <= 0) {//没有库存了
	                    YDLib::output(ErrnoStatus::STATUS_60560);
	                }
	                $seckill = $redis->get("seckill_".SUPPLIER_ID."_id_".$data['seckill_id']);
	                if (empty($seckill)) {//redis不存在
	                    YDLib::output(ErrnoStatus::STATUS_60560);
	                }
	                $seckill = json_decode($seckill,true);

	                if (strtotime($seckill['starttime']) >= time() && strtotime($seckill['endtime']) >= time()) {
	                    YDLib::output(ErrnoStatus::STATUS_60562);//未开始
	                } else if (strtotime($seckill['starttime']) <= time() && strtotime($seckill['endtime']) <= time()) {
	                    YDLib::output(ErrnoStatus::STATUS_60561);//已结束
	                }

	                if ($seckill['is_restrictions'] == '2') {//限购
	                    //查询是否已经购买过
	                    $user_order_num = OrderChildProductModel::getInfoByTypeIDAndUserId($data['seckill_id'], $data['user_id']);
	                    if ($user_order_num >= $seckill['restrictions_num']) {
	                        YDLib::output(ErrnoStatus::STATUS_60563);//已达到购买限额
	                    }

	                    if ($value['num'] > $seckill['restrictions_num']) {
	                        YDLib::output(ErrnoStatus::STATUS_60563);//已达到购买限额
	                    }
	                }

	                $seckill_price = bcadd($seckill_price, bcmul($seckill['seckill_price'], $value['num'],2),2);
	            }


	            $productInfo['num'] = $value['num'];
	            $productInfo['discount_amount'] = '0';
	            //限购情况下计算商品优惠价
	            $actual_amount = bcmul($productInfo['sale_price'],$value['num'],2);
	            if ($seckill) {
	                $productInfo['actual_amount'] = bcmul($seckill['seckill_price'],$value['num'],2);
	                $productInfo['discount_amount'] = bcsub($actual_amount, $productInfo['actual_amount'],2);
	                $actual_amount = $productInfo['actual_amount'];
	            } else {
	                $productInfo['actual_amount'] = bcmul($productInfo['sale_price'],$value['num'],2);
	            }

	            $product_original_amount_total = bcadd($product_original_amount_total,$actual_amount,2);
	            $sale_num_total = bcadd($sale_num_total,$value['num']);
	            $product[] = $productInfo;
	        }

	        //获取收货地址信息(检测是否是本人收货地址)
	        $charge = '0.00';
	        if ($data['delivery_type'] != '1' && $data['address_id'] > 0) {
    	        $addressInfo = UserAddressModel::getInfoByID($data['address_id'],$data['user_id']);
    	        if ($addressInfo) {
					//获取运费
    	        	$charge = FreightSetModel::getFreightBYProvinceID($addressInfo['province']);
    	        }
	        }

	        //获取卡券信息与添加卡券信息
	        $coupan = [];//优惠券信息
	        $coupan_discount_amount = 0;//优惠券抵扣金额
	        if (isset($data['user_coupan_id']) && !empty($data['user_coupan_id']) && is_numeric($data['user_coupan_id'])) {

	            $search['user_id'] = $data['user_id'];
	            $search['user_coupan_id'] = $data['user_coupan_id'];
	            $search['status'] = 1;
	            $coupanList = UserCoupanModel::getList($search,1,100);
	            if (!is_array($coupanList['rows']) || count($coupanList['rows']) == 0) {
	                YDLib::output(ErrnoStatus::STATUS_60519);
	            }

	            $coupanInfo = $coupanList['rows'][0];

	            //检测优惠券对于本单的有效性
	            //`use_type` tinyint(4) DEFAULT '1' COMMENT '使用类型：1店铺优惠券2商品优惠券',
	            //`use_product_ids` text COMMENT '适用商品id，逗号隔开',
	            //`sill_type` tinyint(4) DEFAULT '1' COMMENT '门槛类型：1无门槛2有门槛',
	            //`sill_price` decimal(10,2) DEFAULT '0.00' COMMENT '适用门槛：满多少元可用',
	            //`pre_type` tinyint(4) DEFAULT '1' COMMENT '优惠类型：1减免N元2打M折',
	            //`pre_value` decimal(10,2) DEFAULT '0.00' COMMENT '优惠值',
	            if ($coupanInfo['time_type'] == 1 && $coupanInfo['start_time'] > date("Y-m-d H:i:s")) {
	                YDLib::output(ErrnoStatus::STATUS_60520);
	            } else {
	                if ($coupanInfo['use_type'] == 2) {
	                    $product_id_list = explode(',', $coupanInfo['use_product_ids']);
	                    $use = FALSE;
	                    $use_amount = 0;
	                    foreach ($product as $k => $v) {
	                        if (in_array($v['id'], $product_id_list)) {
	                            $use_amount = bcadd($use_amount, $v['actual_amount']);
	                            $use = TRUE;
	                        }
	                    }
	                    if (!$use) {
	                        YDLib::output(ErrnoStatus::STATUS_60520);
	                    } else {
	                        if ($coupanInfo['sill_type'] == 2 && $use_amount < $coupanInfo['sill_price']) {
	                            YDLib::output(ErrnoStatus::STATUS_60520);
	                        }
	                    }

	                    if ($coupanInfo['pre_type'] == 1) {
	                        $coupan_discount_amount = $coupanInfo['pre_value'];
	                    } else if ($coupanInfo['pre_type'] == 2) {
	                        $coupan_discount_amount = bcsub($use_amount,bcmul($use_amount,bcdiv($coupanInfo['pre_value'],100,4),2),2);
	                    }

	                    if ($coupan_discount_amount > $use_amount) {
	                        $coupan_discount_amount = $use_amount;
	                    }
	                } else {
	                    if ($coupanInfo['sill_type'] == 2 && $product_original_amount_total < $coupanInfo['sill_price']) {
	                        YDLib::output(ErrnoStatus::STATUS_60520);
	                    }
	                    if ($coupanInfo['pre_type'] == 1) {
	                        $coupan_discount_amount = $coupanInfo['pre_value'];
	                    } else if ($coupanInfo['pre_type'] == 2) {
	                        $coupan_discount_amount = bcsub($product_original_amount_total,bcmul($product_original_amount_total,bcdiv($coupanInfo['pre_value'],100,4),2),2);
	                    }

	                    if ($coupan_discount_amount > $product_original_amount_total) {
	                        $coupan_discount_amount = $product_original_amount_total;
	                    }
	                }
	            }
	        }


	        $orderData = [];

	        $orderData['coupan_discount_amount'] = $coupan_discount_amount;//卡券抵扣金额
			$orderData['freight_charge_original_amount'] = $charge;//原始支付运费金额
			$orderData['freight_charge_actual_amount'] = $charge;//实际支付运费金额
			$orderData['freight_charge_discount_amount'] = 0;//优惠运费金额

			$orderData['product_original_amount'] = $product_original_amount_total;//商品原始金额
			if ($seckill) {
			    $orderData['product_actual_amount'] = $seckill_price;//商品实际支付金额 = 限购价
			    $orderData['product_discount_amount'] = bcsub($product_original_amount_total, $seckill_price,2);//商品优惠金额 = 原始金额-限购总金额
			} else {
			    $orderData['product_actual_amount'] = $product_original_amount_total;//商品实际支付金额
			    $orderData['product_discount_amount'] = 0;//商品优惠金额
			}
	        $resData = [];
	        $resData['order_original_amount'] = bcadd($orderData['product_original_amount'],$orderData['freight_charge_original_amount'],2);//订单原始价格金额(订单总额+总邮费)
	        $resData['order_discount_amount'] = bcadd(bcadd($orderData['coupan_discount_amount'], $orderData['product_discount_amount'],2),$orderData['freight_charge_discount_amount'],2);//订单优惠金额(商品优惠+运费优惠+卡券抵扣)
	        $resData['order_actual_amount'] = bcsub($resData['order_original_amount'], $resData['order_discount_amount'],2);//订单实际支付金额(订单实际总额+实际总邮费)
	        $resData['freight_charge_actual_amount'] = $orderData['freight_charge_actual_amount'];//实际支付运费金额

	        //$resData['coupan_discount_amount'] = $coupan_discount_amount;//卡券抵扣金额

	    } catch (\Exception $e) {
	        YDLib::output(ErrnoStatus::STATUS_60508);
	    }

	    YDLib::output(ErrnoStatus::STATUS_SUCCESS,$resData);
	}


	/**
	 * 获取拼团订单优惠金额
	 * @return num
	 */
	public static function getGroupMoney($data)
	{
		$product_original_amount = bcmul($data['seckill']['sale_price'],$data['num'],2);
		$product_actual_amount = bcmul($data['seckill']['group_price'],$data['num'],2);
		$product_discount_amount = bcsub($product_original_amount,$product_actual_amount,2);
        $freight_charge_actual_amount = '0.00';
        if ($data['delivery_type'] != '1' && $data['address_id'] > 0) {
	        $addressInfo = UserAddressModel::getInfoByID($data['address_id'],$data['user_id']);
	        if ($addressInfo) {
	        	$freight_charge_actual_amount = FreightSetModel::getFreightBYProvinceID($addressInfo['province']);
	        }
        }

        $resData = [];
        $resData['order_original_amount'] = bcadd($product_original_amount,$freight_charge_actual_amount,2);
        $resData['order_discount_amount'] = $product_discount_amount;
        $resData['order_actual_amount'] = bcadd($product_actual_amount,$freight_charge_actual_amount,2);
        $resData['freight_charge_actual_amount'] = $freight_charge_actual_amount;

	    YDLib::output(ErrnoStatus::STATUS_SUCCESS,$resData);
	}




	/**
	 * 生成保证金订单
	 * @return num
	 */
	public static function addMargin($data)
	{
		$pdo = self::_pdo('db_w');
		$pdo->beginTransaction();
		try {

			//获取收货地址信息(检测是否是本人收货地址)
			if ($data['delivery_type'] != '1') {
				$addressInfo = UserAddressModel::getInfoByID($data['address_id'],$data['user_id']);

				if (!$addressInfo) {
					$pdo->rollback();
					YDLib::output(ErrnoStatus::STATUS_60507);
				}

				//获取运费
				$charge = FreightSetModel::getFreightBYProvinceID($addressInfo['province']);

			} else {
				//门店自提订单，收货地址默认为空
				$charge = '0.00';
				$addressInfo = [];
				$addressInfo['province'] = 0;
				$addressInfo['province_txt'] = '';
				$addressInfo['city'] = 0;
				$addressInfo['city_txt'] = '';
				$addressInfo['area'] = 0;
				$addressInfo['area_txt'] = '';
				$addressInfo['street'] = 0;
				$addressInfo['street_txt'] = '';
				$addressInfo['address'] = '';
				$addressInfo['name'] = '';
				$addressInfo['mobile'] = '';
			}



			//生成保证金
			$orderSeckillData['margin']  =  $data['margin'];
			$orderSeckillData['product_id']  =  $data['product_id'];
			$orderSeckillData['user_id']  =  $data['user_id'];
			$orderSeckillData['supplier_id']  =  SUPPLIER_ID;
			$orderSeckillData['seckill_id']  =  $data['seckill_id'];
			$orderSeckillData['order_no']  =  SerialNumber::createSN(SerialNumber::SN_ORDER_MARGIN);
			$orderSeckillData['address_id']  =  $data['address_id'];
			$orderSeckillData['accept_name']  =  $addressInfo['name'];
			$orderSeckillData['pay_type']  =  CommonBase::ORDER_PAY_TYPE_ONLINE;
			$orderSeckillData['accept_mobile']  =  $addressInfo['mobile'];
			$orderSeckillData['delivery_type'] = $data['delivery_type'];//选择收货方式：0 快递 ，1门店自提
			$orderSeckillData['order_status'] = CommonBase::STATUS_PENDING_PAYMENT;//待付款状态
			$orderSeckillData['order_from'] = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
			$orderSeckillData['freight_charge_actual_amount']  =  $charge;
			$order_id = SeckillOrderModel::addData($orderSeckillData);

			$pdo->commit();
		} catch (\Exception $e) {
			$pdo->rollback();
		}

		return $order_id;
}

}