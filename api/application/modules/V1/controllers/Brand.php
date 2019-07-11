<?php
use Brand\BrandModel;
use Product\ProductModel;
use Custom\YDLib;
use Common\CommonBase;
/**
 * 品牌管理
 * 
 * @version v0.01
 * @author zhaoyu
 *         @time 2018-05-08
 */
class BrandController extends BaseController {
	
	/**
	 * 获取品牌
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Brand/list
	 * 测试： http://testapi.qudiandang.com/v1/Brand/list
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * is_hit　：int 是否推荐 非必填 【空：全部品牌，1：否，2：是】
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
	 *         "id": 1,
	 *         "name": "aaa",
	 *         "en_name": "fdaf",
	 *         "alias_name": "fdsaf",
	 *         "first_letter": "A",
	 *         "logo_url": "/upload/brand/2018/05/08/0476071e3f36d6dd43b618f2f840ae3b_588.jpg",
	 *         "description": "fdasfdsa",
	 *         "status": 2,
	 *         "is_hit": 2,
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
		$is_hit = $this->getRequest ()->getPost ( "is_hit" );
		$is_hit = isset ( $is_hit ) && is_numeric ( $is_hit ) ? $is_hit : '';
		$data = BrandModel::getBrandList ( array (
				'is_hit' => $is_hit 
		) );

		$newData = [];

		$i = 0;

		if (is_array ( $data )) {
			foreach ( $data as $key => $value ) {
				if (! empty ( $value ['logo_url'] )) {
					$data [$key] ['logo_url'] = HOST_FILE . CommonBase::imgSize ( $value ['logo_url'], 3 );
				} else {
					$data [$key] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
				}
				//该品牌商品数
                $selfProductNum = BrandModel::getProductNum($value ['id']);
                $channelProductNum = BrandModel::getChannelProductNum($value ['id']);
                $data [$key] ['product_num'] = $selfProductNum + $channelProductNum;
                $data [$key] ['supplier_id'] = SUPPLIER_ID;
                if ($data [$key] ['product_num']){
                    $newData[$i] = $data [$key] ;
                    $i ++;
                }
			}
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $newData );
	}
}
