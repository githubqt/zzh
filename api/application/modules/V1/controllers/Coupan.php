<?php
use Common\CommonBase;
use Coupan\CoupanModel;
use Coupan\UserCoupanModel;
use Product\ProductModel;
use Core\Qzcode;
use Custom\YDLib;
use User\UserModel;
use Sms\SmsModel;
use Services\Msg\MsgService;
use Supplier\SupplierModel;
/**
 * *
 * 优惠券管理
 * 
 * @version v0.01
 * @author laiqingtao
 *         @time 2018-05-19
 */
class CoupanController extends BaseController {
	/**
	 * 获取商家优惠券列表
	 *
	 * <pre>
	 * POST参数
	 * page: 页码 非必填 【空：1】
	 * rows: 条数 非必填 【空：10】
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Coupan/list
	 * 测试： http://testapi.qudiandang.com/v1/Coupan/list
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         "page": 1,
	 *         "total": "2",
	 *         "list": [
	 *         {
	 *         "id": "2",
	 *         "supplier_id": "10001",
	 *         "name": "端午大促销啦",
	 *         "type": "1",
	 *         "status": "2",
	 *         "total_num": "10000",
	 *         "give_num": "2",
	 *         "remain_num": "9998",
	 *         "use_num": "2",
	 *         "use_type": "1",
	 *         "use_product_ids": null,
	 *         "sill_type": "2",
	 *         "sill_price": "200.00",
	 *         "pre_type": "2",
	 *         "pre_value": "80.00",
	 *         "time_type": "1",
	 *         "start_time": "2018-05-21 18:39:07",
	 *         "end_time": "2018-05-31 18:39:12",
	 *         "give_type": "2",
	 *         "give_value": "1",
	 *         "is_more": "2",
	 *         "status_txt": "进行中",
	 *         "use_type_txt": "店铺优惠券",
	 *         "sill_txt": "满200.00元 ",
	 *         "pre_txt": "打80.00折 ",
	 *         "time_txt": "可用时间：2018-05-21 18:39:07至2018-05-31 18:39:12"
	 *         },
	 *         {
	 *         "id": "4",
	 *         "supplier_id": "10001",
	 *         "name": "部分商品券111",
	 *         "type": "1",
	 *         "status": "2",
	 *         "total_num": "10001",
	 *         "give_num": "1",
	 *         "remain_num": "10000",
	 *         "use_num": "0",
	 *         "use_type": "2",
	 *         "use_product_ids": "13,14,15",
	 *         "sill_type": "1",
	 *         "sill_price": "0.00",
	 *         "pre_type": "2",
	 *         "pre_value": "100.00",
	 *         "time_type": "2",
	 *         "start_time": "2018-05-22 13:54:01",
	 *         "end_time": "2018-05-22 13:54:01",
	 *         "give_type": "1",
	 *         "give_value": "0",
	 *         "is_more": "1",
	 *         "status_txt": "进行中",
	 *         "use_type_txt": "商品优惠券",
	 *         "sill_txt": "无使用门槛 ",
	 *         "pre_txt": "打100.00折 ",
	 *         "time_txt": "可用时间：不限"
	 *         }
	 *         ]
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": 50014,
	 *         "errmsg": "请求失败",
	 *         "result": ''
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
		$user_id = $this->user_id;
		$coupan_id = $this->_request->getPost ( 'coupan_id' );
		$list = CoupanModel::getList ( ['user_id'=> $user_id,'remain_num'=>'1','coupan_id'=>$coupan_id], $page, $rows );
		if ($list == false) {
			$data ['page'] = $page;
			$data ['total'] = 0;
			$data ['list'] = [ ];
		} else {
			$data ['page'] = $page;
			$data ['total'] = $list ['total'];
			$data ['list'] = $list ['rows'];
		}
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data, FALSE );
	}
	
	/**
	 * 查看商家优惠券
	 *
	 * <pre>
	 * POST参数
	 * id ： 卡券id 必填参数
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Coupan/detail
	 * 测试： http://testapi.qudiandang.com/v1/Coupan/detail
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         "id": "4",
	 *         "supplier_id": "10001",
	 *         "name": "部分商品券111",
	 *         "type": "1",
	 *         "status": "2",
	 *         "total_num": "10001",
	 *         "give_num": "0",
	 *         "remain_num": "10001",
	 *         "use_num": "0",
	 *         "use_type": "2",
	 *         "use_product_ids": "13,14,15",
	 *         "sill_type": "1",
	 *         "sill_price": "0.00",
	 *         "pre_type": "2",
	 *         "pre_value": "100.00",
	 *         "time_type": "2",
	 *         "start_time": "2018-05-22 13:54:01",
	 *         "end_time": "2018-05-22 13:54:01",
	 *         "give_type": "1",
	 *         "give_value": "0",
	 *         "is_more": "1",
	 *         "status_txt": "进行中",
	 *         "use_type_txt": "商品优惠券",
	 *         "sill_txt": "无使用门槛 ",
	 *         "pre_txt": "打100.00折 ",
	 *         "time_txt": "可用时间：不限",
	 *         "product": [
	 *         {
	 *         "id": "13",
	 *         "name": "商品10",
	 *         "sale_price": "10000.00"
	 *         },
	 *         {
	 *         "id": "14",
	 *         "name": "劳力士手表，金边，镶钻",
	 *         "sale_price": "10000.00"
	 *         },
	 *         {
	 *         "id": "15",
	 *         "name": "【520礼物】爱马仕（HERMES） 香水女士男士淡香水持久香氛 大地男士淡香水50ml",
	 *         "sale_price": "3000.00"
	 *         }
	 *         ]
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": 60519,
	 *         "errmsg": "优惠券不存在",
	 *         "result": []
	 *         }
	 *         </pre>
	 */
	public function detailAction() {
		$id = $this->_request->getPost ( 'id' );
		
		if (! isset ( $id ) || ! is_numeric ( $id ) || $id <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40101 );
		}
		
		$detail = CoupanModel::getInfoByID ( $id );
		if (! $detail) {
			YDLib::output ( ErrnoStatus::STATUS_60519 );
		}
		
		if ($detail ['use_type'] == 2) {
			$detail ['product'] = ProductModel::getInfoByIDs ( $detail ['use_product_ids'] );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $detail, FALSE );
	}
	
	/**
	 * 领取优惠券
	 *
	 * <pre>
	 * POST参数
	 * user_id ： 用户id 必填参数
	 * coupan_id ： 卡券id 必填参数
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Coupan/get
	 * 测试： http://testapi.qudiandang.com/v1/Coupan/get
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": 0,
	 *         "errmsg": "请求成功",
	 *         "result": 17
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": 50014,
	 *         "errmsg": "请求失败",
	 *         "result": ''
	 *         }
	 *         </pre>
	 */
	public function getAction() {
		$user_id = $this->user_id;
		
		if (! isset ( $user_id ) || ! is_numeric ( $user_id ) || $user_id <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		$coupan_id = $this->_request->getPost ( 'coupan_id' );
		
		if (! isset ( $coupan_id ) || ! is_numeric ( $coupan_id ) || $coupan_id <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40101 );
		}
		
		$detail = CoupanModel::getInfoByID ( $coupan_id );
		if (! $detail) {
			YDLib::output ( ErrnoStatus::STATUS_60519 );
		}
		
		if ($detail ['remain_num'] <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_60401 );
		}
		
		if ($detail ['give_type'] == 2) {
			$count = UserCoupanModel::getCount ( $user_id, $coupan_id );
			if ($count >= $detail ['give_value']) {
				YDLib::output ( ErrnoStatus::STATUS_60403 );
			}
		}
		
		$data = [ ];
		$data ['supplier_id'] = SUPPLIER_ID;
		$data ['user_id'] = $user_id;
		$data ['coupan_id'] = $coupan_id;
		$data ['status'] = 1;
		$data ['give_at'] = date ( "Y-m-d H:i:s" );
		$res = UserCoupanModel::addData ( $data );
		
		if (! $res) {
			YDLib::output ( ErrnoStatus::STATUS_60402 );
		}
		
		$updata ['give_num'] = 1;
		$updata ['remain_num'] = - 1;
		$resd = CoupanModel::autoUpdateByID ( $updata, $coupan_id );
		if (! $resd) {
			YDLib::output ( ErrnoStatus::STATUS_60402 );
		}
		
		//发送信息
		//发送短信提醒
		$user_info = UserModel::getAdminInfo($user_id);
		if ($user_info) {
		    //下单短信
// 		    $smsdata = [ ];
// 		    $smsdata ['mobile'] = $user_info ['mobile'];
// 		    $smsdata ['model_id'] = '12';
// 		    $params = array (
// 		        '0' => $detail['name'],
// 		        '1' => $detail['sill_txt'].' '.$detail['pre_txt'].','.$detail['time_txt']
		        
// 		    );
// 		    $smsdata ['params'] = $params;
// 		    $smsdata ['sms_type'] = '5';
			$suppplier_detail = SupplierModel::getInfoByID(SUPPLIER_ID);
			$weichat_url = sprintf(M_URL, $suppplier_detail['domain']).'mobile/couponsCenter';
		
			
			$msgData = [
					'params' => [
							 '0' => $detail['name'],
		        			 '1' => $detail['pre_txt'],
							 '2' => $detail['time_txt'],
					],
					'weixin_params' => [
							'url' => $weichat_url,
							'pagepath' => [
									'appid' => MINI_APPID,
									'pagepath' => 'pages/index?domain=${'.$suppplier_detail['domain'].'}&share_url=${'.urlencode(SHOM_URL_MINI.$suppplier_detail['domain'].'mobile/couponsCenter').'}'
							]
							
					]
			];
			
			
		    // 用户下单发送短信
		    //SmsModel::SendSmsJustFire ( $smsdata );
			MsgService::fireMsg('10', $user_info ['mobile'], $user_id,$msgData);
		
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $res, FALSE );
	}
	
	/**
	 * 获取个人优惠券列表
	 *
	 * <pre>
	 * POST参数
	 * page: 页码 非必填 【空：1】
	 * rows: 条数 非必填 【空：10】
	 * user_id ： 用户id 必填参数
	 * status： 状态 非必填 默认全部
	 * 【1：未使用，2：已使用，3：已失效】
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Coupan/userList
	 * 测试： http://testapi.qudiandang.com/v1/Coupan/userlist
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         "page": 1,
	 *         "total": "2",
	 *         "list": [
	 *         {
	 *         "c_name": "端午大促销啦",
	 *         "c_status": "2",
	 *         "time_type": "1",
	 *         "start_time": "2018-05-21 18:39:07",
	 *         "end_time": "2018-05-31 18:39:12",
	 *         "use_type": "1",
	 *         "sill_type": "2",
	 *         "sill_price": "200.00",
	 *         "pre_type": "2",
	 *         "pre_value": "80.00",
	 *         "id": "7",
	 *         "status": "1",
	 *         "give_at": "2018-05-22 21:03:18",
	 *         "use_at": null,
	 *         "order_id": "0",
	 *         "order_price": "0",
	 *         "discount_price": "0",
	 *         "coupan_id": "2",
	 *         "user_coupan_id": "7",
	 *         "c_status_txt": "进行中",
	 *         "use_type_txt": "店铺优惠券",
	 *         "sill_txt": "满200.00元 ",
	 *         "pre_txt": "打80.00折 ",
	 *         "time_txt": "可用时间：2018-05-21 18:39:07至2018-05-31 18:39:12",
	 *         "status_txt": "未使用"
	 *         },
	 *         {
	 *         "c_name": "部分商品券111",
	 *         "c_status": "2",
	 *         "time_type": "2",
	 *         "start_time": "2018-05-22 13:54:01",
	 *         "end_time": "2018-05-22 13:54:01",
	 *         "use_type": "2",
	 *         "sill_type": "1",
	 *         "sill_price": "0.00",
	 *         "pre_type": "2",
	 *         "pre_value": "100.00",
	 *         "id": "10",
	 *         "status": "1",
	 *         "give_at": "2018-05-22 21:04:55",
	 *         "use_at": null,
	 *         "order_id": "0",
	 *         "order_price": "0",
	 *         "discount_price": "0",
	 *         "coupan_id": "4",
	 *         "user_coupan_id": "10",
	 *         "c_status_txt": "进行中",
	 *         "use_type_txt": "商品优惠券",
	 *         "sill_txt": "无使用门槛 ",
	 *         "pre_txt": "打100.00折 ",
	 *         "time_txt": "可用时间：不限",
	 *         "status_txt": "未使用"
	 *         }
	 *         ]
	 *         }
	 *         }
	 *        
	 *        
	 *         失败：
	 *         {
	 *         "errno": 50014,
	 *         "errmsg": "请求失败",
	 *         "result": ''
	 *         }
	 *         </pre>
	 */
	public function userListAction() {
		$page = $this->_request->getPost ( 'page' );
		$page = ! empty ( $page ) ? intval ( $page ) : 1;
		$page = $page > 0 ? $page : 1;
		
		$rows = $this->_request->getPost ( 'rows' );
		$rows = ! empty ( $rows ) ? intval ( $rows ) : 10;
		$rows = $rows > 0 ? $rows : 10;
		
		$user_id =$this->user_id;
		$status = $this->_request->getPost ( 'status' );
		
		if (! isset ( $user_id ) || ! is_numeric ( $user_id ) || $user_id <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		$search ['user_id'] = $user_id;
		
		if (isset ( $status ) && ! empty ( $status ) && in_array ( $status, [ 
				1,
				2,
				3 
		] )) {
			$search ['status'] = $status;
		}
		
		$list = UserCoupanModel::getList ( $search, $page, $rows );
		if ($list == false) {
			$data ['page'] = $page;
			$data ['total'] = 0;
			$data ['list'] = [ ];
		} else {
			$data ['page'] = $page;
			$data ['total'] = $list ['total'];
			$data ['list'] = $list ['rows'];
		}
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data, FALSE );
	}
	
	/**
	 * 
	 * 获取个人单张优惠券
	 *
	 * <pre>
	 * POST参数
	 * coupan_id ： 优惠卷ID
	 * user_id ： 用户id 必填参数
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Coupan/solaCoupan
	 * 测试： http://testapi.qudiandang.com/v1/Coupan/solaCoupan
	 *
	 * </pre>
	 *
	 * 
	 */
	public function solaCoupanAction() {
		
		$user_id =$this->user_id;
	
		if (! isset ( $user_id ) || ! is_numeric ( $user_id ) || $user_id <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		$coupan_id = $this->_request->getPost ( 'coupon_id' );
		
		if (! isset ( $coupan_id ) || ! is_numeric ( $coupan_id ) || $coupan_id <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40101 );
		}
		
		$status = $this->_request->getPost ( 'status' );
		
		if (isset ( $status ) && ! empty ( $status ) && in_array ( $status, [
				1,
				2,
				3
		] )) {
			$search ['status'] = $status;
		}
		
		$search ['coupan_id'] = $coupan_id;
		$search ['user_id'] = $user_id;
		$list = UserCoupanModel::getsolaCoupanBy ( $search );
		if ($list == false) {
			$data ['page'] = $page;
			$data ['total'] = 0;
			$data ['list'] = [ ];
		} else {
			$data ['page'] = $page;
			$data ['total'] = $list ['total'];
			$data ['list'] = $list ['rows'];
		}
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data, FALSE );
	}
	
	
	
	
}
