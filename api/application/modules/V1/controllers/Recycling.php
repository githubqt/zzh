<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少
// +----------------------------------------------------------------------
// | Author: 昌少 Date:2018/11/13 Time:11:41
// +----------------------------------------------------------------------
use Services\Recovery\RecoveryService;
use Supplier\AdminModel;
use Services\Recovery\RecoveryOrderService;
class RecyclingController extends BaseRecyclingController {
	
	/**
	 * 登录
	 */
	public function loginAction() {
		try {
			$supplier_id = $this->getPost ( 'supplier_id' );
			$username = $this->getPost ( 'username' );
			$password = $this->getPost ( 'password' );
			
			if (! $supplier_id) {
				throw new Exception ( ErrnoStatus::STATUS_70001 );
			}
			if (! $username) {
				throw new Exception ( ErrnoStatus::STATUS_70002 );
			}
			if (! $password) {
				throw new Exception ( ErrnoStatus::STATUS_70003 );
			}
			
			$user = AdminModel::getUser ( $supplier_id, $username );
			
			if (! $user) {
				throw new Exception ( ErrnoStatus::STATUS_70004 );
			}
			
			if (! AdminModel::login ( $supplier_id, $user, $password )) {
				throw new Exception ( ErrnoStatus::STATUS_70005 );
			}
			// 记录日志
			$num = \Admin\LoginLogModel::getLoginNum ( $user ['id'] );
			
			$rt = \Admin\LoginLogModel::addLogin ( [ 
					'admin_id' => $user ['id'],
					'login_at' => time (),
					'num' => $num + 1,
					'ip' => Publicb::GetIP () 
			] );
			
			if (! $rt) {
				throw new Exception ( ErrnoStatus::STATUS_70005 );
			}
			
			$token = Publicb::getAdminLoginToken ( $user ['id'], $supplier_id );
			$supplier = \Supplier\SupplierModel::getInfoByID ( $supplier_id );
			
			$admin = [ 
					'id' => $user ['id'],
					'fullname' => $user ['fullname'],
					'name' => $user ['name'],
					'supplier_id' => $user ['supplier_id'],
					'mobile' => $user ['mobile'],
					'supplier' => [ 
							'id' => $supplier ['id'],
							'domain' => $supplier ['domain'],
							'shop_name' => $supplier ['shop_name'],
							'company' => $supplier ['company'] 
					] 
			];
			$this->success ( [ 
					'admin' => $admin,
					'token' => $token 
			] );
		} catch ( Exception $exception ) {
			$this->error ( $exception->getMessage () );
		}
	}
	
	/**
	 * 退出
	 */
	public function logoutAction() {
		try {
			$user_id = $this->user_id;
			if ($user_id) {
				\Services\Auth\TokenAuthService::remove ( $user_id, $this->supplier_id, 'admin' );
			}
			$this->success ();
		} catch ( Exception $exception ) {
			$this->error ( $exception->getMessage () );
		}
	}
	
	/**
	 * 首页广告图
	 */
	public function slideAction() {
		$data = [ ];
		$data [] = [ 
				'id' => 1,
				'name' => '箱包',
				'english' => 'handbags',
				'cover' => '../static/imgs/bags/01.jpg' 
		];
		$this->success ( $data );
	}
	
	/**
	 * 首页分类图
	 */
	public function typeAction() {
		$data = [ ];
		$data [] = [ 
				'id' => 902,
				'name' => '箱包',
				'english' => 'handbags',
				'cover' => '../static/imgs/bags/bao.jpg' 
		];
		$this->success ( $data );
	}
	
	/**
	 * 获取品牌
	 */
	public function brandAction() {
		$brand = \Brand\BrandModel::getBrandList ();
		$data = [ ];
		foreach ( $brand as $k => $v ) {
			$data [$k] ['id'] = $v ['id'];
			$data [$k] ['name'] = $v ['name'];
			$data [$k] ['en_name'] = $v ['en_name'];
			$data [$k] ['alias_name'] = $v ['alias_name'];
			$data [$k] ['first_letter'] = $v ['first_letter'];
			$data [$k] ['logo_url'] = $v ['logo_url'];
		}
		unset ( $brand );
		$this->success ( $data );
	}
	
	/**
	 * 获取分类
	 */
	public function categoryAction() {
		try {
			$pid = $this->getQuery ( 'pid' );
			if ($pid === '' || ! is_numeric ( $pid )) {
				$this->error ( ErrnoStatus::STATUS_PARAMS_ERROR );
			}
			$category = \Category\CategoryModel::getChild ( $pid );
			$data = [ ];
			foreach ( $category as $k => $v ) {
				$data [$k] ['id'] = $v ['id'];
				$data [$k] ['name'] = $v ['name'];
				$data [$k] ['logo_url'] = $this->buildPath ( $v ['logo_url'], 1 );
				$data [$k] ['parent_id'] = $v ['parent_id'];
				$data [$k] ['first_letter'] = $v ['first_letter'];
				$data [$k] ['root_id'] = $v ['root_id'];
			}
			unset ( $category );
			$this->success ( $data );
		} catch ( Exception $exception ) {
			$this->error ( $exception->getMessage () );
		}
	}
	
	/**
	 * 创建回收单
	 */
	public function createAction() {
		$brand_id = $this->getPost ( 'brand_id' ); // 品牌ID
		$category_id = $this->getPost ( 'category_id' ); // 分类ID
		$material = $this->getPost ( 'material' ); // 材质
		$havetime = $this->getPost ( 'havetime' ); // 使用时间
		$flaw_ids = $this->getPost ( 'flaw_ids' ); // 瑕疵ID 以，分隔
		$enclosure_ids = $this->getPost ( 'enclosure_ids' ); // 附件ID 以，分隔
		$size = $this->getPost ( 'size' ); // 尺寸
		$note = $this->getPost ( 'note' ); // 描述内容
		$img_m = $this->getPost ( 'img_m' ); // 主要位置图片
		$img_s = $this->getPost ( 'img_s' ); // 补充位置图片
		$id = ( int ) $this->getPost ( 'id' ); // 编号ID
		
		try {
			// 是否登录
			if (empty ( $this->user ) || ! is_array ( $this->user )) {
				throw new Exception ( ErrnoStatus::STATUS_40015 );
			}
			
			if ($id) {
				$info = RecoveryOrderService::getInfoByID ( $id );
				if (! is_array ( $info ) || empty ( $info )) {
					throw new Exception ( ErrnoStatus::STATUS_70008 );
				}
			}
			$params = [ ];
			$params ['id'] = $id;
			$params ['brand_id'] = $brand_id;
			$params ['category_id'] = $category_id;
			$params ['material'] = $material;
			$params ['use_time_note'] = $havetime;
			$params ['flaw_ids'] = ! empty ( $flaw_ids ) ? implode ( ',', $flaw_ids ) : '';
			$params ['enclosure_ids'] = ! empty ( $enclosure_ids ) ? implode ( ',', $enclosure_ids ) : '';
			$params ['size'] = $size;
			$params ['note'] = $note ? $note : '无';
			$params ['img_m'] = $img_m;
			$params ['img_s'] = $img_s;
			
			$recyclingService = new RecoveryOrderService ();
			$recyclingService->setParam ( $params );
			$recyclingService->setUser ( $this->user );
			$recyclingService->create ();
			$this->success ();
		} catch ( Exception $exception ) {
			$this->error ( $exception->getMessage () );
		}
	}
	
	/**
	 * 获取订单列表
	 */
	public function orderListAction() {
		try {
			// 是否登录
			if (empty ( $this->user ) || ! is_array ( $this->user )) {
				throw new Exception ( ErrnoStatus::STATUS_40015 );
			}
			$search = [ 
					'supplier_id' => $this->user ['supplier_id'] 
			];
			
			// 状态是否存在
			$status = $this->getQuery ( 'status' );
			if (in_array ( $status, array_keys ( RecoveryOrderService::SETTLEMENT_VALUE ) )) {
				$search ['recovery_status'] = $status;
			}
			
			$list = RecoveryOrderService::getList ( $search );
			foreach ( $list ['rows'] as $k => $v ) {
				$list ['rows'] [$k] ['cover'] = $v ['cover'] ? $this->buildPath ( $v ['cover'] ) : '';
			}
			$this->success ( ( array ) $list );
		} catch ( Exception $exception ) {
			$this->error ( $exception->getMessage () );
		}
	}
	
	/**
	 * 获取回收订单详细
	 * $id 回收订单ID
	 */
	public function orderDetailAction() {
		$id = $this->getQuery ( 'id' );
		try {
			// 是否登录
			if (empty ( $this->user ) || ! is_array ( $this->user )) {
				throw new Exception ( ErrnoStatus::STATUS_40015 );
			}
			$detail = RecoveryOrderService::getInfoByID ( $id );
			$detail ['cover'] = '';
			if (count ( $detail ['imglist'] ) && $detail ['imglist'] [0] ['img_url']) {
				$detail ['cover'] = $this->buildPath ( $detail ['imglist'] [0] ['img_url'] );
			}
			
			$img1 = [ ]; // 位置图片
			$img2 = [ ]; // 补充图片
			$detail ['img_count'] = count ( $detail ['imglist'] );
			if ($detail ['img_count']) {
				foreach ( $detail ['imglist'] as $k => $v ) {
					if ($v ['img_url']) {
						$fullurl = $this->buildPath ( $v ['img_url'] );
						$detail ['imglist'] [$k] ['img_url'] = $fullurl;
						$detail ['imglist'] [$k] ['img_url2'] = $v ['img_url'];
						if ($v ['img_note'] == 0) {
							// 补充图片
							$img2 [] = [ 
									'auth_url' => $fullurl,
									'url' => $v ['img_url'] 
							];
						} else {
							// 位置图片
							$img1 [$v ['img_note']] = [ 
									'auth_url' => $fullurl,
									'url' => $v ['img_url'] 
							];
						}
					}
				}
			}
			
			$position = [ ];
			foreach ( $img1 as $item ) {
				$position [] = $item;
			}
			
			$detail ['position'] = $position; // 位置图片
			$detail ['extras'] = $img2; // 补充图片
			$detail ['enclosure_ids'] = $detail ['recovery_enclosure'] ? explode ( ',', $detail ['recovery_enclosure'] ) : [ ];
			$detail ['flaw_ids'] = $detail ['recovery_flaw'] ? explode ( ',', $detail ['recovery_flaw'] ) : [ ];
			$detail ['category_parent_id'] = $detail ['category_id'] ? RecoveryOrderService::getCategoryPid ( $detail ['category_id'] ) : 0;
			
			$oet = $detail ['offer_expire_time'] ? strtotime ( $detail ['offer_expire_time'] ) : 0;
			$detail ['remaining_time'] = $oet > time () ? $oet - time () : 0;
			
			$detail ['last_category_name'] = $detail ['category_name'];
			if ($detail ['category_name']) {
				$cate = explode ( '|', $detail ['category_name'] );
				if (count ( $cate )) {
					$detail ['last_category_name'] = $cate [count ( $cate ) - 1];
				}
			}
			
			$detail ['real_recovery_status'] = $detail ['recovery_status'];
			// 如果已估计中且估计时间到期，则跳入下个状态
			if ($detail ['recovery_status'] == RecoveryOrderService::RECOVERY_STATUS_TWENTY && $detail ['remaining_time'] === 0) {
				$detail ['recovery_status'] = RecoveryOrderService::RECOVERY_STATUS_THIRTY;
				$detail ['status_txt'] = RecoveryOrderService::SETTLEMENT_VALUE [RecoveryOrderService::RECOVERY_STATUS_THIRTY];
			}
			
			$this->success ( ( array ) $detail );
		} catch ( Exception $exception ) {
			$this->error ( $exception->getMessage () );
		}
	}
	
	/**
	 * 删除回收订单详细
	 * $id 回收订单ID
	 */
	public function deleteOrderAction() {
		$id = $this->getQuery ( 'id' );
		try {
			// 是否登录
			if (empty ( $this->user ) || ! is_array ( $this->user )) {
				throw new Exception ( ErrnoStatus::STATUS_40015 );
			}
			$detail = RecoveryOrderService::getInfoByID ( $id );
			
			if (! $detail) {
				throw new Exception ( ErrnoStatus::STATUS_70008 );
			}
			
			if (! in_array ( $detail ['recovery_status'], [ 
					RecoveryOrderService::RECOVERY_STATUS_FIFTEEN,
					RecoveryOrderService::RECOVERY_STATUS_FORTY,
					RecoveryOrderService::RECOVERY_STATUS_EIGHTY 
			] )) {
				throw new Exception ( ErrnoStatus::STATUS_70007 );
			}
			
			RecoveryOrderService::deleteRecovery ( $detail ['id'] );
			
			$this->success ();
		} catch ( Exception $exception ) {
			$this->error ( $exception->getMessage () );
		}
	}
	
	/**
	 * 我要卖 表单选项
	 */
	public function optionsAction() {
		$brand_id = $this->getPost ( 'brand_id' );
		
		if ($brand_id) {
			$BrandData = \Brand\BrandModel::getInfoByID ( $brand_id );
			$num ['common'] = intval ( $BrandData ['common'] + 1 );
			\Brand\BrandModel::updateCommon ( $num, $brand_id );
		}
		
		$data = [ ];
		// 包体瑕疵
		$data ['flaw'] = RecoveryService::RECOVERY_FLAW_STATUS;
		
		// 附件
		$data ['enclosure'] = RecoveryService::RECOVERY_ENCLOSURE_STATUS;
		// 拍照角度
		$position = [ 
				[ 
						"id" => "1",
						"name" => "正面",
						"cover" => "../static/imgs/bags/08.png" 
				],
				[ 
						"id" => "2",
						"name" => "背面",
						"cover" => "../static/imgs/bags/01.png" 
				],
				[ 
						"id" => "3",
						"name" => "编号",
						"cover" => "../static/imgs/bags/02.png" 
				],
				[ 
						"id" => "4",
						"name" => "侧面",
						"cover" => "../static/imgs/bags/03.png" 
				],
				[ 
						"id" => "5",
						"name" => "底部",
						"cover" => "../static/imgs/bags/04.png" 
				],
				[ 
						"id" => "6",
						"name" => "肩带五金件",
						"cover" => "../static/imgs/bags/05.png" 
				],
				[ 
						"id" => "7",
						"name" => "拉链",
						"cover" => "../static/imgs/bags/06.png" 
				],
				[ 
						"id" => "8",
						"name" => "内衬",
						"cover" => "../static/imgs/bags/07.png" 
				] 
		];
		$data ['position'] = $position;
		$this->success ( $data );
	}
	
	/**
	 * 常用品牌接口
	 */
	public function commonAction() {
		$data = \Brand\BrandModel::getCommonList ();
		if ($data) {
			$this->success ( $data );
		}
	}
	
	/**
	 * 搜索品牌接口
	 */
	
	public function searchAction(){
		$name = $this->getPost ( 'name' );
		$data  = \Brand\BrandModel::getSearcList($name);
		if ($data) {
			$this->success ( $data );
		}else{
			$this->error ('err');
		}
	}
	
}