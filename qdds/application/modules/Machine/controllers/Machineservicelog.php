<?php

use Machine\MachineservicelogModel;
use Supplier\SupplierModel;
use Admin\AdminModel;
use Assemble\Support\Arr;

/***
 * 库存管理
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
class MachineservicelogController extends BaseController
{
	 /**
     * 商品库存列表
     * @return boolean
     * @version zhaoyu 
     * @time 2018-05-16
     */
    public function listAction()
    {
        $request = $this->_request->getRequest();
        $format = Arr::get($request, 'format');
        if ($format == 'list') {
            $list = MachineservicelogModel::getList(Arr::get($request, 'info', []));
            $this->result($list);
        }
       $this->getView();
    }


}
