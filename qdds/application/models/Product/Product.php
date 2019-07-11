<?php
/**
 * 权限model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-05
 */

namespace Product;

use Core\GoldPrice;
use Custom\YDLib;
use Common\CommonBase;
use Category\CategoryModel;
use Brand\BrandModel;
use Admin\AdminModel;
use Image\ImageModel;
use Attribute\AttributeModel;
use Attribute\AttributeValueModel;
use Product\ProductAttributeModel;
use Seckill\SeckillModel;
use Category\CategoryAttributeModel;
use Score\ScoreRuleProductModel;
use Multipoint\MultipointModel;
use Product\ProductMultiPointModel;
use Product\ProductChannelModel;
use Assemble\Builder;
use Assemble\Support\Arr;
use Assemble\Support\Date;
use Supplier\SupplierModel;

class ProductModel extends \BaseModel
{
    protected static $_tableName = 'product';

    /** 是否上架到商城 */
    const  ON_STATUS_1 = 1;//未上架
    const  ON_STATUS_2 = 2;//已上架

    const  ON_STATUS_VALUE = [self::ON_STATUS_1 => '未上架', self::ON_STATUS_2 => '已上架'];

    /** 渠道状态 */
    const  CHANNEL_STATUS_1 = 1;//未上架
    const  CHANNEL_STATUS_2 = 2;//待审核
    const  CHANNEL_STATUS_3 = 3;//已上架

    const  CHANNEL_STATUS_VALUE = [
        self::CHANNEL_STATUS_1 => '未上架',
        self::CHANNEL_STATUS_2 => '待审核',
        self::CHANNEL_STATUS_3 => '已上架'
    ];

    /** 是否按照上海金价上调 */
    const  IS_UP_1 = 1;//不上调
    const  IS_UP_2 = 2;//上调



    /* 获取列表*/
    public static function getList($attribute = array(), $page = 0, $rows = 10)
    {
        $limit = ($page) * $rows;
        if (!empty($attribute['info']) && is_array($attribute['info']) && count($attribute['info']) > 0) {
            extract($attribute['info']);
        }
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        $pdo = YDLib::getPDO('db_r');
        $fileds = " a.*,b.name as brand_name,c3.name c3_name,c2.name c2_name,c1.name c1_name,a.category_id";
        $sql = 'SELECT 
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'brand b
		       	ON
		       		b.id = a.brand_id  
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_multi_point pm
		       	ON
		       		a.id = pm.product_id AND pm.is_del = 2      
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c3
		       	ON
		       		c3.id = a.category_id
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c2
		       	ON
		       		c2.id = c3.parent_id    
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c1
		       	ON
		       		c1.id = c2.parent_id 
		        WHERE
        		    a.is_del = 2
        		AND 
        			a.supplier_id = ' . $adminInfo['supplier_id'] . '
        		';
        
        
        if ($is_supplement_info == '1') {
            $sql .= " AND a.is_supplement_info = '1' ";
        }
        
        if ($AppraisalProduct == '1') {
            $sql .= " AND a.appraisal_status = '1' ";
            
        }
        
        if ($just_bag) {
            //只获取包的分类
            $twocategory = CategoryModel::getParentTwoList('902');
            $category_id = [];
            if ($twocategory) {
                foreach ($twocategory as $key=>$value) {
                    $threecategory = CategoryModel::getParentTwoList($value['id']);
                    if ($threecategory) {
                        foreach ($threecategory as $k=>$v) {
                            $category_id[] = $v['id'];
                        }
                    }
                }
            }
            
            if ($category_id) {
                $sql .= " AND a.category_id IN (".implode(',', $category_id).")";
            }
        }
        
        if (isset($name) && !empty($name)) {
            $sql .= " AND a.name like '%" . $name . "%' ";
        }

        if (isset($self_code) && !empty($self_code)) {
            $sql .= " AND a.self_code like '%" . $self_code . "%' ";
        }

        if (isset($custom_code) && !empty($custom_code)) {
            $sql .= " AND a.custom_code like '%" . $custom_code . "%' ";
        }

        if (isset($type) && !empty($type)) {
            $sql .= " AND a.type = '" . $type . "' ";
        }

        if (isset($is_supplement_info) && !empty($is_supplement_info)) {
            $sql .= " AND a.is_supplement_info = '" . $is_supplement_info . "' ";
        }
        if (isset($appraisal_status) && !empty($appraisal_status)) {
            $sql .= " AND a.appraisal_status = '" . $appraisal_status . "' ";
        }

        if (isset($on_status) && !empty($on_status)) {
            $sql .= " AND a.on_status = " . $on_status;
        }

        if (isset($channel_status) && !empty($channel_status)) {
            $sql .= " AND a.channel_status = " . $channel_status;
        }

        if (isset($not_in) && !empty($not_in)) {//过滤正在使用的秒杀

            $ids = SeckillModel::getYesAll()['ids'];
            if ($ids) {
                $sql .= " AND a.id NOT IN( " . $ids . ")";
            }
        }

        if (isset($not_in_pintuan) && !empty($not_in_pintuan)) {//过滤正在使用的拼团
            if ($seckill_id) {
                $ids = SeckillModel::getPinTuanYesAll($seckill_id)['ids'];
            } else {
                $ids = SeckillModel::getPinTuanYesAll()['ids'];
            }
            if ($ids) {
                $sql .= " AND a.id NOT IN( " . $ids . ")";
            }
        }

        if (isset($admin_name) && !empty($admin_name)) {
            $sql .= " AND a.admin_name like '%{$admin_name}%'" ;
        }

        if (isset($id) && !empty($id)) {
            $sql .= " AND a.id = " . $id;
        }

        if (isset($multi_point_id) && !empty($multi_point_id)) {
            $sql .= " AND pm.multi_point_id = " . $multi_point_id;
        }

        if (isset($brand_name) && !empty($brand_name)) {
            $sql .= " AND b.name like '%" . $brand_name . "%' ";
        }

        if (isset($brand_id) && !empty($brand_id)) {
            if (is_numeric($brand_id)) {
                $sql .= " AND a.brand_id = " . $brand_id;
            } else if (is_array($brand_id)) {
                $sql .= " AND a.brand_id in (" . implode(',', $brand_id) . ") ";
            }
        }

        if (isset($category_name) && !empty($category_name)) {
            $sql .= " AND a.category_name like '%" . $category_name . "%' ";
        }
        
        if (isset($category_id) && is_array($category_id)) {
            $sql .= " AND a.category_id in (" . implode(',', $category_id) . ")";
        }
        
        if (isset($start_time) && !empty($start_time)) {
            $sql .= " AND a.created_at >= '".$start_time." 00:00:00'";
        }
        
        if (isset($end_time) && !empty($end_time)) {
            $sql .= " AND a.created_at <= '".$end_time." 23:59:59'";
        }
        
        $result['total'] = $pdo->YDGetOne(str_replace("[*]", "count(DISTINCT a.id) as num", $sql));
        if ($sort && $order) {
            $sql .= " GROUP BY a.id ORDER BY a.{$sort} {$order} limit {$limit},{$rows}";
        } else {
            $sql .= " GROUP BY a.id ORDER BY a.id DESC limit {$limit},{$rows}";
        }
        $result['list'] = $pdo->YDGetAll(str_replace("[*]", $fileds, $sql));
        if (is_array($result['list']) && count($result['list']) > 0) {
            foreach ($result['list'] as $key => $value) {
                $result['list'][$key]['on_status_txt'] = self::ON_STATUS_VALUE[$value['on_status']];
                $result['list'][$key]['channel_status_txt'] = self::CHANNEL_STATUS_VALUE[$value['channel_status']];
                if ($value['brand_id'] == '0') {
                    $result['list'][$key]['brand_name'] = '其他品牌';
                }

                //是否添加过积分规则
                if ($is_score_rule == '1') {
                    $result['list'][$key]['is_score_rule'] = '-';
                    if ($score_rule_id) {
                        $val = ScoreRuleProductModel::getInfoByProductId($value['id'], $score_rule_id);
                    } else {
                        $val = ScoreRuleProductModel::getInfoByProductId($value['id']);
                    }
                    if ($val) {
                        $result['list'][$key]['is_score_rule'] = '已参加其他积分规则';
                    }
                }

                //上海金价
                $gold_price = GoldPrice::getGoldPrice();
                //处理销售价与渠道价
                $result['list'][$key]['gold_sale_price'] = $value['sale_price'];
                if ($value['sale_is_up'] == self::IS_UP_2) {
                    $result['list'][$key]['sale_price'] = bcmul(bcadd($gold_price,$value['sale_up_price'],2),$value['weight'],2).'（浮动）';
                    $result['list'][$key]['gold_sale_price'] = bcmul(bcadd($gold_price,$value['sale_up_price'],2),$value['weight'],2);
                }
                
                $result['list'][$key]['gold_channel_price'] = $value['channel_price'];
                if ($value['channel_is_up'] == self::IS_UP_2) {
                    $result['list'][$key]['channel_price'] = bcmul(bcadd($gold_price,$value['channel_up_price'],2),$value['weight'],2).'（浮动）';
                    $result['list'][$key]['gold_channel_price'] = bcmul(bcadd($gold_price,$value['channel_up_price'],2),$value['weight'],2);
                }
                
                $result['list'][$key]['is_supplement_info_txt'] = $value['is_supplement_info'] == '1'?'否':'是';
                $result['list'][$key]['appraisal_status_txt'] = $value['appraisal_status'] == '1'?'否':'是';
                
                //是否能设置
                $twocategory = CategoryModel::getParentTwoList('902');
                $category_id = [];
                if ($twocategory) {
                    foreach ($twocategory as $kk=>$vv) {
                        $threecategory = CategoryModel::getParentTwoList($vv['id']);
                        if ($threecategory) {
                            foreach ($threecategory as $k=>$v) {
                                $category_id[] = $v['id'];
                            }
                        }
                    }
                }
                $result['list'][$key]['is_ok_appraisal'] = '0';
                if ($category_id && in_array($value['category_id'], $category_id)) {
                    $result['list'][$key]['is_ok_appraisal'] = '1';
                }
            }
        }

        if ($result) {
            return $result;
        } else {
            return false;
        }

    }
    
    
    /* 获取列表*/
    public static function getListCategoryId($attribute = array())
    {
       
        if (!empty($attribute) && is_array($attribute) && count($attribute) > 0) {
            extract($attribute);
        }
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        $pdo = YDLib::getPDO('db_r');
        $fileds = " a.category_id";
        $sql = 'SELECT
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'brand b
		       	ON
		       		b.id = a.brand_id
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_multi_point pm
		       	ON
		       		a.id = pm.product_id AND pm.is_del = 2
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c3
		       	ON
		       		c3.id = a.category_id
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c2
		       	ON
		       		c2.id = c3.parent_id
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c1
		       	ON
		       		c1.id = c2.parent_id
		        WHERE
        		    a.is_del = 2
        		AND
        			a.supplier_id = ' . $adminInfo['supplier_id'] . '
        		';
    
        if (isset($name) && !empty($name)) {
            $sql .= " AND a.name like '%" . $name . "%' ";
        }
    
        if (isset($self_code) && !empty($self_code)) {
            $sql .= " AND a.self_code like '%" . $self_code . "%' ";
        }
    
        if (isset($custom_code) && !empty($custom_code)) {
            $sql .= " AND a.custom_code like '%" . $custom_code . "%' ";
        }
    
        if (isset($type) && !empty($type)) {
            $sql .= " AND a.type = '" . $type . "' ";
        }
    
        if (isset($on_status) && !empty($on_status)) {
            $sql .= " AND a.on_status = " . $on_status;
        }
    
        if (isset($channel_status) && !empty($channel_status)) {
            $sql .= " AND a.channel_status = " . $channel_status;
        }
    
        if (isset($not_in) && !empty($not_in)) {//过滤正在使用的秒杀
    
            $ids = SeckillModel::getYesAll()['ids'];
            if ($ids) {
                $sql .= " AND a.id NOT IN( " . $ids . ")";
            }
        }
        

        if ($just_bag) {
            //只获取包的分类
            $twocategory = CategoryModel::getParentTwoList('902');
            $category_id = [];
            if ($twocategory) {
                foreach ($twocategory as $key=>$value) {
                    $threecategory = CategoryModel::getParentTwoList($value['id']);
                    if ($threecategory) {
                        foreach ($threecategory as $k=>$v) {
                            $category_id[] = $v['id'];
                        }
                    }
                }
            }
        
            if ($category_id) {
                $sql .= " AND a.category_id IN (".implode(',', $category_id).")";
            }
        }
        

        if (isset($is_supplement_info) && !empty($is_supplement_info)) {
            $sql .= " AND a.is_supplement_info = '" . $is_supplement_info . "' ";
        }
        if (isset($appraisal_status) && !empty($appraisal_status)) {
            $sql .= " AND a.appraisal_status = '" . $appraisal_status . "' ";
        }
    
        if (isset($not_in_pintuan) && !empty($not_in_pintuan)) {//过滤正在使用的拼团
            if ($seckill_id) {
                $ids = SeckillModel::getPinTuanYesAll($seckill_id)['ids'];
            } else {
                $ids = SeckillModel::getPinTuanYesAll()['ids'];
            }
            if ($ids) {
                $sql .= " AND a.id NOT IN( " . $ids . ")";
            }
        }
    
        if (isset($admin_name) && !empty($admin_name)) {
            $sql .= " AND a.admin_name like '%{$admin_name}%'" ;
        }
    
        if (isset($id) && !empty($id)) {
            $sql .= " AND a.id = " . $id;
        }
    
        if (isset($multi_point_id) && !empty($multi_point_id)) {
            $sql .= " AND pm.multi_point_id = " . $multi_point_id;
        }
    
        if (isset($brand_name) && !empty($brand_name)) {
            $sql .= " AND b.name like '%" . $brand_name . "%' ";
        }
    
        if (isset($category_name) && !empty($category_name)) {
            $sql .= " AND a.category_name like '%" . $category_name . "%' ";
        }
        
        if (isset($category_id) && is_array($category_id)) {
            $sql .= " AND a.category_id in (" . implode(',', $category_id) . ")";
        }
    
        if (isset($start_time) && !empty($start_time)) {
            $sql .= " AND a.created_at >= '".$start_time." 00:00:00'";
        }
        
        if (isset($end_time) && !empty($end_time)) {
            $sql .= " AND a.created_at <= '".$end_time." 23:59:59'";
        }
        $sql .= " GROUP BY a.id,a.category_id ";
        
        $result = $pdo->YDGetAll(str_replace("[*]", $fileds, $sql));
        
        $category_id = '';
        if ($result) {
            $category_id = [];
            foreach ($result as $key => $value) {
                $category_id[$key] = $value['category_id'];
            }
            $category_id = implode(',', $category_id);
        }
        return $category_id;
    }
    
    
    /* 获取列表*/
    public static function getListBrandId($attribute = array())
    {
         
        if (!empty($attribute) && is_array($attribute) && count($attribute) > 0) {
            extract($attribute);
        }
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        $pdo = YDLib::getPDO('db_r');
        $fileds = " a.brand_id";
        $sql = 'SELECT
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'brand b
		       	ON
		       		b.id = a.brand_id
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_multi_point pm
		       	ON
		       		a.id = pm.product_id AND pm.is_del = 2
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c3
		       	ON
		       		c3.id = a.category_id
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c2
		       	ON
		       		c2.id = c3.parent_id
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c1
		       	ON
		       		c1.id = c2.parent_id
		        WHERE
        		    a.is_del = 2
        		AND
        			a.supplier_id = ' . $adminInfo['supplier_id'] . '
        		';
    
        if (isset($name) && !empty($name)) {
            $sql .= " AND a.name like '%" . $name . "%' ";
        }
    
        if (isset($self_code) && !empty($self_code)) {
            $sql .= " AND a.self_code like '%" . $self_code . "%' ";
        }
    
        if (isset($custom_code) && !empty($custom_code)) {
            $sql .= " AND a.custom_code like '%" . $custom_code . "%' ";
        }
    
        if (isset($type) && !empty($type)) {
            $sql .= " AND a.type = '" . $type . "' ";
        }
    
        if (isset($on_status) && !empty($on_status)) {
            $sql .= " AND a.on_status = " . $on_status;
        }
    
        if (isset($channel_status) && !empty($channel_status)) {
            $sql .= " AND a.channel_status = " . $channel_status;
        }
    
        if (isset($not_in) && !empty($not_in)) {//过滤正在使用的秒杀
    
            $ids = SeckillModel::getYesAll()['ids'];
            if ($ids) {
                $sql .= " AND a.id NOT IN( " . $ids . ")";
            }
        }

        if ($just_bag) {
            //只获取包的分类
            $twocategory = CategoryModel::getParentTwoList('902');
            $category_id = [];
            if ($twocategory) {
                foreach ($twocategory as $key=>$value) {
                    $threecategory = CategoryModel::getParentTwoList($value['id']);
                    if ($threecategory) {
                        foreach ($threecategory as $k=>$v) {
                            $category_id[] = $v['id'];
                        }
                    }
                }
            }
        
            if ($category_id) {
                $sql .= " AND a.category_id IN (".implode(',', $category_id).")";
            }
        }
        

        if (isset($is_supplement_info) && !empty($is_supplement_info)) {
            $sql .= " AND a.is_supplement_info = '" . $is_supplement_info . "' ";
        }
        if (isset($appraisal_status) && !empty($appraisal_status)) {
            $sql .= " AND a.appraisal_status = '" . $appraisal_status . "' ";
        }
    
        if (isset($not_in_pintuan) && !empty($not_in_pintuan)) {//过滤正在使用的拼团
            if ($seckill_id) {
                $ids = SeckillModel::getPinTuanYesAll($seckill_id)['ids'];
            } else {
                $ids = SeckillModel::getPinTuanYesAll()['ids'];
            }
            if ($ids) {
                $sql .= " AND a.id NOT IN( " . $ids . ")";
            }
        }
    
        if (isset($admin_name) && !empty($admin_name)) {
            $sql .= " AND a.admin_name like '%{$admin_name}%'" ;
        }
    
        if (isset($id) && !empty($id)) {
            $sql .= " AND a.id = " . $id;
        }
    
        if (isset($multi_point_id) && !empty($multi_point_id)) {
            $sql .= " AND pm.multi_point_id = " . $multi_point_id;
        }
    
        if (isset($brand_name) && !empty($brand_name)) {
            $sql .= " AND b.name like '%" . $brand_name . "%' ";
        }
    
        if (isset($category_name) && !empty($category_name)) {
            $sql .= " AND a.category_name like '%" . $category_name . "%' ";
        }
    
        if (isset($category_id) && is_array($category_id)) {
            $sql .= " AND a.category_id in (" . implode(',', $category_id) . ")";
        }
    
        if (isset($start_time) && !empty($start_time)) {
            $sql .= " AND a.created_at >= '".$start_time." 00:00:00'";
        }
        
        if (isset($end_time) && !empty($end_time)) {
            $sql .= " AND a.created_at <= '".$end_time." 23:59:59'";
        }
        $sql .= " GROUP BY a.id,a.brand_id ";
    
        $result = $pdo->YDGetAll(str_replace("[*]", $fileds, $sql));
    
        $brand_id = '';
        if ($result) {
            $brand_id = [];
            foreach ($result as $key => $value) {
                $brand_id[$key] = $value['brand_id'];
            }
            $brand_id = implode(',', $brand_id);
        }
        return $brand_id;
    }
    


    /* 获取列表(后台下单专用,包含供应商品) */
    public static function getListAddOrder($attribute = array(), $page = 1, $rows = 10) {
        $limit = $page * $rows;
        
        if (! empty ( $attribute['info'] ) && is_array ( $attribute['info'] ) && count ( $attribute['info'] ) > 0) {
            extract ( $attribute['info'] );
        }
        
        $auth = self::auth();

        $fileds = " a.id,a.name,a.self_code,a.market_price,a.category_id,a.category_name,a.brand_id,
				b.name brand_name,a.logo_url,a.stock,c.id is_id,a.weight,a.channel_price,a.channel_up_price,a.channel_is_up,a.is_return,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.sale_price ELSE c.sale_price END sale_price,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.sale_up_price ELSE c.sale_up_price END sale_up_price,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.sale_is_up ELSE c.sale_is_up END sale_is_up,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.now_at ELSE c.now_at END now_at,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.sale_num ELSE c.sale_num END sale_num";
        $sql = 'SELECT 
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'brand b
		       	ON
		       		b.id = a.brand_id    
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		c.product_id = a.id    
		       	AND
		       		c.supplier_id = '. $auth['supplier_id'].'    
		       	AND
		       		c.is_del = '.self::DELETE_SUCCESS.'    		       		  
		        WHERE
        		    a.is_del = 2
        		AND 
        			( a.supplier_id = ' . $auth['supplier_id'] . '
        			OR
        			  c.id is not null 
        			)
                AND (
                        (
                            a.supplier_id = ' . $auth['supplier_id'] . ' 
                        AND 
                            a.on_status = 2
                        )
                        OR
                        (
                            a.supplier_id != ' . $auth['supplier_id'] . ' 
                        AND 
                            c.on_status = 2 
                        AND 
                            a.channel_status = 3
                        )
                     )
        		AND 
        			a.stock > 0
        		';

        if (isset ( $name ) && ! empty ( $name )) {
            $sql .= " AND a.name like '%" . $name . "%' ";
        }

        if (isset ( $type ) && ! empty ( $type )) {
            $sql .= " AND a.type = '" . $type . "' ";
        }

        if (isset ( $ids ) && ! empty ( $ids )) {
            $sql .= " AND a.id IN (" . $ids . ")";
        }
        
        if (isset ( $id ) && ! empty ( $id )) {
            $sql .= " AND a.id = " . $id ;
        }
        
        if (isset ( $self_code ) && ! empty ( $self_code )) {
            $sql .= " AND a.self_code = " . $self_code ;
        }

        if (isset($not_in) && !empty($not_in)) {//过滤正在使用的秒杀

            $ids = SeckillModel::getYesAll()['ids'];
            if ($ids) {
                $sql .= " AND a.id NOT IN( " . $ids . ")";
            }
        }

        if (isset($not_in_pintuan) && !empty($not_in_pintuan)) {//过滤正在使用的拼团
            if (isset($seckill_id)) {
                $ids = SeckillModel::getPinTuanYesAll($seckill_id)['ids'];
            } else {
                $ids = SeckillModel::getPinTuanYesAll()['ids'];
            }
            if ($ids) {
                $sql .= " AND a.id NOT IN( " . $ids . ")";
            }
        }
        
        if (isset ( $category_id ) && ! empty ( $category_id )) {
            if (is_numeric ( $category_id )) {
                $sql .= " AND a.category_id = '" . $category_id . "' ";
            } else if (is_array( $category_id )){
                $sql .= " AND a.category_id IN (" . implode(',', $category_id) . ")";
            } else {
                $sql .= " AND a.category_id IN (" . $category_id . ")";
            }
        }

        if (isset ( $brand_name ) && ! empty ( $brand_name )) {
            $sql .= " AND b.name like '%" . $brand_name . "%' ";
        }
        
        if (isset ( $brand_id ) && ! empty ( $brand_id )) {
            if (is_numeric ( $brand_id )) {
                $sql .= " AND a.brand_id = '" . $brand_id . "' ";
            } else if (is_array( $brand_id )){
                $sql .= " AND a.brand_id IN (" . implode(',', $brand_id) . ")";
            } else {
                $sql .= " AND a.brand_id IN (" . $brand_id . ")";
            }
        }

        $pdo = YDLib::getPDO ( 'db_r' );
        $result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );

        $sort = isset ( $sort ) ? $sort : 'id';
        $order = isset ( $order ) ? $order : 'DESC';
        $sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
        $result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
        if (is_array($result ['list']) && count($result ['list']) > 0) {
            //上海金价
            $gold_price = GoldPrice::getGoldPrice();
            foreach ( $result['list'] as $key => $value) {
                if ($value['brand_id'] == '0') {
                    $result['list'][$key]['brand_name'] = '其他品牌';
                }
                if (!empty($value['is_id'])) {
                    $result['list'][$key]['product_type_txt'] = '供应商品';
                    //处理商品供应价
                    if ($value['channel_is_up'] == self::IS_UP_2) {
                        $result['list'][$key]['channel_price'] = bcmul(bcadd($gold_price,$value['channel_up_price'],2),$value['weight'],2);
                    }
                    //处理商品销售价
                    if ($value['sale_is_up'] == self::IS_UP_2) {
                        $result['list'][$key]['sale_price'] = bcadd($result['list'][$key]['channel_price'],$value['sale_up_price'],2);
                    }
                } else {
                    $result['list'][$key]['product_type_txt'] = '自有商品';
                    //处理商品渠道价
                    if ($value['channel_is_up'] == self::IS_UP_2) {
                        $result['list'][$key]['channel_price'] = bcmul(bcadd($gold_price,$value['channel_up_price'],2),$value['weight'],2);
                    }
                    //处理商品销售价
                    if ($value['sale_is_up'] == self::IS_UP_2) {
                        $result['list'][$key]['sale_price'] = bcmul(bcadd($gold_price,$value['sale_up_price'],2),$value['weight'],2);
                    }
                }
                $result['list'][$key]['is_return_txt'] = '是';
                if ($value['is_return'] == '1') {//否
                    $result['list'][$key]['is_return_txt'] = '否';
                }
            }
        }
        return $result;
    }
    
    
    /* 获取分类id列表(后台下单专用,包含供应商品) */
    public static function getCategoryListAddOrder($attribute = array(), $page = 1, $rows = 10) {
        $limit = $page * $rows;
    
        if (! empty ( $attribute ) && is_array ( $attribute ) && count ( $attribute ) > 0) {
            extract ( $attribute );
        }
    
        $auth = self::auth();
    
        $fileds = " a.category_id";
        $sql = 'SELECT
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'brand b
		       	ON
		       		b.id = a.brand_id
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		c.product_id = a.id
		       	AND
		       		c.supplier_id = '. $auth['supplier_id'].'
		       	AND
		       		c.is_del = '.self::DELETE_SUCCESS.'
		        WHERE
        		    a.is_del = 2
        		AND
        			( a.supplier_id = ' . $auth['supplier_id'] . '
        			OR
        			  c.id is not null
        			)
                AND (
                        (
                            a.supplier_id = ' . $auth['supplier_id'] . '
                        AND
                            a.on_status = 2
                        )
                        OR
                        (
                            a.supplier_id != ' . $auth['supplier_id'] . '
                        AND
                            c.on_status = 2
                        AND
                            a.channel_status = 3
                        )
                     )
        		AND
        			a.stock > 0
        		';
    
        if (isset ( $name ) && ! empty ( $name )) {
            $sql .= " AND a.name like '%" . $name . "%' ";
        }
    
        if (isset ( $type ) && ! empty ( $type )) {
            $sql .= " AND a.type = '" . $type . "' ";
        }
    
        if (isset ( $ids ) && ! empty ( $ids )) {
            $sql .= " AND a.id IN (" . $ids . ")";
        }
    
        if (isset ( $category_id ) && ! empty ( $category_id )) {
            if (is_numeric ( $category_id )) {
                $sql .= " AND a.category_id = '" . $category_id . "' ";
            } else {
                $sql .= " AND a.category_id IN (" . $category_id . ")";
            }
        }
    
        if (isset ( $brand_id ) && ! empty ( $brand_id )) {
            if (is_numeric ( $category_id )) {
                $sql .= " AND a.brand_id = '" . $brand_id . "' ";
            } else {
                $sql .= " AND a.brand_id IN (" . $brand_id . ")";
            }
        }
    
        $pdo = YDLib::getPDO ( 'db_r' );
       
        
        $sql .= " GROUP BY a.category_id";
        $result = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
        
        $category_id = '';
        if ($result) {
            $category_id = [];
            foreach ($result as $key => $value) {
                $category_id[$key] = $value['category_id'];
            }
            $category_id = implode(',', $category_id);
        }
        return $category_id;
    
    }
    
    
    
    
    /* 获取品牌id列表(后台下单专用,包含供应商品) */
    public static function getBrandListAddOrder($attribute = array(), $page = 1, $rows = 10) {
        $limit = $page * $rows;
    
        if (! empty ( $attribute ) && is_array ( $attribute ) && count ( $attribute ) > 0) {
            extract ( $attribute );
        }
    
        $auth = self::auth();
    
        $fileds = " a.brand_id";
        $sql = 'SELECT
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'brand b
		       	ON
		       		b.id = a.brand_id
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		c.product_id = a.id
		       	AND
		       		c.supplier_id = '. $auth['supplier_id'].'
		       	AND
		       		c.is_del = '.self::DELETE_SUCCESS.'
		        WHERE
        		    a.is_del = 2
        		AND
        			( a.supplier_id = ' . $auth['supplier_id'] . '
        			OR
        			  c.id is not null
        			)
                AND (
                        (
                            a.supplier_id = ' . $auth['supplier_id'] . '
                        AND
                            a.on_status = 2
                        )
                        OR
                        (
                            a.supplier_id != ' . $auth['supplier_id'] . '
                        AND
                            c.on_status = 2
                        AND
                            a.channel_status = 3
                        )
                     )
        		AND
        			a.stock > 0
        		';
    
        if (isset ( $name ) && ! empty ( $name )) {
            $sql .= " AND a.name like '%" . $name . "%' ";
        }
    
        if (isset ( $type ) && ! empty ( $type )) {
            $sql .= " AND a.type = '" . $type . "' ";
        }
    
        if (isset ( $ids ) && ! empty ( $ids )) {
            $sql .= " AND a.id IN (" . $ids . ")";
        }
    
        if (isset ( $category_id ) && ! empty ( $category_id )) {
            if (is_numeric ( $category_id )) {
                $sql .= " AND a.category_id = '" . $category_id . "' ";
            } else {
                $sql .= " AND a.category_id IN (" . $category_id . ")";
            }
        }
    
        if (isset ( $brand_id ) && ! empty ( $brand_id )) {
            if (is_numeric ( $category_id )) {
                $sql .= " AND a.brand_id = '" . $brand_id . "' ";
            } else {
                $sql .= " AND a.brand_id IN (" . $brand_id . ")";
            }
        }
    
        $pdo = YDLib::getPDO ( 'db_r' );
         
    
        $sql .= " GROUP BY a.brand_id";
        $result = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
    
        $brand_id = '';
        if ($result) {
            $brand_id = [];
            foreach ($result as $key => $value) {
                $brand_id[$key] = $value['brand_id'];
            }
            $brand_id = implode(',', $brand_id);
        }
        return $brand_id;
    
    }
    
    

    /* 供应商品列表*/
    public static function getChannelList(array $search = [])
    {
        $builder = new Builder();

        $auth = self::auth();

        // 选择列语句
        $fileds = " a.*,b.name as brand_name,c3.name c3_name,c2.name c2_name,c1.name c1_name,c.sale_num is_sale_num,
        		c.on_status is_on_status,c.now_at is_now_at,c.sale_price is_sale_price,c.id is_id,s1.is_del,
        		c.sale_up_price is_sale_up_price,c.sale_is_up is_sale_is_up";
        $builder->select(explode(',', $fileds));

        // 表和表连接语句
        $form = CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		c.product_id = a.id    
		       	AND
		       		c.supplier_id = ' . $auth['supplier_id'] . '    
		       	AND
		       		c.is_del = ' . self::DELETE_SUCCESS . '   
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'brand b
		       	ON
		       		b.id = a.brand_id    
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c3
		       	ON
		       		c3.id = a.category_id
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c2
		       	ON
		       		c2.id = c3.parent_id    
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c1
		       	ON
		       		c1.id = c2.parent_id
		        LEFT JOIN
		            ' . SupplierModel::getFullTable() . ' s1 
		        ON
		            a.supplier_id = s1.id';
        $builder->from($form);

        // 必要查询条件
        $builder->where('a.is_del', self::DELETE_SUCCESS);
        $builder->where('a.supplier_id', '!=', $auth['supplier_id']);
        $builder->where('s1.is_del', self::DELETE_SUCCESS);

        // 搜索查询条件
        // 商品编号搜索
        $selfCode = Arr::value($search, 'self_code');
        if ($selfCode) {
            $builder->where('a.self_code', $selfCode);
        }

        // 商品名称搜索
        $productName = Arr::value($search, 'product_name');
        if ($productName) {
            $builder->where('a.name', 'like', "%{$productName}%");
        }

        // 品牌名称搜索
        $brandName = Arr::value($search, 'brand_name');
        $brandName and $builder->where('b.name', 'like', "%$brandName%");

        // 分类名称搜索
        $categoryName = Arr::value($search, 'category_name');
        $categoryName and $builder->where('a.category_name', 'like', "%{$categoryName}%");
        
        // 分类id搜索
        $categoryId = Arr::value($search, 'category_id');
        if (is_array($categoryId) && count($categoryId) > '0') {
            $builder->where('a.category_id', 'in', $categoryId);
        }
        
        // 品牌id搜索
        $brand_id = Arr::value($search, 'brand_id');
        if (is_array($brand_id) && count($brand_id) > '0') {
            $builder->where('a.brand_id', 'in', $brand_id);
        }
        
        // 商品类型搜索
        $type = Arr::value($search, 'type');
        if ($type) {
            $builder->where('a.type', $type);
        }

        // 渠道状态搜索
        $channel_status = Arr::value($search, 'channel_status');
        if ($channel_status) {
            $builder->where('a.channel_status', $channel_status);
        }

        //是否已经上架到自己的微商城
        $is_on_status = Arr::value($search, 'is_on_status');
        if ($is_on_status) {
            if ($is_on_status == '1') {//未上架到自己的微商城
                $builder->where('c.id', 'is null');
            } else if ($is_on_status == '2') {
                $builder->where('c.id', 'is not null');
            }
        }

        // 商品编号搜索
        $selfCode = Arr::value($search, 'self_code');
        if ($selfCode) {
            $builder->where('a.self_code', $selfCode);
        }

        // 添加时间搜索
        $start = Arr::value($search, 'start_time');
        $end = Arr::value($search, 'end_time');

        if ($start && $end && $start <= $end) {
            $builder->where('a.created_at', '>=', Date::startOfDay($start));
            $builder->where('a.created_at', '<=', Date::endOfDay($end));
        }

        // 是否上架搜索
        $onStatus = Arr::value($search, 'on_status');
        $onStatus and $builder->where('a.on_status', $onStatus);

        //库存大于0
        $use_type = Arr::value($search, 'use_type');
        if (isset($use_type) && $use_type == 'add') {
            $builder->where('a.stock', '>', 0);
        }
        // 修正多表连表时排序字段增加别名前缀，单表不需要此操作
        $builder->orderAlias('a');

        $result = static::paginate($builder);
        //上海金价
        $gold_price = GoldPrice::getGoldPrice();
        foreach ($result['rows'] as $key => $value) {
            $result['rows'][$key]['is_on_status_txt'] = ProductChannelModel::ON_STATUS_VALUE[$value['is_on_status']];
            $result['rows'][$key]['full_logo_url'] = HOST_FILE . ltrim($value['logo_url'],'/');
            $result['rows'][$key]['is_return_text'] = $value['is_return'] == '2'?'是':'否';
            //价格转换
            $channel_price = $result['rows'][$key]['channel_price'];
            if ($value['channel_is_up'] == self::IS_UP_2) {
                $channel_price = bcmul(bcadd($gold_price,$value['channel_up_price'],2),$value['weight'],2);
                if (isset($use_type) && $use_type == 'add') {
                    $result['rows'][$key]['channel_price'] = $channel_price;
                } else {
                    $result['rows'][$key]['channel_price'] = $channel_price.'（浮动）';
                }
            }
            if ($value['is_sale_is_up'] == self::IS_UP_2) {
                if (isset($use_type) && $use_type == 'add') {
                    $result['rows'][$key]['is_sale_price'] = bcadd($channel_price,$value['is_sale_up_price'],2);
                } else {
                    $result['rows'][$key]['is_sale_price'] = bcadd($channel_price,$value['is_sale_up_price'],2).'（浮动）';
                }
            }
        }
        return $result;
    }
    
    
    
    /* 供应商品品牌列表*/
    public static function getChannelBrand(array $search = [])
    {
        $builder = new Builder();
    
        $auth = self::auth();
    
        // 选择列语句
        $fileds = " a.brand_id";
        $builder->select(explode(',', $fileds));
    
        // 表和表连接语句
        $form = CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		c.product_id = a.id
		       	AND
		       		c.supplier_id = ' . $auth['supplier_id'] . '
		       	AND
		       		c.is_del = ' . self::DELETE_SUCCESS . '
		        LEFT JOIN
		            ' . SupplierModel::getFullTable() . ' s1
		        ON
		            a.supplier_id = s1.id';
        $builder->from($form);
    
        //是否已经上架到自己的微商城
        $is_on_status = Arr::value($search, 'is_on_status');
        if ($is_on_status) {
            if ($is_on_status == '1') {//未上架到自己的微商城
                $builder->where('c.id', 'is null');
            } else if ($is_on_status == '2') {
                $builder->where('c.id', 'is not null');
            }
        }
    
        // 渠道状态搜索
        $channel_status = Arr::value($search, 'channel_status');
        if ($channel_status) {
            $builder->where('a.channel_status', $channel_status);
        }
    
        // 商品类型搜索
        $type = Arr::value($search, 'type');
        if ($type) {
            $builder->where('a.type', $type);
        }
    
        // 必要查询条件
        $builder->where('a.is_del', self::DELETE_SUCCESS);
        $builder->where('a.supplier_id', '!=', $auth['supplier_id']);
        $builder->where('s1.is_del', self::DELETE_SUCCESS);
        $builder->groupBy('a.brand_id');
    
        $query = self::newRead();
        $result = $query->YDGetAll($builder->showSql()['query']);
    
        $brand_id = '';
        if ($result) {
            $brand_id = [];
            foreach ($result as $key => $value) {
                $brand_id[$key] = $value['brand_id'];
            }
            $brand_id = implode(',', $brand_id);
        }
        return $brand_id;
    }
    
    
    
    /* 供应商品分类列表*/
    public static function getChannelCategory(array $search = [])
    {
        $builder = new Builder();
    
        $auth = self::auth();
    
        // 选择列语句
        $fileds = " a.category_id";
        $builder->select(explode(',', $fileds));
    
        // 表和表连接语句
        $form = CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		c.product_id = a.id
		       	AND
		       		c.supplier_id = ' . $auth['supplier_id'] . '
		       	AND
		       		c.is_del = ' . self::DELETE_SUCCESS . '
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c3
		       	ON
		       		c3.id = a.category_id
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c2
		       	ON
		       		c2.id = c3.parent_id    
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'category c1
		       	ON
		       		c1.id = c2.parent_id
		        LEFT JOIN
		            ' . SupplierModel::getFullTable() . ' s1
		        ON
		            a.supplier_id = s1.id';
        $builder->from($form);
    
        //是否已经上架到自己的微商城
        $is_on_status = Arr::value($search, 'is_on_status');
        if ($is_on_status) {
            if ($is_on_status == '1') {//未上架到自己的微商城
                $builder->where('c.id', 'is null');
            } else if ($is_on_status == '2') {
                $builder->where('c.id', 'is not null');
            }
        }
        
        // 渠道状态搜索
        $channel_status = Arr::value($search, 'channel_status');
        if ($channel_status) {
            $builder->where('a.channel_status', $channel_status);
        }
        
        // 商品类型搜索
        $type = Arr::value($search, 'type');
        if ($type) {
            $builder->where('a.type', $type);
        }
        
        // 必要查询条件
        $builder->where('a.is_del', self::DELETE_SUCCESS);
        $builder->where('a.supplier_id', '!=', $auth['supplier_id']);
        $builder->where('s1.is_del', self::DELETE_SUCCESS);
        $builder->groupBy('a.category_id');
    
        $query = self::newRead();
        $result = $query->YDGetAll($builder->showSql()['query']);
        
        $category_id = '';
        if ($result) {
            $category_id = [];
            foreach ($result as $key => $value) {
                $category_id[$key] = $value['category_id'];
            }
            $category_id = implode(',', $category_id);
        }
        return $category_id;
    }


    /**
     * 添加
     *
     * @param array $info 商品主要信息
     * @param array $item 图片信息
     * @return mixed
     *
     */
    public static function add($info, $item)
    {
        $multi_point_id = '';
        //多网点
        if ($info['multi_point_id']) {
            $multi_point_id = $info['multi_point_id'];
        }
        unset($info['multi_point_id']);

        $adminId = AdminModel::getAdminID();
        $adminInfo = AdminModel::getAdminLoginInfo($adminId);
        $db = YDLib::getPDO('db_w');
        $db->beginTransaction();
        try {

            $info['category_name'] = self::getCategoryName($info);
            $info['admin_id'] = $adminId;
            $info['admin_name'] = $adminInfo['fullname'];
            $info['supplier_id'] = $adminInfo['supplier_id'];
            $info['is_del'] = '2';
            $result = $db->insert(self::$_tableName, $info, ['ignore' => true]);

            $db->update('img', ['is_del' => '1', 'deleted_at' => date('Y-m-d h:i:s')], ['obj_id' => $result, 'type' => 'product', 'is_del' => '2']);
            if (is_array($item) && count($item) > 0) {
                foreach ($item as $key => $value) {
                    $imgList = [];
                    $imgList['supplier_id'] = $adminInfo['supplier_id'];
                    $imgList['img_url'] = $value;
                    $imgList['obj_id'] = $result;
                    $imgList['type'] = 'product';
                    $imgList['img_type'] = pathinfo($value, PATHINFO_EXTENSION);
                    $imgList['is_del'] = 2;
                    $lastId = $db->insert('img', $imgList, ['ignore' => true]);
                    if (!$lastId) {
                        $db->rollback();
                        return FALSE;
                    }
                }
            }

            if ($multi_point_id) {
                $product_multi = [];
                $product_multi['product_id'] = $result;
                $product_multi['multi_point_id'] = $multi_point_id;
                $product_multi['supplier_id'] = $adminInfo['supplier_id'];
                $product_multi['is_del'] = '2';
                $product_multi_last_id = $db->insert('product_multi_point', $product_multi, ['ignore' => true]);
                if (!$product_multi_last_id) {
                    $db->rollback();
                    return FALSE;
                }
            }


            $db->commit();
            return $result;
        } catch (\Exception $e) {
            $db->rollback();
            return FALSE;
        }

    }

    /**
     * 获取单条数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getInfoByID($id)
    {
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['id'] = intval($id);

        $pdo = self::_pdo('db_r');
        $info = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
        $info['full_logo_url'] = HOST_FILE . ltrim($info['logo_url'],'/');
        $info['multi_point'] = [];
        $multi_point = ProductMultiPointModel::getInfoByProductId($info['id']);
        if ($multi_point) {
            foreach ($multi_point as $key => $value) {
                $multi_point[$key]['multi_point_name'] = MultipointModel::getInfoByID($value['multi_point_id'])['name'];
            }
            $info['multi_point'] = $multi_point;
        }

        $info['category_name'] = self::getCategoryName($info);
        if ($info['brand_id'] == '0') {
            $info['brand_name'] = '其他品牌';
        } else {
            $info['brand_name'] = BrandModel::getInfoByID($info['brand_id'])['name'];
        }
        $info['imglist'] = ImageModel::getInfoByTypeOrID(intval($id));
        $productAbList = ProductAttributeModel::getInfoByProductId(intval($id));
        $info['attr'] = '0';
        if (empty($productAbList)) {
            $info['attr'] = '1';//分类带出来的属性查看不显示
            $productAbList = CategoryAttributeModel::getattrInfoByCategoryId($info['category_id']);
        }

        $produceArray = [];
        //已选中值
        foreach ($productAbList as $key => $value) {
            $produceArray[$value['attribute_id']]['attribute_id'] = $value['attribute_id'];
            $produceArray[$value['attribute_id']]['attribute_name'] = $value['attribute_name'];
            $produceArray[$value['attribute_id']]['attribute_value_id'] = $value['attribute_value_id'] ? $value['attribute_value_id'] : '';
            $produceArray[$value['attribute_id']]['attribute_value_name'] = $value['attribute_value_name'] ? $value['attribute_value_name'] : '';
            $produceArray[$value['attribute_id']]['input_type'] = $value['input_type'];
            //$produceArray[$value['attribute_id']]['is_must_input'] = $value['is_must_input'];
            $produceArray[$value['attribute_id']]['is_now'][] = $value;
        }
        //所有值
        foreach ($produceArray as $key => $value) {
            //不等于输入框
            if ($value['input_type'] != 3) {
                $produceArray[$key]['values'] = AttributeValueModel::getInfoByAttributeId($key);
                //标记已选择中的值
                $is_nowList = array_column($value['is_now'], 'attribute_value_id');
                foreach ($produceArray[$key]['values'] as $k => $v) {
                    if (in_array($v['id'], $is_nowList)) {
                        $produceArray[$key]['values'][$k]['selectd'] = 1;
                    } else {
                        $produceArray[$key]['values'][$k]['selectd'] = 0;
                    }
                }
            }
        }
        $info['attribute'] = $produceArray;
        return $info;
    }

    /**
     * 获取供应商品数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getChannelInfoByID($id)
    {
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['id'] = intval($id);

        $pdo = self::_pdo('db_r');
        $info = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();

        $info['category_name'] = self::getCategoryName($info);
        if ($info['brand_id'] == '0') {
            $info['brand_name'] = '其他品牌';
        } else {
            $info['brand_name'] = BrandModel::getInfoByID($info['brand_id'])['name'];
        }
        $info['imglist'] = ImageModel::getInfoByTypeOrIDUse(intval($id));
        $productAbList = ProductAttributeModel::getInfoByProductId(intval($id));
        $produceArray = [];
        // 已选中值
        foreach ($productAbList as $key => $value) {
            $produceArray [$value ['attribute_id']] ['attribute_id'] = $value ['attribute_id'];
            $produceArray [$value ['attribute_id']] ['attribute_name'] = $value ['attribute_name'];
            $produceArray [$value ['attribute_id']] ['input_type'] = $value ['input_type'];
            if ($value ['input_type'] != 2) {
                $produceArray [$value ['attribute_id']] ['attribute_value_id'] = $value ['attribute_value_id'];
                $produceArray [$value ['attribute_id']] ['attribute_value_name'] = $value ['attribute_value_name'];
            } else {
                if (!isset ($produceArray [$value ['attribute_id']] ['attribute_value_id'])) {
                    $produceArray [$value ['attribute_id']] ['attribute_value_id'] = '';
                }
                if (!isset ($produceArray [$value ['attribute_id']] ['attribute_value_name'])) {
                    $produceArray [$value ['attribute_id']] ['attribute_value_name'] = '';
                }
                $produceArray [$value ['attribute_id']] ['attribute_value_id'] .= $value ['attribute_value_id'] . ' ';
                $produceArray [$value ['attribute_id']] ['attribute_value_name'] .= $value ['attribute_value_name'] . ' ';
            }
        }
        $info ['attribute'] = $produceArray;

        //查询渠道上架信息
        $auth = self::auth();
        $channelWhere['supplier_id'] = $auth['supplier_id'];
        $channelWhere['product_id'] = $id;
        $channelDetail = ProductChannelModel::findOneWhere($channelWhere);
        $info['is_sale_num'] = $channelDetail['sale_num'];
        $info['is_on_status'] = $channelDetail['on_status'];
        $info['is_now_at'] = $channelDetail['now_at'];
        $info['is_sale_price'] = $channelDetail['sale_price'];
        $info['is_sale_up_price'] = $channelDetail['sale_up_price'];
        $info['is_sale_is_up'] = $channelDetail['sale_is_up'];
        $info['is_id'] = $channelDetail['id'];
        $info['is_on_status_txt'] = ProductChannelModel::ON_STATUS_VALUE[$info['is_on_status']];
        return $info;
    }


    /**
     * 定价售卖
     *
     * @param interger $info
     * @param interger $id
     * @return mixed
     *
     */
    public static function onSale($info, $id)
    {
        $auth = self::auth();

        $productDetail = self::find($id)->attributes;

        $info['supplier_id'] = $auth['supplier_id'];
        $info['product_id'] = $id;
        $info['product_name'] = $productDetail['name'];
        $info['product_supplier_id'] = $productDetail['supplier_id'];
        $info['on_status'] = ProductChannelModel::ON_STATUS_2;
        $info['now_at'] = date("Y-m-d H:i:s");
        $info['is_del'] = self::DELETE_SUCCESS;
        $info['updated_at'] = date("Y-m-d H:i:s");

        $where['supplier_id'] = $auth['supplier_id'];
        $where['product_id'] = $id;
        $channelDetail = ProductChannelModel::findOneWhere($where);

        if ($channelDetail) {
            $result = ProductChannelModel::updateByID($info, $channelDetail['id']);
        } else {
            $result = ProductChannelModel::addData($info);
        }

        return $result;
    }

    /**
     * 获取单条少一点数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getSingleInfoByID($id)
    {
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['id'] = intval($id);

        $pdo = self::_pdo('db_r');
        $info = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();

        return $info;
    }

    /**
     * 获取多条数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getInfoByIDs($ids)
    {
        if (empty ($ids))
            return false;

        $auth = self::auth();

        $fileds = " a.id,a.name,a.self_code,a.market_price,a.channel_price,a.purchase_price,a.introduction,a.category_id,a.category_name,a.brand_id,
				a.logo_url,a.introduction,a.stock,a.lock_stock,a.supplier_id,c.id is_id,a.weight,a.channel_price,a.channel_up_price,a.channel_is_up,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.sale_price ELSE c.sale_price END sale_price,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.sale_up_price ELSE c.sale_up_price END sale_up_price,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.sale_is_up ELSE c.sale_is_up END sale_is_up,    
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.now_at ELSE c.now_at END now_at,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.sale_num ELSE c.sale_num END sale_num";
        $sql = 'SELECT 
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a  
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		c.product_id = a.id    
		       	AND
		       		c.supplier_id = '. $auth['supplier_id'].'    
		       	AND
		       		c.is_del = '.self::DELETE_SUCCESS.'    		       		  
		        WHERE
        		    a.is_del = 2
        		AND 
        			( a.supplier_id = ' . $auth['supplier_id'] . '
        			OR
        			  c.id is not null 
        			)
                AND (
                        (
                            a.supplier_id = ' . $auth['supplier_id'] . ' 
                        AND 
                            a.on_status = 2
                        )
                        OR
                        (
                            a.supplier_id != ' . $auth['supplier_id'] . ' 
                        AND 
                            c.on_status = 2 
                        AND 
                            a.channel_status = 3
                        )
                     )
        		AND 
        			a.id IN(' . $ids.')
        		';


        $pdo = self::_pdo ( 'db_r' );


        $infos = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
        if (! $infos) {
            return FALSE;
        }

        //上海金价
        $gold_price = GoldPrice::getGoldPrice();
        foreach ( $infos as &$info) {
            if (!empty($info['is_id'])) {
                //处理商品供应价
                if ($info['channel_is_up'] == self::IS_UP_2) {
                    $info['channel_price'] = bcmul(bcadd($gold_price, $info['channel_up_price'], 2), $info['weight'], 2);
                }
                //处理商品销售价
                if ($info['sale_is_up'] == self::IS_UP_2) {
                    $info['sale_price'] = bcadd($info['channel_price'], $info['sale_up_price'], 2);
                }
            } else {
                //处理商品渠道价
                if ($info['channel_is_up'] == self::IS_UP_2) {
                    $info['channel_price'] = bcmul(bcadd($gold_price, $info['channel_up_price'], 2), $info['weight'], 2);
                }
                //处理商品销售价
                if ($info['sale_is_up'] == self::IS_UP_2) {
                    $info['sale_price'] = bcmul(bcadd($gold_price, $info['sale_up_price'], 2), $info['weight'], 2);
                }
            }
        }

        return $infos;

    }


    /**
     * 根据$id 获取单条数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getSupplierIdInfoByID($id)
    {
        if (empty ($id))
            return false;

        $pdo = self::_pdo('db_r');
        $fileds = " a.*,b.company,b.phone";

        $sql = 'SELECT
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        LEFT JOIN
		             ' . CommonBase::$_tablePrefix . 'supplier b
		        ON
		             a.supplier_id = b.id
		       AND
		             a.is_del = 2
			   AND 
		             b.is_del = 2
		       WHERE
        		    a.id = ' . $id;

        $userList = $pdo->YDGetRow(str_replace("[*]", $fileds, $sql));

        return $userList;


    }

    /**
     * 获取商品存档数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getArchivesByID($id)
    {
        $where ['id'] = $id;
        $pdo = self::_pdo('db_r');
        $info = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
        $info ['three_info'] = CategoryModel::getInfoByID($info ['category_id']);
        $info ['two_info'] = CategoryModel::getInfoByID($info ['three_info'] ['parent_id']);
        $info ['one_info'] = CategoryModel::getInfoByID($info ['two_info'] ['parent_id']);
        $info ['brand_info'] = BrandModel::getInfoByID($info ['brand_id']);
        $info ['img_info'] = ImageModel::getInfoByTypeOrIDUse($id);
        $info ['attribute_info'] = ProductAttributeModel::getInfoByProductId($id);
        return $info;
    }


    /**
     * 根据一条自增ID更新库存
     * @param array $data 更新字段作为key的数组
     * @param array $id 表自增id
     * @return boolean 更新结果
     */
    public static function updateStockByID($data, $id)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
    }


    /**
     * 根据一条自增ID更新表记录
     * @param array $data 更新字段作为key的数组
     * @param array $id 表自增id
     * @return boolean 更新结果
     */
    public static function updateByID($data, $id)
    {
        $multi_point_id = '';
        //多网点
        if ($data['multi_point_id']) {
            $multi_point_id = $data['multi_point_id'];
        }
        unset($data['multi_point_id']);

        $item = $data['item'];
        unset($data['item']);
        $adminId = AdminModel::getAdminID();
        $adminInfo = AdminModel::getAdminLoginInfo($adminId);
        $data['category_name'] = self::getCategoryName($data);
        $data['updated_at'] = date("Y-m-d H:i:s");
        $db = self::_pdo('db_w');
        $db->beginTransaction();
        try {
            $db->update('img', ['is_del' => '1', 'deleted_at' => date('Y-m-d h:i:s')], ['obj_id' => intval($id), 'type' => 'product', 'is_del' => '2']);
            if (is_array($item) && count($item) > 0) {
                foreach ($item as $key => $value) {
                    $imgList = [];
                    $imgList['supplier_id'] = $adminInfo['supplier_id'];
                    $imgList['img_url'] = $value;
                    $imgList['obj_id'] = intval($id);
                    $imgList['type'] = 'product';
                    $imgList['img_type'] = pathinfo($value, PATHINFO_EXTENSION);
                    $imgList['is_del'] = 2;
                    $lastId = $db->insert('img', $imgList, ['ignore' => true]);
                    if ($lastId === FALSE) {
                        $db->rollback();
                        return FALSE;
                    }
                }
            }

            //删除旧属性
            ProductAttributeModel::deleteByProductID($id);
            //添加属性
            if (is_array($data['select']) && count($data['select']) > 0) {
                foreach ($data['select'] as $key => $value) {
                    $atrVal = json_decode($value, TRUE);
                    $atrVal['product_id'] = $id;
                    $atrVal['type'] = 2;
                    $atrVal['supplier_id'] = $adminInfo['supplier_id'];
                    $add = ProductAttributeModel::addData($atrVal);
                    if ($add === FALSE) {
                        $db->rollback();
                        return FALSE;
                    }
                }
            }
            unset($data['select']);
            if (is_array($data['checkbox']) && count($data['checkbox']) > 0) {
                foreach ($data['checkbox'] as $key => $value) {
                    $atrVal = json_decode($value, TRUE);
                    $atrVal['product_id'] = $id;
                    $atrVal['type'] = 2;
                    $atrVal['supplier_id'] = $adminInfo['supplier_id'];
                    $add = ProductAttributeModel::addData($atrVal);
                    if ($add === FALSE) {
                        $db->rollback();
                        return FALSE;
                    }
                }
            }
            unset($data['checkbox']);
            if (is_array($data['input']) && count($data['input']) > 0) {
                foreach ($data['input'] as $key => $value) {
                    $abInfo = AttributeModel::getInfoByID($key);
                    if (!$abInfo) {
                        $db->rollback();
                        return FALSE;
                    }
                    $atrVal = [];
                    $atrVal['supplier_id'] = $adminInfo['supplier_id'];
                    $atrVal['product_id'] = $id;
                    $atrVal['attribute_id'] = $key;
                    $atrVal['attribute_name'] = $abInfo['name'];
                    $atrVal['attribute_value_name'] = $value;
                    $atrVal['type'] = 1;
                    $add = ProductAttributeModel::addData($atrVal);
                    if ($add === FALSE) {
                        $db->rollback();
                        return FALSE;
                    }
                }
            }
            unset($data['input']);

            $res = $db->update(self::$_tableName, $data, array('id' => intval($id)));
            if (!$res) {
                $db->rollback();
                return FALSE;
            }

            //多网点
            if ($multi_point_id) {
                $del = ProductMultiPointModel::deleteByProductID($id);

                $product_multi = [];
                $product_multi['product_id'] = $id;
                $product_multi['multi_point_id'] = $multi_point_id;
                $product_multi['supplier_id'] = $adminInfo['supplier_id'];
                $product_multi['is_del'] = '2';
                $product_multi_last_id = $db->insert('product_multi_point', $product_multi, ['ignore' => true]);
                if (!$product_multi_last_id) {
                    $db->rollback();
                    return FALSE;
                }
            }


            $db->commit();
            return TRUE;
        } catch (\Exception $e) {
            $db->rollback();
            return FALSE;
        }


    }

    /**
     * 根据表自增 ID删除记录
     * @param int $id 表自增 ID
     * @return boolean 删除是否成功
     */
    public static function deleteByID($id)
    {
        $data['is_del'] = self::DELETE_FAIL;
        $data['updated_at'] = date("Y-m-d H:i:s");
        $data['deleted_at'] = date("Y-m-d H:i:s");

        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
    }

    /**
     * 根据表自增 ID下架商品
     * @param int $id 表自增 ID
     * @return boolean 是否成功
     */
    public static function unstatusByID($data, $id)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");

        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
    }
    
    
    /**
     * 根据表自增 ID下架商品
     * @param int $id 表自增 ID
     * @return boolean 是否成功
     */
    public static function addAppraisalByProductID($data,$ids)
    {
        $pdo = self::_pdo('db_w');
        $pdo->beginTransaction();
        try {
            $data['updated_at'] = date("Y-m-d H:i:s");
            foreach ($ids as $key=>$id) {
                $up = $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
                if (!$up) {
                    $pdo->rollback();
                    return false;
                }
            }
            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollback();
            return false;
        }
    }


    /**
     * 获得分类名称
     * @param array $info
     * @return string
     */
    private static function getCategoryName($info)
    {
        $threeInfo = CategoryModel::getInfoByID($info['category_id']);
        $twoInfo = CategoryModel::getInfoByID($threeInfo['parent_id']);
        $oneInfo = CategoryModel::getInfoByID($twoInfo['parent_id']);
        return $oneInfo['name'] . "|" . $twoInfo['name'] . "|" . $threeInfo['name'];
    }

    /**
     * 编辑商品库存
     * @param integer $num 数量
     * @param integer $id ID
     * @return bool
     */
    public static function editStock($num, $id)
    {
        $sql = "
            UPDATE
                " . CommonBase::$_tablePrefix . self::$_tableName . "
            SET
                updated_at = '" . date("Y-m-d H:i:s") . "', stock=stock+" . $num . "
            WHERE 
            	id=" . intval($id);
        $pdo = self::_pdo('db_w');
        return $pdo->exec($sql);
    }

    /**
     * 获得商品编号
     * @return integer
     */
    public static function getSelfCode()
    {
        $adminId = AdminModel::getAdminID();
        $adminInfo = AdminModel::getAdminLoginInfo($adminId);
        $pdo = self::_pdo('db_r');
        $num = $pdo->clear()->select('count(*) as num')->from(self::$_tableName)->where(['supplier_id' => $adminInfo['supplier_id']])->getOne();
        return sprintf("%05d%04d%05d", $adminInfo['supplier_id'], mt_rand(1000, 9999), ($num + 1));
    }

    /**
     * 获取单条商品数据（用于生成订单）
     *
     * @param interger $id 商品id
     * @param boll $forupdate 是否行级所
     * @return mixed
     *
     */
    public static function getInfoByIDUseAddOrder($id, $forupdate = false)
    {

        $auth = self::auth();

        $fileds = " a.id,a.name,a.self_code,a.market_price,a.channel_price,a.purchase_price,a.introduction,a.category_id,a.category_name,a.brand_id,
				a.logo_url,a.introduction,a.stock,a.lock_stock,a.supplier_id,c.id is_id,a.weight,a.channel_price,a.channel_up_price,a.channel_is_up,a.is_return,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.sale_price ELSE c.sale_price END sale_price,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.sale_up_price ELSE c.sale_up_price END sale_up_price,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.sale_is_up ELSE c.sale_is_up END sale_is_up,    
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.now_at ELSE c.now_at END now_at,
                CASE WHEN a.supplier_id = " . $auth['supplier_id'] . " THEN a.sale_num ELSE c.sale_num END sale_num";
        $sql = 'SELECT 
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a  
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		c.product_id = a.id    
		       	AND
		       		c.supplier_id = '. $auth['supplier_id'].'    
		       	AND
		       		c.is_del = '.self::DELETE_SUCCESS.'    		       		  
		        WHERE
        		    a.is_del = 2
        		AND 
        			( a.supplier_id = ' . $auth['supplier_id'] . '
        			OR
        			  c.id is not null 
        			)
                AND (
                        (
                            a.supplier_id = ' . $auth['supplier_id'] . ' 
                        AND 
                            a.on_status = 2
                        )
                        OR
                        (
                            a.supplier_id != ' . $auth['supplier_id'] . ' 
                        AND 
                            c.on_status = 2 
                        AND 
                            a.channel_status = 3
                        )
                     )
        		AND 
        			a.id = '.$id.'
        		limit 1	
        		';

        if ($forupdate) {
            $sql .= " for UPDATE";
            $pdo = self::_pdo ( 'db_w' );
        } else {
            $pdo = self::_pdo ( 'db_r' );
        }

        $info = $pdo->YDGetRow ( str_replace ( "[*]", $fileds, $sql ) );
        if (! $info) {
            return FALSE;
        }

        //上海金价
        $gold_price = GoldPrice::getGoldPrice();
        if (!empty($info['is_id'])) {
            //处理商品供应价
            if ($info['channel_is_up'] == self::IS_UP_2) {
                $info['channel_price'] = bcmul(bcadd($gold_price,$info['channel_up_price'],2),$info['weight'],2);
            }
            //处理商品销售价
            if ($info['sale_is_up'] == self::IS_UP_2) {
                $info['sale_price'] = bcadd($info['channel_price'],$info['sale_up_price'],2);
            }
        } else {
            //处理商品渠道价
            if ($info['channel_is_up'] == self::IS_UP_2) {
                $info['channel_price'] = bcmul(bcadd($gold_price,$info['channel_up_price'],2),$info['weight'],2);
            }
            //处理商品销售价
            if ($info['sale_is_up'] == self::IS_UP_2) {
                $info['sale_price'] = bcmul(bcadd($gold_price,$info['sale_up_price'],2),$info['weight'],2);
            }
        }

        $info ['category_name'] = self::getCategoryName ( $info );
        $info ['brand_name'] = BrandModel::getInfoByID ( $info ['brand_id'] ) ['name'];
        $info['imglist'] = ImageModel::getInfoByTypeOrID(intval($id));
        //商品属性
        $info['attribute'] = ProductAttributeModel::getInfoByProductId(intval($id));

        return $info;
    }

    /**
     * 获取商品图片与属性
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getAttributeByID($id) {
        $info ['imglist'] = ImageModel::getInfoByTypeOrID ( intval ( $id ) );
        if (is_array ( $info ['imglist'] )) {
            foreach ( $info ['imglist'] as $key => $value ) {
                if (! empty ( $value ['img_url'] )) {
                    $info ['imglist'] [$key] ['img_url'] = HOST_FILE . self::imgSize ( $value ['img_url'], 4 );
                } else {
                    $info ['imglist'] [$key] ['img_url'] = HOST_STATIC . 'common/images/common.png';
                }
            }
        }
        // 商品属性
        $productAbList = ProductAttributeModel::getInfoByProductId ( intval ( $id ) );
        $produceArray = [ ];
        // 已选中值
        foreach ( $productAbList as $key => $value ) {
            $produceArray [$value ['attribute_id']] ['attribute_id'] = $value ['attribute_id'];
            $produceArray [$value ['attribute_id']] ['attribute_name'] = $value ['attribute_name'];
            $produceArray [$value ['attribute_id']] ['input_type'] = $value ['input_type'];
            if ($value ['input_type'] != 2) {
                $produceArray [$value ['attribute_id']] ['attribute_value_id'] = $value ['attribute_value_id'];
                $produceArray [$value ['attribute_id']] ['attribute_value_name'] = $value ['attribute_value_name'];
            } else {
                if (! isset ( $produceArray [$value ['attribute_id']] ['attribute_value_id'] )) {
                    $produceArray [$value ['attribute_id']] ['attribute_value_id'] = '';
                }
                if (! isset ( $produceArray [$value ['attribute_id']] ['attribute_value_name'] )) {
                    $produceArray [$value ['attribute_id']] ['attribute_value_name'] = '';
                }
                $produceArray [$value ['attribute_id']] ['attribute_value_id'] .= $value ['attribute_value_id'] . ' ';
                $produceArray [$value ['attribute_id']] ['attribute_value_name'] .= $value ['attribute_value_name'] . ' ';
            }
        }
        $info ['attribute'] = $produceArray;
        return $info;
    }

    /**
     * 字段自更新
     * @param array $data 更新字段作为key的数组
     * @param array $id 表自增id
     * @return boolean 更新结果
     */
    public static function autoUpdateByID($data, $id)
    {
        $sql = "UPDATE " . self::getTb() . " SET ";
        foreach ($data as $key => $val) {
            if ($val > 0) $val = '+' . $val;
            $sql .= "`{$key}` = (`{$key}` {$val}),";
        }
        $sql = substr($sql, 0, -1);

        $sql .= " WHERE id = " . $id;

        $pdo = self::_pdo('db_w');

        return $pdo->YDExecute($sql);
    }

    /**
     * 是否存在
     * @param $self_code
     * @param $supplier_id
     * @return bool
     */
    public static function isExist($self_code, $supplier_id)
    {
        $builder = new Builder();
        $builder->from(self::getFullTable());
        $builder->where('supplier_id', $supplier_id);
        $builder->where('self_code', $self_code);
        $builder->where('is_del', self::DELETE_SUCCESS);
        $sql = $builder->showSql()['count'];
        $row = self::newRead()->YDGetRow($sql);
        return $row['aggregate'] > 0;
    }

    /**
     * 获取主键ID
     * @param $self_code
     * @param $supplier_id
     * @return mixed
     */
    public static function getPrimaryId($self_code, $supplier_id)
    {
        $builder = new Builder();
        $builder->select(['id']);
        $builder->from(self::getFullTable());
        $builder->where('supplier_id', $supplier_id);
        $builder->where('self_code', $self_code);
        $builder->where('is_del', self::DELETE_SUCCESS);
        $sql = $builder->showSql()['query'];
        $row = self::newRead()->YDGetRow($sql);
        return $row['id'];
    }

    /**
     * 根据商品ID更新采购价
     * @param $product_id
     * @param int $price
     * @return bool
     */
    public static function updatePurchasePrice($product_id, $price = 0)
    {
        $ProductModel = self::find($product_id);
        $ProductModel->purchase_price = $price;
        return $ProductModel->save();
    }

    /**
     * 获取商品浏览量
     * @param $product_id
     * @return integer
     */
    public static function getTotalBrowseNum($product_id = 0)
    {

        $adminId = AdminModel::getAdminID();
        $adminInfo = AdminModel::getAdminLoginInfo($adminId);

        $sql = 'SELECT 
        		   SUM( browse_num ) num
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . '
		        WHERE
		            supplier_id = '. $adminInfo['supplier_id'];

        if ($product_id > 0) {
            $sql .= " AND id = ".$product_id;
        }

        $sql1 = 'SELECT 
        		   SUM( browse_num ) num
        		FROM
		            ' . CommonBase::$_tablePrefix . 'product_channel
		        WHERE
		            supplier_id = '. $adminInfo['supplier_id'];

        if ($product_id > 0) {
            $sql1 .= " AND product_id = ".$product_id;
        }

        $pdo = YDLib::getPDO ( 'db_r' );
        $num = $pdo->YDGetOne ($sql);
        $num1 = $pdo->YDGetOne ($sql1);

        $num += $num1;
        return $num;
    }
    
    
    

    
    
    
    
    
    
    
    
    
    
    
    



}