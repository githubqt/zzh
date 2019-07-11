<?php
/** 
 * 基础控制器
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
use Admin\AdminModel;
use Brand\BrandModel;
use Category\CategoryModel;
use Attribute\AttributeModel;
use Attribute\AttributeValueModel;
use Core\Qzcode;
use Core\Barcodegen;
use Multipoint\MultipointModel;
use Upload\uploadJson;
use Upload\file_manager_json;
use Product\ProductModel;
use Recovery\RecoveryModel;
use Appraisal\AppraisalModel;



class PublicController extends SimpleBaseController 
{
	/**
	 *  获得城市三级地址
	 */
	public function areaAction()
	{
		$pid = $this->_request->get('pid');
		$pid = isset($pid) && is_numeric($pid)?$pid:0;
		
		
		$jsonData = [];
		$jsonData['code'] = '200';
		$jsonData['msg'] = '查询成功！';
		$jsonData['data'] = AreaModel::getChild($pid);
		echo $this->apiOut($jsonData);
		exit;
	}	
	
	/**
	 *  获得城市三级地址(根据名称)
	 */
	public function areanameAction()
	{
	    $pname = $this->_request->get('pname');
	    $pid = $this->_request->get('pid');
	
	    $area = AreaModel::getChildByName($pname,$pid);
	    
	    $jsonData = [];
	    $jsonData['code'] = '200';
	    $jsonData['msg'] = '查询成功！';
	    $jsonData['data'] = $area;
	    echo $this->apiOut($jsonData);
	    exit;
	}
	
	
	/**
	 *  获得三级分类
	 */
	public function getCategoryAction()
	{
	    $pid = $this->_request->get('pid');
	    $pid = isset($pid) && is_numeric($pid)?$pid:0;
	
	    $jsonData = [];
	    $jsonData['code'] = '200';
	    $jsonData['msg'] = '查询成功！';
	    $jsonData['data'] = CategoryModel::getParentTwoList($pid);
	    echo $this->apiOut($jsonData);
	    exit;
	}
	
	
	
	/**
	 * 根据供应商商品获得分类菜单
	 */
	public function channelCategoryAction()
	{
        
	    if ($_REQUEST['type'] == '1') {//供应商品列表
	        $info['type'] = '1';//非赠品
            $info['channel_status'] = '3';//已上架到渠道
	        $info['is_on_status'] = '1';//未上架到自己的微商城
	        $three_Category_ids = ProductModel::getChannelCategory($info);
	    } else if ($_REQUEST['type'] == '2') {//供应销售列表
	        $info['type'] = '1';//非赠品
            $info['channel_status'] = '3';//已上架到渠道
	        $info['is_on_status'] = '2';//已上架到自己的微商城
	        $three_Category_ids = ProductModel::getChannelCategory($info);
	    } else if ($_REQUEST['type'] == '3') {//竞价拍/秒杀
	        $info['on_status'] = '2';
	        $info['not_in'] = '1';
	        $info['type'] = '1';
	        $three_Category_ids = ProductModel::getListCategoryId($info);
	    } else if ($_REQUEST['type'] == '4') {//优惠券
	        $info['on_status'] = '2';
	        $info['type'] = '1';
	        $three_Category_ids = ProductModel::getListCategoryId($info);
	    } else if ($_REQUEST['type'] == '5') {//拼团
	        $info['on_status'] = '2';
	        $info['type'] = '1';
	        $info['not_in_pintuan'] = '1';
	        if ($_REQUEST['seckill_id']) {
	            $info['seckill_id'] = $_REQUEST['seckill_id'];
	        }
	        $three_Category_ids = ProductModel::getListCategoryId($info);
	    } else if ($_REQUEST['type'] == '6') {//订单
	        $info['on_status'] = '2';
	        $three_Category_ids = ProductModel::getCategoryListAddOrder($info);
	    } else if ($_REQUEST['type'] == '7') {//积分规则
	        $info['on_status'] = '2';
	        $three_Category_ids = ProductModel::getListCategoryId($info);
	    } else if ($_REQUEST['type'] == '8') {//积分规则
	        $three_Category_ids = ProductModel::getListCategoryId();
	    } else if ($_REQUEST['type'] == '11') {//回收
            $info['type'] = 'category';
            $three_Category_ids = RecoveryModel::getCategoryAndBandIDs($info);
        } else if ($_REQUEST['type'] == '12') {//鉴定
            $info['type'] = 'category';
            $info['is_product'] = $_REQUEST['is_product'];
            $three_Category_ids = AppraisalModel::getCategoryAndBandIDs($info);
        } else if ($_REQUEST['type'] == '13') {//只有包的商品列表
            $info['just_bag'] = 1;
	        $three_Category_ids = ProductModel::getListCategoryId($info);
	    } else if ($_REQUEST['type'] == '14') {//只有包的商品列表
            $info['just_bag'] = 1;
            $info['appraisal_status'] = '1';
	        $three_Category_ids = ProductModel::getListCategoryId($info);
	    }
	   
	   $three_category = CategoryModel::getAllByids($three_Category_ids);
	    
    	//获取二级
    	$two_id = [];
    	if (is_array($three_category) && count($three_category) > 0) {
    	    foreach ($three_category as $key => $value) {
    	        $two_id[] = $value['parent_id'];
    	    }
    	    $two_id = implode(',', $two_id);
    	}
    	
    	$two_category = CategoryModel::getAllByids($two_id);
    	
    	//获取一级
    	$one_id = '';
    	if (is_array($two_category) && count($two_category) > 0) {
    	    foreach ($two_category as $key => $value) {
    	        $one_id[$key] = $value['parent_id'];
    	    }
    	    $one_id = implode(',', $one_id);
    	}
    	$one_category = CategoryModel::getAllByids($one_id);
    	
    	$categoryArray = [];
    	if (is_array($one_category) && count($one_category) > 0) {
    	    foreach ($one_category as $key => $value) {
    	        if ($value['parent_id'] == 0) {
    	            $info = [];
    	            $info['id'] = $value['id'];
    	            $info['text'] = $value['name'];
    	            $info['parent_id'] = $value['parent_id'];
    	            $info['root_id'] = $value['root_id'];
    	            $info['state'] = "closed";
    	            $categoryArray[] = $info;
    	        }
    	    }
    	}
    	if (is_array($two_category) && count($two_category) > 0) {
    	    foreach ($two_category as $key => $value) {
    	        foreach ($categoryArray as $k => $val) {
    	            if ($value['parent_id'] == $val['id']) {
    	                $info = [];
    	                $info['id'] = $value['id'];
    	                $info['text'] = $value['name'];
    	                $info['parent_id'] = $value['parent_id'];
    	                $info['root_id'] = $value['root_id'];
    	                $info['state'] = "closed";
    	                $categoryArray[$k]['children'][] = $info;
    	            }
    	        }
    	    }
    	} else {
    	    foreach ($categoryArray as $k => $val) {
    	        $categoryArray[$k]['children'][] = [];
    	    }
    	}
    	
    	if (is_array($categoryArray) && count($categoryArray) > 0) {
    	    foreach ($categoryArray as $key => $value) {
    	        if (is_array($value['children']) && count($value['children']) > 0) {
    	            foreach ($value['children'] as $k => $val) {
    	                $i=0;
    	                foreach ($three_category as $c => $v) {
    	                    if ($val['id'] == $v['parent_id']) {
    	                        $info = [];
    	                        $info['id'] = $v['id'];
    	                        $info['text'] = $v['name'];
    	                        $info['parent_id'] = $v['parent_id'];
    	                        $info['root_id'] = $v['root_id'];
    	                        $categoryArray[$key]['children'][$k]['children'][] = $info;
    	                        $i=1;
    	                    }
    	                }
    	                if ($i != 1) {
    	                    unset($categoryArray[$key]['children'][$k]['state']);
    	                }
    	            }
    	        } else {
    	            unset($categoryArray[$key]['state']);
    	        }
    	    }
    	}
    	
	
	    echo $this->apiOut($categoryArray);
	    exit;
	
	}
	
	
	/**
	 * 获得分类菜单
	 */
	public function categoryAction()
	{
		
		$category = CategoryModel::getAll();
		$categoryArray = [];
		if (is_array($category) && count($category) > 0) {
			foreach ($category as $key => $value) {
				if ($value['parent_id'] == 0) {
					$info = [];
					$info['id'] = $value['id'];
					$info['text'] = $value['name'];
					$info['parent_id'] = $value['parent_id'];
					$info['root_id'] = $value['root_id'];
					$info['state'] = "closed";
					$categoryArray[] = $info;
					unset($category[$key]);
				}
			}
		}
		if (is_array($category) && count($category) > 0) {
			foreach ($category as $key => $value) {
				foreach ($categoryArray as $k => $val) {
					if ($value['parent_id'] == $val['id']) {
						$info = [];
						$info['id'] = $value['id'];
						$info['text'] = $value['name'];
						$info['parent_id'] = $value['parent_id'];
						$info['root_id'] = $value['root_id'];
						$info['state'] = "closed";
						$categoryArray[$k]['children'][] = $info;
						unset($category[$key]);
					}
				}
            }
		} else {
			foreach ($categoryArray as $k => $val) {
				$categoryArray[$k]['children'][] = [];
			}
		}
		
		if (is_array($category) && count($category) > 0) {
			foreach ($categoryArray as $key => $value) {
				if (is_array($value['children']) && count($value['children']) > 0) {
					foreach ($value['children'] as $k => $val) {
					    $i=0;
						foreach ($category as $c => $v) {
							if ($val['id'] == $v['parent_id']) {
								$info = [];
								$info['id'] = $v['id'];
								$info['text'] = $v['name'];
								$info['parent_id'] = $v['parent_id'];
								$info['root_id'] = $v['root_id'];
								$categoryArray[$key]['children'][$k]['children'][] = $info;
							
								//CategoryModel::updateByID(['root_id'=>$val['parent_id'],'name'=>$v['name']],$v['id']);
								unset($category[$c]);
								$i=1;
							}
						}
						if ($i != 1) {
						    unset($categoryArray[$key]['children'][$k]['state']);
						}
					}
				} else {
                    unset($categoryArray[$key]['state']);
                }
			}
		}

		echo $this->apiOut($categoryArray);
        exit;
		
	}
	
	public function brandAction()
	{
		$jsonData = BrandModel::getAll();
		if (is_array($jsonData) && count($jsonData) > 0) {
			foreach ($jsonData as $key => $value) {
				$jsonData[$key]['name'] = $value['name']."(".$value['en_name'].")";
			}
		}
		echo $this->apiOut($jsonData);
        exit;
	}
	
	
	/**
	 * 根据供应商商品获得品牌菜单
	 */
	public function channelBrandAction()
	{
	
	    if ($_REQUEST['type'] == '1') {//供应商品列表
	        $info['type'] = '1';//非赠品
	        $info['channel_status'] = '3';//已上架到渠道
	        $info['is_on_status'] = '1';//未上架到自己的微商城
	        $brand_ids = ProductModel::getChannelBrand($info);
	    } else if ($_REQUEST['type'] == '2') {//供应销售列表
	        $info['type'] = '1';//非赠品
	        $info['channel_status'] = '3';//已上架到渠道
	        $info['is_on_status'] = '2';//已上架到自己的微商城
	        $brand_ids = ProductModel::getChannelBrand($info);
	    } else if ($_REQUEST['type'] == '3') {//竞价拍/秒杀
	        $info['on_status'] = '2';
	        $info['not_in'] = '1';
	        $info['type'] = '1';
	        $brand_ids = ProductModel::getListBrandId($info);
	    } else if ($_REQUEST['type'] == '4') {//优惠券/上架商品
	        $info['on_status'] = '2';
	        $info['type'] = '1';
	        $brand_ids = ProductModel::getListBrandId($info);
	    } else if ($_REQUEST['type'] == '5') {//拼团
	        $info['on_status'] = '2';
	        $info['type'] = '1';
	        $info['not_in_pintuan'] = '1';
	        if ($_REQUEST['seckill_id']) {
	            $info['seckill_id'] = $_REQUEST['seckill_id'];
	        }
	        $brand_ids = ProductModel::getListBrandId($info);
	    } else if ($_REQUEST['type'] == '6') {//订单
	        $info['on_status'] = '2';
	        $brand_ids = ProductModel::getBrandListAddOrder($info);
	    } else if ($_REQUEST['type'] == '7') {//积分规则
	        $info['on_status'] = '2';
	        $brand_ids = ProductModel::getListBrandId($info);
	    } else if ($_REQUEST['type'] == '8') {//积分规则
	        $brand_ids = ProductModel::getListBrandId();
	    } else if ($_REQUEST['type'] == '9') {//赠品
	        $info['type'] = '2';
	        $brand_ids = ProductModel::getListBrandId($info);
	    } else if ($_REQUEST['type'] == '10') {//渠道售卖商品
	        $info['channel_status'] = '3';
	        $info['type'] = '1';
	        $brand_ids = ProductModel::getListBrandId($info);
	    } else if ($_REQUEST['type'] == '11') {//回收
            $info['type'] = 'brand';
            $brand_ids = RecoveryModel::getCategoryAndBandIDs($info);
        } else if ($_REQUEST['type'] == '12') {//鉴定
            $info['type'] = 'brand';
            $info['is_product'] = $_REQUEST['is_product'];
            $brand_ids = AppraisalModel::getCategoryAndBandIDs($info);
        } else if ($_REQUEST['type'] == '13') {//只有包的商品列表
            $info['just_bag'] = 1;
	        $brand_ids = ProductModel::getListBrandId($info);
	    } else if ($_REQUEST['type'] == '14') {//只有包的商品列表
            $info['just_bag'] = 1;
            $info['appraisal_status'] = '1';
	        $brand_ids = ProductModel::getListBrandId($info);
	    }
	
	    $jsonData = BrandModel::getAllByids($brand_ids);
	    if (is_array($jsonData) && count($jsonData) > 0) {
	        foreach ($jsonData as $key => $value) {
	            $jsonData[$key]['name'] = $value['name']."(".$value['en_name'].")";
	        }
	    }
	    if ($brand_ids) {
	        if (in_array('0', explode(',', $brand_ids))) {
	            $other = [];
	            $other['id'] = '0';
	            $other['name'] = '其他品牌';
	            $jsonData[count($jsonData)] = $other;
	        }
	    }
	    
	    echo $this->apiOut($jsonData);
	    exit;
	    
	}
	

	public function umcallAction()
	{
		header("Access-Control-Allow-Origin: *");
		echo $_GET['call'];

	}
	
	//属性(一级主属性)
	public function attributeAction()
	{
	    $jsonData = AttributeModel::getAll();
	    echo $this->apiOut($jsonData);
	    exit;
	}
	//属性(属性明细)
	public function attributeValueAction()
	{
	    $attribute_id = $this->_request->get('id');
	    $jsonData = AttributeValueModel::getInfoByAttributeId($attribute_id);
	    echo $this->apiOut($jsonData);
	    exit;
	}
	
	//二维码
	public function qcodeAction()
	{
	    $content = $this->_request->get('content');
		$Qzcode = new Qzcode;
		$Qzcode->create_code($content);		
	    exit;
	}
	//条码
	public function barcodeAction()
	{
	    $content = $this->_request->get('content');
		$barcodegen = new Barcodegen;
		$barcodegen->createCode($content);	
	    exit;
	}
	
	//网点
	public function multiPointAction()
	{
	    $jsonData = MultipointModel::getAll();
	    
	    echo $this->apiOut($jsonData);
	    exit;
	}

	
	//编辑器上传图片（微信上传）
	public function uploadJsonAction() {
	    $up = new uploadJson;
	    echo $up->uploadJson();
	    exit;
	}
	
	
	//编辑器上传图片（微信上传）
	public function flieManagerJsonAction() {
	    $up = new file_manager_json;
	    echo $up->fileManagerJson();
	    exit;
	}
	
	
	
	/**
	 * 获得分类菜单（只有包）
	 */
	public function getBadCategoryAction()
	{
	
	    $category = CategoryModel::getAllByids('902');
	    $all_category = CategoryModel::getAll();
	    $categoryArray = [];
	    if (is_array($category) && count($category) > 0) {
	        foreach ($category as $key => $value) {
	            if ($value['parent_id'] == 0) {
	                $info = [];
	                $info['id'] = $value['id'];
	                $info['text'] = $value['name'];
	                $info['parent_id'] = $value['parent_id'];
	                $info['root_id'] = $value['root_id'];
	                $info['state'] = "closed";
	                $categoryArray[] = $info;
	                unset($category[$key]);
	            }
	        }
	    }
	    if (is_array($all_category) && count($all_category) > 0) {
	        foreach ($all_category as $key => $value) {
	            foreach ($categoryArray as $k => $val) {
	                if ($value['parent_id'] == $val['id']) {
	                    $info = [];
	                    $info['id'] = $value['id'];
	                    $info['text'] = $value['name'];
	                    $info['parent_id'] = $value['parent_id'];
	                    $info['root_id'] = $value['root_id'];
	                    $info['state'] = "closed";
	                    $categoryArray[$k]['children'][] = $info;
	                    unset($all_category[$key]);
	                }
	            }
	        }
	    } else {
	        foreach ($categoryArray as $k => $val) {
	            $categoryArray[$k]['children'][] = [];
	        }
	    }
	
	    if (is_array($all_category) && count($all_category) > 0) {
	        foreach ($categoryArray as $key => $value) {
	            if (is_array($value['children']) && count($value['children']) > 0) {
	                foreach ($value['children'] as $k => $val) {
	                    $i=0;
	                    foreach ($all_category as $c => $v) {
	                        if ($val['id'] == $v['parent_id']) {
	                            $info = [];
	                            $info['id'] = $v['id'];
	                            $info['text'] = $v['name'];
	                            $info['parent_id'] = $v['parent_id'];
	                            $info['root_id'] = $v['root_id'];
	                            $categoryArray[$key]['children'][$k]['children'][] = $info;
	                            	
	                            //CategoryModel::updateByID(['root_id'=>$val['parent_id'],'name'=>$v['name']],$v['id']);
	                            unset($all_category[$c]);
	                            $i=1;
	                        }
	                    }
	                    if ($i != 1) {
	                        unset($categoryArray[$key]['children'][$k]['state']);
	                    }
	                }
	            } else {
	                unset($categoryArray[$key]['state']);
	            }
	        }
	    }
	
	    echo $this->apiOut($categoryArray);
	    exit;
	
	}
	
}
