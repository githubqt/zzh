<?php
use Custom\YDLib;
use Common\CommonBase;
use Order\OrderModel;
use Order\OrderChildModel;
use Order\OrderChildProductModel;
use User\UserModel;
use Seckill\SeckillProductModel;
use Seckill\SeckillLogModel;
use Seckill\SeckillModel;
use Seckill\SeckillOrderModel;
use Product\ProductModel;
/**
 * 订单管理
 * @version v0.01
 * @author lqt
 * @time 2018-05-14
 */
class OrderController extends BaseController
{
							
	/**
	 * 商城-订单确认页-提交订单接口
	 *
	 * <pre>
	 *   POST参数
	 * 	   user_id ：		用户id			[必填参数]
	 * 	   delivery_type ：	快递方式 		[非填参数，0快递1门店自提，默认0]
	 * 	   address_id ：	收货地址id		[delivery_type=0快递时为必填参数，门店自提无需必填]
	 * 	   seckill_id： 		限购活动id		[非填参数,如果是限购的商品带上限购商品]
	 * 	   product：		商品信息数组	[必填参数] json字符串 [{"product_id":15,"num":1}]
	 * 		如下：
	 * 			[
	 * 				{
	 * 					"product_id":1, //商品ID   [必填参数]
	 * 					"num",1 		//商品数量  [必填参数]
	 * 				},
	 * 				{
	 * 					"product_id":2,
	 * 					"num",1
	 * 				}
	 * 			]
	 * 	   user_coupan_id：	会员优惠券id		[非填参数，用user_coupan_id，非coupan_id]
	 * </pre>
	 *
	 * <pre>
	 *    调用方式：
	 *        正式：   http://api.qudiandang.com/v1/Order/add
	 *        测试：   http://testapi.qudiandang.com/v1/Order/add
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 * <pre>
	 * 成功：
	 * 		{
	 * 		    "errno": 0,
	 * 		    "errmsg": "请求成功",
	 * 		    "result": {
	 * 				"order_id": "60",
	 * 				"payurl": "https://testapi.qudiandang.com/v1/payment/pay?identif=test&orderId=60",
	 * 			}
	 * 		}
	 * 
	 * 失败：
	 * {
	 *     "errno": 50014,
	 *     "errmsg": "库存不足",
	 *     "result": {
	 *         "stock": 10 //实际库存
	 *     }
	 * }
	 * </pre>
	 */
	public function addAction()
	{	
		$data = [];
		$user_id = $this->user_id;
		$delivery_type = $this->_request->getPost('delivery_type');	
	    $address_id = $this->_request->getPost('address_id');
	    $seckill_id = $this->_request->getPost('seckill_id');
		$user_coupan_id = $this->_request->getPost('user_coupan_id'); 		
		$product = $this->_request->getPost('product');
		
		if (!isset($user_id) || !is_numeric($user_id) || $user_id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40015);
        }			
		
		if (!isset($delivery_type) || !is_numeric($delivery_type) || $delivery_type != 1) {			
            $delivery_type = 0;
        }
		
	    if ($delivery_type == 0) {
    	    if (!isset($address_id) || !is_numeric($address_id) || $address_id <= 0) {
    	        YDLib::output(ErrnoStatus::STATUS_40098);
    	    }
	    }					
		
		if (!isset($product) || empty($product)) {
			YDLib::output(ErrnoStatus::STATUS_60273);
		}	
			
		$product = json_decode($product,TRUE);
		if (!is_array($product) || count($product) == 0) {			
            YDLib::output(ErrnoStatus::STATUS_60273);
        }	
		
		foreach ($product as $key => $value) {
			if (!isset($value['product_id']) || !is_numeric($value['product_id']) || $value['product_id'] <= 0) {			
	            YDLib::output(ErrnoStatus::STATUS_40009);
	        }
								
			if (!isset($value['num']) || !is_numeric($value['num']) || $value['num'] <= 0) {			
	            YDLib::output(ErrnoStatus::STATUS_40013);
	        }			
		}

		//格式化生成订货单的数据
		$data['user_id'] = $user_id;
		$data['address_id'] = $address_id;
		$data['seckill_id'] = $seckill_id;
		$data['user_coupan_id'] = $user_coupan_id;
		$data['product'] = $product;
		$data['delivery_type'] = $delivery_type;
		
		$res = OrderModel::addOrder($data);	
   		
	}	

	/**
	 * 商城-拼团-提交订单接口
	 *
	 * <pre>
	 *   POST参数
	 * 	   user_id ：		用户id			[必填参数]
	 * 	   delivery_type ：	快递方式 		[非填参数，0快递1门店自提，默认0]
	 * 	   address_id ：	收货地址id		[必填参数]
	 * 	   id ：			拼团商品主键ID	[必填参数]
	 * 	   num ：			商品数量		[必填参数]
	 * 	   tuan_id：	        团长活动id		[非填参数，空为团长]
	 * </pre>
	 *
	 * <pre>
	 *    调用方式：
	 *        正式：   http://api.qudiandang.com/v1/Order/addGroup
	 *        测试：   http://testapi.qudiandang.com/v1/Order/addGroup
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 * <pre>
	 * 成功：
	 * 		{
	 * 		    "errno": 0,
	 * 		    "errmsg": "请求成功",
	 * 		    "result": {
	 * 				"order_id": "60",
	 * 				"payurl": "https://testapi.qudiandang.com/v1/payment/pay?identif=test&orderId=60",
	 * 			}
	 * 		}
	 * 
	 * 失败：
	 * {
	 *     "errno": 50014,
	 *     "errmsg": "库存不足",
	 *     "result": {
	 *         "stock": 10 //实际库存
	 *     }
	 * }
	 * </pre>
	 */
	public function addGroupAction()
	{	
	    $data = [];
	    $user_id = $this->user_id;
	    $delivery_type = $this->_request->getPost('delivery_type');
	    $address_id = $this->_request->getPost('address_id');
	    $id = $this->_request->getPost('id');
	    $num = $this->_request->getPost('num');
	    $tuan_id = $this->_request->getPost('tuan_id');
	
	    if (!isset($user_id) || !is_numeric($user_id) || $user_id <= 0) {
	        YDLib::output(ErrnoStatus::STATUS_40015);
	    }	
	
	    if (!isset($delivery_type) || !is_numeric($delivery_type) || $delivery_type != 1) {
	        $delivery_type = 0;
	    }
		
	    if ($delivery_type == 0) {
    	    if (!isset($address_id) || !is_numeric($address_id) || $address_id < 0) {
    	        $address_id = 0;
    	    }
	    }		

	    if (!isset($id) || !is_numeric($id) || $id <= 0) {
	        YDLib::output(ErrnoStatus::STATUS_40103);
	    }
		
	    if (!isset($num) || !is_numeric($num) || $num <= 0) {
	        YDLib::output(ErrnoStatus::STATUS_40020);
	    }
		
	    if (!isset($tuan_id) || !is_numeric($tuan_id) || $tuan_id < 0) {
	        $tuan_id = 0;
	    }
		
	    $detail = SeckillProductModel::getInfoByID($id);
		if (!$detail) {
			YDLib::output(ErrnoStatus::STATUS_60570);
		}		
	
	    //格式化生成订货单的数据
	    $data['user_id'] = $user_id;
		$data['delivery_type'] = $delivery_type;
	    $data['address_id'] = $address_id;
		$data['id'] = $id;		
		$data['num'] = $num;		
		$data['tuan_id'] = $tuan_id;		
	    $data['seckill'] = $detail;		
		
	    $res = OrderModel::addGroupOrder($data);
   		
	}	

	/**
	 * 商城-订单确认页-获取订单会员可用优惠券接口
	 *
	 * <pre>
	 *   POST参数
	 * 	   user_id ： 	用户id 			[必填参数]
     *     seckill_id 活动id  查询活动信息
	 * 	   product：	商品信息数组	[必填参数] json字符串 [{"product_id":15,"num":1}]
	 * 		如下：
	 * 			[
	 * 				{
	 * 					"product_id":1, //商品ID   [必填参数]
	 * 					"num",1 		//商品数量  [必填参数]
	 *				},
	 * 				{
	 * 					"product_id":2,
	 * 					"num",1
	 *				}
	 * 			]
	 * </pre>
	 *
	 * <pre>
	 *    调用方式：
	 *        正式：   http://api.qudiandang.com/v1/Order/coupan
	 *        测试：   http://testapi.qudiandang.com/v1/Order/coupan
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 * <pre>
	 * 成功：
	 * 		{
	 * 		    "errno": "0",
	 * 		    "errmsg": "请求成功",
	 * 		    "result": [
	 * 		        {
	 * 		            "c_name": "端午大促销啦",
	 * 		            "c_status": "2",
	 * 		            "time_type": "1",
	 * 		            "start_time": "2018-05-21 18:39:07",
	 * 		            "end_time": "2018-05-31 18:39:12",
	 * 		            "use_type": "1",
	 * 		            "sill_type": "2",
	 * 		            "sill_price": "200.00",
	 * 		            "pre_type": "2",
	 * 		            "pre_value": "80.00",
	 * 		            "id": "7",
	 * 		            "status": "1",
	 * 		            "give_at": "2018-05-22 21:03:18",
	 * 		            "use_at": null,
	 * 		            "order_id": "0",
	 * 		            "order_price": "0",
	 * 		            "discount_price": "0",
	 * 		            "coupan_id": "2",//---------------------------------------------------------------优惠券ID
	 * 		            "user_coupan_id": "7",//----------------------------------------------------------会员优惠券ID，下单时使用
	 * 		            "c_status_txt": "进行中",
	 * 		            "use_type_txt": "店铺优惠券",
	 * 		            "sill_txt": "满200.00元 ",
	 * 		            "pre_txt": "打80.00折 ",
	 * 		            "time_txt": "可用时间：2018-05-21 18:39:07至2018-05-31 18:39:12",
	 * 		            "status_txt": "未使用",
	 * 		            "can_use": 1//--------------------------------------------------------------------是否可用 1可用2不可用
	 * 		        },
	 * 		        {
	 * 		            "c_name": "部分商品券111",
	 * 		            "c_status": "2",
	 * 		            "time_type": "2",
	 * 		            "start_time": "2018-05-22 13:54:01",
	 * 		            "end_time": "2018-05-22 13:54:01",
	 * 		            "use_type": "2",
	 * 		            "sill_type": "1",
	 * 		            "sill_price": "0.00",
	 * 		            "pre_type": "2",
	 * 		            "pre_value": "100.00",
	 * 		            "id": "10",
	 * 		            "status": "1",
	 * 		            "give_at": "2018-05-22 21:04:55",
	 * 		            "use_at": null,
	 * 		            "order_id": "0",
	 * 		            "order_price": "0",
	 * 		            "discount_price": "0",
	 * 		            "coupan_id": "4",
	 * 		            "user_coupan_id": "10",
	 * 		            "c_status_txt": "进行中",
	 * 		            "use_type_txt": "商品优惠券",
	 * 		            "sill_txt": "无使用门槛 ",
	 * 		            "pre_txt": "打100.00折 ",
	 * 		            "time_txt": "可用时间：不限",
	 * 		            "status_txt": "未使用",
	 * 		            "can_use": 2
	 * 		        }
	 * 		    ]
	 * 		}
	 *
	 * 失败：
	 * {
	 *     "errno": 50014,
	 *     "errmsg": "库存不足",
	 *     "result": {
	 *         "stock": 10 //实际库存
	 *     }
	 * }
	 * </pre>
	 */
	public function coupanAction()
	{	
		$data = [];
		$user_id = $this->user_id;
		$product = $this->_request->getPost('product');
		
	    $seckill_id = $this->_request->getPost('seckill_id');
		$seckill_id = !empty($seckill_id)?intval($seckill_id):'';			
		
		if (!isset($user_id) || !is_numeric($user_id) || $user_id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40015);
        }	
		
		if (!isset($product) || empty($product)) {
			YDLib::output(ErrnoStatus::STATUS_60273);
		}	
			
		$product = json_decode($product,TRUE);
		if (!is_array($product) || count($product) == 0) {			
            YDLib::output(ErrnoStatus::STATUS_60273);
        }	
		
		foreach ($product as $key => $value) {
			if (!isset($value['product_id']) || !is_numeric($value['product_id']) || $value['product_id'] <= 0) {			
	            YDLib::output(ErrnoStatus::STATUS_40009);
	        }
								
			if (!isset($value['num']) || !is_numeric($value['num']) || $value['num'] <= 0) {			
	            YDLib::output(ErrnoStatus::STATUS_40013);
	        }			
		}
		
		//格式化生成订货单的数据
		$data['user_id'] = $user_id;
		$data['product'] = $product;
		$data['seckill_id'] = $seckill_id;
	
		$res = OrderModel::getOrderCoupan($data);		
	}	
   
    /**
     * 获得订单列表
     *
     * <pre>
     *   POST参数
     *		page: 			页码  		非必填 【空：1】
     *		rows: 			条数  		非必填 【空：10】
	 *  	user_id ： 		用户id 		必填参数
     *		status：		订单状态 	非必填  默认全部 
	 * 		【1：待付款，2：待发货，3：待收货，4：已完成，5：已取消，6：可售后，7：待成团】
     * </pre>
     *
     * <pre>
     *    调用方式：
     *        正式：   http://api.qudiandang.com/v1/Order/list
     *        测试：   http://testapi.qudiandang.com/v1/Order/list
     *
     * </pre>
     *
     * @return string 返回JSON数据格式
     * <pre>
     * 成功：
     *	{
     *	    "errno": "0",
     *	    "errmsg": "请求成功",
     *	    "result": {
     *	        "page": 1,//----------------------------------------------------------------------------------------当前页码
     *	        "total": "8",//-------------------------------------------------------------------------------------总条数
     *	        "list": [
     *	            {
     *	                "id": "52",//-------------------------------------------------------------------------------子订单ID
     *	                "child_order_no": "201805311000000114",//---------------------------------------------------子订单编号
     *	                "child_status": "20",//---------------------------------------------------------------------子订单状态值
     *	                "sale_num": "2",//--------------------------------------------------------------------------商品总数
     *	                "child_order_actual_amount": "13100.00",//--------------------------------------------------实付款
     *	                "order_id": "69",//-------------------------------------------------------------------------主订单ID
     *	                "status": 1,//------------------------------------------------------------------------------前台订单状态值
     *	                "child_status_txt": "待付款",//-------------------------------------------------------------子订单状态文本
     *	                "payurl": "https:.......",//----------------------------------------------------------------支付url
     *	                "product": [//------------------------------------------------------------------------------商品详情
     *	                    {
     *	                        "id": "83",
     *	                        "product_id": "15",//---------------------------------------------------------------商品ID
     *	                        "logo_url": "http://file.qudiandang.c.......df0a_497_500x500.jpg",//----------------商品主图
     *	                        "product_name": "爱马仕HERMES香水女士男......士淡香水50ml",//-----------------------商品名称
     *	                        "sale_price": "3000.00",//----------------------------------------------------------销售价
     *	                        "market_price": "5000.00",//--------------------------------------------------------公价
     *	                        "sale_num": "1",//------------------------------------------------------------------商品数量
     *	                        "self_code": "1000022",//-----------------------------------------------------------商品编码
     *	                        "actual_amount": "3000.00",//-------------------------------------------------------商品实际金额
     *	                        "supplier_id": "10001",//-----------------------------------------------------------商户ID
     *	                        "discount_type": "0",//-------------------------------------------------------------活动类型：1.秒杀0.无活动
     *	                        "discount_id": "0"//----------------------------------------------------------------活动ID
     *	                    },
     *	                    {
     *	                        "id": "84",
     *	                        "product_id": "1",
     *	                        "logo_url": "http://static.qudiandang.com/common/images/common.png",
     *	                        "product_name": "商品1",
     *	                        "sale_price": "10000.00",
     *	                        "market_price": "1000000.00",
     *	                        "sale_num": "1",
     *	                        "self_code": "10000001",
     *	                        "actual_amount": "10000.00",
     *	                        "supplier_id": "10001",
     *	                        "discount_type": "0",
     *	                        "discount_id": "0"
     *	                    }
     *	                ]
     *	            },
     *	            {
     *	                "id": "8",
     *	                "child_order_no": "02100010000118051600007",
     *	                "child_status": "待付款",
     *	                "sale_num": "2",
     *	                "child_order_original_amount": "6000.00",
     *	                "product": [
     *	                    {
     *	                        "id": "12",
     *	                        "product_id": "15",
     *	                        "logo_url": "http://file.qudiandang.com//upload/product/2018/05/15/16b4485eb0763a327804ff95c249df0a_497.jpg",
     *	                        "product_name": "【520礼物】爱马仕（HERMES） 香水女士男士淡香水持久香氛 大地男士淡香水50ml",
     *	                        "sale_price": "3000.00",
     *	                        "market_price": "5000.00",
     *	                        "sale_num": "2"
     *	                    }
     *	                ]
     *	            }
     *	        ]
     *	    }
     *	}
     *
     * 失败：
     * 	{
     * 	    "errno": 40015,
     * 	    "errmsg": "用户ID不能为空",
     * 	    "result": []
     * 	}
     * </pre>
     */
    public function listAction() 
    {	        
       	$page = $this->_request->getPost('page');
		$page = !empty($page)?intval($page):1;
		$page = $page>0?$page:1;
								
       	$rows = $this->_request->getPost('rows');
		$rows = !empty($rows)?intval($rows):10;
		$rows = $rows>0?$rows:10;
					
		$user_id = $this->user_id;
		$status = $this->_request->getPost('status');		
     	
		if (!isset($user_id) || !is_numeric($user_id) || $user_id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40015);
        }
		$search['user_id'] = $user_id;
		
		if (isset($status) && !empty($status) && in_array($status, CommonBase::ORDER_STATUS)) {
			if ($status == 6) {
				$search['is_after_sales'] = CommonBase::SERVICE_NONE;
			}			
            $status = CommonBase::ORDER_STATUS_VALUE[$status];
			$status = implode(',', $status);
			$search['status'] = $status;
        }	

        $list = OrderChildModel::getList($search,$page,$rows);
       
       	if ($list == false) {
       		$data['page'] = $page;
       		$data['total'] = 0;
	   		$data['list'] = [];  				
       	} else {
       		$data['page'] = $page;
       		$data['total'] = $list['total'];
	   		$data['list'] = $list['list']; 
			if (is_array($data['list']) && count($data['list']) > 0) {
				foreach ($data['list'] as $key => $value) {					
					foreach (CommonBase::ORDER_STATUS_VALUE as $k => $v) {
						if (in_array($value['child_status'], $v)) {
							$num = $k==6?4:$k;
							$data['list'][$key]['status'] = $num;
						}
					}
					
					$data['list'][$key]['child_status_txt'] = CommonBase::STATUS_VALUE[$value['child_status']];
					
					if ($data['list'][$key]['child_status'] == '50' && $data['list'][$key]['delivery_type'] == '1') {
						$data['list'][$key]['child_status_txt'] = '待自提';
					}
					
                    $product = OrderChildProductModel::getInfoByChildID($value['id']);


                    if ($value['child_status'] == OrderModel::STATUS_PENDING_PAYMENT) {
					    $payurl = 'https://'.$_SERVER['HTTP_HOST'].'/v1/payment/pay?identif='.SUPPLIER_DOMAIN.'&orderId='.$value['order_id'];//跳转支付页面
					    $data['list'][$key]['payurl'] = $payurl;
					
					    //是否有商品下架或删除
					    $data['list'][$key]['is_normal'] = 'yes';
					    
					}
					
					if ($product) {
					    foreach ($product as $kk=>$vv) {
					        $product[$kk]['product_is_normal'] = 'yes';
					        $nowproduct = ProductModel::getInfoByIDNew($vv['product_id']);
					        if ($nowproduct == false) {
					            $data['list'][$key]['is_normal'] = 'no';
					            $product[$kk]['product_is_normal'] = 'no';
					        }else{
					        	$data['list'][$key]['is_return'] = $nowproduct['is_return'];
					        }
					    }
					}
					
					$data['list'][$key]['product'] = $product;
					//订单类型
					$data['list'][$key]['discount_type'] = '0';
					$data['list'][$key]['discount_id'] = '0';
					$data['list'][$key]['discount_product_id'] = '0';
					$data['list'][$key]['tuan_id'] = '0';
                    $data['list'][$key]['is_channel'] = '1';//1普通订单2供应订单
					if (count($data['list'][$key]['product']) == 1) {
						$data['list'][$key]['discount_type'] = $data['list'][$key]['product'][0]['discount_type'];
						$data['list'][$key]['discount_id'] = $data['list'][$key]['product'][0]['discount_id'];
						$data['list'][$key]['discount_product_id'] = $data['list'][$key]['product'][0]['discount_product_id'];
                        $data['list'][$key]['is_channel'] = $data['list'][$key]['product'][0]['is_channel'];
					}
					//拼团
					if ($data['list'][$key]['discount_type'] == 4) {
						$tuan_info = SeckillLogModel::getInfos($data['list'][$key]['discount_product_id'],$data['list'][$key]['order_id']);
						if ($tuan_info) {
							$data['list'][$key]['tuan_id'] = $tuan_info['tuan_id'];
						}
					} else if ($data['list'][$key]['discount_type'] == 3) {
						
						
						$data['list'][$key]['sckill'] =  SeckillModel :: getUserOrderByID($data['list'][$key]['discount_id']);
						
						$data['list'][$key]['child_order_actual_amount'] = bcadd($data['list'][$key]['child_order_actual_amount'],$data['list'][$key]['sckill']['order_sale_price'],2);
						
					}

				}
			}       		
       	}  	
       		YDLib::output(ErrnoStatus::STATUS_SUCCESS,$data,FALSE);
   		
	}

    /**
     * 获得订单详情
     *
     * <pre>
     *   POST参数
	 * 	   user_id ： 	用户id 			[必填参数]
	 * 	   id ：		子订单号 		[必填参数]
     * </pre>
     *
     * <pre>
     *    调用方式：
     *        正式：   http://api.qudiandang.com/v1/Order/detail
     *        测试：   http://testapi.qudiandang.com/v1/Order/detail
     *
     * </pre>
     *
     * @return string 返回JSON数据格式
     * <pre>
     * 成功：
     * 	{
     * 	    "errno": "0",
     * 	    "errmsg": "请求成功",
     * 	    "result": {
     * 	        "id": "2",
     * 	        "user_id": "1",
     * 	        "child_order_no": "02100010000118051600002",
     * 	        "child_status": "20",
     * 	        "sale_num": "1",
     * 	        "child_order_original_amount": "3000.00",//----原始金额
     * 	        "child_order_discount_amount": "1000.00",//----优惠金额
     * 	        "child_order_actual_amount": "2000.00",//----实际金额
     *          "child_freight_charge_actual_amount": "10",//----运费金额
     * 	        "status": 1,
     * 	        "child_status_txt": "待付款",
     *          "province_name": "山东",
     *          "city_name": "菏泽市",
     *          "area_name": "牡丹区",
     *          "street_name": "胡集镇",
     *          "address": "姚刘庄",
     *          "accept_name": "老黄",//-----------------收货人
     *          "accept_mobile": "18513854271",//--------收货人手机
     *          "express_id": "2",//---------------------快递id
     *          "express_name": "申通",//----------------待收货状态下显示
     *          "express_no": "234243242342432",//-------待收货状态下显示
     *          "delivery_type": "0",//-------收货方式：0快递 ，1门店自提
     *          "delivery_no": "234243242342432",//-------自提码
     * 	        "product": [
     * 	            {
     * 	                "id": "7",
     * 	                "product_id": "15",
     * 	                "logo_url": "http://file.qudiandang.com//upload/product/2018/05/15/16b4485eb0763a327804ff95c249df0a_497.jpg",
     * 	                "product_name": "【520礼物】爱马仕（HERMES） 香水女士男士淡香水持久香氛 大地男士淡香水50ml",
     * 	                "sale_price": "3000.00",
     * 	                "market_price": "5000.00",
     * 	                "sale_num": "1"
     * 	            }
     * 	        ]
     * 	    }
     * 	}
     *
     * 失败：
     * 	{
     * 	    "errno": 40015,
     * 	    "errmsg": "用户ID不能为空",
     * 	    "result": []
     * 	}
     * </pre>
     */
    public function detailAction() 
    {	        
		$user_id = $this->user_id;
		$id = $this->_request->getPost('id');	
		
		
		if (!isset($user_id) || !is_numeric($user_id) || $user_id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40015);
        }	
		
		if (!isset($id) || !is_numeric($id) || $id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40058);
        }		
		
		$orderInfo = OrderChildModel::getInfoByID($id);

		if (!$orderInfo) {
			YDLib::output(ErrnoStatus::STATUS_60503);
		}
		
		if ($orderInfo['user_id'] != $user_id) {
			YDLib::output(ErrnoStatus::STATUS_50017);
		}
		
		foreach (CommonBase::ORDER_STATUS_VALUE as $k => $v) {
			if (in_array($orderInfo['child_status'], $v)) {
				$num = $k==6?4:$k;
				$orderInfo['status'] = $num;
			}
		}
		$orderInfo['child_status_txt'] = CommonBase::STATUS_VALUE[$orderInfo['child_status']];
		if ($orderInfo['child_status'] == '50' && $orderInfo['delivery_type'] == '1') {
			$orderInfo['child_status_txt'] = '待自提';
		}		
		$product = OrderChildProductModel::getInfoByChildID($orderInfo['id']);   		
		
		
		if ($orderInfo['child_status'] == OrderModel::STATUS_PENDING_PAYMENT) {
		    $payurl = 'https://'.$_SERVER['HTTP_HOST'].'/v1/payment/pay?identif='.SUPPLIER_DOMAIN.'&orderId='.$orderInfo['order_id'];//跳转支付页面
		    $orderInfo['payurl'] = $payurl;//是否有商品下架或删除
		    $orderInfo['is_normal'] = 'yes';
		
		}
		
		if ($product) {
		    foreach ($product as $kk=>$vv) {
		        $product[$kk]['product_is_normal'] = 'yes';
		        $nowproduct = ProductModel::getInfoByIDNew($vv['product_id']);
		        if ($nowproduct == false) {
		            $orderInfo['is_normal'] = 'no';
		            $product[$kk]['product_is_normal'] = 'no';
		        }
		    }
		}
		
		$orderInfo['product'] = $product;
		
		// 订单类型
		
		$orderInfo ['discount_type'] = '0';
		$orderInfo ['discount_id'] = '0';
		$orderInfo ['discount_product_id'] = '0';
		$orderInfo ['tuan_id'] = '0';
        $orderInfo['is_channel'] = '1';//1普通订单2供应订单
		if (count ( $orderInfo ['product'] ) == 1) {
			$orderInfo ['discount_type'] = $orderInfo ['product'] [0] ['discount_type'];
			$orderInfo ['discount_id'] = $orderInfo ['product'] [0] ['discount_id'];
			$orderInfo ['discount_product_id'] = $orderInfo ['product'] [0] ['discount_product_id'];
			$orderInfo ['is_channel'] = $orderInfo ['product'] [0] ['is_channel'];
		}
		// 拼团
		if ($orderInfo ['discount_type'] == 4) {
			$tuan_info = SeckillLogModel::getInfos ( $orderInfo ['discount_product_id'], $orderInfo ['order_id'] );
			if ($tuan_info) {
				$orderInfo ['tuan_id'] = $tuan_info ['tuan_id'];
			}
		} else if ($orderInfo['discount_type'] == 3) {

            $orderInfo['sckill'] =  SeckillModel :: getUserOrderByID($orderInfo['discount_id']);
            $orderInfo['child_order_actual_amount'] = bcadd($orderInfo['child_order_actual_amount'],$orderInfo['sckill']['order_sale_price'],2);
		}
		
		
		
   		YDLib::output(ErrnoStatus::STATUS_SUCCESS,$orderInfo,FALSE);
	}

    /**
     * 通过自提码获得订单详情
     *
     * <pre>
     *   POST参数
	 * 	   code ：		自提码 		[必填参数]
     * </pre>
     *
     * <pre>
     *    调用方式：
     *        正式：   http://api.qudiandang.com/v1/Order/codedetail
     *        测试：   http://testapi.qudiandang.com/v1/Order/codedetail
     *
     * </pre>
     *
     * @return string 返回JSON数据格式
     * <pre>
     * 成功：
     * 	{
     * 	    "errno": "0",
     * 	    "errmsg": "请求成功",
     * 	    "result": {
     * 	        "id": "2",
     * 	        "user_id": "1",
     * 	        "child_order_no": "02100010000118051600002",
     * 	        "child_status": "20",
     * 	        "sale_num": "1",
     * 	        "child_order_original_amount": "3000.00",//----原始金额
     * 	        "child_order_discount_amount": "1000.00",//----优惠金额
     * 	        "child_order_actual_amount": "2000.00",//----实际金额
     *          "child_freight_charge_actual_amount": "10",//----运费金额
     * 	        "status": 1,
     * 	        "child_status_txt": "待付款",
     * 	        "created_at": "2018-07-18 10:23:47",//-------下单时间
     *          "delivery_type": "0",//-------收货方式：0快递 ，1门店自提
     *          "delivery_no": "234243242342432",//-------自提码
     * 	        "product": [
     * 	            {
     * 	                "id": "7",
     * 	                "product_id": "15",
     * 	                "logo_url": "http://file.qudiandang.com//upload/product/2018/05/15/16b4485eb0763a327804ff95c249df0a_497.jpg",
     * 	                "product_name": "【520礼物】爱马仕（HERMES） 香水女士男士淡香水持久香氛 大地男士淡香水50ml",
     * 	                "sale_price": "3000.00",
     * 	                "market_price": "5000.00",
     * 	                "sale_num": "1"
     * 	            }
     * 	        ]
     * 	    }
     * 	}
     *
     * 失败：
     * 	{
     * 	    "errno": 40015,
     * 	    "errmsg": "用户ID不能为空",
     * 	    "result": []
     * 	}
     * </pre>
     */
    public function codedetailAction() 
    {
    	$code = $this->_request->getPost('code');	
			        
		if (!isset($code) || empty($code)) {			
            YDLib::output(ErrnoStatus::STATUS_40019);
        }		
		
		$orderInfo = OrderChildModel::getInfoByCode($code);

		if (!$orderInfo) {
			YDLib::output(ErrnoStatus::STATUS_60310);
		}
		
		if ($orderInfo['child_status'] != CommonBase::STATUS_ALREADY_SHIPPED) {
			YDLib::output(ErrnoStatus::STATUS_50023);
		}			
		
		foreach (CommonBase::ORDER_STATUS_VALUE as $k => $v) {
			if (in_array($orderInfo['child_status'], $v)) {
				$num = $k==6?4:$k;
				$orderInfo['status'] = $num;
			}
		}
		$orderInfo['child_status_txt'] = CommonBase::STATUS_VALUE[$orderInfo['child_status']];
		if ($orderInfo['child_status'] == '50' && $orderInfo['delivery_type'] == '1') {
			$orderInfo['child_status_txt'] = '待自提';
		}		
		$orderInfo['product'] = OrderChildProductModel::getInfoByChildID($orderInfo['id']);   		
		$orderInfo['user'] = UserModel::getAdminInfo($orderInfo['user_id']);   		
		$orderInfo['user']['sex_txt'] = CommonBase::SEX_VALUE[$orderInfo['user']['sex']];
   		YDLib::output(ErrnoStatus::STATUS_SUCCESS,$orderInfo,FALSE);
	}

	/**
	 * 订单取消接口
	 *
	 * <pre>
	 *   POST参数
	 * 	   user_id ： 	用户id 			[必填参数]
	 * 	   id ：		子订单号 		[必填参数]
	 * </pre>
	 *
	 * <pre>
	 *    调用方式：
	 *        正式：   http://api.qudiandang.com/v1/Order/cancel
	 *        测试：   http://testapi.qudiandang.com/v1/Order/cancel
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 * <pre>
	 * 成功：
	 * 		{
	 * 		    "errno": 0,
	 * 		    "errmsg": "请求成功",
	 * 		    "result": 17
	 * 		}
	 *
	 * 失败：
	 * {
     * 	    "errno": 40015,
     * 	    "errmsg": "用户ID不能为空",
     * 	    "result": []
	 * }
	 * </pre>
	 */
	public function cancelAction()
	{	
		$user_id = $this->user_id;
		$id = $this->_request->getPost('id');	
		
		if (!isset($user_id) || !is_numeric($user_id) || $user_id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40015);
        }	
		
		if (!isset($id) || !is_numeric($id) || $id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40058);
        }		
		
		$orderInfo = OrderChildModel::getInfoByID($id);
		if (!$orderInfo) {
			YDLib::output(ErrnoStatus::STATUS_60503);
		}

		if ($orderInfo['user_id'] != $user_id) {
			YDLib::output(ErrnoStatus::STATUS_50017);
		}	

		if ($orderInfo['child_status'] != CommonBase::STATUS_PENDING_PAYMENT) {
			YDLib::output(ErrnoStatus::STATUS_60514);
		}				
		$res = OrderModel::cancelBYChildID($id,$orderInfo['order_id']);	
		if (!$res) {
			YDLib::output(ErrnoStatus::STATUS_60510);
		}  
		YDLib::output(ErrnoStatus::STATUS_SUCCESS);		
	}

	/**
	 * 确认收货接口
	 *
	 * <pre>
	 *   POST参数
	 * 	   user_id ： 	用户id 			[必填参数]
	 * 	   id ：		子订单号 		[必填参数]
	 * </pre>
	 *
	 * <pre>
	 *    调用方式：
	 *        正式：   http://api.qudiandang.com/v1/Order/delivery
	 *        测试：   http://testapi.qudiandang.com/v1/Order/delivery
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 * <pre>
	 * 成功：
	 * 		{
	 * 		    "errno": 0,
	 * 		    "errmsg": "请求成功",
	 * 		    "result": 17
	 * 		}
	 *
	 * 失败：
	 * {
	 *     "errno": 50014,
	 *     "errmsg": "库存不足",
	 *     "result": {
	 *         "stock": 10 //实际库存
	 *     }
	 * }
	 * </pre>
	 */
	public function deliveryAction()
	{	
		$user_id = $this->user_id;
		$id = $this->_request->getPost('id');	
		
		if (!isset($user_id) || !is_numeric($user_id) || $user_id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40015);
        }	
		
		if (!isset($id) || !is_numeric($id) || $id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40058);
        }		
		
		$orderInfo = OrderChildModel::getInfoByID($id);
		if (!$orderInfo) {
			YDLib::output(ErrnoStatus::STATUS_60503);
		}

		if ($orderInfo['user_id'] != $user_id) {
			YDLib::output(ErrnoStatus::STATUS_50017);
		}	
			
		if ($orderInfo['child_status'] != CommonBase::STATUS_ALREADY_SHIPPED) {
			YDLib::output(ErrnoStatus::STATUS_50018);
		}				
		
		$res = OrderModel::deliveryBYChildID($id,$orderInfo['order_id']);	
		if (!$res) {
			YDLib::output(ErrnoStatus::STATUS_60054);
		}  
		YDLib::output(ErrnoStatus::STATUS_SUCCESS);		
	}
	
	/**
	 * 确认提货接口
	 *
	 * <pre>
	 *   POST参数
	 * 	   id ：		子订单号 		[必填参数]
	 * </pre>
	 *
	 * <pre>
	 *    调用方式：
	 *        正式：   http://api.qudiandang.com/v1/Order/codedelivery
	 *        测试：   http://testapi.qudiandang.com/v1/Order/codedelivery
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 * <pre>
	 * 成功：
	 * 		{
	 * 		    "errno": 0,
	 * 		    "errmsg": "请求成功",
	 * 		    "result": 17
	 * 		}
	 *
	 * 失败：
	 * {
	 *     "errno": 50014,
	 *     "errmsg": "库存不足",
	 *     "result": {
	 *         "stock": 10 //实际库存
	 *     }
	 * }
	 * </pre>
	 */
	public function codedeliveryAction()
	{	
		$id = $this->_request->getPost('id');	

		if (!isset($id) || !is_numeric($id) || $id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40058);
        }		
		
		$orderInfo = OrderChildModel::getInfoByID($id);
		if (!$orderInfo) {
			YDLib::output(ErrnoStatus::STATUS_60310);
		}
			
		if ($orderInfo['child_status'] != CommonBase::STATUS_ALREADY_SHIPPED) {
			YDLib::output(ErrnoStatus::STATUS_50023);
		}				
		
		$res = OrderModel::codedeliveryBYChildID($id,$orderInfo['order_id']);	
		if (!$res) {
			YDLib::output(ErrnoStatus::STATUS_60054);
		}  
		YDLib::output(ErrnoStatus::STATUS_SUCCESS);		
	}
	
	/**
	 * 订单删除接口
	 *
	 * <pre>
	 *   POST参数
	 * 	   user_id ： 	用户id 			[必填参数]
	 * 	   id ：		子订单号 		[必填参数]
	 * </pre>
	 *
	 * <pre>
	 *    调用方式：
	 *        正式：   http://api.qudiandang.com/v1/Order/delete
	 *        测试：   http://testapi.qudiandang.com/v1/Order/delete
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 * <pre>
	 * 成功：
	 * 		{
	 * 		    "errno": 0,
	 * 		    "errmsg": "请求成功",
	 * 		    "result": 17
	 * 		}
	 *
	 * 失败：
	 * {
     * 	    "errno": 40015,
     * 	    "errmsg": "用户ID不能为空",
     * 	    "result": []
	 * }
	 * </pre>
	 */
	public function deleteAction()
	{	
		$user_id = $this->user_id;
		$id = $this->_request->getPost('id');	
		
		if (!isset($user_id) || !is_numeric($user_id) || $user_id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40015);
        }	
		
		if (!isset($id) || !is_numeric($id) || $id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40058);
        }		
		
		$orderInfo = OrderChildModel::getInfoByID($id);
		if (!$orderInfo) {
			YDLib::output(ErrnoStatus::STATUS_60503);
		}

		if ($orderInfo['user_id'] != $user_id) {
			YDLib::output(ErrnoStatus::STATUS_50017);
		}	
			
		if (!in_array($orderInfo['child_status'], CommonBase::ORDER_CAN_DELETE)) {
			YDLib::output(ErrnoStatus::STATUS_60515);
		}				
		
		$res = OrderModel::deleteBYChildID($id,$orderInfo['order_id']);		
		if (!$res) {
			YDLib::output(ErrnoStatus::STATUS_60512);
		}  
		YDLib::output(ErrnoStatus::STATUS_SUCCESS);		
	}	
	
	/**
	 * 订单评价接口
	 *
	 * <pre>
	 *   POST参数
	 * 	   user_id ： 	用户id 			[必填参数]
	 * 	   id ：		子订单号 		[必填参数]
	 * 	   content ：	评价内容 		[必填参数]
	 * </pre>
	 *
	 * <pre>
	 *    调用方式：
	 *        正式：   http://api.qudiandang.com/v1/Order/comment
	 *        测试：   http://testapi.qudiandang.com/v1/Order/comment
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 * <pre>
	 * 成功：
	 * 		{
	 * 		    "errno": 0,
	 * 		    "errmsg": "请求成功",
	 * 		    "result": 17
	 * 		}
	 *
	 * 失败：
	 * {
     * 	    "errno": 40015,
     * 	    "errmsg": "用户ID不能为空",
     * 	    "result": []
	 * }
	 * </pre>
	 */
	public function commentAction()
	{	
		$user_id = $this->user_id;
		$id = $this->_request->getPost('id');	
		$content = $this->_request->getPost('content');	
		
		if (!isset($user_id) || !is_numeric($user_id) || $user_id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40015);
        }	
		
		if (!isset($id) || !is_numeric($id) || $id <= 0) {			
            YDLib::output(ErrnoStatus::STATUS_40058);
        }		
		
		$orderInfo = OrderChildModel::getInfoByID($id);
		if (!$orderInfo) {
			YDLib::output(ErrnoStatus::STATUS_60503);
		}

		if ($orderInfo['user_id'] != $user_id) {
			YDLib::output(ErrnoStatus::STATUS_50017);
		}	

		if ($orderInfo['child_status'] != CommonBase::STATUS_SUCCESSFUL_TRADE) {
			YDLib::output(ErrnoStatus::STATUS_60516);
		}
		
		if ($orderInfo['is_comment'] != CommonBase::COMMENT_NONE) {
			YDLib::output(ErrnoStatus::STATUS_60516);
		}								
		
		$res = OrderModel::commentBYChildID($id,$orderInfo['order_id']);	
		if (!$res) {
			YDLib::output(ErrnoStatus::STATUS_60518);
		}  
		YDLib::output(ErrnoStatus::STATUS_60517);		
	}	
		
	
	
	
	/**
	 * 商城-购物车-获取优惠金额
	 *
	 * <pre>
	 *   POST参数
	 * 	   user_id ：		用户id			[必填参数]
	 * 	   delivery_type ：	快递方式 		[非填参数，0快递1门店自提，默认0]
	 * 	   address_id ：	收货地址id		[必填参数]
	 * 	   seckill_id： 		限购活动id		[非填参数,如果是限购的商品带上限购商品]
	 * 	   product：		商品信息数组	[必填参数] json字符串 [{"product_id":15,"num":1}]
	 * 		如下：
	 * 			[
	 * 				{
	 * 					"product_id":1, //商品ID   [必填参数]
	 * 					"num",1 		//商品数量  [必填参数]
	 * 				},
	 * 				{
	 * 					"product_id":2,
	 * 					"num",1
	 * 				}
	 * 			]
	 * 	   user_coupan_id：	会员优惠券id		[非填参数，用user_coupan_id，非coupan_id]
	 * </pre>
	 *
	 * <pre>
	 *    调用方式：
	 *        正式：   http://api.qudiandang.com/v1/Order/getMoney
	 *        测试：   http://testapi.qudiandang.com/v1/Order/getMoney
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 * <pre>
	 * 成功：
	 * 		{
	 * 		    "errno": 0,
	 * 		    "errmsg": "请求成功",
	 * 		    "result": {
	 * 				"order_original_amount": "220.00",//-------------------------------------------------订单原始金额
	 * 				"order_discount_amount": "20.00",//--------------------------------------------------订单优惠金额
	 *              "order_actual_amount": "20.00"//-----------------------------------------------------订单实际金额
	 * 			}
	 * 		}
	 *
	 * 失败：
	 * {
	 *     "errno": -1,
	 *     "errmsg": "系统错误"
	 * }
	 * </pre>
	 */
	public function getMoneyAction()
	{
	    $data = [];
	    $user_id = $this->user_id;
	    $delivery_type = $this->_request->getPost('delivery_type');
	    $address_id = $this->_request->getPost('address_id');
	    $seckill_id = $this->_request->getPost('seckill_id');
	    $user_coupan_id = $this->_request->getPost('user_coupan_id');
	    $product = $this->_request->getPost('product');
	
	    if (!isset($user_id) || !is_numeric($user_id) || $user_id <= 0) {
	        YDLib::output(ErrnoStatus::STATUS_40015);
	    }	
	
	    if (!isset($delivery_type) || !is_numeric($delivery_type) || $delivery_type != 1) {
	        $delivery_type = 0;
	    }
		
	    if ($delivery_type == 0) {
    	    if (!isset($address_id) || !is_numeric($address_id) || $address_id < 0) {
    	        $address_id = 0;
    	    }
	    }		
	
	    if (!isset($product) || empty($product)) {
	        YDLib::output(ErrnoStatus::STATUS_60273);
	    }
	    	
	    $product = json_decode($product,TRUE);
	    if (!is_array($product) || count($product) == 0) {
	        YDLib::output(ErrnoStatus::STATUS_60273);
	    }
	
	    foreach ($product as $key => $value) {
	        if (!isset($value['product_id']) || !is_numeric($value['product_id']) || $value['product_id'] <= 0) {
	            YDLib::output(ErrnoStatus::STATUS_40009);
	        }
	
	        if (!isset($value['num']) || !is_numeric($value['num']) || $value['num'] <= 0) {
	            YDLib::output(ErrnoStatus::STATUS_40013);
	        }
	    }
	
	    //格式化生成订货单的数据
	    $data['user_id'] = $user_id;
	    $data['address_id'] = $address_id;
	    $data['seckill_id'] = $seckill_id;
	    $data['user_coupan_id'] = $user_coupan_id;
	    $data['product'] = $product;
	    $data['delivery_type'] = $delivery_type;
	
	    $res = OrderModel::getMoney($data);
	     
	}

	/**
	 * 商城-拼团-获取优惠金额
	 *
	 * <pre>
	 *   POST参数
	 * 	   user_id ：		用户id			[必填参数]
	 * 	   delivery_type ：	快递方式 		[非填参数，0快递1门店自提，默认0]
	 * 	   address_id ：	收货地址id		[必填参数]
	 * 	   id ：			拼团商品主键ID	[必填参数]
	 * 	   num ：			商品数量		[必填参数]
	 * </pre>
	 *
	 * <pre>
	 *    调用方式：
	 *        正式：   http://api.qudiandang.com/v1/Order/getGroupMoney
	 *        测试：   http://testapi.qudiandang.com/v1/Order/getGroupMoney
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 * <pre>
	 * 成功：
	 * 		{
	 * 		    "errno": 0,
	 * 		    "errmsg": "请求成功",
	 * 		    "result": {
	 * 				"order_original_amount": "220.00",//-------------------------------------------------订单原始金额
	 * 				"order_discount_amount": "20.00",//--------------------------------------------------订单优惠金额
	 *              "order_actual_amount": "20.00"//-----------------------------------------------------订单实际金额
	 * 			}
	 * 		}
	 *
	 * 失败：
	 * {
	 *     "errno": -1,
	 *     "errmsg": "系统错误"
	 * }
	 * </pre>
	 */
	public function getGroupMoneyAction()
	{
	    $data = [];
	    $user_id = $this->user_id;
	    $delivery_type = $this->_request->getPost('delivery_type');
	    $address_id = $this->_request->getPost('address_id');
	    $id = $this->_request->getPost('id');
	    $num = $this->_request->getPost('num');
	
	    if (!isset($user_id) || !is_numeric($user_id) || $user_id <= 0) {
	        YDLib::output(ErrnoStatus::STATUS_40015);
	    }	
	
	    if (!isset($delivery_type) || !is_numeric($delivery_type) || $delivery_type != 1) {
	        $delivery_type = 0;
	    }
		
	    if ($delivery_type == 0) {
    	    if (!isset($address_id) || !is_numeric($address_id) || $address_id < 0) {
    	        $address_id = 0;
    	    }
	    }		

	    if (!isset($id) || !is_numeric($id) || $id <= 0) {
	        YDLib::output(ErrnoStatus::STATUS_40103);
	    }
		
	    if (!isset($num) || !is_numeric($num) || $num <= 0) {
	        YDLib::output(ErrnoStatus::STATUS_40020);
	    }
		
	    $detail = SeckillProductModel::getInfoByID($id);
		if (!$detail) {
			YDLib::output(ErrnoStatus::STATUS_60570);
		}		
	
	    //格式化生成订货单的数据
	    $data['user_id'] = $user_id;
		$data['delivery_type'] = $delivery_type;
	    $data['address_id'] = $address_id;
		$data['id'] = $id;		
		$data['num'] = $num;		
	    $data['seckill'] = $detail;		
		
	    $res = OrderModel::getGroupMoney($data);
	     
	}
	
	/*
	 * 打开保证金支付确定页面
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Order/addMargin
	 * 测试： http://testapi.qudiandang.com/v1/Order/addMargin
	 *
	 * </pre>
	 *
	 * id　： 【必填】活动关联ID
	 * margin： 【必填】保证金
	 * user_id ：【必填】用户ID
	 * address_id ：【必填】收货地址
	 * product_id ： 【必填】商品ID
	 * delivery_type ：【必填】支付状态
	 *
	 */
	public function addMarginAction() {
		
		
		$id = $this->_request->getPost ( 'id' );
		$user_id = $this->user_id;
		$address_id = $this->_request->getPost ( 'address_id' );
		$product_id = $this->_request->getPost ( 'product_id' );
		$margin = $this->_request->getPost ( 'margin' );
		$delivery_type = $this->_request->getPost ( 'delivery_type' );
		
		if (! isset ( $user_id ) || ! is_numeric ( $user_id ) || $user_id <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		  $info = SeckillModel::getInfoByID($id);
		  
		if($info['endtime'] >= date ( 'Y-m-d H:i:s' ) ){
		
		  
		if (! isset ( $product_id ) || ! is_numeric ( $product_id ) || $product_id <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40009 );
		}
		
		if (! isset ( $delivery_type ) || ! is_numeric ( $delivery_type ) || $delivery_type != 1) {
			$delivery_type = 0;
		}

        if ($delivery_type == 0) {
            if (! isset ( $address_id ) || ! is_numeric ( $address_id ) || $address_id <= 0) {
                YDLib::output ( ErrnoStatus::STATUS_40098 );
            }
        }
		
		if (! isset ( $margin ) || ! is_numeric ( $margin ) || $margin <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_60589 );
		}
		
		// 格式化生成订货单的数据
		$resData ['id'] = $id;
		$resData ['user_id'] = $user_id;
		$resData ['address_id'] = $address_id;
		$resData ['product_id'] = $product_id;
		$resData ['margin'] = $margin;
		$resData ['delivery_type'] = $delivery_type;
		$resData ['payurl'] = 'https://' . $_SERVER ['HTTP_HOST'] . '/v1/payment/marginpay?identif=' . SUPPLIER_DOMAIN . '&margin=' . $margin; // 跳转支付页面
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $resData );
	}
}
	
	
	
	
	/*
	 * 回调创建保证金支付
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Order/createMargin
	 * 测试： http://testapi.qudiandang.com/v1/Order/createMargin
	 *
	 * </pre>
	 * 
	 * 
	 *  参数数组 $info[]
	 *  			参见marginpay.phtml   里面数据
	 *  	noteData['info[seckill_id]'] = <?php echo $info['id'];?>;
  	 *		noteData['info[margin]'] = <?php echo $info['margin'];?>;
  	 *		noteData['info[user_id]'] = <?php echo $info['user_id'];?>;
  	 *		noteData['info[address_id]'] = <?php echo $info['address_id'];?>;
  	 *		noteData['info[product_id]'] = <?php echo $info['product_id'];?>;
  	 *		noteData['info[delivery_type]'] = <?php echo $info['delivery_type'];?>;
	 *
	 */
	public function createMarginAction() {
	
		if (! empty ( $_REQUEST ['format'] ) && $_REQUEST ['format'] == "add") {
			if (! empty ( $_REQUEST ['info'] )) {
				$info ['info'] = $_REQUEST ['info'];
			}
			
			
			$OrderInfo = SeckillOrderModel::getPersonalOrderByID($info ['info']);
			
			if($OrderInfo){
				
				$data = [ ];
				$data ['code'] = '200';
				$data ['id'] = $OrderInfo['id'];
				echo json_encode ( $data );
				exit ();
				
				
			}else{
			
			$res = OrderModel::addMargin ( $info ['info'] );
			$data = [ ];
			$data ['code'] = '200';
			$data ['id'] = $res;
			if ($res == true) {
				echo json_encode ( $data );
				exit ();
				}
				
			}
	  	}
	}	
}
