<?php
use Custom\YDLib;
use User\UserAddressModel;

/**
 * 收货地址管理
 * 
 * @version v0.01
 * @author zhaoyu
 *         @time 2018-05-14
 */
class AddressController extends BaseController {
	
	/**
	 * 获得地址列表
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
	 * 正式： http://api.qudiandang.com/v1/address/list
	 * 测试： http://testapi.qudiandang.com/v1/address/list
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
	 *         "row": 2,//------------------总数
	 *         "list": [
	 *         {
	 *         "id": 1,//--------------------------------地址id
	 *         "name": "赖小清",//-----------------------收货人名字
	 *         "mobile": 15811137696,//------------------收货人手机号
	 *         "province": "山东",//---------------------省
	 *         "city": "菏泽市",//-----------------------市
	 *         "area": "鄄城县",//-----------------------县（区）
	 *         "street": "闫什镇",//---------------------乡镇
	 *         "address": "长楹天街",//------------------详细地址
	 *         "user_id": 2,//--------------------------用户id
	 *         "is_default": 2//------------------------是否默认 2:默认 1:正常
	 *         },
	 *         ...
	 *         ],
	 *         "page": 1
	 *         ]
	 *        
	 *        
	 *        
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
		$data = [ ];
		$data ['user_id'] = $this->user_id;
		
		$page = $this->_request->getPost ( 'page' );
		$page = ! is_numeric ( $page ) || $page < 1 ? 1 : $page;
		
		$rows = $this->_request->get ( 'rows' );
		$rows = ! is_numeric ( $rows ) || $rows < 1 ? 10 : $rows;
		
		if (! isset ( $data ['user_id'] ) || ! is_numeric ( $data ['user_id'] )) {
			
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		if (! isset ( $rows ) || ! is_numeric ( $rows )) {
			YDLib::output ( ErrnoStatus::STATUS_40095 );
		}
		
		if (! isset ( $page ) || ! is_numeric ( $page )) {
			YDLib::output ( ErrnoStatus::STATUS_40096 );
		}
		
		$data = UserAddressModel::getList ( $data, $page, $rows );
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
	
	/**
	 * 添加收货地址接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户ID [必填参数]
	 * name : 收货人 [必填参数]
	 * mobile : 手机号 [必填参数]
	 * province : 省份ID [必填参数]
	 * city ： 城市ID [必填参数]
	 * area ： 区域ID [必填参数]
	 * street ： 街道ID [必填参数]
	 * address ： 详细地址 [必填参数]
	 * is_default ： 是否默认 2是 1否 [必填参数]
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/address/add
	 * 测试： http://testapi.qudiandang.com/v1/address/add
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
		$data ['mobile'] = $this->_request->getPost ( 'mobile' );
		$data ['user_id'] = $this->user_id;
		$data ['name'] = $this->_request->getPost ( 'name' );
		$data ['province'] = $this->_request->getPost ( 'province' );
		$data ['city'] = $this->_request->getPost ( 'city' );
		$data ['area'] = $this->_request->getPost ( 'area' );
		$data ['street'] = $this->_request->getPost ( 'street' );
		$data ['address'] = $this->_request->getPost ( 'address' );
		$data ['is_default'] = $this->_request->getPost ( 'is_default' );
		
		if (! in_array ( $data ['is_default'], [ 
				1,
				2 
		] )) {
			YDLib::output ( ErrnoStatus::STATUS_40097 );
		}
		
		if (empty ( $data ['name'] ) || ! YDLib::validData ( $data ['name'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40051 );
		}
		
		if (empty ( $data ['province'] ) || ! is_array ( AreaModel::getInfoByID ( $data ['province'] ) )) {
			YDLib::output ( ErrnoStatus::STATUS_40053 );
		}
		
		if (empty ( $data ['city'] ) || ! is_array ( AreaModel::getInfoByID ( $data ['city'] ) )) {
			YDLib::output ( ErrnoStatus::STATUS_40054 );
		}
		
		if (empty ( $data ['area'] ) || ! is_array ( AreaModel::getInfoByID ( $data ['area'] ) )) {
			YDLib::output ( ErrnoStatus::STATUS_40055 );
		}
		
		if (empty ( $data ['street'] ) || ! is_array ( AreaModel::getInfoByID ( $data ['street'] ) )) {
			YDLib::output ( ErrnoStatus::STATUS_40056 );
		}
		
		if (empty ( $data ['address'] ) || ! YDLib::validData ( $data ['address'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40056 );
		}
		
		if ($data ['is_default'] == 2) {
			$res = UserAddressModel::setNoDefault ( $data ['user_id'] );
		}
		
		$lastID = UserAddressModel::addAddress ( $data );
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $lastID );
	}
	
	/**
	 * 获得默认地址
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户ID [必填]
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/address/getDefault
	 * 测试： http://testapi.qudiandang.com/v1/address/getDefault
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
	 *         "id":1,
	 *         "name":"zhaoyu",
	 *         "mobile":13381090057,
	 *         "province":1,
	 *         "city":1,
	 *         "area":1,
	 *         "street":1,
	 *         "address":12121,
	 *         "supplier_id":10001,
	 *         "user_id":1,
	 *         "is_default":2,
	 *         "province_txt":"",
	 *         "city_txt":"",
	 *         "area_txt":"",
	 *         "street_txt":""
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
	public function getDefaultAction() {
		$user_id = $this->user_id;
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		$data = UserAddressModel::getDefault ( $user_id );
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
	
	/**
	 * 获得地址详情
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户ID [必填]
	 * address_id : 地址ID [必填]
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/address/addressInfo
	 * 测试： http://testapi.qudiandang.com/v1/address/addressInfo
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
	 *         "id":1,
	 *         "name":"zhaoyu",
	 *         "mobile":13381090057,
	 *         "province":1,
	 *         "city":1,
	 *         "area":1,
	 *         "street":1,
	 *         "address":12121,
	 *         "supplier_id":10001,
	 *         "user_id":1,
	 *         "is_default":2,
	 *         "province_txt":"",
	 *         "city_txt":"",
	 *         "area_txt":"",
	 *         "street_txt":""
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
	public function addressInfoAction() {
		$user_id = $this->user_id;
		$address_id = $this->_request->getPost ( 'address_id' );
		
		if (empty ( $user_id ) || ! is_numeric ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		if (empty ( $address_id ) || ! is_numeric ( $address_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40016 );
		}
		
		$data = UserAddressModel::getInfoByID ( $address_id, $user_id );
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
	
	/**
	 * 编辑地址
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户ID [必填参数]
	 * address_id : 地址ID [必填参数]
	 * name : 收货人 [必填参数]
	 * mobile : 手机号 [必填参数]
	 * province : 省份ID [必填参数]
	 * city ： 城市ID [必填参数]
	 * area ： 区域ID [必填参数]
	 * street ： 街道ID [必填参数]
	 * address ： 详细地址 [必填参数]
	 * is_default ： 是否默认 2是 1否 [必填参数]
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/address/updateInfo
	 * 测试： http://testapi.qudiandang.com/v1/address/updateInfo
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
	public function updateInfoAction() {
		$user_id = $this->user_id;
		$address_id = $this->_request->getPost ( 'address_id' );
		$data = [ ];
		$data ['mobile'] = $this->_request->getPost ( 'mobile' );
		$data ['name'] = $this->_request->getPost ( 'name' );
		$data ['province'] = $this->_request->getPost ( 'province' );
		$data ['city'] = $this->_request->getPost ( 'city' );
		$data ['area'] = $this->_request->getPost ( 'area' );
		$data ['street'] = $this->_request->getPost ( 'street' );
		$data ['address'] = $this->_request->getPost ( 'address' );
		$data ['is_default'] = $this->_request->getPost ( 'is_default' );
		
		if (empty ( $user_id ) || ! is_numeric ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		if (empty ( $address_id ) || ! is_numeric ( $address_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40016 );
		}
		
		if (! in_array ( $data ['is_default'], [ 
				1,
				2 
		] )) {
			YDLib::output ( ErrnoStatus::STATUS_40097 );
		}
		
		if (empty ( $data ['name'] ) || ! YDLib::validData ( $data ['name'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40051 );
		}
		
		if (empty ( $data ['province'] ) || ! is_array ( AreaModel::getInfoByID ( $data ['province'] ) )) {
			YDLib::output ( ErrnoStatus::STATUS_40053 );
		}
		
		if (empty ( $data ['city'] ) || ! is_array ( AreaModel::getInfoByID ( $data ['city'] ) )) {
			YDLib::output ( ErrnoStatus::STATUS_40054 );
		}
		
		if (empty ( $data ['area'] ) || ! is_array ( AreaModel::getInfoByID ( $data ['area'] ) )) {
			YDLib::output ( ErrnoStatus::STATUS_40055 );
		}
		
		if (empty ( $data ['street'] ) || ! is_array ( AreaModel::getInfoByID ( $data ['street'] ) )) {
			YDLib::output ( ErrnoStatus::STATUS_40056 );
		}
		
		if (empty ( $data ['address'] ) || ! YDLib::validData ( $data ['address'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40056 );
		}
		
		if ($data ['is_default'] == 2) {
			$res = UserAddressModel::setNoDefault ( $user_id );
		}
		
		$res = UserAddressModel::updateByID ( $data, $user_id, $address_id );
		if ($res) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
		} else {
			YDLib::output ( ErrnoStatus::STATUS_FAIL );
		}
	}
	
	/**
	 * 设置默认地址
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户ID [必填参数]
	 * address_id : 地址ID [必填参数]
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/address/setDefault
	 * 测试： http://testapi.qudiandang.com/v1/address/setDefault
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
	public function setDefaultAction() {
		$user_id = $this->user_id;
		$address_id = $this->_request->getPost ( 'address_id' );
		
		if (empty ( $user_id ) || ! is_numeric ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		if (empty ( $address_id ) || ! is_numeric ( $address_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40016 );
		}
		
		$info = UserAddressModel::getInfoByID ( $address_id, $user_id );
		if (! $info) {
			YDLib::output ( ErrnoStatus::STATUS_60020 );
		}
		
		$res = UserAddressModel::setNoDefault ( $user_id );
		
		$data ['is_default'] = 2;
		$res = UserAddressModel::updateByID ( $data, $user_id, $address_id );
		if ($res) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
		} else {
			YDLib::output ( ErrnoStatus::STATUS_FAIL );
		}
	}
	
	/**
	 * 删除地址
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户ID [必填]
	 * address_id : 地址ID [必填]
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/address/delele
	 * 测试： http://testapi.qudiandang.com/v1/address/delele
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
	public function deleleAction() {
		$user_id = $this->user_id;
		$address_id = $this->_request->getPost ( 'address_id' );
		
		if (empty ( $user_id ) || ! is_numeric ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		if (empty ( $address_id ) || ! is_numeric ( $address_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40016 );
		}
		
		$res = UserAddressModel::deleteByID ( $user_id, $address_id );
		if ($res) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
		} else {
			YDLib::output ( ErrnoStatus::STATUS_FAIL );
		}
	}
}
