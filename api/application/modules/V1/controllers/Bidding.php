<?php
use Custom\YDLib;
use Seckill\SeckillModel;
use Product\ProductModel;
use Seckill\SeckillLogModel;
use Seckill\SeckillOrderModel;
use Order\OrderModel;
use Common\CommonBase;
use User\UserModel;
use Common\Crypt3Des;
use Payment\PaymentTransactionModel;
use Payment\PaymentrefundModel;
use Payment\MarginpaymentModel;
/**
 * 竞价拍列表
 */
class BiddingController extends BaseController {
	const AUCTION_PROCEED = 'HOME'; // 预告中和进行中
	const SUCCESSFUL_BIDDER = 2; // 出价出局
	const FAILURE_BIDDER = 1; // 出价领先
	const ORDER_UNPAID = 1; // 未支付
	const ORDER_HAVE_PAID = 2; // 已支付
	const ORDER_REFUNDED = 3; // 已退款
	
	/**
	 * 列表
	 *
	 * @return array @time 2018-05-21
	 *        
	 *        
	 *         <pre>
	 *         调用方式：
	 *         正式： http://api.qudiandang.com/v1/bidding/list
	 *         测试： http://testapi.qudiandang.com/v1/bidding/list
	 *        
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         "total": "7",
	 *         "list": [
	 *         {
	 *         "product_name": "iPhone Two X",
	 *         "starttime": "2018-07-25 16:47:29",
	 *         "endtime": "2018-07-31 16:47:32",
	 *         "status": "1",
	 *         "start_price": "8888.00",
	 *         "end_price": "0.00",
	 *         "bid_lncrement": "10000.00",
	 *         "total_price": "8888.00",
	 *         "onlookers_num": "0",
	 *         "apply_num": "0",
	 *         "bigding_price": "111111111",
	 *         "action_num": "1",
	 *         "supplier_id": "10001",
	 *         "product_id": "16",
	 *         "logo_url": "http://file.qudiandang.com//upload/product/2018/07/05/8a04a8a0db65212d4c19b2687346b739_372_560x560.jpg"
	 *         },
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 */
	public function listAction() {
		$status = $this->_request->getPost ( 'status' );
		
		if ($status == self::AUCTION_PROCEED) { // 预告中和进行中主页展示
			$page = $this->_request->getPost ( 'page' );
			$page = ! is_numeric ( $page ) || $page < 1 ? 1 : $page;
			
			$rows = $this->_request->getPost ( 'rows' );
			$rows = ! is_numeric ( $rows ) || $rows < 1 ? 10 : $rows;
			
			$info ['info'] ['sort'] = isset ( $_REQUEST ['sort'] ) ? trim ( $_REQUEST ['sort'] ) : 'id';
			$info ['info'] ['order'] = isset ( $_REQUEST ['order'] ) ? trim ( $_REQUEST ['order'] ) : 'DESC';
			
			$list = SeckillModel::getProceedAll ( $info, $page, $rows );
			
			foreach ( $list ['list'] as $key => $val ) {
				$pInfo = ProductModel::getInfoByID ( $list ['list'] [$key] ['product_id'], false );
				
				if (empty ( $pInfo )) {
					// YDLib::output ( ErrnoStatus::STATUS_60590, $list );
				} else {
					$list ['list'] [$key] ['logo_url'] = $pInfo ['logo_url'];
				}
			}
			
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $list );
		} else { // 拍卖详情列表
			$page = $this->_request->getPost ( 'page' );
			$page = ! is_numeric ( $page ) || $page < 1 ? 1 : $page;
			
			$rows = $this->_request->getPost ( 'rows' );
			$rows = ! is_numeric ( $rows ) || $rows < 1 ? 10 : $rows;
			
			$status = $this->_request->getPost ( 'status' );
			
			if (! isset ( $rows ) || ! is_numeric ( $rows )) {
				YDLib::output ( ErrnoStatus::STATUS_40095 );
			}
			
			if (! isset ( $page ) || ! is_numeric ( $page )) {
				YDLib::output ( ErrnoStatus::STATUS_40096 );
			}
			
			$info ['info'] ['sort'] = isset ( $_REQUEST ['sort'] ) ? trim ( $_REQUEST ['sort'] ) : 'id';
			$info ['info'] ['order'] = isset ( $_REQUEST ['order'] ) ? trim ( $_REQUEST ['order'] ) : 'DESC';
			$info ['info'] ['status'] = $status;
			
			$list = SeckillModel::getBiddingAll ( $info, $page, $rows );
			
			foreach ( $list ['list'] as $key => $val ) {
				$pInfo = ProductModel::getInfoByID ( $list ['list'] [$key] ['product_id'], false );
				// 获取拍卖次数
				$data ['id'] = $list ['list'] [$key] ['id'];
				$data ['product_id'] = $list ['list'] [$key] ['product_id'];
				$count = SeckillLogModel::getCount ( $data );
				
				if (empty ( $count )) {
					$list ['list'] [$key] ['count'] = 0;
				} else {
					$list ['list'] [$key] ['count'] = $count;
					$skData ['order_people_num'] = $count;
					SeckillModel::updateByID ( $skData, $list ['list'] [$key] ['id'] );
				}
				
				if (empty ( $pInfo )) {
					// YDLib::output ( ErrnoStatus::STATUS_60590, $list );
				} else {
					$list ['list'] [$key] ['logo_url'] = $pInfo ['logo_url'];
				}
			}
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $list );
		}
	}
	
	/*
	 * 出价记录列表
	 *
	 * 参数 id 非空
	 * 参数 supplier_id 非空
	 * 参数 product_id 非空
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/bidding/record
	 * 测试： http://testapi.qudiandang.com/v1/bidding/record
	 *
	 * {
	 * "errno": "0",
	 * "errmsg": "请求成功",
	 * "result": {
	 * "total": "4",
	 * "list": [
	 * {
	 * "status": "1",
	 * "supplier_id": "10001",
	 * "user_id": "4",
	 * "money": "4.00",
	 * "product_id": "62",
	 * "created_at": "2018-07-19 19:02:00"
	 * },
	 * {
	 * "status": "2",
	 * "supplier_id": "10001",
	 * "user_id": "3",
	 * "money": "3.00",
	 * "product_id": "62",
	 * "created_at": "2018-07-19 19:01:22"
	 * },
	 *
	 * {
	 * "errno": "60585",
	 * "errmsg": "数据异常",
	 * "result": []
	 * }
	 *
	 *
	 * </pre>
	 */
	public function recordAction() {
		$data ['id'] = $this->_request->getPost ( 'id' );
		
		if (! isset ( $data ['id'] ) || ! is_numeric ( $data ['id'] )) {
			
			YDLib::output ( ErrnoStatus::STATUS_60589 );
		}
		
		$data ['product_id'] = $this->_request->getPost ( 'product_id' );
		
		if (! isset ( $data ['product_id'] ) || ! is_numeric ( $data ['product_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_60025 );
		}
		
		$page = isset ( $_REQUEST ['page'] ) && intval ( $_REQUEST ['page'] ) >= 1 ? $_REQUEST ['page'] : 1;
		$pagesize = isset ( $_REQUEST ['pagesize'] ) && intval ( $_REQUEST ['pagesize'] ) >= 10 ? $_REQUEST ['pagesize'] : 10;
		
		$list = SeckillLogModel::getBidderAll ( $data, $page, $pagesize );
		if (! empty ( $list )) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $list );
		} else {
			YDLib::output ( ErrnoStatus::STATUS_60589, $list );
		}
	}
	
	/*
	 * 商品参数接口
	 *
	 * 参数id 商品product_id 非空
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/bidding/product
	 * 测试： http://testapi.qudiandang.com/v1/bidding/product
	 *
	 * {
	 * "errno": "0",
	 * "errmsg": "请求成功",
	 * "result": [
	 * {
	 * "id": "214",
	 * "supplier_id": "10001",
	 * "product_id": "16",
	 * "attribute_id": "2",
	 * "attribute_name": "规格",
	 * "attribute_value_id": "26",
	 * "attribute_value_name": "ccc",
	 * "type": "2",
	 * "is_del": "2",
	 * "created_at": "2018-07-02 14:47:29",
	 * "updated_at": "2018-07-20 10:13:09",
	 * "deleted_at": "2018-07-05 13:18:55",
	 * "input_type": "1"
	 * }
	 * ]
	 * }
	 *
	 * {
	 * "errno": "60585",
	 * "errmsg": "数据异常",
	 * "result": []
	 * }
	 *
	 */
	public function productAction() {
		$seckill_id = $this->_request->getPost ( 'id' );
		
		$info ['user_id'] = $this->user_id;
		
		if (! isset ( $seckill_id ) || ! is_numeric ( $seckill_id )) {
			YDLib::output ( ErrnoStatus::STATUS_10003 );
		}
		
		$product = SeckillModel::getInfoByBiddingId ( $seckill_id );
		
		if (! empty ( $product )) {
			// 判断是否拍卖结束
			if ($product ['is_restrictions'] != 3) {
				// 更新围观人数
				$data ['onlookers_num'] = intval ( $product ['onlookers_num'] + 1 );
				$onlookers_num = SeckillModel::updateByID ( $data, $product ['id'] );
			}
		} else {
			YDLib::output ( ErrnoStatus::STATUS_60589 );
		}
		
		$product_id = SeckillModel::getInfoByBiddingId ( $seckill_id );
		// 返回时间戳
		$product_id ['endtime_txt'] = strtotime ( $product_id ['endtime'] );
		$product_id ['starttime_txt'] = strtotime ( $product_id ['starttime'] );
		
		// 刷新更改状态 是否拍卖完成 1.预告中2.进行中 3.已完成',
		if (strtotime ( $product_id ['starttime'] ) >= time () && $product_id ['is_restrictions'] != 1) {
			$data ['is_restrictions'] = '1'; // 预告中
			SeckillModel::updateByID ( $data, $product_id ['id'] );
		}
		
		if (strtotime ( $product_id ['starttime'] ) <= time () && strtotime ( $product_id ['endtime'] ) >= time () && $product_id ['is_restrictions'] != 2) {
			
			$data ['is_restrictions'] = '2'; // 进行中
			SeckillModel::updateByID ( $data, $product_id ['id'] );
		}
		
		if (strtotime ( $product_id ['starttime'] ) <= time () && strtotime ( $product_id ['endtime'] ) <= time () && $product_id ['is_restrictions'] != 3) {
			$data ['is_restrictions'] = '3'; // 结束
			SeckillModel::updateByID ( $data, $product_id ['id'] );
		}
		
		$product_id = SeckillModel::getInfoByBiddingId ( $seckill_id );
		
		// 预告中需要倒计时返回时间戳
		$product_id ['time'] = 0;
		if ($product_id ['is_restrictions'] == 1) {
			$product_id ['time'] = floor ( strtotime ( $product_id ['starttime'] ) - time () );
		}
		// 进行中倒计时
		$product_id ['time_proceed'] = 0;
		if ($product_id ['is_restrictions'] == 2) {
			$product_id ['time_proceed'] = floor ( strtotime ( $product_id ['endtime'] ) - time () );
		}
		
		$count = SeckillLogModel::getCount ( $product_id );
		$product_id ['count'] = $count;
		
		// 取图片
		$pInfo = ProductModel::getInfoByID ( $product_id ['product_id'] );
		
		// 取定金信息
		$product_id ['orderList'] = [ ];
		if (! empty ( $info ['user_id'] )) {
			$order = SeckillOrderModel::getOrderRow ( $product_id, $info ['user_id'] );
			$product_id ['orderList'] = $order ['list'];
		}
		
		if (empty ( $pInfo )) {
			// YDLib::output ( ErrnoStatus::STATUS_60590, $list );
		} else {
			$product_id ['imglist'] = $pInfo ['imglist'] ? $pInfo ['imglist'] : $pInfo ['logo_url'];
		}
		
		if (! empty ( $product_id )) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $product_id );
		} else {
			YDLib::output ( ErrnoStatus::STATUS_60589, $product_id );
		}
	}
	
	/*
	 * 加价接口
	 *
	 * 参数 id 非空
	 * 参数 user_id 非空
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/bidding/premium
	 * 测试： http://testapi.qudiandang.com/v1/bidding/premium
	 *
	 *
	 * </pre>
	 */
	public function premiumAction() {
		$data ['id'] = $this->_request->getPost ( 'id' );
		
		if (! isset ( $data ['id'] ) || ! is_numeric ( $data ['id'] )) {
			
			YDLib::output ( ErrnoStatus::STATUS_60589 );
		}
		
		$data ['user_id'] = $this->user_id;
		
		if (! isset ( $data ['user_id'] ) || ! is_numeric ( $data ['user_id'] )) {
			
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		// 获取到活动商品
		$info = SeckillModel::getInfoByID ( $data ['id'] );
		
		if (! $info) {
			YDLib::output ( ErrnoStatus::STATUS_50027 ); // 竞拍不存在
		}
		
		if ($info ['starttime'] > date ( 'Y-m-d H:i:s' )) {
			YDLib::output ( ErrnoStatus::STATUS_50028 ); // 竞拍未开始
		}
		
		if ($info ['endtime'] < date ( 'Y-m-d H:i:s' )) {
			YDLib::output ( ErrnoStatus::STATUS_50029 ); // 竞拍已结束
		}
		
		$pdo = YDLib::getPDO ( 'db_w' );
		$pdo->beginTransaction ();
		try {
			$addData = [ ];
			$addData ['seckill_id'] = $info ['id'];
			$addData ['seckill_type'] = $info ['type'];
			$addData ['status'] = self::SUCCESSFUL_BIDDER;
			$addData ['supplier_id'] = $info ['supplier_id'];
			$addData ['user_id'] = $data ['user_id'];
			$addData ['money'] = bcadd ( $info ['total_price'], $info ['bid_lncrement'], 2 );
			$addData ['product_id'] = $info ['product_id'];
			$addData ['order_status'] = self::ORDER_UNPAID;
			$addInfo = SeckillLogModel::addData ( $addData );
			if (! $addInfo) {
				$pdo->rollback ();
				YDLib::output ( ErrnoStatus::STATUS_50030 ); // 加价失败
			}
			
			$totalData ['total_price'] = $addData ['money'];
			$res = SeckillModel::updateByID ( $totalData, $data ['id'] );
			if (! $res) {
				$pdo->rollback ();
				YDLib::output ( ErrnoStatus::STATUS_50030 ); // 加价失败
			}
			
			$premiumData ['status'] = self::FAILURE_BIDDER;
			$premiumInfo = SeckillLogModel::updateByID ( $premiumData, $addInfo );
			if (! $premiumInfo) {
				$pdo->rollback ();
				YDLib::output ( ErrnoStatus::STATUS_50030 ); // 加价失败
			}
			
			$typeData ['id'] = $addInfo;
			$typeData ['seckill_id'] = $info ['id'];
			$typeInfo = SeckillLogModel::getTypeInfoByID ( $typeData );
			$biddingInfo = SeckillLogModel::updateByID ( [ 
					'status' => self::SUCCESSFUL_BIDDER 
			], $typeInfo ['id'] );
			if (! $biddingInfo) {
				$pdo->rollback ();
				YDLib::output ( ErrnoStatus::STATUS_50030 ); // 加价失败
			}
			$pdo->commit ();
		} catch ( \Exception $e ) {
			$pdo->rollback ();
			YDLib::output ( ErrnoStatus::STATUS_50030 );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	/*
	 * 竞拍结束后订单生成退保证金接口
	 *
	 * 参数 关联活动id 非空
	 * 参数 user_id 非空
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/bidding/endrefundMargin
	 * 测试： http://testapi.qudiandang.com/v1/bidding/endrefundMargin
	 *
	 *
	 * </pre>
	 */
	public function endrefundMarginAction() {
		$data ['seckill_id'] = $this->_request->getPost ( 'id' );
		
		if (! isset ( $data ['seckill_id'] ) || ! is_numeric ( $data ['seckill_id'] )) {
			
			YDLib::output ( ErrnoStatus::STATUS_60589 );
		}
		
		define ( 'W_WEIXIN_APPID', WEIXIN_APPID );
		define ( 'W_WEIXIN_APPSECRET', WEIXIN_APPSECRET );
		$seckillOrderInfo = SeckillOrderModel::getSeckillidByID ( $data ['seckill_id'] );
		
		// 应退人数
		$needReturn = count ( $seckillOrderInfo );
		// 已退人数
		$hasReturned = 0;
		
		if (is_array ( $seckillOrderInfo ) && $needReturn > 0) {
			foreach ( $seckillOrderInfo as $key => $val ) {
				
				if ($seckillOrderInfo [$key] ['status'] != 2 && $seckillOrderInfo [$key] ['is_robot'] == '1') {
					$detail = PaymentTransactionModel::getOrderByID ( $seckillOrderInfo [$key] ['order_no'] );
					if ($detail) {
						$is_reture = PaymentrefundModel::create ( $detail );
						// 退款状态日志
						if ($is_reture) {
							YDLib::testLog ( [ 
									"seckill_order:{$detail['id']} 退款成功" 
							] );
							$hasReturned ++;
						} else {
							YDLib::testLog ( [ 
									"seckill_order:{$detail['id']} 退款失败" 
							] );
						}
					}
				} else {
					if ($seckillOrderInfo [$key] ['status'] != 2) {
						$Machine = PaymentrefundModel::returnMachineUpdate ( $seckillOrderInfo [$key] ['order_no'] );
						// 机器人更新状态日志
						if ($Machine) {
							YDLib::testLog ( [ 
									"seckill_order: 更新成功" 
							] );
							$hasReturned ++;
						} else {
							YDLib::testLog ( [ 
									"seckill_order: 更新失败" 
							] );
						}
					}
				}
			}
			
			// 判断是否全部退款完成，记录日志
			if ($needReturn == $hasReturned) {
				YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
			} else {
				$hasReturnedFailed = $needReturn - $hasReturned;
				YDLib::output ( ErrnoStatus::STATUS_50032 );
			}
		} else {
			YDLib::output ( ErrnoStatus::STATUS_50031 ); // 没人交保证金
		}
	}
	
	/*
	 * 个人竞拍查询接口
	 *
	 * 参数 user_id 非空
	 * 参数 status 非空 0.全部拍品 1.参与拍品 2.已获拍 3.未获拍
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/bidding/personal
	 * 测试： http://testapi.qudiandang.com/v1/bidding/personal
	 *
	 *
	 * </pre>
	 */
	public function personalAction() {
		$data ['status'] = $this->_request->getPost ( 'status' );
		
		$data ['user_id'] = $this->user_id;
		
		if (! isset ( $data ['user_id'] ) || ! is_numeric ( $data ['user_id'] )) {
			
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		$info = SeckillOrderModel::getPersonalByID ( $data );
		
		foreach ( $info as $key => $val ) {
			$info [$key] ['end_type'] = 1;
			
			$info [$key] ['list'] [$key] = SeckillModel::getInfoByID ( $info [$key] ['seckill_id'] );
			
			$productInfo = ProductModel::getInfoByID ( $info [$key] ['product_id'] );
			
			foreach ( $info [$key] ['list'] as $k => $v ) {
				$info [$key] ['list'] [$key] ['logo_url'] = $productInfo ['logo_url'];
				
				// 预告中
				if ($info [$key] ['list'] [$key] ['is_restrictions'] == 1) {
					$info [$key] ['time_type'] = 1;
					$info [$key] ['time_start_txt'] = floor ( strtotime ( $info [$key] ['list'] [$key] ['starttime'] ) - strtotime ( date ( 'y-m-d H:i:s', time () ) ) );
				}
				
				// 进行中
				if ($info [$key] ['list'] [$key] ['is_restrictions'] == 2) {
					if (strtotime ( $info [$key] ['list'] [$key] ['endtime'] ) > strtotime ( date ( 'y-m-d H:i:s', time () ) )) {
						$info [$key] ['time_type'] = 2;
						$info [$key] ['time_end_txt'] = floor ( strtotime ( $info [$key] ['list'] [$key] ['endtime'] ) - strtotime ( date ( 'y-m-d H:i:s', time () ) ) );
					} else {
						$info [$key] ['time_type'] = 3;
						$info [$key] ['time_end_txt'] = floor ( strtotime ( $info [$key] ['list'] [$key] ['endtime'] ) - strtotime ( date ( 'y-m-d H:i:s', time () ) ) );
					}
				}
				
				// 已完成
				if ($info [$key] ['list'] [$key] ['is_restrictions'] == 3) {
					$info [$key] ['time_type'] = 3;
				}
			}
			
			if ($info [$key] ['is_margin'] == 2) {
				$info [$key] ['margin_txt'] = '已支付';
			} else if ($info [$key] ['is_margin'] == 3) {
				$info [$key] ['margin_txt'] = '已退款';
			}
			
			switch ($info [$key] ['status']) {
				case 1 :
					
					$info [$key] ['list'] [$key] ['status_txt'] = '参拍中';
					
					break;
				
				case 2 :
					
					$info [$key] ['list'] [$key] ['status_txt'] = '已获拍';
					
					$orderInfo = OrderModel::getInfoByID ( $info [$key] ['list'] [$key] ['number'] );
					$info [$key] ['pay_status'] = $orderInfo ['status'];
					$info [$key] ['end_type'] = 2;
					$info [$key] ['order_txt'] = '已支付';
					$info [$key] ['list'] [$key] ['order_status'] = $orderInfo ['status'];
					// 判断是否等于待付款状态
					if ($orderInfo ['status'] == CommonBase::STATUS_PENDING_PAYMENT) {
						$info [$key] ['payurl'] = 'https://' . $_SERVER ['HTTP_HOST'] . '/v1/payment/pay?identif=' . SUPPLIER_DOMAIN . '&orderId=' . $info [$key] ['list'] [$key] ['number']; // 跳转支付页面
						$info [$key] ['order_txt'] = '未支付';
						$info [$key] ['list'] [$key] ['order_status'] = $orderInfo ['status'];
					}
					
					break;
				
				case 3 :
					
					$info [$key] ['list'] [$key] ['status_txt'] = '未拍中';
					
					break;
				case 6 :
					$info [$key] ['end_type'] = 2;
					$info [$key] ['list'] [$key] ['order_status'] = '80';
					break;
				
				default :
					break;
			}
		}
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $info );
	}
	
	/*
	 * 微信支付保证金失败接口
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/bidding/payfailure
	 * 测试： http://testapi.qudiandang.com/v1/bidding/payfailure
	 *
	 *
	 * </pre>
	 */
	public function payfailureAction() {
		$orderId = $this->_request->getPost ( 'orderId' );
		
		if (! isset ( $orderId ) || ! is_numeric ( $orderId )) {
			
			YDLib::output ( ErrnoStatus::STATUS_40088 );
		}
		
		$orderInfo = OrderModel::getUserOrderByID ( $orderId );
		
		$data ['paymentId'] = 1;
		$data ['out_trade_no'] = $orderInfo ['order_no'];
		$data ['return_payment_no'] = $orderInfo ['order_no'];
		$data ['return_payment_data'] = '';
		$data ['orderid'] = $orderInfo ['id'];
		$res = MarginpaymentModel::payFailure ( $data );
		if ($res) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $res );
		} else {
			YDLib::output ( ErrnoStatus::STATUS_60589, $res );
		}
	}
	
	/**
	 * 个人竞拍小气泡显示数据
	 *
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/bidding/bubble
	 * 测试： http://testapi.qudiandang.com/v1/bidding/bubble
	 */
	public function bubbleAction() {
		$user_id = $this->user_id;
		
		if (! isset ( $user_id ) || ! is_numeric ( $user_id )) {
			
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		$info = SeckillOrderModel::getBubbleCount ( $user_id );
		
		if ($info) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $info );
		}
	}
}
