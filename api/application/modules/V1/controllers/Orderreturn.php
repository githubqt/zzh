<?php
use Custom\YDLib;
use Common\CommonBase;
use Common\SerialNumber;
use User\UserAddressModel;
use Order\OrderReturnModel;

/**
 * 退货订单管理
 * 
 * @version v0.01
 * @author huangxianguo
 *         @time 2018-05-16
 */
class OrderreturnController extends BaseController {
	/**
	 * 申请退货接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 当前登录用户ID 【必填】
	 * order_child_id ：子订单id 【必填】
	 * product_id ：商品id 【必填】
	 * num：退货数量 【必填】
	 * note：退款原因
	 * items：数组格式的图片url
	 * 例如：items[0] : XXXXXXXX.jpg
	 * items[1] : AAAAAAAA.jpg
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Orderreturn/add
	 * 测试： http://testapi.qudiandang.com/v1/Orderreturn/add
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
	public function addAction() {
		$data = [ ];
		$data ['user_id'] = $this->user_id;
		$data ['order_child_id'] = intval ( $this->_request->getPost ( 'order_child_id' ) );
		$data ['product_id'] = intval ( $this->_request->getPost ( 'product_id' ) );
		$data ['num'] = intval ( $this->_request->getPost ( 'num' ) );
		$data ['note'] = trim ( $this->_request->getPost ( 'note' ) );
		$data ['items'] = $this->_request->getPost ( 'items' );
		
		if (! isset ( $data ['user_id'] ) || ! is_numeric ( $data ['user_id'] )) {
			
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		if (empty ( $data ['order_child_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40058 );
		}
		
		if (empty ( $data ['product_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40009 );
		}
		
		if (empty ( $data ['num'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40013 );
		}
		
		$res = OrderReturnModel::addReturn ( $data );
		if ($res == false) {
			YDLib::output ( ErrnoStatus::STATUS_60505 );
		}
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 售后订单列表接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户ID [必填参数]
	 * page : 页码 默认 1
	 * rows : 每次取多少行 默认10
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Orderreturn/list
	 * 测试： http://testapi.qudiandang.com/v1/Orderreturn/list
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' =>
	 *         "total"：2
	 *         "page": 1,
	 *         "list": [
	 *         {
	 *         "id": 10, 退货id
	 *         "order_no": 401805171000000000, 退货编号
	 *         "user_id": 1, 用户id
	 *         "child_order_actual_amount": 6000, 实际付钱
	 *         "back_money": 6000, 退款金额
	 *         "child_status": 10, 退货状态
	 *         "child_status_name": "待审核", 退货状态名称
	 *         "type": "退货单", 退换货类型
	 *         "num": 1, 数量
	 *         "product_list": [ 商品信息
	 *         {
	 *         "product_name": "【520礼物】爱马仕（HERMES） 大地男士淡香水50ml", 商品名称
	 *         "product_id": 15, 商品id
	 *         "sale_num": 1, 退货数量
	 *         "sale_price": 3000, 购买价
	 *         "market_price": 5000, 公价
	 *         "logo_url": "http://file.qudiandang.com//upload/product/2018/05/15/16b4485eb0763a327804ff95c249df0a_497.jpg"
	 *         }
	 *         ]
	 *         },
	 *         {
	 *         "id": 9,
	 *         "order_no": 40180517,
	 *         "user_id": 1,
	 *         "child_order_actual_amount": 6000,
	 *         "back_money": 6000,
	 *         "child_status": 10,
	 *         "child_status_name": "待审核",
	 *         "type": "退货单",
	 *         "num": 1,
	 *         "product_list": [
	 *         {
	 *         "product_name": "【520礼物】爱马仕（HERMES） 香水女士男士淡香水持久香氛 大地男士淡香水50ml",
	 *         "product_id": 15,
	 *         "sale_num": 1,
	 *         "sale_price": 3000,
	 *         "market_price": 5000,
	 *         "logo_url": "http://file.qudiandang.com//upload/product/2018/05/15/16b4485eb0763a327804ff95c249df0a_497.jpg"
	 *         }
	 *         ]
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
	public function listAction() {
		$page = $this->_request->getPost ( 'page' );
		$page = ! is_numeric ( $page ) || $page < 1 ? 1 : $page;
		
		$rows = $this->_request->get ( 'rows' );
		$rows = ! is_numeric ( $rows ) || $rows < 1 ? 10 : $rows;
		
		$user_id = $this->user_id;
		if (! isset ( $user_id ) || ! is_numeric ( $user_id )) {
			
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		if (! isset ( $rows ) || ! is_numeric ( $rows )) {
			YDLib::output ( ErrnoStatus::STATUS_40095 );
		}
		
		if (! isset ( $page ) || ! is_numeric ( $page )) {
			YDLib::output ( ErrnoStatus::STATUS_40096 );
		}
		
		$data = OrderReturnModel::getList ( $user_id, $page, $rows );
		$list = [ ];
		if ($data == false) {
			$list ['total'] = '0';
			$list ['page'] = $page;
			$list ['list'] = [ ];
		} else {
			$list ['total'] = $data ['total'];
			$list ['page'] = $page;
			$list ['list'] = $data ['list'];
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $list );
	}
	
	/**
	 * 填写物流信息
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Orderreturn/addExpress
	 * 测试： http://testapi.qudiandang.com/v1/Orderreturn/addExpress
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * $id :售后订单ID【必填】
	 * express_name ： 快递公司名称 【必填】
	 * express_num ： 快递单号 【必填】
	 * express_note：快递说明
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功",
	 *         "data": []
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "-1",
	 *         "errmsg": "系通繁忙，稍后再试",
	 *         "data": ""
	 *         }
	 *         </pre>
	 */
	public function addExpressAction() {
		$order_return_id = $this->_request->getPost ( 'id' );
		if (! $order_return_id) {
			YDLib::output ( ErrnoStatus::STATUS_60052 );
		}
		// $data['mobile'] = trim($this->_request->getPost('mobile'));
		$data ['express_name'] = trim ( $this->_request->getPost ( 'express_name' ) );
		$data ['express_num'] = trim ( $this->_request->getPost ( 'express_num' ) );
		$data ['express_note'] = trim ( $this->_request->getPost ( 'express_note' ) );
		$data ['user_id'] = trim ( $this->_request->getPost ( 'user_id' ) );
		
		if (empty ( $data ['express_name'] )) {
			YDLib::output ( ErrnoStatus::STATUS_60077 );
		}
		
		if (empty ( $data ['express_num'] )) {
			YDLib::output ( ErrnoStatus::STATUS_60079 );
		}
		
		/*
		 * if(empty($data['mobile'])){
		 * YDLib::output(ErrnoStatus::STATUS_60078);
		 * }
		 */
		$add = OrderReturnModel::setExpressInfo ( $order_return_id, $data );
		
		if ($add) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
		} else {
			YDLib::output ( ErrnoStatus::STATUS_60084 );
		}
	}
	
	/**
	 * 退货详情页面接口
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Orderreturn/returnInfo
	 * 测试： http://testapi.qudiandang.com/v1/Orderreturn/returnInfo
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * $id :退货订单ID【必填】
	 *
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
	 *         "id": 10, 退货id
	 *         "order_no": 401805171000000000, 退货编号
	 *         "user_id": 1, 用户id
	 *         "child_order_actual_amount": 6000, 实际付钱
	 *         "back_money": 6000, 退款金额
	 *         "child_status": 10, 退货状态
	 *         "child_status_name": "待审核", 退货状态名称
	 *         "type": "退货单", 退换货类型
	 *         "num": 1, 数量
	 *         "note": "我要退货" 申请退货说明
	 *         "express_company": "顺丰", 快递公司名称
	 *         "express_no": 423423424234320000, 快递单号
	 *         "express_note": "已发货啦，请注意查收11", 快递说明
	 *         "img": [
	 *         {
	 *         "id": 116,
	 *         "obj_id": 10,
	 *         "type": "order_return",
	 *         "img_url": "http://file.qudiandang.com//upload/product/2018/05/09/280bf32eccad8a4cd7997704966c4421_937.png",
	 *         "img_type": "png"
	 *         },
	 *         {
	 *         "id": 117,
	 *         "obj_id": 10,
	 *         "type": "order_return",
	 *         "img_url": "http://file.qudiandang.com//upload/product/2018/05/09/1c30b1e2933ac94f8a246c3c0fc13d3d_356.jpg",
	 *         "img_type": "jpg"
	 *         }
	 *         ],
	 *         "product_list": [ 商品信息
	 *         {
	 *         "product_name": "【520礼物】爱马仕（HERMES） 大地男士淡香水50ml", 商品名称
	 *         "product_id": 15, 商品id
	 *         "sale_num": 1, 退货数量
	 *         "sale_price": 3000, 购买价
	 *         "market_price": 5000, 公价
	 *         "logo_url": "http://file.qudiandang.com//upload/product/2018/05/15/16b4485eb0763a327804ff95c249df0a_497.jpg"
	 *         }
	 *         ]
	 *         }
	 *         ]
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "-1",
	 *         "errmsg": "系通繁忙，稍后再试",
	 *         "data": ""
	 *         }
	 *         </pre>
	 */
	public function returnInfoAction() {
		$order_return_id = $this->_request->getPost ( 'id' );
		if (! $order_return_id) {
			YDLib::output ( ErrnoStatus::STATUS_60052 );
		}
		
		$info = OrderReturnModel::getAllById ( $order_return_id );
		
		if ($info) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $info );
		} else {
			YDLib::output ( ErrnoStatus::STATUS_60080 );
		}
	}
}
