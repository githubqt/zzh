<?php
// +----------------------------------------------------------------------
// | 采购管理
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/20 Time:10:39
// +----------------------------------------------------------------------

use Assemble\Support\Arr;
use Purchase\PurchaseModel;
use Assemble\Support\Validate;
use Services\Purchase\PurchaseService;
use Services\Purchase\PurchaseReturnService;
use Purchase\PurchaseReturnModel;
use Purchase\PurchaseReturnProductModel;
use Image\ImageModel;

class PurchaseController extends BaseController
{
    /**
     * 采购列表
     */
    public function listAction()
    {
        $request = $this->_request->getRequest();
        if (Arr::get($request, 'format') == 'list') {
            $list = PurchaseModel::getList(Arr::get($request, 'info', []));
            $this->result($list);
        }
        $data = [];
        $data ['purchase_status'] = json_encode(PurchaseService::PURCHASE_ORDER_STATUS_CODE);
        $data ['pay_types'] = PurchaseModel::PAY_TYPES;
        $data ['order_status'] = PurchaseService::getStatus();
        $this->getView()->assign("data", $data);
    }

    /**
     * 添加采购
     */
    public function addAction()
    {
        $request = $this->_request->getRequest();

        //查看详细
        if ($this->_request->isPost() && Arr::get($request, 'format') == 'detail') {
            $detail = \Product\ProductModel::getInfoByID($request['product_id']);
            //处理价格
            if ($detail['channel_is_up'] == \Product\ProductModel::IS_UP_2) {
                //上海金价
                $gold_price = \Core\GoldPrice::getGoldPrice();
                $detail['channel_price'] = bcmul(bcadd($gold_price,$detail['channel_up_price'],2),$detail['weight'],2).'（浮动）';
            }
            $this->success('操作成功', $detail);
        }

        //选择商品
        if ($this->_request->isPost() && Arr::get($request, 'format') == 'select_product') {
            $info = Arr::get($request, 'info', []);
            $info['type'] = '1';//非赠品
            $info['channel_status'] = '3';//已上架到渠道
            $info['use_type'] = 'add';//添加采购单
            $products = \Product\ProductModel::getChannelList($info);
            $this->result($products);
        }

        if (Arr::get($request, 'format') == 'add') {
            //采购单参数
            $params = Arr::get($request, 'info', []);
            //提交的已选商品数
            $params['product'] = json_decode(Arr::get($request, 'hdn_product'), true);
            $PurchaseService = new PurchaseService();
            $PurchaseService->setRequest($params);
            $result = $PurchaseService->createPurchase();
            if ($result) {
                $this->success();
            } else {
                $this->error($PurchaseService->getError());
            }
        }

        // 获取采购人信息
        $auth = \Admin\AdminModel::getCurrentLoginInfo();
        $assigns = [];
        $assigns ['order_status'] = [];
        $assigns ['pay_types'] = PurchaseModel::PAY_TYPES;
        $assigns ['auth'] = $auth;
        $assigns ['supplier'] = \Supplier\SupplierModel::find($auth['supplier_id']);
        $this->getView()->assign("assigns", $assigns);
    }

    /**
     * 查看采购详细
     */
    public function detailAction()
    {
        $request = $this->_request->getRequest();
        //查询快递
        if ($this->_request->isPost()) {
            $PurchaseService = new PurchaseService();
            //查询快递
            if ($request['format'] == 'query_express') {
                $result = $PurchaseService->queryPurchaseExpress($request['child_order_no']);
                if ($result) {
                    $this->success('查询成功', $result);
                } else {
                    $this->error($PurchaseService->getError());
                }
            }
            $this->error();
        } else {
            $id = Arr::get($request, 'id', 0);
            $PurchaseService = new PurchaseService();
            $result = $PurchaseService->purchaseDetail($id);
            $type = Arr::get($request, 'type', '1');//1原查看2订单查看
            if ($result) {
                $assigns = [];
                $assigns ['id'] = $id;
                $assigns ['purchase'] = $result;
                $assigns ['pay_types'] = PurchaseModel::PAY_TYPES;
                $assigns ['type'] = $type;
                $this->getView()->assign("assigns", $assigns);
            } else {
                $this->error(404);
            }
        }
    }

    /**
     * 删除采购
     */
    public function deleteAction()
    {
        $request = $this->_request->getRequest();
        $id = Arr::get($request, 'id', 0);
        if ($this->_request->isXmlHttpRequest()) {
            $PurchaseService = new PurchaseService();
            $result = $PurchaseService->purchaseDelete($id);
            if ($result) {
                $this->success('删除成功');
            } else {
                $this->error($PurchaseService->getError());
            }
        }
        $this->error('删除失败');
    }

    /**
     * 审核采购单
     */
    public function auditAction()
    {
        $request = $this->_request->getRequest();
        $id = Arr::get($request, 'id', 0);
        if ($this->_request->isXmlHttpRequest()) {
            $PurchaseService = new PurchaseService();
            $result = $PurchaseService->purchaseAudit($id);
            if ($result) {
                $this->success('审核成功');
            } else {
                $this->error($PurchaseService->getError());
            }
        }
        $this->error('审核失败');
    }

    /**
     * 取消采购单
     */
    public function cancelAction()
    {
        $request = $this->_request->getRequest();
        $id = Arr::get($request, 'id', 0);
        if ($this->_request->isXmlHttpRequest()) {
            $PurchaseService = new PurchaseService();
            $result = $PurchaseService->purchaseCancel($id);
            if ($result) {
                $this->success('取消成功');
            } else {
                $this->error($PurchaseService->getError());
            }
        }
        $this->error('取消失败');
    }

    /**
     * 退货处理
     */
    public function returnAction()
    {
        $request = $this->_request->getRequest();
        $order = Arr::get($request, 'order');
        $service = new PurchaseReturnService();
        $list = $service->showChildProducts($order);
        !$list and header("Location:/index.php?m=Purchase&c=Purchase&a=list");
        if (Arr::get($request, 'format') == 'add') {
            //退货单参数
            $params = Arr::get($request, 'info', []);
            //退货图片
            $images = Arr::get($request, 'items', []);
            if ($params['child_order_no'] != $order){
                $this->error('订单号错误');
            }
            // 判断退货数量
            if (!is_array($params['return_num'])){
                $this->error('退货数量错误');
            }

            $returnNum = 0;
            foreach ($params['return_num'] as $k=> $v){
                $returnNum += $v;
            }

            if ($returnNum == 0){
                $this->error('至少选择一个商品进行退货');
            }

            // 判断退货类型
            if (!$params['role_id']){
                $this->error('请选择退货类型');
            }
           // 判断退货描述
            if (!$params['note']){
                $this->error('请输入退货描述');
            }
            //退货图片
            if (count($images) == 0){
                $this->error('请上传图片');
            }
            if (count($images) > 9){
                $this->error('上传图片不能超过9张');
            }

            if ($service->purchaseReturn($params, $images)){
                $this->success('退货成功');
            }
            $this->error($service->getError());
        }
        $this->getView()->assign("order", $order);
        $this->getView()->assign("list", $list);
        $this->getView()->assign("returnReasons", PurchaseReturnService::PURCHASE_RETURN_REASONS);
    }

    public function cancelReturnAction()
    {

    }

    /**
     * 确认收货
     */
    public function receiptAction()
    {
        $request = $this->_request->getRequest();
        if ($this->_request->isXmlHttpRequest()) {
            $PurchaseService = new PurchaseService();
            $result = $PurchaseService->purchaseReceipt($request['purchase_id'], $request['child_order_no']);
            if ($result) {
                $this->success('确认收货成功');
            } else {
                $this->error($PurchaseService->getError());
            }
        }
        $this->error('取消失败');
    }

    /**
     * 商户提交支付汇款账号操作
     */
    public function payAction()
    {
        $request = $this->_request->getRequest();
        //提交汇款信息
        if ($this->_request->isPost()) {
            $request['img_url'] = $request['items'];
            $validate = new \Image\ImageModel();
            if (!$validate->validate()->check($request)) {
                $this->error($validate->getError());
            }
            $PurchaseService = new PurchaseService();
            $result = $PurchaseService->purchasePay(
                $request['purchase_id'],
                $request['remittance_account'],
                $request['items']
            );
            if ($result) {
                $this->success();
            } else {
                $this->error($PurchaseService->getError());
            }

        } else {
            $id = Arr::get($request, 'id', 0);
            $PurchaseService = new PurchaseService();
            $result = $PurchaseService->purchaseDetail($id);
            if ($result) {
                $assigns = [];
                $assigns ['id'] = $id;
                $assigns ['purchase'] = $result;
                $assigns ['pay_types'] = PurchaseModel::PAY_TYPES;
                $this->getView()->assign("assigns", $assigns);
            } else {
                $this->error(404);
            }
        }
    }


}