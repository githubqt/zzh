<?php
use Category\CategoryModel;
use Product\ProductModel;
use Custom\YDLib;
use Common\CommonBase;
/**
 * 分类管理
 * 
 * @version v0.01
 * @author zhaoyu
 *         @time 2018-05-08
 */
class CategoryController extends BaseController {
	
	/**
	 * 获取品类
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Category/list
	 * 测试： http://testapi.qudiandang.com/v1/Category/list
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * pid　：int 父级ID 非必填 【空：获取所有分类，0：获取一级分类，其他：获取子分类】
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
	 *         "id": 150,
	 *         "name": "NB",
	 *         "logo_url": null,
	 *         "parent_id": 146,
	 *         "first_letter": "N",
	 *         "children": "",
	 *         "root_id": 3,
	 *         "sort": 0,
	 *         "description": null,
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
	public function listAction() {
		$pid = $this->getRequest ()->getPost ( "pid" );
		$pid = isset ( $pid ) ? $pid : '';
		if (! is_numeric ( $pid )) {
			$data = Category\CategoryModel::getAllList ();
		} else {
			$data = Category\CategoryModel::getChild ( $pid );
		}
		if (is_array ( $data )) {
			foreach ( $data as $key => $value ) {
				if (! empty ( $value ['logo_url'] )) {
					$data [$key] ['logo_url'] = HOST_FILE . CommonBase::imgSize ( $value ['logo_url'], 3 );
				} else {
					$data [$key] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
				}
				
			    if ($value['name'] == '金银首饰' || $value['name'] == '珠宝玉石' || $value['name'] == '精品钻石') {
			        unset($data[$key]);
			    }
			    
			}
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
	
	/**
	 * 通过一级分类获取二级三级子分类
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Category/child
	 * 测试： http://testapi.qudiandang.com/v1/Category/child
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * id　：int 一级分类ID 必填
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": 0,
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         "id": 1,
	 *         "name": "奶粉",
	 *         "logo_url": "http://file.qudiandang.com//upload/xgbb/erpcategory/2017/04/25/65198002a97f6d0059924e222a4c1958_561.jpg",
	 *         "parent_id": 0,
	 *         "first_letter": "",
	 *         "children": "20,21,22,23,24,25,790,791",
	 *         "root_id": 0,
	 *         "sort": 0,
	 *         "description": null,
	 *         "child": [
	 *         {
	 *         "id": 20,
	 *         "name": "牛奶粉",
	 *         "logo_url": "/upload/xgbb/erpcategory/2017/04/25/f1483a99edd630de6b45dbd5c6259c77_431.jpg",
	 *         "parent_id": 1,
	 *         "first_letter": "",
	 *         "children": "",
	 *         "root_id": 1,
	 *         "sort": 0,
	 *         "description": null,
	 *         "child": [
	 *         {
	 *         "id": 26,
	 *         "name": "一段",
	 *         "logo_url": "http://file.qudiandang.com//upload/xgbb/erpcategory/2017/04/25/65198002a97f6d0059924e222a4c1958_337.jpg",
	 *         "parent_id": 20,
	 *         "first_letter": "Y",
	 *         "children": "",
	 *         "root_id": 1,
	 *         "sort": 0,
	 *         "description": null,
	 *         },
	 *         {
	 *         "id": 27,
	 *         "name": "二段",
	 *         "logo_url": "http://file.qudiandang.com//upload/xgbb/erpcategory/2017/04/25/65198002a97f6d0059924e222a4c1958_740.jpg",
	 *         "parent_id": 20,
	 *         "first_letter": "E",
	 *         "children": "",
	 *         "root_id": 1,
	 *         "sort": 0,
	 *         "description": null,
	 *         }
	 *         ]
	 *         },
	 *         {
	 *         "id": 21,
	 *         "name": "羊奶粉",
	 *         "logo_url": "/upload/xgbb/erpcategory/2017/04/25/f1483a99edd630de6b45dbd5c6259c77_135.jpg",
	 *         "parent_id": 1,
	 *         "first_letter": "",
	 *         "children": "",
	 *         "root_id": 1,
	 *         "sort": 0,
	 *         "description": null,
	 *         "child": [
	 *         {
	 *         "id": 31,
	 *         "name": "一段",
	 *         "logo_url": "http://file.qudiandang.com//upload/xgbb/erpcategory/2017/04/25/65198002a97f6d0059924e222a4c1958_337.jpg",
	 *         "parent_id": 21,
	 *         "first_letter": "Y",
	 *         "children": "",
	 *         "root_id": 1,
	 *         "sort": 0,
	 *         "description": null,
	 *         }
	 *         ]
	 *         }
	 *         ]
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "60002",
	 *         "errmsg": "用户已存在"
	 *         }
	 *         </pre>
	 */
	public function childAction() {
		$id = $this->getRequest ()->getPost ( "id" );
		
		if (! isset ( $id ) || ! is_numeric ( $id ) || $id <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_40099 );
		}
		
		$deatil = Category\CategoryModel::getTopInfoBYID ( $id );
		if (! $deatil) {
			YDLib::output ( ErrnoStatus::STATUS_40100 );
		}
		
		if (! empty ( $deatil ['logo_url'] )) {
			$deatil ['logo_url'] = HOST_FILE . CommonBase::imgSize ( $deatil ['logo_url'], 3 );
		} else {
			$deatil ['logo_url'] = HOST_STATIC . 'common/images/common.png';
		}
		
		$deatil ['child'] = Category\CategoryModel::getChild ( $id );
		if (is_array ( $deatil ['child'] ) && count ( $deatil ['child'] ) > 0) {
			foreach ( $deatil ['child'] as $key => $value ) {
				if (! empty ( $value ['logo_url'] )) {
					$deatil ['child'] [$key] ['logo_url'] = HOST_FILE . CommonBase::imgSize ( $value ['logo_url'], 1 );
				} else {
					$deatil ['child'] [$key] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
				}
				$deatil ['child'] [$key] ['child'] = Category\CategoryModel::getChild ( $value ['id'] );
				if (is_array ( $deatil ['child'] [$key] ['child'] ) && count ( $deatil ['child'] [$key] ['child'] ) > 0) {
					foreach ( $deatil ['child'] [$key] ['child'] as $k => $v ) {
						if (! empty ( $v ['logo_url'] )) {
							$deatil ['child'] [$key] ['child'] [$k] ['logo_url'] = HOST_FILE . CommonBase::imgSize ( $v ['logo_url'], 1 );
						} else {
							$deatil ['child'] [$key] ['child'] [$k] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
						}
					}
				}
			}
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $deatil );
	}
}
