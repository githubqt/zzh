<?php
/***
 * 
 * 多网点
 * 
 */
use Custom\YDLib;
use Multipoint\MultipointModel;
use Product\ProductModel;
class MultipointController extends BaseController {
	
	/**
	 * *
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Multipoint/list
	 * 测试： http://testapi.qudiandang.com/v1/Multipoint/list
	 *
	 *
	 * "errno": "0",
	 * "errmsg": "请求成功",
	 * "result":
	 */
	public function listAction() {
		$id = $this->getRequest ()->getPost ( "id" );
		
		$list = MultipointModel::getList ( $id );
		
		foreach ( $list as $key => $val ) {
			$list [$key] ['list'] = MultipointModel::getProvinceAll ( $val ['province_id'] );
			
			foreach ( $list [$key] ['list'] as $k => $v ) {
				$list [$key] ['list'] [$k] ['data'] = MultipointModel::geCompleteAll ( $v ['area_id'] );
			}
		}
		
		if ($list) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $list );
		}
	}
	
	/**
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Multipoint/content
	 * 测试： http://testapi.qudiandang.com/v1/Multipoint/content
	 */
	public function contentAction() {
		$id = $this->getRequest ()->getPost ( "id" );
		
		$list = MultipointModel::getList ( $id );
		
		if (is_array ( $list ) && count ( $list ) > 0) {
			foreach ( $list as $key => $val ) {
				$list [$key] ['list'] = MultipointModel::getProvinceAll ( $val ['province_id'] );
				foreach ( $list [$key] ['list'] as $k => $v ) {
					$list [$key] ['list'] [$k] ['data'] = MultipointModel::geCompleteAll ( $v ['area_id'] );
				}
			}	
			
			if ($list) {
				YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $list );
			}
		}
	}
	
	/**
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Multipoint/address
	 * 测试： http://testapi.qudiandang.com/v1/Multipoint/address
	 */
	public function addressAction() {
		$id = $this->getRequest ()->getPost ( "id" );
		
		if (empty ( $id )) {
			YDLib::output ( ErrnoStatus::STATUS_40103 );
		}
		$info = MultipointModel::getInfoByID ( $id, 0, 0 );
		
		if ($info) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $info );
		}
	}
}