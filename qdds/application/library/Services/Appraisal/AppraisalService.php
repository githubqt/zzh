<?php
// +----------------------------------------------------------------------
// | 鉴定操作类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：黄献国 
// +----------------------------------------------------------------------
// | Author: 黄献国  Date:2018/11/20 Time:17:48
// +----------------------------------------------------------------------


namespace Services\Appraisal;


use Admin\AdminModel;
use Appraisal\AppraisalModel;
use Supplier\SupplierModel;
use Category\CategoryModel;
use Brand\BrandModel;
use Product\ProductModel;
use Image\ImageModel;
use Services\Appraisal\AppraisalBaseService;
use Services\SystemPermissions\SystemPermissionsService;
use Custom\YDLib;
use Appraisal\AppraisalPolicyModel;

class AppraisalService extends AppraisalBaseService
{

    /* 获取列表*/
    public static function getList(array $search = [])
    {
        $result = AppraisalModel::getList($search);
        if (is_array($result['rows']) && count($result['rows']) > 0) {
            foreach ($result['rows'] as $key => $value) {
                $result['rows'][$key] = self::dataSwitch($value);
                if ($value['brand_id'] == '0') {
                    $result['rows'][$key]['brand_name'] = '其他品牌';
                }
            }
        }
        return $result;
    }
    
    /* 获取数据*/
    public static function getInfoByID($id)
    {
        $detail = AppraisalModel::find($id)->toArray();
        if ($detail) {
            $detail = self::dataSwitch($detail);
            //汇款图片
            $detail['voucher_img']  = ImageModel::getInfoByTypeOrID($id,'appraisal_voucher');
            //鉴定图片
            $detail['appraisal_img']  = ImageModel::getInfoByTypeOrID($id,'appraisal');
            //商户信息
            $detail['company']  = SupplierModel::getInfoByID($detail['supplier_id'])['company'];
            //商品信息
            if ($detail['product_id']) {
                $detail['product'] = ProductModel::getInfoByID($detail['product_id']);
            }
            //鉴定结果
            $detail['is_genuine_txt'] = self::APPRAISAL_IS_GENUINE_VALUE[$detail['is_genuine']];
            
            if ($detail['appraisal_flaw']) {
                $detail['appraisal_flaw'] = explode(',', $detail['appraisal_flaw']);
            }
            if ($detail['appraisal_enclosure']) {
                $detail['appraisal_enclosure'] = explode(',', $detail['appraisal_enclosure']);
            }

            $detail['new_product_name'] = $detail['brand_name'].CategoryModel::getInfoByID($detail['category_id'])['name'];
            
            //保单信息
            if ($detail['policy_id'] && $detail['policy_id'] > 0) {
                $detail['policy'] = AppraisalPolicyModel::find($detail['policy_id'])->toArray();
            }
            
        }
        return $detail;
    }

    /* 数据转换*/
    public static function dataSwitch($detail)
    {
        //瑕疵
        $detail['flaw_txt'] = self::getFlaw($detail['appraisal_flaw']);
        //附件
        $detail['enclosure_txt'] = self::getEnclosure($detail['appraisal_enclosure']);
        //状态
        $detail['status_txt'] = self::SETTLEMENT_VALUE[$detail['appraisal_status']];
        //分类
        $detail['category_name'] = CategoryModel::getCategoryName($detail['category_id']);
        //品牌
        $detail['brand_name'] = BrandModel::getInfoByID($detail['brand_id'])['name'];
        return $detail;
    }

    /**
     * 获得预定义鉴定编号
     * @return integer
     */
    public static function getSelfCodeList($num)
    {

        $adminId = AdminModel::getAdminID();
        $adminInfo = AdminModel::getAdminLoginInfo($adminId);
        //$num = AppraisalModel::getCountNum($adminInfo['supplier_id']);

        //if (!$product_code) {
            $product_code = '1'.sprintf("%05d%03d%05d", $adminInfo['supplier_id'], mt_rand(100, 999), ($num + 1));
        //}

        return 'JD-'.$product_code.mt_rand(1000, 9999);
    }

    /**
     * 获得鉴定编号
     * @return integer
     */
    public static function getSelfCode($product_code = '')
    {

        $adminId = AdminModel::getAdminID();
        $adminInfo = AdminModel::getAdminLoginInfo($adminId);
        $num = AppraisalModel::getCountNum($adminInfo['supplier_id']);

        if (!$product_code) {
            $product_code = '1'.sprintf("%05d%03d%05d", $adminInfo['supplier_id'], mt_rand(100, 999), ($num + 1));
        }

        return 'JD'.$product_code.mt_rand(1000, 9999);
    }
    
    //添加鉴定信息
    public static function addAppraisalInfo($data)
    {
        // 开始事务
        $pdo = YDLib::getPDO('db_w');
        $pdo->beginTransaction();
        try {
            $logo_url = [];
            if ($data['logo_url']) {
                $logo_url = $data['logo_url'];
                unset($data['logo_url']);
            }
            
            $other_url = [];
            if ($data['other_url']) {
                $other_url = $data['other_url'];
                unset($data['other_url']);
            }
            
            if (!$data['product_id']) {
                unset($data['product_id']);
            } else {
                $up['is_supplement_info'] = '2';
                $upProduct = ProductModel::unstatusByID($up, $data['product_id']);
                if($upProduct == false) {
                    $pdo->rollback();
                    $jsonData['code'] = '500';
                    $jsonData['msg'] = '更新商品信息失败！';
                    return $jsonData;
                }
            }
            
            $adminId = AdminModel::getAdminID();
            $adminInfo = AdminModel::getAdminLoginInfo($adminId);
            //获取设置 
            $appraisal_price = SystemPermissionsService::getConfig('appraisal')['options']['appraisal_price'];
            
            //添加主信息
            /* if (!$data['appraisal_code']) {
                $appraisal_code = self::getSelfCode($data['product_code']);
                $data['appraisal_code'] = AppraisalModel::getInfoByWhere(['appraisal_code' => $appraisal_code])?self::getSelfCode($data['product_code']):$appraisal_code;
            } */
            unset($data['product_code']);
            
            $data['appraisal_price'] = $appraisal_price;
            $data['option_admin_id'] = $adminId;
            $data['option_admin_name'] = $adminInfo['fullname']?$adminInfo['fullname']:$adminInfo['name'];
            $data['supplier_id'] = $adminInfo['supplier_id'];
            if ($appraisal_price > '0.00') {
                $data['appraisal_status'] = self::APPRAISAL_STATUS_TEN;
            } else {
                $data['appraisal_status'] = self::APPRAISAL_STATUS_TWENTY;
            }
            $last_id = AppraisalModel::addData($data);
            if($last_id == false) {
                $pdo->rollback();
                $jsonData['code'] = '500';
                $jsonData['msg'] = '添加主信息失败！';
                return $jsonData;
            }
            //添加主图片
            if ($logo_url) {
                foreach ($logo_url as $key=>$val) {
                    $imgList = [];
                    $imgList['supplier_id'] = $adminInfo['supplier_id'];
                    $imgList['img_url'] = $val;
                    $imgList['obj_id'] = intval($last_id);
                    $imgList['type'] = 'appraisal';
                    $imgList['img_type'] = pathinfo($val, PATHINFO_EXTENSION);
                    $imgList['img_note'] = $key;
                    $imgLastId = ImageModel::addData($imgList);
                    if ($imgLastId === FALSE) {
                        $pdo->rollback();
                        $jsonData['code'] = '500';
                        $jsonData['msg'] = '添加细节图片失败！';
                        return $jsonData;
                    }
                }
            }
            
            //添加其他图片
            if ($other_url) {
                foreach ($other_url as $key=>$val) {
                    $otherImgList = [];
                    $otherImgList['supplier_id'] = $adminInfo['supplier_id'];
                    $otherImgList['img_url'] = $val;
                    $otherImgList['obj_id'] = intval($last_id);
                    $otherImgList['type'] = 'appraisal';
                    $otherImgList['img_type'] = pathinfo($val, PATHINFO_EXTENSION);
                    $otherImgList['img_note'] = '0';
                    $otherImgListId = ImageModel::addData($otherImgList);
                    if ($otherImgListId === FALSE) {
                        $pdo->rollback();
                        $jsonData['code'] = '500';
                        $jsonData['msg'] = '添加其他细节图片失败！';
                        return $jsonData;
                    }
                }
            }
            
            $pdo->commit();
            $jsonData['code'] = '200';
            $jsonData['msg'] = '添加成功！';
            return $jsonData;
        } catch (\Exception $exception) {
            $pdo->rollback();
            $jsonData['code'] = '500';
            $jsonData['msg'] = '添加失败！';
            return $jsonData;
        }
    }
    
    
    
    /* 更新*/
    public static function updateByID($data, $id)
    {
        return AppraisalModel::updateByID($data, $id);
    }
    
    
    
    //付款
    public static function addprrice($data,$logo_url,$id)
    {
        // 开始事务
        $pdo = YDLib::getPDO('db_w');
        $pdo->beginTransaction();
        try {
            
            $last_id = self::updateByID($data,$id);
            if($last_id == false) {
                $pdo->rollback();
                $jsonData['code'] = '500';
                $jsonData['msg'] = '添加付款信息失败！';
                return $jsonData;
            }
            $adminId = AdminModel::getAdminID();
            $adminInfo = AdminModel::getAdminLoginInfo($adminId);
            //添加主图片
            if ($logo_url) {
                $imgList = [];
                $imgList['supplier_id'] = $adminInfo['supplier_id'];
                $imgList['img_url'] = $logo_url;
                $imgList['obj_id'] = intval($id);
                $imgList['type'] = 'appraisal_voucher';
                $imgList['img_type'] = pathinfo($logo_url, PATHINFO_EXTENSION);
                $imgLastId = ImageModel::addData($imgList);
                if ($imgLastId === FALSE) {
                    $pdo->rollback();
                    $jsonData['code'] = '500';
                    $jsonData['msg'] = '添加凭证图片失败！';
                    return $jsonData;
                }
            }
    
            $pdo->commit();
            $jsonData['code'] = '200';
            $jsonData['msg'] = '付款成功！';
            return $jsonData;
        } catch (\Exception $exception) {
            $pdo->rollback();
            $jsonData['code'] = '500';
            $jsonData['msg'] = '付款失败！';
            return $jsonData;
        }
    }
    
    
    
    //添加鉴定信息
    public static function updateAppraisalInfo($data,$id)
    {
        // 开始事务
        $pdo = YDLib::getPDO('db_w');
        $pdo->beginTransaction();
        try {
            $logo_url = [];
            if ($data['logo_url']) {
                $logo_url = $data['logo_url'];
                unset($data['logo_url']);
            }
    
            $other_url = [];
            if ($data['other_url']) {
                $other_url = $data['other_url'];
                unset($data['other_url']);
            }
    
            unset($data['product_code']);
    
            if (!$data['product_id']) {
                unset($data['product_id']);
            } else {
                $up['is_supplement_info'] = '2';
                $upProduct = ProductModel::unstatusByID($up, $data['product_id']);
                if($upProduct == false) {
                    $pdo->rollback();
                    $jsonData['code'] = '500';
                    $jsonData['msg'] = '更新商品信息失败！';
                    return $jsonData;
                }
            }
    
            $adminId = AdminModel::getAdminID();
            $adminInfo = AdminModel::getAdminLoginInfo($adminId);
            //添加主信息
            $data['appraisal_status'] = self::APPRAISAL_STATUS_TWENTY;
            
            $last_id = self::updateByID($data,$id);
            if($last_id == false) {
                $pdo->rollback();
                $jsonData['code'] = '500';
                $jsonData['msg'] = '编辑主信息失败！';
                return $jsonData;
            }
            
            //删除原来的
            $del = ImageModel::deleteByID($id,'appraisal');
            if ($del === FALSE) {
                $pdo->rollback();
                $jsonData['code'] = '500';
                $jsonData['msg'] = '删除原细节图片失败！';
                return $jsonData;
            }
            
            //编辑主图片
            if ($logo_url) {
                foreach ($logo_url as $key=>$val) {
                    $imgList = [];
                    $imgList['supplier_id'] = $adminInfo['supplier_id'];
                    $imgList['img_url'] = $val;
                    $imgList['obj_id'] = intval($id);
                    $imgList['type'] = 'appraisal';
                    $imgList['img_type'] = pathinfo($val, PATHINFO_EXTENSION);
                    $imgList['img_note'] = $key;
                    $imgLastId = ImageModel::addData($imgList);
                    if ($imgLastId === FALSE) {
                        $pdo->rollback();
                        $jsonData['code'] = '500';
                        $jsonData['msg'] = '编辑细节图片失败！';
                        return $jsonData;
                    }
                }
            }
            
            //编辑其他图片
            if ($other_url) {
                foreach ($other_url as $key=>$val) {
                    $otherImgList = [];
                    $otherImgList['supplier_id'] = $adminInfo['supplier_id'];
                    $otherImgList['img_url'] = $val;
                    $otherImgList['obj_id'] = intval($id);
                    $otherImgList['type'] = 'appraisal';
                    $otherImgList['img_type'] = pathinfo($val, PATHINFO_EXTENSION);
                    $otherImgList['img_note'] = '0';
                    $otherImgListId = ImageModel::addData($otherImgList);
                    if ($otherImgListId === FALSE) {
                        $pdo->rollback();
                        $jsonData['code'] = '500';
                        $jsonData['msg'] = '编辑其他细节图片失败！';
                        return $jsonData;
                    }
                }
            }
            
            $pdo->commit();
            $jsonData['code'] = '200';
            $jsonData['msg'] = '编辑成功！';
            return $jsonData;
        } catch (\Exception $exception) {
            $pdo->rollback();
            $jsonData['code'] = '500';
            $jsonData['msg'] = '编辑失败！';
            return $jsonData;
        }
    }
    
    
    

}