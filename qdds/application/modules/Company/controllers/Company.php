<?php
// +----------------------------------------------------------------------
// | 公司设置
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/9/4 Time:17:32
// +----------------------------------------------------------------------


class CompanyController extends BaseController
{
    public function setAction()
    {
        $request = $this->_request->getRequest();
        //保存信息
        if ($this->_request->isXmlHttpRequest()) {
            if (!$request['info']['shop_instructions']) {
                $this->error('请输入本店说明');
            }

            if (!$request['info']['customer_tel']) {
                $this->error('请输入客服电话！');
            }

            $request['info']['customer_tel'] = str_replace(array("\r\n", "\r", "\n"), ",", $request['info']['customer_tel']);
            $mobileArray = explode(',',$request['info']['customer_tel']);
            if (!is_array($mobileArray) || count($mobileArray) == 0) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '至少输入1个客服电话！';
                echo $this->apiOut($jsonData);
                exit;
            }

            $mobileNew = [];
            foreach ($mobileArray as $key => $value) {
                if ($value != '') {
                    if (\Custom\YDLib::validTel($value)) {
                        $jsonData['code'] = '500';
                        $jsonData['msg'] = '请输入正确的客服电话！';
                        echo $this->apiOut($jsonData);
                        exit;
                    } else {
                        array_push($mobileNew,$value);
                    }
                }
            }

            if (count($mobileNew) == 0) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '至少输入1个客服电话！';
                echo $this->apiOut($jsonData);
                exit;
            }

            if (count($mobileNew) > 5) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '最多输入5个客服电话！';
                echo $this->apiOut($jsonData);
                exit;
            }


            $result = \Supplier\SupplierModel::updateByID($request['info'], $request['id']);
            if ($result) {
                $this->success('保存成功');
            } else {
                $this->error();
            }
        }
        $auth = \Admin\AdminModel::getCurrentLoginInfo();
        $supplier = \Supplier\SupplierModel::find($auth['supplier_id']);
        $supplier['customer_tel_show'] = str_replace(array(","), "\n", $supplier['customer_tel']);
        $this->getView()->assign("supplier", $supplier);
    }
}