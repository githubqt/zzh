<?php
use Product\ProductModel;
use Product\ProductChannelModel;
use Custom\YDLib;
use Coupan\CoupanModel;
use Seckill\SeckillModel;
use Seckill\SeckillProductModel;
use User\UserConcernModel;
use Core\Qzcode;
use Supplier\SupplierModel;
use Multipoint\MultipointModel;

/**
 * *
 * 商品信息
 *
 * @version v0.01
 * @author huangxianguo
 *         @time 2018-05-04 dsmn
 */
class ProductController extends BaseController {
	const SORT_FIRLD = array (
			'now_at',
			'sale_price',
			'sale_num' 
	);
	const ORDER_TYPE = array (
			'DESC',
			'ASC' 
	);
	
	/**
	 * 获取商品列表接口
	 * 获取最新上架展示信息接口：时间降序
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Product/list
	 * 测试： http://testapi.qudiandang.com/v1/Product/list
	 * </pre>
	 *
	 * <pre>
	 * 参数：
	 * page: 页码 非必填 【空：1】
	 * rows: 条数 非必填 【空：10】
	 * name: 商品名称 非必填 【搜索项】
	 * brand_id： 品牌ID 非必填 【搜索项，多个用逗号隔开】
	 * category_id： 分类ID 非必填 【搜索项，多个用逗号隔开】
	 * order: 排序方式 非必填 【空：DESC，DESC：降序，ASC：升序】
	 * sort: 排序字段 非必填 【空：id，now_at：上架时间，sale_price：价格，sale_num：销量】
	 * coupan_id ： 优惠券id 非必填 【查询优惠券可用的商品】
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         "page": 1,//------------------------------------------------------------------------------当前页码
	 *         "total": "9",//---------------------------------------------------------------------------总条数
	 *         "list": [
	 *         {
	 *         "id": "16",//---------------------------------------------------------------------商品ID
	 *         "name": "iPhone Two X",//---------------------------------------------------------商品名称
	 *         "self_code": "32423424234",//-----------------------------------------------------商品编码
	 *         "market_price": "210000.00",//----------------------------------------------------公价
	 *         "sale_price": "100000.00",//------------------------------------------------------销售价
	 *         "category_id": "36",//------------------------------------------------------------分类ID
	 *         "category_name": "奶粉|特殊配方奶粉|二段",//--------------------------------------分类名称
	 *         "brand_id": "1",//----------------------------------------------------------------品牌ID
	 *         "brand_name": "品牌1",//----------------------------------------------------------品牌名称
	 *         "logo_url": "http://file.qudiandang.com//upload/product/9e85_645.jpg",//----------商品主图
	 *         "stock": "18",//------------------------------------------------------------------现有库存
	 *         "now_at": "2018-05-16 07:24:57"//-------------------------------------------------上架时间
	 *         },
	 *         {
	 *         "id": "15",
	 *         "name": "【520礼物】爱马仕（HERMES） 香水女士男士淡香水持久香氛 大地男士淡香水50ml",
	 *         "self_code": "1000022",
	 *         "market_price": "5000.00",
	 *         "sale_price": "3000.00",
	 *         "category_id": "41",
	 *         "category_name": "奶粉|孕妇奶粉|孕妇羊奶粉",
	 *         "brand_id": "20",
	 *         "brand_name": "爱马仕",
	 *         "logo_url": "http://file.qudiandang.com//upload/product/2018/05/15/16b4485eb0763a327804ff95c249df0a_497.jpg",
	 *         "stock": "8",
	 *         "now_at": null
	 *         }
	 *         ]
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": 40015,
	 *         "errmsg": "用户ID不能为空",
	 *         "result": []
	 *         }
	 *         </pre>
	 */
	public function listAction() {
		$page = $this->_request->getPost ( 'page' );
		$page = ! empty ( $page ) ? intval ( $page ) : 1;
		$page = $page > 0 ? $page : 1;
		
		$rows = $this->_request->getPost ( 'rows' );
		$rows = ! empty ( $rows ) ? intval ( $rows ) : 10;
		$rows = $rows > 0 ? $rows : 10;
		
		$brand_id = $this->_request->getPost ( 'brand_id' );
		$brand_id = ! empty ( $brand_id ) ? trim ( $brand_id ) : '';
		
		$category_id = $this->_request->getPost ( 'category_id' );
		$category_id = ! empty ( $category_id ) ? trim ( $category_id ) : '';
		
		$multi_point_id = $this->_request->getPost ( 'multi_point_id' );
		$multi_point_id = ! empty ( $multi_point_id ) ? trim ( $multi_point_id ) : '';
		
		$name = $this->_request->getPost ( 'name' );
		$name = ! empty ( $name ) ? trim ( $name ) : '';
		
		$longitude = $this->_request->getPost ( 'longitude' );
		
		$latitude = $this->_request->getPost ( 'latitude' );
		
		$coupanData = $this->_request->getPost ( 'data' );
		
		// 校验字符串
		if (! empty ( $name )) {
			YDLib::validData ( $name );
		}
		
		// 校验字符串
		if (! empty ( $brand_id )) {
			YDLib::validData ( $brand_id );
		}
		
		// 校验字符串
		if (! empty ( $category_id )) {
			YDLib::validData ( $category_id );
		}
		
		$sort = $this->_request->getPost ( 'sort' );
		$sort = in_array ( $sort, self::SORT_FIRLD ) ? $sort : 'a.id';
		
		$order = $this->_request->getPost ( 'order' );
		$order = in_array ( $order, self::ORDER_TYPE ) ? $order : 'DESC';
		
		$info = array (
				'brand_id' => $brand_id,
				'category_id' => $category_id,
				'multi_point_id' => $multi_point_id,
				'name' => $name,
				'sort' => $sort,
				'order' => $order,
				'type' => '1',
				'data' => $coupanData 
		);
		
		$coupan_id = $this->_request->getPost ( 'coupan_id' );
		
		if ($coupan_id) {
			$coupanInfo = CoupanModel::getInfoByID ( $coupan_id );
			if (! $coupanInfo) {
				YDLib::output ( ErrnoStatus::STATUS_60519 );
			}
			if ($coupanInfo ['use_type'] == 2) {
				$info ['ids'] = $coupanInfo ['use_product_ids'];
			}
		}
		
		$GoldValue = ProductModel::getGoldValue ( $page, $rows );
		$list = ProductModel::getList ( $info, $page, $rows );
		
		foreach ( $list ['list'] as $key => $val ) {
			if ($val ['multi_point_id']) {
				$list ['list'] [$key] ['multi_point_data'] = MultipointModel::getInfoByID ( $val ['multi_point_id'], $longitude, $latitude );
			}
		}
		
		if ($list == false) {
			$data ['page'] = $page;
			$data ['total'] = 0;
			$data ['list'] = [ ];
		} else {
			$data ['page'] = $page;
			$data ['total'] = $list ['total'];
			$data ['list'] = $list ['list'];
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data, FALSE );
	}
	
	/**
	 * 获取人气推荐商品接口
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Product/recommended
	 * 测试： http://testapi.qudiandang.com/v1/Product/recommended
	 * </pre>
	 *
	 * <pre>
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功",
	 *         "result": [
	 *         {
	 *         "id": "11",
	 *         "name": "商品8",
	 *         "self_code": "10000008",
	 *         "market_price": "1000000.00",
	 *         "sale_price": "10000.00",
	 *         "category_id": "26",
	 *         "category_name": "||",
	 *         "brand_id": "9",
	 *         "brand_name": "品牌9",
	 *         "logo_url": "http://static.qudiandang.com/common/images/common.png",
	 *         "stock": "112",
	 *         "now_at": "2018-05-15 15:30:18",
	 *         "sale_num": "0"
	 *         },
	 *         {
	 *         "id": "12",
	 *         "name": "商品9",
	 *         "self_code": "10000009",
	 *         "market_price": "1000000.00",
	 *         "sale_price": "10000.00",
	 *         "category_id": "27",
	 *         "category_name": "||",
	 *         "brand_id": "10",
	 *         "brand_name": "品牌10",
	 *         "logo_url": "http://static.qudiandang.com/common/images/common.png",
	 *         "stock": "110",
	 *         "now_at": "2018-05-15 15:30:21",
	 *         "sale_num": "0"
	 *         },
	 *         {
	 *         "id": "13",
	 *         "name": "商品10",
	 *         "self_code": "10000010",
	 *         "market_price": "1000000.00",
	 *         "sale_price": "10000.00",
	 *         "category_id": "27",
	 *         "category_name": "||",
	 *         "brand_id": "10",
	 *         "brand_name": "品牌10",
	 *         "logo_url": "http://file.qudiandang.com//upload/product/2018/05/09/1c30b1e2933ac94f8a246c3c0fc13d3d_760.jpg",
	 *         "stock": "110",
	 *         "now_at": "2018-05-15 15:30:23",
	 *         "sale_num": "0"
	 *         },
	 *         {
	 *         "id": "14",
	 *         "name": "劳力士手表，金边，镶钻",
	 *         "self_code": "10000011",
	 *         "market_price": "1000000.00",
	 *         "sale_price": "10000.00",
	 *         "category_id": "789",
	 *         "category_name": "||",
	 *         "brand_id": "3",
	 *         "brand_name": "品牌3",
	 *         "logo_url": "http://file.qudiandang.com//upload/product/2018/05/15/f788e9b52dd4cfb43305ae9ae95157e0_163.jpg",
	 *         "stock": "17",
	 *         "now_at": "2018-05-15 15:30:26",
	 *         "sale_num": "0"
	 *         },
	 *         {
	 *         "id": "15",
	 *         "name": "【520礼物】爱马仕（HERMES） 香水女士男士淡香水持久香氛 大地男士淡香水50ml",
	 *         "self_code": "1000022",
	 *         "market_price": "5000.00",
	 *         "sale_price": "3000.00",
	 *         "category_id": "41",
	 *         "category_name": "||",
	 *         "brand_id": "20",
	 *         "brand_name": "爱马仕",
	 *         "logo_url": "http://file.qudiandang.com//upload/product/2018/05/15/16b4485eb0763a327804ff95c249df0a_497.jpg",
	 *         "stock": "20",
	 *         "now_at": null,
	 *         "sale_num": "0"
	 *         }
	 *         ]
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": 40015,
	 *         "errmsg": "用户ID不能为空",
	 *         "result": []
	 *         }
	 *         </pre>
	 */
	public function recommendedAction() {
		$info = array (
				'order' => 'DESC',
				'type' => '1' 
		);
		
		$list = ProductModel::getList ( $info, 1, 5 );
		
		if ($list == false) {
			$data = [ ];
		} else {
			$data = $list ['list'];
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data, FALSE );
	}
	
	/**
	 * 获取商品详情接口
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Product/detail
	 * 测试： http://testapi.qudiandang.com/v1/Product/detail
	 * </pre>
	 *
	 * <pre>
	 * 参数：
	 * id: 商品ID 必填
	 * u_id 用户id 非必填，登陆后上传
	 * seckill_id 活动id 查询活动信息
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         "id": "15",//-----------------------------------------------------------------------------商品ID
	 *         "name": "【520礼物】爱马仕（HERMES） 香水女士男士淡香水持久香氛 大地男士淡香水50ml",//-----商品名称
	 *         "self_code": "1000022",//-----------------------------------------------------------------商品编码
	 *         "market_price": "5000.00",//--------------------------------------------------------------公价
	 *         "sale_price": "3000.00",//----------------------------------------------------------------销售价
	 *         "category_id": "41",//--------------------------------------------------------------------分类ID
	 *         "category_name": "奶粉|孕妇奶粉|孕妇羊奶粉",//--------------------------------------------分类名称
	 *         "brand_id": "20",//-----------------------------------------------------------------------品牌ID
	 *         "introduction": "\t\t\t\t\t\t\t \t\t\t\t\t\t\t",//-------------------------------------商品详情
	 *         "logo_url": "http://file.qudiandang.com//upload/product/497.jpg",//-----------------------商品主图
	 *         "stock": "8",//---------------------------------------------------------------------------现有库存
	 *         "now_at": null,//-------------------------------------------------------------------------上架时间
	 *         "brand_name": "爱马仕",//-----------------------------------------------------------------商品品牌
	 *         "is_like": "0",//-------------------------------------------------------------------------是否收藏：0未收藏，1已收藏
	 *         "imglist": [//----------------------------------------------------------------------------商品附图信息
	 *         {
	 *         "id": "106",
	 *         "obj_id": "15",
	 *         "type": "product",
	 *         "img_url": "http://file.qudiandang.com//upload/product/858.jpg",//----------------商品附图
	 *         "img_type": "jpg"
	 *         },
	 *         {
	 *         "id": "107",
	 *         "obj_id": "15",
	 *         "type": "product",
	 *         "img_url": "http://file.qudiandang.com//upload/product/2018/05/15/66e95d6b9a54fd815c0eabff283164ae_999.jpg",
	 *         "img_type": "jpg"
	 *         }
	 *         ],
	 *         "attribute": {//--------------------------------------------------------------------------商品属性
	 *         "6": {
	 *         "attribute_id": "6",
	 *         "attribute_name": "尺寸",//-------------------------------------------------------属性名称
	 *         "input_type": "1",
	 *         "attribute_value_id": "30",
	 *         "attribute_value_name": "2cm"//---------------------------------------------------属性值
	 *         },
	 *         "7": {
	 *         "attribute_id": "7",
	 *         "attribute_name": "使用人群",
	 *         "input_type": "2",
	 *         "attribute_value_id": "32 33 34 ",
	 *         "attribute_value_name": "风情少妇 天仙美女 家庭辣妈 "
	 *         },
	 *         "8": {
	 *         "attribute_id": "8",
	 *         "attribute_name": "产地",
	 *         "input_type": "3",
	 *         "attribute_value_id": null,
	 *         "attribute_value_name": "北京"
	 *         }
	 *         },
	 *         "seckill": { //---------------------------------------------------秒杀活动信息
	 *         "id": "1",//--------------------------------------------------活动id
	 *         "product_id": "16",//-----------------------------------------商品id
	 *         "starttime": "2018-05-23 10:50:05",//-------------------------活动开始时间
	 *         "endtime": "2018-05-24 10:50:09",//---------------------------活动结束时间
	 *         "is_restrictions": "2",//-------------------------------------是否限购 1：不限 2：限购
	 *         "restrictions_num": "1",//------------------------------------限购个数
	 *         "seckill_price": "5499.00",//---------------------------------秒杀价格
	 *         "order_del": "5"//--------------------------------------------未付款订单几分钟后失效
	 *         }
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": 60025,
	 *         "errmsg": "商品不存在",
	 *         "result": []
	 *         }
	 *         </pre>
	 */
	public function detailAction() {
		$id = $this->_request->getPost ( 'id' );
		$user_id = $this->_request->getPost ( 'u_id' );
		$id = ! empty ( $id ) ? intval ( $id ) : '';
		
		$seckill_id = $this->_request->getPost ( 'seckill_id' );
		$seckill_id = ! empty ( $seckill_id ) ? intval ( $seckill_id ) : '';
		
		if (empty ( $id )) {
			YDLib::output ( ErrnoStatus::STATUS_40009 );
		}
		
		$detail = ProductModel::getInfoByIDNew ( $id );
		
		if (! $detail) {
			$detail = ProductModel::getSingleInfoByID ( $id );
			$detail ['imglist'] [0] ['img_url'] = $detail ['imglist'] ? $detail ['imglist'] : $detail ['logo_url'];
			if (! $detail) {
				YDLib::output ( ErrnoStatus::STATUS_60025 );
			}
		}
		
		// 更新商品浏览量
		$productData ['browse_num'] = intval ( $detail ['browse_num'] + 1 );
		ProductModel::updateByID ( $productData, $detail ['id'] );
		
		$productInfo = ProductChannelModel::getInfoByID ( $id );
		
		if ($productInfo) {
			// 更新渠道商品浏览量
			$productClData ['browse_num'] = intval ( $productInfo ['browse_num'] + 1 );
			ProductChannelModel::updateByID ( $productClData, $id );
		}
		
		$detail ['seckill_type'] = '2';
		// 查询活动信息
		$detail ['seckill'] = [ ];
		if (! $seckill_id) { // 限时秒杀
			$seckill = SeckillModel::getInfoByProductId ( $id );
			if ($seckill) {
				$detail ['seckill'] = $seckill;
				$product = SeckillModel::getInfoByID ( $seckill ['seckill_id'] );
				// 更新围观人数
				$data ['onlookers_num'] = intval ( $product ['onlookers_num'] + 1 );
				$onlookers_num = SeckillModel::updateByID ( $data, $product ['id'] );
				$detail ['seckill_type'] = '1';
			}
		} else { // 其他活动
			$seckill = SeckillProductModel::getSeckillInfo ( $seckill_id, $id );
			if ($seckill) {
				$detail ['seckill'] = $seckill;
			}
		}
		
		// 查询是否收藏
		$detail ['is_like'] = '0';
		if ($user_id) {
			if (UserConcernModel::getInfoByID ( $user_id, $id )) {
				$detail ['is_like'] = '1';
			}
		}
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $detail, FALSE );
	}
	
	/**
	 * 获取限时抢购列表接口
	 *
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Product/seckillList
	 * 测试： http://testapi.qudiandang.com/v1/Product/seckillList
	 * </pre>
	 *
	 * <pre>
	 * 参数：
	 * page: 页码 非必填 【空：1】
	 * rows: 条数 非必填 【空：10】
	 * order: 排序方式 非必填 【空：DESC，DESC：降序，ASC：升序】
	 * sort: 排序字段 非必填 【空：id】
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         "page": 1,
	 *         "total": "1",
	 *         "list": [
	 *         {
	 *         "id": "1",//----------------------------------------限购id
	 *         "starttime": "2018-05-23 10:50:05",//---------------开始时间
	 *         "endtime": "2018-05-24 10:50:09",//-----------------结束时间
	 *         "seckill_price": "5499.00",//-----------------------秒杀价
	 *         "product_id": "16",//-------------------------------商品id
	 *         "product_name": "iPhone Two X",//-------------------商品名称
	 *         "self_code": "32423424234",//-----------------------商品编码
	 *         "market_price": "210000.00",//----------------------公价
	 *         "sale_price": "100000.00",//------------------------原销售价
	 *         "logo_url": "http://file.qudiandang.com//upload/product/2018/05/16/832fa1afa996670560ab47811ba19e85_645.jpg",
	 *         "stock": "7",//-------------------------------------商品库存数
	 *         "status_txt": "已结束"//----------------------------状态：已结束，未开始，抢购中，抢购完
	 *         }
	 *         ...
	 *         ]
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": -1,
	 *         "errmsg": "系统繁忙",
	 *         "result": []
	 *         }
	 *         </pre>
	 */
	public function seckillListAction() {
		$page = $this->_request->getPost ( 'page' );
		$page = ! empty ( $page ) ? intval ( $page ) : 1;
		$page = $page > 0 ? $page : 1;
		
		$rows = $this->_request->getPost ( 'rows' );
		$rows = ! empty ( $rows ) ? intval ( $rows ) : 10;
		$rows = $rows > 0 ? $rows : 10;
		
		$sort = $this->_request->getPost ( 'sort' );
		$sort = in_array ( $sort, self::SORT_FIRLD ) ? $sort : 'id';
		
		$order = $this->_request->getPost ( 'order' );
		
		$status = $this->_request->getPost ( 'status' );
		
		$info = array (
				'sort' => $sort,
				'order' => $order,
				'type' => '1',
				'status' => $status 
		);
		
		$list = SeckillModel::getList ( $info, $page, $rows );
		
		if ($list == false) {
			$data ['page'] = $page;
			$data ['total'] = 0;
			$data ['list'] = [ ];
		} else {
			$data ['page'] = $page;
			$data ['total'] = $list ['total'];
			$data ['list'] = $list ['list'];
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data, FALSE );
	}
	
	/**
	 * 商品推广接口
	 *
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Product/promote
	 * 测试： http://testapi.qudiandang.com/v1/Product/promote
	 * </pre>
	 *
	 * <pre>
	 * 参数：
	 * id: 商品ID
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 */
	public function promoteAction() {
		$id = $this->_request->get ( 'id' );
		$detail = ProductModel::getInfoByIDNew($id);
		
		$suppplier_detail = SupplierModel::getInfoByID ( $detail ['supplier_id'] );
		$url = sprintf ( M_URL, $suppplier_detail ['domain'] ) . 'details?id=' . $id;
		
		$Qzcode = new Qzcode ();
        $detail['shop_name'] = $suppplier_detail['shop_name'];
		$url = $Qzcode->shareProduct ( $url, $detail );
		if ($url) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $url, FALSE );
		}
	}
	
	/**
	 * 商品推广图下载接口
	 *
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Product/copy
	 * 测试： http://testapi.qudiandang.com/v1/Product/copy
	 * </pre>
	 *
	 * <pre>
	 * 参数：
	 * id: 商品ID
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 */
	public function copyAction() {
		$url = $this->_request->get ( 'url' );
		
		$url = self::down_images ( $url );
		
		if ($url) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $url, FALSE );
		}
	}
	
	/**
	 * 图片下载到本地
	 */
	function down_images($url) {
		$header = array (
				"Connection: Keep-Alive",
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
				"Pragma: no-cache",
				"Accept-Language: zh-Hans-CN,zh-Hans;q=0.8,en-US;q=0.5,en;q=0.3",
				"User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:29.0) Gecko/20100101 Firefox/29.0" 
		);
		
		$ch = curl_init ();
		
		curl_setopt ( $ch, CURLOPT_URL, $url );
		
		// curl_setopt($ch, CURLOPT_HEADER, $v);
		
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
		
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
		
		curl_setopt ( $ch, CURLOPT_ENCODING, 'gzip,deflate' );
		
		$content = curl_exec ( $ch );
		
		$curlinfo = curl_getinfo ( $ch );
		
		// print_r($curlinfo);
		
		// 关闭连接
		
		curl_close ( $ch );
		
		if ($curlinfo ['http_code'] == 200) {
			
			if ($curlinfo ['content_type'] == 'image/jpeg') {
				
				$exf = '.jpg';
			} else if ($curlinfo ['content_type'] == 'image/png') {
				
				$exf = '.png';
			} else if ($curlinfo ['content_type'] == 'image/gif') {
				
				$exf = '.gif';
			}
			
			// 存放图片的路径及图片名称 *****这里注意 你的文件夹是否有创建文件的权限 chomd -R 777 mywenjian
			
			$filename = date ( "YmdHis" ) . uniqid () . $exf; // 这里默认是当前文件夹，可以加路径的 可以改为$filepath = '../'.$filename
			$filepath = 'images/' . $filename;
			var_dump ( $content );
			$res = file_put_contents ( $filepath, $content );
			// $res = file_put_contents($filename, $content);//同样这里就可以改为$res = file_put_contents($filepath, $content);
			// echo $filepath;
			return $res;
		}
	}
	public function likeAction() {
		$page = $this->_request->getRequest ( 'page' );
		$page = ! empty ( $page ) ? intval ( $page ) : 1;
		$page = $page > 0 ? $page : 1;
		
		$rows = $this->_request->getRequest ( 'rows' );
		$rows = ! empty ( $rows ) ? intval ( $rows ) : 10;
		$rows = $rows > 0 ? $rows : 10;
		
		$brand_id = $this->_request->getPost ( 'brand_id' );
		$brand_id = ! empty ( $brand_id ) ? trim ( $brand_id ) : '';
		
		$category_id = $this->_request->getPost ( 'category_id' );
		$category_id = ! empty ( $category_id ) ? trim ( $category_id ) : '';
		
		$name = $this->_request->getPost ( 'name' );
		$name = ! empty ( $name ) ? trim ( $name ) : '';
		
		// 校验字符串
		if (! empty ( $name )) {
			YDLib::validData ( $name );
		}
		
		// 校验字符串
		if (! empty ( $brand_id )) {
			YDLib::validData ( $brand_id );
		}
		
		// 校验字符串
		if (! empty ( $category_id )) {
			YDLib::validData ( $category_id );
		}
		
		// $sort = $this->_request->getPost ( 'sort' );
		// $sort = in_array ( $sort, self::SORT_FIRLD ) ? $sort : 'id';
		//
		// $order = $this->_request->getPost ( 'order' );
		// $order = in_array ( $order, self::ORDER_TYPE ) ? $order : 'DESC';
		
		$info = array (
				'brand_id' => $brand_id,
				'category_id' => $category_id,
				'name' => $name,
				'guess_like' => 1,
				
				// 'sort' => $sort,
				// 'order' => $order,
				'type' => '1' 
		);
		
		$coupan_id = $this->_request->getPost ( 'coupan_id' );
		
		if ($coupan_id) {
			$coupanInfo = CoupanModel::getInfoByID ( $coupan_id );
			if (! $coupanInfo) {
				YDLib::output ( ErrnoStatus::STATUS_60519 );
			}
			if ($coupanInfo ['use_type'] == 2) {
				$info ['ids'] = $coupanInfo ['use_product_ids'];
			}
		}
		
		ProductModel::getGoldValue ( $page, $rows );
		$list = ProductModel::getList ( $info, $page, $rows );
		
		if ($list == false) {
			$data ['page'] = $page;
			$data ['total'] = 0;
			$data ['list'] = [ ];
		} else {
			$data ['page'] = $page;
			$data ['total'] = $list ['total'];
			$data ['list'] = $list ['list'];
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data, FALSE );
	}
}
