<?php
use Product\ProductModel;
use Core\Qzcode;
use Supplier\SupplierModel;
use Admin\AdminModel;
use Seckill\SeckillModel;
use Seckill\SeckillProductModel;

/**
 * *
 * 售卖中商品管理
 * 
 * @version v0.01
 * @author zhaoyu
 *         @time 2018-05-17
 */
class ProductnowController extends BaseController {
	/**
	 * 商品列表
	 * 
	 * @return boolean
	 * @version zhaoyu
	 *          @time 2018-05-09
	 */
	public function listAction() {
		if (! empty ( $_REQUEST ['format'] ) && $_REQUEST ['format'] == "list") {
			$page = isset ( $_REQUEST ['page'] ) ? trim ( $_REQUEST ['page'] ) : '';
			$rows = isset ( $_REQUEST ['rows'] ) ? trim ( $_REQUEST ['rows'] ) : '';
			if (! empty ( $_REQUEST ['info'] )) {
				$info ['info'] = $_REQUEST ['info'];
			}
			$info ['info'] ['sort'] = isset ( $_REQUEST ['sort'] ) ? trim ( $_REQUEST ['sort'] ) : '';
			$info ['info'] ['order'] = isset ( $_REQUEST ['order'] ) ? trim ( $_REQUEST ['order'] ) : 'DESC';
			$jsonData = [ ];
			$info ['info'] ['on_status'] = 2;
			$info ['info'] ['type'] = '1';
			$list = ProductModel::getList ( $info, $page - 1, $rows );
			if ($list == false) {
				$jsonData ['code'] = '500';
				$jsonData ['msg'] = '获取列表失败！';
				echo $this->apiOut ( $jsonData );
				exit ();
			}
			
			$jsonData ['total'] = $list ['total'];
			$jsonData ['rows'] = $list ['list'];
			echo $this->apiOut ( $jsonData );
			exit ();
		}
		
		// 商户信息
		$adminId = AdminModel::getAdminID ();
		$adminInfo = AdminModel::getAdminLoginInfo ( $adminId );
		$suppplier = SupplierModel::getInfoByID ( $adminInfo ['supplier_id'] );
		$suppplier ['m_url'] = sprintf ( M_URL, $suppplier ['domain'] );
		
		$this->getView ()->assign ( "suppplier", $suppplier );
	}
	
	/**
	 * 渠道售卖中商品列表
	 * 
	 * @return boolean
	 * @version zhaoyu
	 *          @time 2018-05-09
	 */
	public function listchannelAction() {
		if (! empty ( $_REQUEST ['format'] ) && $_REQUEST ['format'] == "list") {
			$page = isset ( $_REQUEST ['page'] ) ? trim ( $_REQUEST ['page'] ) : '';
			$rows = isset ( $_REQUEST ['rows'] ) ? trim ( $_REQUEST ['rows'] ) : '';
			if (! empty ( $_REQUEST ['info'] )) {
				$info ['info'] = $_REQUEST ['info'];
			}
			$info ['info'] ['sort'] = isset ( $_REQUEST ['sort'] ) ? trim ( $_REQUEST ['sort'] ) : '';
			$info ['info'] ['order'] = isset ( $_REQUEST ['order'] ) ? trim ( $_REQUEST ['order'] ) : 'DESC';
			$jsonData = [ ];
			$info ['info'] ['channel_status'] = ProductModel::CHANNEL_STATUS_3;
			$info ['info'] ['type'] = '1';
			$list = ProductModel::getList ( $info, $page - 1, $rows );
			if ($list == false) {
				$jsonData ['code'] = '500';
				$jsonData ['msg'] = '获取列表失败！';
				echo $this->apiOut ( $jsonData );
				exit ();
			}
			
			$jsonData ['total'] = $list ['total'];
			$jsonData ['rows'] = $list ['list'];
			echo $this->apiOut ( $jsonData );
			exit ();
		}
		
		$this->getView ();
	}
	
	/**
	 * 下架商品
	 *
	 * @return boolean
	 * @version zhaoyu
	 *          @time 2018-05-17
	 */
	public function unstatusAction() {
		$id = $this->_request->get ( 'id' );
		
		$channel = $this->_request->get ( 'channel' );
		
		$productInfo = SeckillModel::getProductionID ( $id );
		
		if ($productInfo) {
			foreach ( $productInfo as $key => $val ) {
				
				if (strtotime ( $val ['starttime'] ) >= time () && strtotime ( $val ['endtime'] ) >= time () || strtotime ( $val ['starttime'] ) <= time () && strtotime ( $val ['endtime'] ) >= time ()) {
					$jsonData ['code'] = '500';
					$jsonData ['msg'] = '此商品在活动中不能下架！';
					echo $this->apiOut ( $jsonData );
					exit ();
				}
			}
		}
		
		$spellInfo = SeckillProductModel::getProductionID ( $id );
		
		if ($spellInfo) {
			foreach ( $spellInfo as $key => $val ) {
				
				if (strtotime ( $val ['starttime'] ) >= time () && strtotime ( $val ['endtime'] ) >= time () || strtotime ( $val ['starttime'] ) <= time () && strtotime ( $val ['endtime'] ) >= time ()) {
					$jsonData ['code'] = '500';
					$jsonData ['msg'] = '此商品在活动中不能下架！';
					echo $this->apiOut ( $jsonData );
					exit ();
				}
			}
		}
		
		if (isset ( $channel ) && $channel == '2') { // 渠道下架
			$data ['channel_status'] = 1;
			$res = ProductModel::unstatusByID ( $data, $id );
		} else {
			$data ['on_status'] = 1;
			$res = ProductModel::unstatusByID ( $data, $id );
		}
		
		if (! $res) {
			$jsonData ['code'] = '500';
			$jsonData ['msg'] = '下架失败！';
			echo $this->apiOut ( $jsonData );
			exit ();
		}
		$jsonData ['code'] = '200';
		$jsonData ['msg'] = '下架成功！';
		echo $this->apiOut ( $jsonData );
		exit ();
	}
	public function promoteAction() {
		$id = $this->_request->get ( 'id' );
		$format = $this->_request->get ( 'format' );
		$detail = ProductModel::getSingleInfoByID ( $id );
        //上海金价
        $gold_price = \Core\GoldPrice::getGoldPrice();
        //处理销售价与渠道价
        if ($detail['sale_is_up'] == ProductModel::IS_UP_2) {
            $detail['sale_price'] = bcmul(bcadd($gold_price,$detail['sale_up_price'],2),$detail['weight'],2);
        }


		$suppplier_detail = SupplierModel::getInfoByID ( $detail ['supplier_id'] );
		$url = sprintf ( M_URL, $suppplier_detail ['domain'] ) . 'details?id=' . $id;
		
		// $url='http://test.m.zhahehe.com/mobile/details?id='.$id;
		// http://test.testm.zhahehe.com/mobile/details?id= 测试
		// http://test.m.zhahehe.com/mobile/details?id= 正式
		
		if ($format == 'img') {
			
			$Qzcode = new Qzcode ();
            $detail['shop_name'] = $suppplier_detail['shop_name'];
			$Qzcode->shareProduct ( $url, $detail );
			exit ();
		}
		
		if ($format == 'download') {
			$file = $detail ['id'] . ".png";
			header ( "Content-type:application/octet-stream" );
			$filename = basename ( $file );
			header ( "Content-Disposition:attachment;filename = " . $filename );
			header ( "Accept-ranges:bytes" );
			ob_start ();
			$Qzcode = new Qzcode ();
			$Qzcode->shareProduct ( $url, $detail ); // 图文合并二维码
			$image_data = ob_get_contents ();
			ob_end_clean ();
			echo $image_data;
			exit ();
		}
		
		$this->getView ()->assign ( "id", $id );
	}
	
	/*
	 * 打印
	 *
	 */
	public function printAction() {
		$id = $this->_request->get ( 'id' );
		$detail = ProductModel::getSupplierIdInfoByID ( $id );
        //上海金价
        $gold_price = \Core\GoldPrice::getGoldPrice();
        //处理销售价与渠道价
        if ($detail['sale_is_up'] == ProductModel::IS_UP_2) {
            $detail['sale_price'] = bcmul(bcadd($gold_price,$detail['sale_up_price'],2),$detail['weight'],2);
        }
		$num = $this->_request->get ( 'num' );
		
		$adminId = AdminModel::getAdminID ();
		$adminInfo = AdminModel::getAdminLoginInfo ( $adminId );
		$suppplier = SupplierModel::getInfoByID ( $adminInfo ['supplier_id'] );
		$suppplier ['m_url'] = sprintf ( M_URL, $suppplier ['domain'] );
		
		$this->getView ()->assign ( "detail", $detail );
		$this->getView ()->assign ( "id", $id );
		$this->getView ()->assign ( "num", $num );
		$this->getView ()->assign ( "suppplier", $suppplier ['m_url'] );
	}
}




