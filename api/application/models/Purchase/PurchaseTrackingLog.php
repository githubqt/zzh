<?php
// +----------------------------------------------------------------------
// | 采购跟踪日志
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/27 Time:17:41
// +----------------------------------------------------------------------


namespace Purchase;

use Custom\YDLib;
use Assemble\Source;
use Supplier\SupplierModel;

class PurchaseTrackingLogModel extends \BaseModel
{
    public static function log($purchase_id,$child_order_no,$oper_type, $content,$status = '1')
    {

        $auth = self::auth();
        if (empty($auth)) {
            throw new \InvalidArgumentException('未登录');
        }

        if ($auth['supplier_id']){
            $supplier = SupplierModel::find($auth['supplier_id'], ['company']);
            if (is_null($supplier)) {
                throw new \InvalidArgumentException('商户不存在');
            }
        }

        $log = new self();
        $log->purchase_id = $purchase_id;
        $log->child_order_no = $child_order_no;
        $log->content = $content;
        $log->admin_id = $auth['id'];
        $log->admin_name = $auth['fullname'];
        $log->type = $auth['type'];
        $log->type_name = Source::getSourceName($auth['type']);
        $log->oper_type = $oper_type;
        $log->status = $status;
        $log->supplier_id = $auth['supplier_id'];
        $log->supplier_company = $supplier->company ?:'平台';
        return $log->save();
    }
    
    /*
     * 通过订单ID获取所有日志
     * 
     */
    public static function getProductByLogId($child_order_no)
    {
    	$sql = "SELECT * FROM `" . static::getFullTable() . "` WHERE child_order_no = '{$child_order_no}' AND status = '2'";
    	return self::newRead()->YDGetAll($sql);
    }
    
    
}