<?php

use Product\ProductModel;
use Product\ProductStockLogModel;
use Product\ProductAttributeModel;
use Multipoint\MultipointModel;
use Product\ProductMultiPointModel;
use Services\Appraisal\AppraisalService;
use Brand\BrandModel;
use Category\CategoryModel;
use Appraisal\AppraisalModel;

/***
 * 管理员后台
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
class ProductController extends BaseController
{
    /**
     * 商品列表
     * @return boolean
     * @version zhaoyu
     * @time 2018-05-09
     */
    public function listAction()
    {
        $format = $_REQUEST['format'];
        if (!isset($format) || empty($format)) $format = '';

        if ($format == "list") {
            $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '';
            $rows = isset($_REQUEST['rows']) ? trim($_REQUEST['rows']) : '';
            if (!empty($_REQUEST['info'])) {
                $info['info'] = $_REQUEST['info'];
            }
            $info['info']['sort'] = isset($_REQUEST['sort']) ? trim($_REQUEST['sort']) : 'id';
            $info['info']['order'] = isset($_REQUEST['order']) ? trim($_REQUEST['order']) : 'DESC';
            $info['info']['type'] = '1';
            $info['info']['use_type'] = $_REQUEST['use_type'];
            $jsonData = [];
            $list = ProductModel::getList($info, $page - 1, $rows);
            if ($list == false) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '获取列表失败！';
                echo $this->apiOut($jsonData);
                exit;
            }

            $jsonData['total'] = $list['total'];
            $jsonData['rows'] = $list['list'];
            echo $this->apiOut($jsonData);
            exit;
        }
        
        if ($format == "add_appraisal") {
            $product_ids = $_REQUEST['product_id'];
            $status = $_REQUEST['status'];
            if ($product_ids) {
                $product_ids = explode(',', $product_ids);
                $res = ProductModel::addAppraisalByProductID(['appraisal_status' => $status], $product_ids);
                if ($res == false) {
                    $jsonData['code'] = '500';
                    $jsonData['msg'] = '更新状态失败！';
                    echo $this->apiOut($jsonData);
                    exit;
                }
            }
            $jsonData['code'] = '200';
            $jsonData['msg'] = '更新状态成功！';
            echo $this->apiOut($jsonData);
            exit;
        }

        if ($format == "addOrder") {
            $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '';
            $rows = isset($_REQUEST['rows']) ? trim($_REQUEST['rows']) : '';
            if (!empty($_REQUEST['info'])) {
                $info['info'] = $_REQUEST['info'];
            }
            $info['info']['sort'] = isset($_REQUEST['sort']) ? trim($_REQUEST['sort']) : 'id';
            $info['info']['order'] = isset($_REQUEST['order']) ? trim($_REQUEST['order']) : 'DESC';
            $info['info']['type'] = '1';
            $jsonData = [];
            $list = ProductModel::getListAddOrder($info, $page - 1, $rows);
            if ($list == false) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '获取列表失败！';
                echo $this->apiOut($jsonData);
                exit;
            }

            $jsonData['total'] = $list['total'];
            $jsonData['rows'] = $list['list'];
            echo $this->apiOut($jsonData);
            exit;
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

        $this->getView()->assign('multipoint', MultipointModel::getAll());

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
            $validate = \Assemble\Support\Validate::validation("product");
            $validate->setCheckSupplierId(true);
            if (!$validate->check($info)) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = $validate->getError();
                echo $this->apiOut($jsonData);
                exit;
            }
            $info['type'] = '1';
            $item = $this->_request->get('items');

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

            $add = ProductModel::add($info, $item);
            if (!$add) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '保存失败！';
                echo $this->apiOut($jsonData);
                exit;
            }
            if (!$info['stock']) {
                $info['stock'] = '0';
            }
            //添加日志
            $addLog = [];
            $addLog['num'] = $info['stock'];
            $addLog['name'] = $info['name'];
            $addLog['type'] = \Services\Stock\StockService::LOG_TYPE_1;
            $log = ProductStockLogModel::addData($add, $addLog);
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
        $data['self_code'] = ProductModel::getSelfCode();
        $this->getView()->assign("data", $data);
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
     * 上架商品
     * @return boolean
     * @version zhaoyu
     * @time 2018-05-09
     */
    public function onstatusAction()
    {
        $id = $this->_request->get('id');
        $channel = $this->_request->get('channel');
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

            $info['select'] = $this->_request->get('select');
            $info['checkbox'] = $this->_request->get('checkbox');
            $info['input'] = $this->_request->get('input');
            if (!$info['select'] && !$info['checkbox'] && !$info['input']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请输入填写属性！';
                echo $this->apiOut($jsonData);
                exit;
            }

            if (!$info['logo_url']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请上传商品主图！';
                echo $this->apiOut($jsonData);
                exit;
            }

            $info['item'] = $this->_request->get('items');
            if (!$info['item']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请上传商品附图！';
                echo $this->apiOut($jsonData);
                exit;
            }
            if (!$info['introduction']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请填写商品详情！';
                echo $this->apiOut($jsonData);
                exit;
            }
            if ($info['brand_id'] == '') {
                $info['brand_id'] = '0';
            }
            //$info['on_status'] = 2;
            if ($info['on_status'] == '2') {//保存并上架
                if ($channel == '2') {//上架到渠道
                    $info['channel_status'] = '2';
                    $info['channel_now_at'] = date('Y-m-d H:i:s');
                    unset($info['on_status']);
                } else if ($channel == '1') {//上架到商城
                    $info['now_at'] = date('Y-m-d H:i:s');
                }
            } else if ($info['on_status'] == '1') { //保存
                unset($info['on_status']);
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
        $this->getView()->assign("channel", $channel);

    }


    /**
     * 查看商品
     * @return boolean
     * @version huangixanguo
     * @time 2018-05-09
     */
    public function detailAction()
    {
        $id = $this->_request->get('id');


        $detail = ProductModel::getInfoByID($id);
        //处理销售价与渠道价
        $gold_price = \Core\GoldPrice::getGoldPrice();
        if ($detail['sale_is_up'] == ProductModel::IS_UP_2) {
            $detail['sale_price'] = bcmul(bcadd($gold_price,$detail['sale_up_price'],2),$detail['weight'],2).'（浮动）';
        }
        if ($detail['channel_is_up'] == ProductModel::IS_UP_2) {
            $detail['channel_price'] = bcmul(bcadd($gold_price,$detail['channel_up_price'],2),$detail['weight'],2).'（浮动）';
        }
        $this->getView()->assign("detail", $detail);

    }


    /**
     * 调整库存
     * @return boolean
     * @version huangixanguo
     * @time 2018-05-11
     */
    public function stockAction()
    {
        $id = $this->_request->get('id');
        $format = $this->_request->get('format');
        if (!empty($format) && $format == "edit") {
            $info = $this->_request->get('info');
            if (!$info) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '数据不正确！';
                echo $this->apiOut($jsonData);
                exit;
            }


            if (empty($info['num']) && !is_int($info['num'])) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请输入大于零的整数！';
                echo $this->apiOut($jsonData);
                exit;
            }
            $type = $info['type'];
            $info['type'] = \Services\Stock\StockService::LOG_TYPE_2;//加库存
            if ($type == 'del') {
                $info['num'] = 0 - $info['num'];
                $info['type'] = \Services\Stock\StockService::LOG_TYPE_3;//减库存

                $product = ProductModel::getInfoByID($id);
                if (bcadd($product['stock'], $info['num'], 2) < '0') {
                    $jsonData['code'] = '500';
                    $jsonData['msg'] = '库存不能为负数！';
                    echo $this->apiOut($jsonData);
                    exit;
                }

            }

            //添加日志
            $log = ProductStockLogModel::addData($id, $info);
            if (!$log) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '记录日志失败！';
                echo $this->apiOut($jsonData);
                exit;
            }
            $add = ProductModel::editStock($info['num'], $id);
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
        //处理销售价与渠道价
        $gold_price = \Core\GoldPrice::getGoldPrice();
        if ($detail['sale_is_up'] == ProductModel::IS_UP_2) {
            $detail['sale_price'] = bcmul(bcadd($gold_price,$detail['sale_up_price'],2),$detail['weight'],2).'（浮动）';
        }
        if ($detail['channel_is_up'] == ProductModel::IS_UP_2) {
            $detail['channel_price'] = bcmul(bcadd($gold_price,$detail['channel_up_price'],2),$detail['weight'],2).'（浮动）';
        }
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
    
    
    
    //开具鉴定证书
    public function openappraisalAction()
    {
        $format = $this->_request->get('format');
        if (!empty($format) && $format == "add") {
            $info = $this->_request->get('info');
            $appraisal_flaw = $this->_request->get('appraisal_flaw');
            $appraisal_enclosure = $this->_request->get('appraisal_enclosure');
            $logo_url = $this->_request->get('logo_url');
            $other_url = $this->_request->get('other_url');
            
            /* if ($info['product_id']) {
                $info['category_id'] = $_REQUEST['product_category_id'];
                $info['brand_id'] = $_REQUEST['product_brand_id'];
            } */
            
             if (!$info['category_id']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请选择分类！';
                echo $this->apiOut($jsonData);
                exit;
            }
            if (!$info['brand_id'] || $info['brand_id'] == '0') {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请选择品牌！';
                echo $this->apiOut($jsonData);
                exit;
            }
            if (!$info['use_time_note']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请输入使用时间！';
                echo $this->apiOut($jsonData);
                exit;
            }
            if (!$info['appraisal_size']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请填写尺寸信息！';
                echo $this->apiOut($jsonData);
                exit;
            }
            
            if ($appraisal_flaw) {
                $info['appraisal_flaw'] = implode(',', $appraisal_flaw);
            }

            if ($appraisal_enclosure) {
                $info['appraisal_enclosure'] = implode(',', $appraisal_enclosure);
            }
            
            
            if ($logo_url) {
                foreach ($logo_url as $id=>$url) {
                    if (!$url) {
                        $jsonData['code'] = '500';
                        $jsonData['msg'] = '请完整上传图片信息！';
                        echo $this->apiOut($jsonData);
                        exit;
                    }
                }
                $info['logo_url'] = $logo_url;
            } else {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请完整上传图片信息！';
                echo $this->apiOut($jsonData);
                exit;
            }
            
            if ($other_url) {
                foreach ($other_url as $key=>$url) {
                    if (!$url) {
                        $jsonData['code'] = '500';
                        $jsonData['msg'] = '请完整上传图片信息！';
                        echo $this->apiOut($jsonData);
                        exit;
                    }
                }
                $info['other_url'] = $other_url;
            }  
            
            //判断编号是否已存在
            /* if ($info['appraisal_code']) {
                if (AppraisalModel::getInfoByWhere(['appraisal_code' => $info['appraisal_code']])) {
                    $jsonData['code'] = '500';
                    $jsonData['msg'] = '该编号已存在！';
                    echo $this->apiOut($jsonData);
                    exit;
                }
                $info['appraisal_code'] = trim($info['appraisal_code']);
            } */

            $brand_name = BrandModel::getInfoByID($info['brand_id'])['name'];
            $info['appraisal_name'] = $brand_name.CategoryModel::getInfoByID($info['category_id'])['name'];
            
            $add = AppraisalService::addAppraisalInfo($info);
            echo $this->apiOut($add);
            exit;
        }
    }
    
    
    
    
    
    
    

}
