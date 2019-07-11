<?php
use Machine\MachineModel;
use Core\Qzcode;
use Supplier\SupplierModel;
use Admin\AdminModel;
use Assemble\Support\Arr;

/**
 * *
 * 设备管理
 * 
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-17
 */
class MachineController extends BaseController {
	/**
	 * 设备列表
	 * 
	 * @return boolean
	 * @version zhaoyu
	 * @time 2018-05-09
	 */
	public function listAction() {
        $request = $this->_request->getRequest();
        $format = Arr::get($request, 'format');
        if ($format == 'list') {
            $list = MachineModel::getList(Arr::get($request, 'info', []));
            $this->result($list);
        }

        if ($format == "posdownload") {
            if (!empty($_REQUEST['info'])) {
                $info['info'] = $_REQUEST['info'];
            }
            $info['info']['type'] = '1';
            $filename = $_REQUEST['filename'];
            $fileds = array(
                'id' => 'ID',
                'self_code' => '商品编号',
                'custom_code' => '自定义码',
                'name' => '商品名称',
                'c1_name' => '一级分类',
                'c2_name' => '二级分类',
                'c3_name' => '三级分类',
                'brand_name' => '品牌',
                'sale_price' => '销售价',
                'stock' => '库存'
            );
            DownloadModel::posdownload('Product\ProductModel', $info, $fileds, $filename);
            exit;
        }

        if ($format == "progress") {
            $filename = $_REQUEST['filename'];
            $res = DownloadModel::progress($filename);
            $jsonData = [];
            if ($res == FALSE) {
                $jsonData['code'] = '200';
                $jsonData['msg'] = '文件生成中！';
                $jsonData['done'] = FALSE;
            } else {
                $jsonData['code'] = '200';
                $jsonData['msg'] = '文件生成成功！';
                $jsonData['done'] = TRUE;
            }
            echo $this->apiOut($jsonData);
            exit;
        }

        if ($format == "download") {
            $filename = $_REQUEST['filename'];
            DownloadModel::download($filename);
            exit;
        }

        $this->getView ();
	}

    /**
     * 查看商品
     * @return boolean
     * @version huangixanguo
     * @time 2018-05-09
     */
    public function detailAction()
    {
        $request = $this->_request->getRequest();
        $id = Arr::get($request, 'id', 0);
        $detail = MachineModel::getInfoByID($id);
        $this->getView()->assign("detail", $detail);

    }

    /**
     * 添加商品
     * @return boolean
     * @version zhaoyu
     * @time 2018-05-08
     */
    public function addAction()
    {
        $format = $this->_request->get('format');
        if (!empty($format) && $format == "add") {
            $info = $this->_request->get('info');
            $validate = \Assemble\Support\Validate::validation("machine");
            if (!$validate->check($info)) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = $validate->getError();
                echo $this->apiOut($jsonData);
                exit;
            }
            $auth = MachineModel::auth();
            $info['supplier_id'] = $auth['supplier_id'];
            $info['self_code'] = MachineModel::getSelfCode();
            $machine_id = MachineModel::addData($info);
            if (!$machine_id) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '保存失败！';
                echo $this->apiOut($jsonData);
                exit;
            }
            //添加日志
            $addLog = [];
            $addLog['supplier_id'] = $auth['supplier_id'];
            $addLog['machine_id'] = $machine_id;
            $addLog['machine_name'] = $info['name'];
            $addLog['admin_id'] = $auth['id'];
            $addLog['admin_name'] = $auth['fullname'];
            $addLog['note'] = '添加设备';
            $log = \Machine\MachineservicelogModel::addData($addLog);
            if (!$log) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '记录日志失败！';
                echo $this->apiOut($jsonData);
                exit;
            }
            $jsonData['code'] = '200';
            $jsonData['msg'] = '保存成功！';
            echo $this->apiOut($jsonData);
            exit;
        }
        $this->getView();
    }

    /**
     * 添加商品
     * @return boolean
     * @version zhaoyu
     * @time 2018-05-08
     */
    public function addlogAction()
    {
        $id = $this->_request->get('id');
        $format = $this->_request->get('format');
        if (!empty($format) && $format == "add") {
            $info = $this->_request->get('info');
            $auth = \Machine\MachineservicelogModel::auth();
            $machine = \Machine\MachineModel::getInfoByID($id);
            $addLog = [];
            $addLog['supplier_id'] = $auth['supplier_id'];
            $addLog['machine_id'] = $id;
            $addLog['machine_name'] = $machine['name'];
            $addLog['admin_id'] = $auth['id'];
            $addLog['admin_name'] = $auth['fullname'];
            $addLog['note'] = $info['note'];
            $log = \Machine\MachineservicelogModel::addData($addLog);
            if (!$log) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '记录日志失败！';
                echo $this->apiOut($jsonData);
                exit;
            }
            $jsonData['code'] = '200';
            $jsonData['msg'] = '保存成功！';
            echo $this->apiOut($jsonData);
            exit;
        }
        $this->getView()->assign("id", $id);
    }


    /**
     * 更新商品
     * @return boolean
     * @version zhaoyu
     * @time 2018-05-09
     */
    public function editAction()
    {
        $id = $this->_request->get('id');
        $format = $this->_request->get('format');
        if (!empty($format) && $format == "edit") {
            $info = $this->_request->get('info');
            $info['id'] = $id;
            $validate = \Assemble\Support\Validate::validation("product");
            $validate->setCheckSupplierId(true);
            if (!$validate->check($info)) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = $validate->getError();
                echo $this->apiOut($jsonData);
                exit;
            }

            if (!isset($info['sale_is_up']) || $info['sale_is_up'] != 2) {
                $info['sale_is_up'] = 1;
            }
            if (!isset($info['channel_is_up']) || $info['channel_is_up'] != 2) {
                $info['channel_is_up'] = 1;
            }

            if ($info['sale_is_up'] == 2 || $info['channel_is_up'] == 2) {
                if (empty($info['weight'])) {
                    $jsonData['code'] = '500';
                    $jsonData['msg'] = '请输入黄金重量！';
                    echo $this->apiOut($jsonData);
                    exit;
                }
            }

            $info['item'] = $this->_request->get('items');
            if ($info['brand_id'] == '') {
                $info['brand_id'] = '0';
            }
            $add = ProductModel::updateByID($info, $id);
            if (!$add) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '保存失败！';
                echo $this->apiOut($jsonData);
                exit;
            }

            $jsonData['code'] = '200';
            $jsonData['msg'] = '保存成功！';
            echo $this->apiOut($jsonData);
            exit;
        }


        $detail = ProductModel::getInfoByID($id);
        $this->getView()->assign("detail", $detail);
    }


    /**
     * 删除商品
     * @return boolean
     * @version huangixanguo
     * @time 2018-05-11
     */
    public function deleteAction()
    {
        $id = $this->_request->get('id');

        $detail = ProductModel::find($id);

        if (!$detail){
            $this->error('商品不存在');
        }

        if ($detail->on_status != ProductModel::ON_STATUS_1){
            $this->error(ProductModel::ON_STATUS_VALUE[$detail->on_status] . "商品不允许删除操作");
        }

        //删除属性
        ProductAttributeModel::deleteByProductID($id);
        ProductMultiPointModel::deleteByProductID($id);
        $delete = ProductModel::deleteByID($id);
        if (!$delete) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '删除失败！';
            echo $this->apiOut($jsonData);
            exit;
        }
        $jsonData['code'] = '200';
        $jsonData['msg'] = '删除成功！';
        echo $this->apiOut($jsonData);
        exit;
    }

    public function erweimaAction() {
		$id = $this->_request->get ( 'id' );
		$format = $this->_request->get ( 'format' );
		$detail = MachineModel::getInfoByID ( $id );
		$suppplier_detail = SupplierModel::getInfoByID ( $detail ['supplier_id'] );
		//$url = sprintf ( M_URL, $suppplier_detail ['domain'] ) . '?self_code=' . $detail['self_code'];
        $url = 'http://api.zhahehe.com/v1/Machine/logview?self_code=' . $detail['self_code'];
		// $url='http://test.m.zhahehe.com/mobile/details?id='.$id;
		// http://test.testm.zhahehe.com/mobile/details?id= 测试
		// http://test.m.zhahehe.com/mobile/details?id= 正式
		
		if ($format == 'img') {
			$Qzcode = new Qzcode ();
            $detail['shop_name'] = $suppplier_detail['shop_name'];
			$Qzcode->create_code ( $url );
			exit ();
		}
		
		if ($format == 'download') {
			$file = $detail ['self_code'] . ".png";
			header ( "Content-type:application/octet-stream" );
			$filename = basename ( $file );
			header ( "Content-Disposition:attachment;filename = " . $filename );
			header ( "Accept-ranges:bytes" );
			ob_start ();
			$Qzcode = new Qzcode ();
			$Qzcode->create_code ( $url ); // 图文合并二维码
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
		$detail = MachineModel::getSupplierIdInfoByID ( $id );
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




