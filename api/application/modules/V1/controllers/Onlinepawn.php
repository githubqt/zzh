<?php
use Custom\YDLib;
use Common\CommonBase;
use OnlinePawn\OnlinePawnModel;
/**
 * 在线售卖管理
 * 
 * @version v0.01
 * @author huangxianguo
 *         @time 2018-05-16
 */
class OnlinepawnController extends BaseController {
	/**
	 * 房屋抵押贷款接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 当前登录用户ID 【必填】
	 * name ：小区名称 【必填】
	 * housing_area ：建筑面积 【必填】
	 * housing_year：房屋年限 【必填】
	 * purchase_price：购买价格 【必填】
	 * province_id : 省份 【必填】
	 * city_id ：市 【必填】
	 * area_id ：区 【必填】
	 * address：详细地址 【必填】
	 * loan_price：借款金额 【必填】
	 * mobile ：联系方式 【必填】
	 * housing_img ：数组格式的图片url 【必填】
	 * 例如：housing_img[0] : XXXXXXXX.jpg
	 * housing_img[1] : AAAAAAAA.jpg
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Onlinepawn/addHousing
	 * 测试： http://testapi.qudiandang.com/v1/Onlinepawn/addHousing
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
	public function addHousingAction() {
		$data = [ ];
		$data ['user_id'] = $this->user_id;
		$data ['name'] = $this->_request->get ( 'name' );
		$data ['housing_area'] = $this->_request->get ( 'housing_area' );
		$data ['housing_year'] = $this->_request->get ( 'housing_year' );
		$data ['purchase_price'] = trim ( $this->_request->get ( 'purchase_price' ) );
		$data ['province_id'] = $this->_request->get ( 'province_id' );
		$data ['city_id'] = $this->_request->get ( 'city_id' );
		$data ['area_id'] = $this->_request->get ( 'area_id' );
		$data ['address'] = $this->_request->get ( 'address' );
		$data ['loan_price'] = $this->_request->get ( 'loan_price' );
		$data ['mobile'] = $this->_request->get ( 'mobile' );
		$data ['housing_img'] = $this->_request->get ( 'housing_img' );
		
		if (empty ( $data ['mobile'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40001 );
		}
		if (! isset ( $data ['user_id'] ) || ! is_numeric ( $data ['user_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		if (empty ( $data ['name'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40151 );
		}
		if (empty ( $data ['housing_area'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40152 );
		}
		if (empty ( $data ['housing_year'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40153 );
		}
		$data ['purchase_price'] = $data ['purchase_price'] ? $data ['purchase_price'] : '0';
		
		// YDLib::output(ErrnoStatus::STATUS_40154);
		
		if (empty ( $data ['province_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40155 );
		}
		if (empty ( $data ['city_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40156 );
		}
		if (empty ( $data ['area_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40157 );
		}
		if (empty ( $data ['address'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40158 );
		}
		if (empty ( $data ['loan_price'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40159 );
		}
		if (empty ( $data ['housing_img'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40160 );
		}
		
		$data ['housing_img'] = implode ( ',', $data ['housing_img'] );
		
		$data ['type'] = '1';
		$res = OnlinePawnModel::addData ( $data );
		if ($res == false) {
			YDLib::output ( ErrnoStatus::STATUS_60555 );
		}
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 汽车抵押贷款接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 当前登录用户ID 【必填】
	 * name ：汽车品牌 【必填】
	 * housing_year：行驶里程 【必填】
	 * on_card_time：首次上牌时间 【必填】
	 * purchase_price：购买价格 【必填】
	 * loan_price：借款金额 【必填】
	 * mobile ：联系方式 【必填】
	 * housing_img ：数组格式的图片url 【必填】
	 * 例如：housing_img[0] : XXXXXXXX.jpg
	 * housing_img[1] : AAAAAAAA.jpg
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Onlinepawn/carMortgage
	 * 测试： http://testapi.qudiandang.com/v1/Onlinepawn/carMortgage
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
	public function carMortgageAction() {
		$data = [ ];
		$data ['user_id'] =$this->user_id;
		$data ['name'] = $this->_request->get ( 'name' );
		$data ['housing_year'] = $this->_request->get ( 'housing_year' );
		$data ['purchase_price'] = trim ( $this->_request->get ( 'purchase_price' ) );
		$data ['on_card_time'] = $this->_request->get ( 'on_card_time' );
		$data ['loan_price'] = $this->_request->get ( 'loan_price' );
		$data ['mobile'] = $this->_request->get ( 'mobile' );
		$data ['housing_img'] = $this->_request->get ( 'housing_img' );
		
		if (empty ( $data ['mobile'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40001 );
		}
		if (! isset ( $data ['user_id'] ) || ! is_numeric ( $data ['user_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		if (empty ( $data ['name'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40161 );
		}
		if (empty ( $data ['housing_year'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40162 );
		}
		/*
		 * if(empty($data['purchase_price'])){
		 * YDLib::output(ErrnoStatus::STATUS_40154);
		 * }
		 */
		$data ['purchase_price'] = $data ['purchase_price'] ? $data ['purchase_price'] : '0';
		
		if (empty ( $data ['on_card_time'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40163 );
		}
		if (empty ( $data ['loan_price'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40159 );
		}
		if (empty ( $data ['housing_img'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40160 );
		}
		$data ['housing_img'] = implode ( ',', $data ['housing_img'] );
		$data ['type'] = '2';
		$res = OnlinePawnModel::addData ( $data );
		if ($res == false) {
			YDLib::output ( ErrnoStatus::STATUS_60556 );
		}
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 民品抵押贷款接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 当前登录用户ID 【必填】
	 * name ：民品种类 (三级分类id) 【必填】
	 * housing_area ：民品品牌 （品牌id） 【必填】
	 * product_note：商品描述 【必填】
	 * parts_note：配件描述 多选 多个用数组 像图片那样 1:发票，2：证书，3：包装
	 * purchase_price：购买价格 【必填】
	 * loan_price：借款金额 【必填】
	 * mobile ：联系方式 【必填】
	 * housing_img ：数组格式的图片url 【必填】
	 * 例如：housing_img[0] : XXXXXXXX.jpg
	 * housing_img[1] : AAAAAAAA.jpg
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Onlinepawn/civilMortgage
	 * 测试： http://testapi.qudiandang.com/v1/Onlinepawn/civilMortgage
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
	public function civilMortgageAction() {
		$data = [ ];
		$data ['user_id'] = $this->user_id;
		$data ['name'] = $this->_request->get ( 'name' );
		$data ['housing_area'] = $this->_request->get ( 'housing_area' );
		$data ['product_note'] = $this->_request->get ( 'product_note' );
		$data ['purchase_price'] = trim ( $this->_request->get ( 'purchase_price' ) );
		$data ['parts_note'] = $this->_request->get ( 'parts_note' );
		$data ['loan_price'] = $this->_request->get ( 'loan_price' );
		$data ['mobile'] = $this->_request->get ( 'mobile' );
		$data ['housing_img'] = $this->_request->get ( 'housing_img' );
		
		if (empty ( $data ['mobile'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40001 );
		}
		
		if (! isset ( $data ['user_id'] ) || ! is_numeric ( $data ['user_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		if (empty ( $data ['name'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40161 );
		}
		if (empty ( $data ['housing_area'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40165 );
		}
		if (empty ( $data ['product_note'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40164 );
		}
		/*
		 * if(empty($data['purchase_price'])){
		 * YDLib::output(ErrnoStatus::STATUS_40154);
		 * }
		 */
		$data ['purchase_price'] = $data ['purchase_price'] ? $data ['purchase_price'] : '0';
		if (empty ( $data ['loan_price'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40159 );
		}
		if (empty ( $data ['housing_img'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40160 );
		}
		$data ['housing_img'] = implode ( ',', $data ['housing_img'] );
		$data ['parts_note'] = implode ( ',', $data ['parts_note'] );
		$data ['type'] = '3';
		$res = OnlinePawnModel::addData ( $data );
		if ($res == false) {
			YDLib::output ( ErrnoStatus::STATUS_60557 );
		}
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
}
