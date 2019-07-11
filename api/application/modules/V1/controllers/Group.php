<?php
use Custom\YDLib;
use Common\CommonBase;
use Seckill\SeckillModel;
use Seckill\SeckillLogModel;
use Seckill\SeckillProductModel;
use Product\ProductModel;
use Brand\BrandModel;
use User\UserModel;
use Category\CategoryModel;

/**
 * 拼团管理
 * 
 * @version v0.20
 * @author lqt
 *         @time 2018-07-23
 */
class GroupController extends BaseController {
	
	/**
	 * 拼团列表
	 *
	 * <pre>
	 * POST参数
	 * page: 页码 非必填 【空：1】
	 * rows: 条数 非必填 【空：10】
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Group/list
	 * 测试： http://testapi.qudiandang.com/v1/Group/list
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": 0,
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": 50014,
	 *         "errmsg": "库存不足",
	 *         "result": {
	 *         }
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
		
		$order = $this->_request->getPost ( 'order' );
		
		$info ['order'] = $order;
		$info ['type'] = 4;
		$list = SeckillProductModel::getList ( $info, $page - 1, $rows );
		$data ['page'] = $page;
		if ($list == false) {
			$data ['total'] = 0;
			$data ['list'] = [ ];
		} else {
			foreach ( $list ['list'] as &$val ) {
				if (strtotime ( $val ['starttime'] ) <= time () && strtotime ( $val ['endtime'] ) >= time ()) {
					$val ['status_txt'] = '进行中';
					$val ['status'] = '6';
				} else if (strtotime ( $val ['starttime'] ) >= time () && strtotime ( $val ['endtime'] ) >= time ()) {
					$val ['status_txt'] = '预告中';
					$val ['status'] = '5';
				} else if (strtotime ( $val ['starttime'] ) <= time () && strtotime ( $val ['endtime'] ) <= time ()) {
					$val ['status_txt'] = '已结束';
					$val ['status'] = '7';
				}
				if (! empty ( $val ['logo_url'] )) {
					$val ['logo_url'] = HOST_FILE . CommonBase::imgSize ( $val ['logo_url'], 2 );
				} else {
					$val ['logo_url'] = HOST_STATIC . 'common/images/common.png';
				}
			}
			$data ['total'] = $list ['total'];
			$data ['list'] = $list ['list'];
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
	
	/**
	 * 获取拼团商品详情接口
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Group/detail
	 * 测试： http://testapi.qudiandang.com/v1/Group/detail
	 * </pre>
	 *
	 * <pre>
	 * 参数：
	 * id: 拼团商品ID 必填
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
	 *         "id": "7",
	 *         "seckill_id": "69",
	 *         "product_id": "29",
	 *         "product_name": "【520礼物】爱马仕（HERMES） 香水女士男士淡香水持久香氛 大地男士淡香水50ml香水女士男士淡香水持久香氛 大地男士淡",
	 *         "supplier_id": "10001",
	 *         "sale_price": "4000.00",
	 *         "group_price": "4000.00",
	 *         "oredr_product_num": "0",
	 *         "logo_url": "http://file.qudiandang.com//upload/product/2018/06/29/d9737c18cda5369051f6b5fb0ab2b5f9_995_500x500.jpeg",
	 *         "introduction": "<img src=\"http://file.qudiandang.com/upload/image/2018/06/27/20180627105104_96550.jpg\" alt=\"\" />",
	 *         "stock": "998",
	 *         "brand_id": "1",
	 *         "starttime": "2018-07-23 20:38:01",
	 *         "endtime": "2018-07-28 20:38:02",
	 *         "number": "2",
	 *         "status": "6",
	 *         "is_restrictions": "2",
	 *         "restrictions_num": "2",
	 *         "brand_name": "品牌1",
	 *         "status_txt": "进行中",
	 *         "imglist": [
	 *         {
	 *         "id": "150",
	 *         "obj_id": "29",
	 *         "type": "product",
	 *         "img_url": "http://file.qudiandang.com//upload/product/2018/06/27/95d75fb1f10008115451ea53b691668a_233_560x560.jpg",
	 *         "img_type": "jpg"
	 *         }
	 *         ],
	 *         "attribute": {
	 *         "2": {
	 *         "attribute_id": "2",
	 *         "attribute_name": "规格",
	 *         "input_type": "1",
	 *         "attribute_value_id": "26",
	 *         "attribute_value_name": "ccc"
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
		if (empty ( $id )) {
			YDLib::output ( ErrnoStatus::STATUS_40103 );
		}
		
		$detail = SeckillProductModel::getInfoByID ( $id );
		if (! $detail) {
			YDLib::output ( ErrnoStatus::STATUS_60570 );
		}
		$brand = BrandModel::getInfoByID ( $detail ['brand_id'] );
		$detail ['brand_name'] = $brand['name'];
        $detail ['brand_description'] = !empty($brand['description'])?$brand['description']:'';

        $threeInfo = CategoryModel::getInfoByID ( $detail ['category_id'] );
        $detail ['category_description'] = !empty($threeInfo['description'])?$threeInfo['description']:'';

		if (strtotime ( $detail ['starttime'] ) <= time () && strtotime ( $detail ['endtime'] ) >= time ()) {
			$detail ['status_txt'] = '进行中';
			$detail ['status'] = '6';
		} else if (strtotime ( $detail ['starttime'] ) >= time () && strtotime ( $detail ['endtime'] ) >= time ()) {
			$detail ['status_txt'] = '预告中';
			$detail ['status'] = '5';
		} else if (strtotime ( $detail ['starttime'] ) <= time () && strtotime ( $detail ['endtime'] ) <= time ()) {
			$detail ['status_txt'] = '已结束';
			$detail ['status'] = '7';
		}
		if (! empty ( $detail ['logo_url'] )) {
			$detail ['logo_url'] = HOST_FILE . CommonBase::imgSize ( $detail ['logo_url'], 2 );
		} else {
			$detail ['logo_url'] = HOST_STATIC . 'common/images/common.png';
		}
		
		$attribute = ProductModel::getAttributeByID ( $detail ['product_id'] );
		$detail ['imglist'] = $attribute ['imglist'];
		$detail ['attribute'] = $attribute ['attribute'];
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $detail, FALSE );
	}
	
	/**
	 * 拼团待成团团购列表
	 *
	 * <pre>
	 * POST参数
	 * page: 页码 非必填 【空：1】
	 * rows: 条数 非必填 【空：10】
	 * id: 拼团id 必填 seckill_product表id
	 * user_id: 会员id 非必填 【判断是否已参加过该团】
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Group/grouplist
	 * 测试： http://testapi.qudiandang.com/v1/Group/grouplist
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": 0,
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": 50014,
	 *         "errmsg": "库存不足",
	 *         "result": {
	 *         }
	 *         }
	 *         </pre>
	 */
	public function grouplistAction() {
		$page = $this->_request->getPost ( 'page' );
		$page = ! empty ( $page ) ? intval ( $page ) : 1;
		$page = $page > 0 ? $page : 1;
		
		$rows = $this->_request->getPost ( 'rows' );
		$rows = ! empty ( $rows ) ? intval ( $rows ) : 10;
		$rows = $rows > 0 ? $rows : 10;
		
		$id = $this->_request->getPost ( 'id' );
		if (empty ( $id )) {
			YDLib::output ( ErrnoStatus::STATUS_40103 );
		}
		
		$user_id = $this->_request->getPost ('user_id');
		
		$detail = SeckillProductModel::getInfoByID ( $id );
		
		if (! $detail) {
			YDLib::output ( ErrnoStatus::STATUS_60570 );
		}
		
		if ($detail['endtime'] <= date("Y-m-d H:i:s") ) {
			YDLib::output ( ErrnoStatus::STATUS_60529 );
		}
		
		if($detail['endtime'] >= date("Y-m-d H:i:s")){
				$product = SeckillModel::getInfoByID ( $detail ['seckill_id'] );
			// 更新围观人数
				$data ['onlookers_num'] = intval ( $product ['onlookers_num'] + 1 );
				$onlookers_num = SeckillModel::updateByID ( $data, $product ['id'] );
		}
		
		$info ['seckill_product_id'] = $id; // 关联活动id
		$list = SeckillLogModel::getGroupList ( $info, $page - 1, $rows );
		
		$data ['page'] = $page;
		if ($list == false) {
			$data ['total'] = 0;
			$data ['list'] = [ ];
		} else {
			foreach ( $list ['list'] as &$val ) {
				$user = UserModel::getAdminInfo ( $val ['user_id'] );
				if (! empty ( $user ['user_img'] )) {
					$val ['user_img'] = HOST_FILE . CommonBase::imgSize ( $user ['user_img'], 1 );
				} else {
					$val ['user_img'] = HOST_STATIC . 'common/images/user_photo.jpg';
				}
				if (! empty ( $user ['name'] )) {
					$val ['name'] = $user ['name'];
				} else {
					$val ['name'] = substr_replace ( $user ['mobile'], '***', 3, 4 );
				}
				
				//拼团剩余时间要与活动结束时间对比
				
				if (strtotime($val['created_at'])+24*60*60 < strtotime($detail['endtime'])) {
					$val ['dump_time'] = strtotime ( $val ['created_at'] ) + (24 * 60 * 60) - time ();
				} else {
					$val ['dump_time'] = strtotime ( $detail['endtime'] ) - time ();
					if ($val ['dump_time'] < 0) {
						$val ['dump_time'] = 0;
					}
				} 
				
				
				// 判断是否是已经参与过的拼团
				$val ['canyu'] = FALSE;
				if (isset ( $user_id ) && ! empty ( $user_id )) {
					$canyuInfo = SeckillLogModel::getCanyuInfo ( $user_id, $val ['id'] );
					if ($canyuInfo) {
						$val ['canyu'] = TRUE; // 已参与过
					}
				}
			}
			$data ['total'] = $list ['total'];
			$data ['list'] = $list ['list'];
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
	
	/**
	 * 拼团待成团团购详情
	 *
	 * <pre>
	 * POST参数
	 * id: 团长id 必填 seckill_log表id
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Group/groupdetail
	 * 测试： http://testapi.qudiandang.com/v1/Group/groupdetail
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": 0,
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": 50014,
	 *         "errmsg": "库存不足",
	 *         "result": {
	 *         }
	 *         }
	 *         </pre>
	 */
	public function groupdetailAction() {
		$id = $this->_request->getPost ( 'id' );
		if (empty ( $id )) {
			YDLib::output ( ErrnoStatus::STATUS_40167 );
		}
		
		$detail = SeckillLogModel::getInfoByID ( $id );
		if (! $detail) {
			YDLib::output ( ErrnoStatus::STATUS_40168 );
		}
		
		$seckill_info = SeckillProductModel::getInfoByID ( $detail['seckill_product_id'] );
		if ($seckill_info['endtime'] <= date("Y-m-d H:i:s") ) {
			YDLib::output ( ErrnoStatus::STATUS_60529 );
		}		
		
		$user = UserModel::getAdminInfo ( $detail ['user_id'] );
		if (! empty ( $user ['user_img'] )) {
			$detail ['user_img'] = HOST_FILE . CommonBase::imgSize ( $user ['user_img'], 1 );
		} else {
			$detail ['user_img'] = HOST_STATIC . 'common/images/user_photo.jpg';
		}
		if (! empty ( $user ['name'] )) {
			$detail ['name'] = $user ['name'];
		} else {
			$detail ['name'] = substr_replace ( $user ['mobile'], '***', 3, 4 );
		}
		
		//拼团剩余时间要与活动结束时间对比
		
		if (strtotime($detail['created_at'])+24*60*60 < strtotime($seckill_info['endtime'])) {
			$detail ['dump_time'] = strtotime ( $detail ['created_at'] ) + (24 * 60 * 60) - time ();
		} else {
			$detail ['dump_time'] = strtotime ( $seckill_info['endtime'] ) - time ();
			if ($detail ['dump_time'] < 0) {
				$detail ['dump_time'] = 0;
			}
		} 		
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $detail );
	}
	
	/**
	 * 拼团待成团团购团员信息
	 *
	 * <pre>
	 * POST参数
	 * id: 团长id 必填 seckill_log表id
	 * user_id: 会员id 非必填 【判断是否已参加过该团】
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Group/grouprivplist
	 * 测试： http://testapi.qudiandang.com/v1/Group/grouprivplist
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": 0,
	 *         "errmsg": "请求成功",
	 *         "result": {
	 *         }
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": 50014,
	 *         "errmsg": "库存不足",
	 *         "result": {
	 *         }
	 *         }
	 *         </pre>
	 */
	public function grouprivplistAction() {
		$id = $this->_request->getPost ( 'id' );
		if (empty ( $id )) {
			YDLib::output ( ErrnoStatus::STATUS_40167 );
		}
		
		 $user_id = $this->_request->getPost ('user_id');;
		
		$detail = SeckillLogModel::getInfoByID ( $id );
		if (! $detail) {
			YDLib::output ( ErrnoStatus::STATUS_40168 );
		}
		
		$seckill_info = SeckillProductModel::getInfoByID ( $detail['seckill_product_id'] );
		if ($seckill_info['endtime'] <= date("Y-m-d H:i:s") ) {
			YDLib::output ( ErrnoStatus::STATUS_60529 );
		}			
		
		$info ['tuan_id'] = $id; // 关联活动id
		$info ['order_status'] = '2'; // 已支付
		$list = SeckillLogModel::getGroupPrivList ( $info );
		if (! $list) {
			YDLib::output ( ErrnoStatus::STATUS_40168 );
		}
		foreach ( $list as &$val ) {
			$user = UserModel::getAdminInfo ( $val ['user_id'] );
			if (! empty ( $user ['user_img'] )) {
				$val ['user_img'] = HOST_FILE . CommonBase::imgSize ( $user ['user_img'], 1 );
			} else {
				$val ['user_img'] = HOST_STATIC . 'common/images/user_photo.jpg';
			}
			if (! empty ( $user ['name'] )) {
				$val ['name'] = $user ['name'];
			} else {
				$val ['name'] = substr_replace ( $user ['mobile'], '***', 3, 4 );
			}
			
			//拼团剩余时间要与活动结束时间对比
			
			if (strtotime($val['created_at'])+24*60*60 < strtotime($seckill_info['endtime'])) {
				$val ['dump_time'] = strtotime ( $val ['created_at'] ) + (24 * 60 * 60) - time ();
			} else {
				$val ['dump_time'] = strtotime ( $seckill_info['endtime'] ) - time ();
				if ($val ['dump_time'] < 0) {
					$val ['dump_time'] = 0;
				}
			} 				
			
			if ($val ['tuan_type'] == '1') {
				$val ['tuan_type_txt'] = '拼主';
			} else if ($val ['tuan_type'] == '2') {
				$val ['tuan_type_txt'] = '团员';
			}
			if ($val ['order_status'] == '1') {
				$val ['order_status_txt'] = '待支付';
			} else if ($val ['order_status'] == '2') {
				$val ['order_status_txt'] = '已支付';
			} else if ($val ['order_status'] == '3') {
				$val ['order_status_txt'] = '已退款';
			}
		}
		
		// 该团信息
		$priv = $list [0];
		
		// 判断是否是已经参与过的拼团
		$priv ['canyu'] = FALSE;
		if (isset ( $user_id ) && ! empty ( $user_id )) {
			$canyuInfo = SeckillLogModel::getCanyuInfo ( $user_id, $priv ['id'] );
			if ($canyuInfo) {
				$priv ['canyu'] = TRUE; // 已参与过
			}
		}
		
		// 是否可以购买
		$priv ['canbuy'] = FALSE;
		if ($priv ['status'] == '1' && $priv ['order_status'] == '2' && $priv ['dump_num'] > 0 && $priv ['dump_time'] > 0 && ! $priv ['canyu']) {
			$priv ['canbuy'] = TRUE;
		}
		
		$data ['list'] = $list;
		$data ['priv'] = $priv;
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
}
