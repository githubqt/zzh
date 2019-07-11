<?php
use Brand\BrandModel;
use Product\ProductModel;
use Custom\YDLib;
use Indexdata\IndexdataModel;
use Seckill\SeckillModel;
use Common\CommonBase;
use Supplier\SupplierModel;

/**
 * 首页
 * 
 * @version v0.01
 * @author zhaoyu
 *         @time 2018-05-08
 */
class HomeController extends BaseController {
	
	/**
	 * 首页-获取公司信息接口
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Home/supplier
	 * 测试： http://testapi.qudiandang.com/v1/Home/supplier
	 * </pre>
	 *
	 * <pre>
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": 0,
	 *         "errmsg": "请求成功",
	 *         "result": [
	 *         {
	 *         "id": 10011,//---公司ID
	 *         "domain": "eeee",//---公司domain
	 *         "company": "tt",//---公司名称
	 *         "company_introduction": null,//---公司介绍
	 *         "business_introduction": null,//--业务介绍
	 *         "company_honors": null,//--公司荣誉
	 *         "phone": 010-2322666,//--公司电话
	 *         "mobile": 1888888888,//--联系电话
	 *         }
	 *         ]
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "60002",
	 *         "errmsg": "用户已存在"
	 *         }
	 *         </pre>
	 */
	public function supplierAction() {
		$mem = YDLib::getMem ( 'memcache' );
		$data = $mem->get ( 'supplier_' . SUPPLIER_DOMAIN );
		if (! $data) {
			YDLib::output ( ErrnoStatus::STATUS_10002 );
		}
		
		$res = [ ];
		$res ['id'] = $data ['id'];
		$res ['company'] = $data ['shop_name'];
		$res ['company_introduction'] = $data ['company_introduction'];
		$res ['business_introduction'] = $data ['business_introduction'];
		$res ['company_honors'] = $data ['company_honors'];
		$res ['shop_instructions'] = $data ['shop_instructions'];
		$res ['explain'] = $data ['explain'];
		$res ['phone'] = $data ['phone'];
		$res ['mobile'] = $data ['mobile'];
		$res ['customer_tel'] = explode(',', $data ['customer_tel']);
		
		$browse_num = ProductModel::GetAllSum();
		$res['browse_num'] = intval($browse_num['browse_num']);
		$res['collect_num'] = intval($browse_num['collect_num']);
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $res );
	}
	
	/**
	 * 首页-获取轮播图接口
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Home/Indexdata
	 * 测试： http://testapi.qudiandang.com/v1/Home/Indexdata
	 * </pre>
	 *
	 * <pre>
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": 0,
	 *         "errmsg": "请求成功"
	 *         "result": [
	 *         {
	 *         "id": 2, 轮播图id
	 *         "title_name": "测试", ------------轮播图名称
	 *         "data_type": 2, -----------------类型 1链接单一商品; 2商品列表页; 3链接地址;
	 *         "details": 150, -----------------类型为一时为商品id，为二时为三级分类id， 为3时为链接地址
	 *         "img_path": "http://file.qudiandang.com//upload/Indexdata/2018/05/21/fc1873d1a43ead15b0b4091c4f125157_846.jpg", 图片地址
	 *         "description": "打滴滴服服服" -----说明（可能用不到）
	 *         },
	 *         {
	 *         "id": 3,
	 *         "title_name": "利群",
	 *         "data_type": 3,
	 *         "details": "http://sp.qudiandang.com/index.php?m=Marketing&c=Indexdata&a=add",
	 *         "img_path": "http://file.qudiandang.com//upload/Indexdata/2018/05/19/fc1873d1a43ead15b0b4091c4f125157_712.jpg",
	 *         "description": ""
	 *         }
	 *         ]
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "60558",
	 *         "errmsg": "没有找到轮播图信息"
	 *         }
	 *         </pre>
	 */
	public function IndexdataAction() {
		$data = IndexdataModel::getList ();
		if ($data) {
			foreach ( $data as &$val ) {
				if (! empty ( $val ['img_path'] )) {
					$val ['img_path'] = HOST_FILE . CommonBase::imgSize ( $val ['img_path'], 3 );
				} else {
					$val ['img_path'] = HOST_STATIC . 'common/images/common.png';
				}
				
				//转换url
				if ($val['data_type'] == '3') {
				    $array = explode('.',$val['details']);
				    
				    if (count($array) == '4') {
				        if ($array['1'] == 'm') {
				            $one = explode('//',$array['0']);
				            $com = explode('/',$array['3']);
				            	
				            $com['0'] = $com['0'].'/'.$one['1'];
				            $new_com = implode('/',$com);
				            	
				            $val['details'] = 'https://shopm.'.$array['2'].'.'.$new_com;
				        }
				    }
				}
				
			}
			
			
		} else {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
		}
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
	
	/**
	 * 首页-获取限时抢购接口
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Home/newSeckill
	 * 测试： http://testapi.qudiandang.com/v1/Home/newSeckill
	 * </pre>
	 *
	 * <pre>
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": 0,
	 *         "errmsg": "请求成功"
	 *         "result": {
	 *         "id": 11,------------------------------------------------------------------------------秒杀活动id
	 *         "product_id": 15, ---------------------------------------------------------------------商品id
	 *         "product_name": "爱马仕（HERMES） 香水女士男士淡香水持久香氛 大地男士淡香水50ml",----------商品名称
	 *         "starttime": "2018-05-22 17:16:40",----------------------------------------------------秒杀开始时间
	 *         "endtime": "2018-06-02 17:16:45",------------------------------------------------------秒杀结束时间
	 *         "is_restrictions": 1,------------------------------------------------------------------是否限购 1：不限 2：限购
	 *         "restrictions_num": 0,-----------------------------------------------------------------限购个数
	 *         "order_del": 5,------------------------------------------------------------------------未付款订单几分钟后失效
	 *         "stock": 11,---------------------------------------------------------------------------当前库存
	 *         "market_price": 5000,------------------------------------------------------------------公价
	 *         "sale_price": 3000,--------------------------------------------------------------------销售价
	 *         "seckill_price": 12--------------------------------------------------------------------秒杀价格
	 *         "logo_url": '/upload/Indexdata/2018/05/24/acae896ae1d150f9e7f23d6c03c5ac07_342.jpg'----秒杀图片
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "60559",
	 *         "errmsg": "没有找到最新抢购信息"
	 *         }
	 *         </pre>
	 */
	public function newSeckillAction() {
		$data = SeckillModel::getLastOne ();
		if ($data) {
			/*
			 * foreach ($data as &$val) {
			 * if (!empty($val['img_path'])) {
			 * $val['img_path'] = HOST_FILE.$val['img_path'];
			 * } else {
			 * $val['img_path'] = HOST_STATIC.'common/images/common.png';
			 * }
			 * }
			 */
			$data ['logo_url'] = HOST_STATIC . 'common/images/seckill.jpg';
		} else {
			YDLib::output ( ErrnoStatus::STATUS_60559 );
		}
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
}
