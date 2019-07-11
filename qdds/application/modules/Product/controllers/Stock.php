<?php

use Product\ProductStockLogModel;

/***
 * 库存管理
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
class StockController extends BaseController
{
	 /**
     * 商品库存列表
     * @return boolean
     * @version zhaoyu 
     * @time 2018-05-16
     */
    public function listAction() 
    {
    	
		if (!empty($_REQUEST['format']) && $_REQUEST['format'] == "list") {
           $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '';
           $rows = isset($_REQUEST['rows']) ? trim($_REQUEST['rows']) : '';
           if (!empty($_REQUEST['info'])) {
              $info['info'] = $_REQUEST['info'];
           }
           $info['info']['sort'] = isset($_REQUEST['sort']) ? trim($_REQUEST['sort']) : '';
           $info['info']['order'] = isset($_REQUEST['order']) ? trim($_REQUEST['order']) : 'DESC';
           $jsonData = [];
           $list = ProductStockLogModel::getList($info,$page-1,$rows);
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
		  
       $this->getView();
    }
}
