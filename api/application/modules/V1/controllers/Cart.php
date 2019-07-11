<?php
/**
 * 购物车Controllers
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-16
 */
use Custom\YDLib;
use Product\ProductModel;
use Cart\CartModel;
use Seckill\SeckillModel;
use Product\ProductMultiPointModel;
use Multipoint\MultipointModel;
use Supplier\SupplierModel;
class CartController extends BaseController {
	
	/**
	 * 添加商品进购物车接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * product_id : 商品id，必填
	 * num : 数量，不传默认为1
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Cart/addCart
	 * 测试： http://testapi.qudiandang.com/v1/Cart/addCart
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
	 *         ]
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function addCartAction() {
		$data = [ ];
		$data ['user_id'] = $this->user_id;
		if (empty ( $data ['user_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		$data ['product_id'] = $this->_request->get ( 'product_id' );
		$num = $this->_request->get ( 'num' );
		$data ['num'] = $num >= 1 ? $num : '1';
		if (empty ( $data ['product_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40009 );
		}
		
		// 验证商品信息
		$info = ProductModel::getInfoByIDAllStatus ( $data ['product_id'] );
		
		if($data ['product_id'] && $info['supplier_id']){
		$pmpData   = ProductMultiPointModel::getCartProductBy($data ['product_id'],$info['supplier_id']);
		}
		
		if ($info == false) {
		    // 是否是渠道商品
            $info = \Product\ProductChannelModel::getSingleStatus($data['product_id']);
            
            if (is_null($info) ){
                YDLib::output ( ErrnoStatus::STATUS_60025 );
            }
            // 获取渠道商品源商品信息
            $sourceInfo = ProductModel::find($info['product_id']);
           
            if (is_null($sourceInfo) ){
                YDLib::output ( ErrnoStatus::STATUS_60025 );
            }
            $info['self_code'] = $sourceInfo['self_code'];
            $info['market_price'] = $sourceInfo['market_price'];
		}
		// 判断购物车是否已有该商品
		$cart_product = CartModel::getInfoByUserIDAndProductId ( $data ['user_id'], $data ['product_id'] );
		
		if ($cart_product) {
			$item ['num'] = $cart_product ['num'] + 1;
			$item ['multi_point_id'] = $pmpData ['multi_point_id'];
			$where ['user_id'] = $data ['user_id'];
			$where ['product_id'] = $data ['product_id'];
			$last_id = CartModel::updateByID ( $item, $where );
			if (! $last_id) {
				YDLib::output ( ErrnoStatus::STATUS_60018 );
			}
		} else {
			$data ['self_code'] = $info ['self_code'];
			$data ['market_price'] = $info ['market_price'];
			$data ['sale_price'] = $info ['sale_price'];
			$data ['multi_point_id'] = $pmpData ['multi_point_id'];
			
			// 添加信息
			$last_id = CartModel::addData ( $data );
			if (! $last_id) {
				YDLib::output ( ErrnoStatus::STATUS_60018 );
			}
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 购物车修改商品数量接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * product_id : 商品id，必填
	 * num : 数量，必填，不能为0
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Cart/editNum
	 * 测试： http://testapi.qudiandang.com/v1/Cart/editNum
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
	 *         ]
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function editNumAction() {
		$data = [ ];
		$where ['user_id'] = $this->user_id;
		$where ['product_id'] = $this->_request->get ( 'product_id' );
		$num = $this->_request->get ( 'num' );
		$data ['num'] = $num >= 1 ? $num : '1';
		if (empty ( $where ['user_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		if (empty ( $where ['product_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40009 );
		}
		
		// 验证商品信息
		$info = CartModel::getInfoByUserIDAndProductId ( $where ['user_id'], $where ['product_id'] );
		if ($info == false) {
			YDLib::output ( ErrnoStatus::STATUS_60025 );
		}
		
		// 添加信息
		$last_id = CartModel::updateByID ( $data, $where );
		if (! $last_id) {
			YDLib::output ( ErrnoStatus::STATUS_60018 );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 获取购物车列表接口
	 *
	 *
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Cart/cartList
	 * 测试： http://testapi.qudiandang.com/v1/Cart/cartList
	 * </pre>
	 *
	 * <pre>
	 * 参数：
	 * page: 页码 非必填 【空：1】
	 * rows: 条数 非必填 【空：10】
	 * user_id： 用户ID 必填
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
	 *         "page": 1,
	 *         "total": 2,
	 *         "list": [
	 *         {
	 *         "cart_id": 1,
	 *         "product_id": 1,
	 *         "name": "商品1",
	 *         "market_price": 1000000,
	 *         "sale_price": 10000,
	 *         "num": 1,
	 *         "logo_url": "http://static.qudiandang.com/common/images/common.png"
	 *         },
	 *         {
	 *         "cart_id": 1,
	 *         "product_id": 2,
	 *         "name": "商品2",
	 *         "market_price": 1000000,
	 *         "sale_price": 10000,
	 *         "num": 1,
	 *         "logo_url": "http://static.qudiandang.com/common/images/common.png"
	 *         }
	 *         ]
	 *         }
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function cartListAction() {
		$page = $this->_request->getPost ( 'page' );
		$page = ! empty ( $page ) ? intval ( $page ) : 1;
		$page = $page > 0 ? $page : 1;
		
		$rows = $this->_request->getPost ( 'rows' );
		$rows = ! empty ( $rows ) ? intval ( $rows ) : 50;
		$rows = $rows > 0 ? $rows : 50;
		$user_id = $this->user_id;
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		$list = CartModel::getList ( $user_id, $page, $rows );
		
		foreach ( $list ['list'] as $key => $val ) {
			$info [$key] = ProductMultiPointModel::getCartProductBy ( $val ['product_id'], $val ['supplier_id'] );
		}
		
		if(empty($info)){
            $list = false;
		}else{

            $info = MultipointModel::array_unique_fb ( $info );

            foreach ( (array)$info as $key => $val ) {
                if ($val ['multi_point_id']) {
                    $mpData [$key] = MultipointModel::getInfoByID ( $val ['multi_point_id'], 0, 0 );
                }
            }
            $company  = SupplierModel::getshopNameBySupplierId(SUPPLIER_ID);
            foreach ((array)$mpData as $key => $val){
                $mpData [$key]['productData']   = CartModel::getCartAll($user_id,$val['id'],$val['supplier_id']);
                $mpData [$key]['company']=$company;
            }

            foreach ($list ['list'] as $key => $val)	{
                if(empty($val['multi_point_id'])){
                    $mpData [$key]['productData'] =  [$val] ;
                }
            }
        }

		
		
		if ($list == false) {
			$data ['page'] = $page;
			$data ['total'] = 0;
			$data ['list'] = [ ];
		} else {
			$data ['page'] = $page;
			$data ['total'] = $list ['total'];
			$data ['list'] = $mpData;
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
	
	/**
	 * 获取购物车商品数量接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Cart/getNum
	 * 测试： http://testapi.qudiandang.com/v1/Cart/getNum
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {
	 *         "num": 1
	 *         }
	 *         ]
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function getNumAction() {
		$data = [ ];
		$user_id = $this->user_id;
		
		if (empty ( $user_id )) {
			$num = 0;
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $num );
		}
		
		$num = CartModel::getInfoByUserID ( $user_id );
		if ($num === false) {
			YDLib::output ( ErrnoStatus::STATUS_60551 );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $num );
	}
	
	/**
	 * 删除购物车商品接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * id : 购物车id，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Cart/delCart
	 * 测试： http://testapi.qudiandang.com/v1/Cart/delCart
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
	 *         ]
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function delCartAction() {
		$data = [ ];
		$user_id = $this->user_id;
		$id = $this->_request->get ( 'id' );
		
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		if (empty ( $id )) {
			YDLib::output ( ErrnoStatus::STATUS_40014 );
		}
		
		// 验证商品信息
		$info = CartModel::getInfoByCartId ( $id );
		if ($info == false) {
			YDLib::output ( ErrnoStatus::STATUS_60025 );
		}
		
		// 删除信息
		$last_id = CartModel::deleteByID ( [ 
				'user_id' => $info ['user_id'],
				'product_id' => $info ['product_id'] 
		] );
		if (! $last_id) {
			YDLib::output ( ErrnoStatus::STATUS_60018 );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 确认订单-购物车商品接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * cart_id : 单个或多个购物车id，必填
	 * 例如：cart_id[0]:18
	 * cart_id[1]:19
	 *
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Cart/getCartProduct
	 * 测试： http://testapi.qudiandang.com/v1/Cart/getCartProduct
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => [
	 *         {
	 *         "product_id": 1,
	 *         "name": "商品1",
	 *         "market_price": 1000000,
	 *         "sale_price": 10000,
	 *         "logo_url": "http://static.qudiandang.com/common/images/common.png",
	 *         "num": 1
	 *         },
	 *         {
	 *         "product_id": 2,
	 *         "name": "商品2",
	 *         "market_price": 1000000,
	 *         "sale_price": 10000,
	 *         "logo_url": "http://static.qudiandang.com/common/images/common.png"
	 *         "num": 1
	 *         }
	 *         ]
	 *         ]
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function getCartProductAction() {
		$data = [ ];
		$user_id = $this->user_id;
		$cart_id = $this->_request->get ( 'cart_id' );
		
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		if (empty ( $cart_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40014 );
		}
		
		$item = [ ];
		foreach ( $cart_id as $key => $id ) {
			$cart = CartModel::getInfoByID ( $id );
			
			if ($cart == false) {
				YDLib::output ( ErrnoStatus::STATUS_50013 );
			}
			
			$data = ProductModel::getInfoByIDAllStatus ( $cart ['product_id'] );
			
			if ($data == false) {

			    $data = \Product\ProductChannelModel::findOneWhere([
                    'product_id' =>$cart['product_id'],
                    'supplier_id' =>SUPPLIER_ID,
                    'on_status' =>2,
                ]);
			    
			    if ($data == false){
                    YDLib::output ( ErrnoStatus::STATUS_50012 );
                }

                $info = ProductModel::getInfoByIDUseAddOrder($data['product_id']);
            
				
                $data = [];
                $data['product_id'] = $info['id'];
                $data['name'] = $info['name'];
                $data['market_price'] = $info['market_price'];
                $data['sale_price'] = $info['sale_price'];
                $data['logo_url'] = $info['logo_url'];
              
                if (! empty ( $data ['logo_url'] )) {
                    $data ['logo_url'] = HOST_FILE . ProductModel::imgSize ( $data ['logo_url'], 2 );
                } else {
                    $data ['logo_url'] = HOST_STATIC . 'common/images/common.png';
                }

			}else{
				$data['product_id'] = $cart ['product_id'];
			}
			
			$item [$key] = $data;
			$item [$key] ['num'] = $cart ['num'];
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $item );
	}
	
	/**
	 * 确认订单-立即购买商品接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * product_id : 商品id，必填
	 * num : 数量，必填 不填默认 1
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Cart/getNowBuyProduct
	 * 测试： http://testapi.qudiandang.com/v1/Cart/getNowBuyProduct
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {
	 *         "product_id": 2,
	 *         "name": "商品2",
	 *         "market_price": 1000000,
	 *         "sale_price": 10000,
	 *         "seckill_price":2345.00,//----如果有秒杀活动才会有这个参数
	 *         "logo_url": "http://static.qudiandang.com/common/images/common.png"
	 *         "num": 1
	 *         }
	 *         ]
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function getNowBuyProductAction() {
		$data = [ ];
		$user_id = $this->user_id;
		$product_id = $this->_request->get ( 'product_id' );
		$num = $this->_request->get ( 'num' ) ? $this->_request->get ( 'num' ) : '1';
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		if (empty ( $product_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40009 );
		}
		
		$data = ProductModel::getInfoByIDShot ( $product_id );
		if ($data == false) {
			YDLib::output ( ErrnoStatus::STATUS_50012 );
		}
		$seckill = SeckillModel::getInfoByProductId ( intval ( $product_id ) );
		if ($seckill) {
			$data ['seckill_price'] = $seckill ['seckill_price'];
		}
		$data ['num'] = $num;
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
}
