<?php

/**
 * 商品model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
namespace Product;

use Custom\YDLib;
use Common\CommonBase;
use Category\CategoryModel;
use Brand\BrandModel;
use Core\GoldPrice;
use Image\ImageModel;
use Product\ProductChannelModel;
use Product\ProductAttributeModel;

class ProductModel extends \BaseModel {
	protected static $_tableName = 'product';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
	}
	
	/**
	 * 查询绑定人数
	 *
	 * @param $where 条件array        	
	 * @return num
	 */
	public static function count($where) {
		if (is_array ( $where ) && count ( $where ) > 0) {
			extract ( $where );
		}
		
		$sql = "
    			SELECT 
    				COUNT(p.id) num
        		FROM
		            " . self::getTb () . " p
		        LEFT JOIN 
		        	" . self::$_tablePrefix . "category c3
		        ON
		        	c3.id=p.category_id   		        	
		        WHERE
        		    p.is_del=" . self::DELETE_SUCCESS;
		
		if (isset ( $category_id ) && ! empty ( $category_id )) {
			$sql .= " AND (c3.id = " . $category_id . " OR c3.parent_id = " . $category_id . " OR c3.root_id = " . $category_id . ")";
		}
		if (isset ( $brand_id ) && ! empty ( $brand_id )) {
			$sql .= " AND p.brand_id = " . $brand_id;
		}
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->YDGetOne ( $sql );
	}
	
	/* 获取列表 */
	public static function getList($attribute = array(), $page = 1, $rows = 10) {
		$limit = ($page - 1) * $rows;
		if (! empty ( $attribute ) && is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}
		
		$fileds = " a.id,a.name,a.self_code,a.market_price,a.is_return,a.category_id,a.category_name,a.brand_id,a.sale_is_up,a.weight,
				b.name brand_name,a.logo_url,a.stock,c.id is_id,a.sale_up_price,a.channel_up_price,a.channel_is_up,a.appraisal_status,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.sale_price ELSE c.sale_price END sale_price,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.now_at ELSE c.now_at END now_at,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.collect_num ELSE c.collect_num END collect_num,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.browse_num ELSE c.browse_num END browse_num,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN m.multi_point_id ELSE null END multi_point_id,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.sale_num ELSE c.sale_num END sale_num";
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
		       		c.supplier_id = ' . SUPPLIER_ID . '    
		       	AND
		       		c.is_del = ' . self::DELETE_SUCCESS . ' 
		       LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_multi_point m
		       	ON
		       		a.id = m.product_id
		       	AND
		        	m.supplier_id = ' . SUPPLIER_ID . ' 
		       	AND
		        	m.is_del = ' . self::DELETE_SUCCESS . ' 
		        WHERE
        		    a.is_del = 2
        		AND 
        			( a.supplier_id = ' . SUPPLIER_ID . '
        			OR
        			  c.id is not null 
        			)
                AND (
                        (
                            a.supplier_id = ' . SUPPLIER_ID . ' 
                        AND 
                            a.on_status = 2
                        )
                        OR
                        (
                            a.supplier_id != ' . SUPPLIER_ID . ' 
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
		
		if (isset ( $multi_point_id ) && ! empty ( $multi_point_id )) {
			if ($multi_point_id != -90) {
				$sql .= " AND m.multi_point_id IN (" . $multi_point_id . ")";
			} else {
				$sql .= " AND multi_point_id is null ";
			}
			
		}
		
		if (isset ( $category_id ) && ! empty ( $category_id )) {
			if (is_numeric ( $category_id )) {
				$sql .= " AND a.category_id = '" . $category_id . "' ";
			} else {
				$sql .= " AND a.category_id IN (" . $category_id . ")";
			}
		}
		
		if (isset ( $data ) && ! empty ( $data )) {
			$sql .= "AND a.id IN (" . $data . ")";
		}
		
		if (isset ( $brand_id ) && ! empty ( $brand_id )) {
			if (is_numeric ( $category_id )) {
				$sql .= " AND a.brand_id = '" . $brand_id . "' ";
			} else {
				$sql .= " AND a.brand_id IN (" . $brand_id . ")";
			}
		}
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		
		// 猜你喜欢 按 浏览量、收藏量排序
		if (isset ( $guess_like ) && $guess_like == 1) {
			$sql .= " ORDER BY a.browse_num desc,a.collect_num desc LIMIT {$limit},{$rows}";
		} else {
			$sort = isset ( $sort ) ? $sort : 'a.id';
			$order = isset ( $order ) ? $order : 'DESC';
			$sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
		}
		
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		
		if (is_array ( $result ['list'] )) {
			foreach ( $result ['list'] as $key => $value ) {
				if (! empty ( $value ['logo_url'] )) {
					$result ['list'] [$key] ['logo_url'] = HOST_FILE . self::imgSize ( $value ['logo_url'], 2 );
				} else {
					$result ['list'] [$key] ['logo_url'] = HOST_STATIC . 'common/images/common.png';
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * 获取单条数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByID($id, $on_sale = true) {
		// $mem = YDLib::getMem ( 'memcache' );
		// $mem->delete ( 'product_' . $id );
		// $info = $mem->get ( 'product_' . $id );
		// if (! $info) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		// $where ['supplier_id'] = SUPPLIER_ID;
		
		$where ['id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->clear ()->select ( 'id,name,self_code,market_price,sale_price,category_id,category_name,brand_id,
				introduction,logo_url,stock,now_at,lock_stock,sale_num,collect_num,browse_num' )->from ( self::$_tableName )->where ( $where )->getRow ();
		
		if (! $info) {
			return FALSE;
		}
		
		if (! empty ( $info ['logo_url'] )) {
			$info ['logo_url'] = HOST_FILE . self::imgSize ( $info ['logo_url'], 4 );
		} else {
			$info ['logo_url'] = HOST_STATIC . 'common/images/common.png';
		}
		
		$info ['category_name'] = self::getCategoryName ( $info );
		$info ['brand_name'] = BrandModel::getInfoByID ( $info ['brand_id'] ) ['name'];
		
		// 查询商品属性
		$arrt = self::getAttributeByID ( $id );
		$info ['imglist'] = $arrt ['imglist'];
		$info ['attribute'] = $arrt ['attribute'];
		
		// $mem->delete ( 'product_' . $id );
		// $mem->set ( 'product_' . $id, $info );
		// }
		return $info;
	}
	
	/**
	 * 获取单条数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByIDAllStatus($id) {
		// $mem = YDLib::getMem ( 'memcache' );
		// $mem->delete ( 'product_' . $id );
		// $info = $mem->get ( 'product_' . $id );
		// if (! $info) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->clear ()->select ( 'id,name,self_code,market_price,sale_price,category_id,category_name,brand_id,
				introduction,logo_url,stock,now_at,lock_stock,sale_num,supplier_id' )->from ( self::$_tableName )->where ( $where )->getRow ();
		if (! $info) {
			return FALSE;
		}
		
		if (! empty ( $info ['logo_url'] )) {
			$info ['logo_url'] = HOST_FILE . self::imgSize ( $info ['logo_url'], 4 );
		} else {
			$info ['logo_url'] = HOST_STATIC . 'common/images/common.png';
		}
		
		$info ['category_name'] = self::getCategoryName ( $info );
		$info ['brand_name'] = BrandModel::getInfoByID ( $info ['brand_id'] ) ['name'];
		
		// 查询商品属性
		$arrt = self::getAttributeByID ( $id );
		$info ['imglist'] = $arrt ['imglist'];
		$info ['attribute'] = $arrt ['attribute'];
		
		// $mem->delete ( 'product_' . $id );
		// $mem->set ( 'product_' . $id, $info );
		// }
		return $info;
	}
	
	/**
	 * 获取商品存档数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getArchivesByID($id) {
		$where ['id'] = $id;
		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		$info ['three_info'] = CategoryModel::getInfoByID ( $info ['category_id'] );
		$info ['two_info'] = CategoryModel::getInfoByID ( $info ['three_info'] ['parent_id'] );
		$info ['one_info'] = CategoryModel::getInfoByID ( $info ['two_info'] ['parent_id'] );
		$info ['brand_info'] = BrandModel::getInfoByID ( $info ['brand_id'] );
		$info ['img_info'] = ImageModel::getInfoByTypeOrID ( $id );
		$info ['attribute_info'] = ProductAttributeModel::getInfoByProductId ( $id );
		return $info;
	}
	
	/**
	 * 最新获取单条数据（兼容0库存销售）
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByIDNew($id) {
		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = " a.id,a.name,a.self_code,a.describe,a.on_status,a.is_return,a.market_price,a.category_id,a.category_name,a.brand_id,
				a.logo_url,a.introduction,a.stock,c.id is_id,a.channel_status,a.appraisal_status,a.video_url,a.supplier_id,a.appraisal_status,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.sale_price ELSE c.sale_price END sale_price,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.now_at ELSE c.now_at END now_at,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.collect_num ELSE c.collect_num END collect_num,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.browse_num ELSE c.browse_num END browse_num,               
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.sale_num ELSE c.sale_num END sale_num";
		$sql = 'SELECT 
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a  
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		c.product_id = a.id    
		       	AND
		       		c.supplier_id = ' . SUPPLIER_ID . '    
		       	AND
		       		c.is_del = ' . self::DELETE_SUCCESS . '    		       		  
		        WHERE
        		    a.is_del = 2
        		AND 
        			( a.supplier_id = ' . SUPPLIER_ID . '
        			OR
        			  c.id is not null 
        			)
                AND (
                        (
                            a.supplier_id = ' . SUPPLIER_ID . ' 
                        AND 
                            a.on_status = 2
                        )
                        OR
                        (
                            a.supplier_id != ' . SUPPLIER_ID . ' 
                        AND 
                            c.on_status = 2 
                        AND 
                            a.channel_status = 3
                        )
                     )
        		AND 
        			a.id = ' . $id . '
        		';
		$info = $pdo->YDGetRow ( str_replace ( "[*]", $fileds, $sql ) );
		if (! $info) {
			return FALSE;
		}
		
		if (! empty ( $info ['logo_url'] )) {
			$info ['icon_log'] = HOST_FILE . self::imgSize ( $info ['logo_url'], 5 );
			$info ['logo_url'] = HOST_FILE . self::imgSize ( $info ['logo_url'], 4 );
		} else {
			$info ['logo_url'] = HOST_STATIC . 'common/images/common.png';
			$info ['icon_log'] = HOST_STATIC . 'common/images/common.png';
		}
		
		if(! empty ( $info ['video_url'] )){
			 $info ['video_url'] = HOST_FILE. $info ['video_url'];
		}
		
		$threeInfo = CategoryModel::getInfoByID ( $info ['category_id'] );
		$info ['category_description'] = ! empty ( $threeInfo ['description'] ) ? $threeInfo ['description'] : '';
		$info ['category_name'] = self::getCategoryName ( $info );
		$brand = BrandModel::getInfoByID ( $info ['brand_id'] );
		$info ['brand_name'] = $brand ['name'];
		$info ['brand_description'] = ! empty ( $brand ['description'] ) ? $brand ['description'] : '';
		
		// 查询商品属性
		$arrt = self::getAttributeByID ( $id );
		$info ['imglist'] = $arrt ['imglist'];
		$info ['attribute'] = $arrt ['attribute'];
		
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
	 * 获取多条数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByIDs($ids) {
		$pdo = self::_pdo ( 'db_r' );
		$sql = "
        	SELECT id,name,sale_price
        	FROM " . CommonBase::$_tablePrefix . self::$_tableName . "
        	WHERE id IN (" . $ids . ")
        ";
		$info = $pdo->YDGetAll ( $sql );
		return $info;
	}
	
	/**
	 * 获取单条商品数据（用于生成订单）
	 *
	 * @param interger $id
	 *        	商品id
	 * @param boll $forupdate
	 *        	是否行级所
	 * @return mixed
	 *
	 */
	public static function getInfoByIDUseAddOrder($id, $forupdate = false) {
		$fileds = " a.id,a.name,a.self_code,a.market_price,a.channel_price,a.purchase_price,a.introduction,a.category_id,a.category_name,a.brand_id,
				a.logo_url,a.introduction,a.stock,a.lock_stock,a.supplier_id,c.id is_id,a.is_return,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.sale_price ELSE c.sale_price END sale_price,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.now_at ELSE c.now_at END now_at,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.sale_num ELSE c.sale_num END sale_num";
		$sql = 'SELECT 
        		   [*]
        		FROM
		            ' . CommonBase::$_tablePrefix . self::$_tableName . ' a  
		        LEFT JOIN
		        	' . CommonBase::$_tablePrefix . 'product_channel c
		       	ON
		       		c.product_id = a.id    
		       	AND
		       		c.supplier_id = ' . SUPPLIER_ID . '    
		       	AND
		       		c.is_del = ' . self::DELETE_SUCCESS . '    		       		  
		        WHERE
        		    a.is_del = 2
        		AND 
        			( a.supplier_id = ' . SUPPLIER_ID . '
        			OR
        			  c.id is not null 
        			)
                AND (
                        (
                            a.supplier_id = ' . SUPPLIER_ID . ' 
                        AND 
                            a.on_status = 2
                        )
                        OR
                        (
                            a.supplier_id != ' . SUPPLIER_ID . ' 
                        AND 
                            c.on_status = 2 
                        AND 
                            a.channel_status = 3
                        )
                     )
        		AND 
        			a.id = ' . $id . '
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
		$info ['logo_url_old'] = $info ['logo_url'];
		if (! empty ( $info ['logo_url'] )) {
			$info ['logo_url'] = HOST_FILE . self::imgSize ( $info ['logo_url'], 4 );
		} else {
			$info ['logo_url'] = HOST_STATIC . 'common/images/common.png';
		}
		
		$info ['category_name'] = self::getCategoryName ( $info );
		$info ['brand_name'] = BrandModel::getInfoByID ( $info ['brand_id'] ) ['name'];
		
		// 查询商品属性
		$arrt = self::getAttributeByID ( $id );
		$info ['imglist'] = $arrt ['imglist'];
		$info ['attribute'] = $arrt ['attribute'];
		
		return $info;
	}
	
	/**
	 * 获取单条少量数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByIDShot($id) {
		$mem = YDLib::getMem ( 'memcache' );
		
		$info = $mem->get ( 'product_shot_' . $id );
		if (! $info) {
			$where ['is_del'] = self::DELETE_SUCCESS;
			$where ['supplier_id'] = SUPPLIER_ID;
			$where ['on_status'] = 2;
			$where ['id'] = intval ( $id );
			
			$pdo = self::_pdo ( 'db_r' );
			$info = $pdo->clear ()->select ( 'id as product_id,name,market_price,sale_price,logo_url' )->from ( self::$_tableName )->where ( $where )->getRow ();
			if (! $info) {
				return FALSE;
			}
			
			if (! empty ( $info ['logo_url'] )) {
				$info ['logo_url'] = HOST_FILE . self::imgSize ( $info ['logo_url'], 2 );
			} else {
				$info ['logo_url'] = HOST_STATIC . 'common/images/common.png';
			}
			
			$mem->delete ( 'product_shot_' . $id );
			$mem->set ( 'product_shot_' . $id, $info );
		}
		return $info;
	}
	
	/**
	 * 获得分类名称
	 *
	 * @param array $info        	
	 * @return string
	 */
	private static function getCategoryName($info) {
		$threeInfo = CategoryModel::getInfoByID ( $info ['category_id'] );
		$twoInfo = CategoryModel::getInfoByID ( $threeInfo ['parent_id'] );
		$oneInfo = CategoryModel::getInfoByID ( $twoInfo ['parent_id'] );
		return $oneInfo ['name'] . "|" . $twoInfo ['name'] . "|" . $threeInfo ['name'];
	}
	
	/**
	 * 根据一条自增ID更新表记录
	 *
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateByID($data, $id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
	
	/**
	 * 更新商品销售数量
	 *
	 * @param integer $num
	 *        	更新字段作为key的数组
	 * @param integer $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function addSaleNumByID($id, $num) {
		$sql = "UPDATE  " . self::getTb () . " SET
					sale_num = sale_num + " . intval ( $num ) . ",
					updated_at = '" . date ( "Y-m-d H:i:s" ) . "'
				WHERE
				    id = " . intval ( $id );
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->YDExecute ( $sql );
	}
	
	/**
	 * 字段自更新
	 *
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function autoUpdateByID($data, $id) {
		$sql = "UPDATE " . self::getTb () . " SET ";
		foreach ( $data as $key => $val ) {
			if ($val > 0)
				$val = '+' . $val;
			$sql .= "`{$key}` = (`{$key}` {$val}),";
		}
		$sql = substr ( $sql, 0, - 1 );
		
		$sql .= " WHERE id = " . $id;
		
		$pdo = self::_pdo ( 'db_w' );
		
		return $pdo->YDExecute ( $sql );
	}
	
	/**
	 * 获取单条少一点数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getSingleInfoByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
		
		if (! empty ( $info ['logo_url'] )) {
			$info ['logo_url'] = HOST_FILE . self::imgSize ( $info ['logo_url'], 4 );
		} else {
			$info ['logo_url'] = HOST_STATIC . 'common/images/common.png';
		}
		
		if ($info ['channel_status'] != '3' && $info ['on_status'] == 1) {
			$info ['is_channel_status'] = '1';
		}
		
		$threeInfo = CategoryModel::getInfoByID ( $info ['category_id'] );
		$info ['category_description'] = ! empty ( $threeInfo ['description'] ) ? $threeInfo ['description'] : '';
		$info ['category_name'] = self::getCategoryName ( $info );
		$brand = BrandModel::getInfoByID ( $info ['brand_id'] );
		$info ['brand_name'] = $brand ['name'];
		$info ['brand_description'] = ! empty ( $brand ['description'] ) ? $brand ['description'] : '';
		
		// 查询商品属性
		$arrt = self::getAttributeByID ( $id );
		$info ['imglist'] = $arrt ['imglist'];
		$info ['attribute'] = $arrt ['attribute'];
		return $info;
	}
	public static function createIndex() {
		$el = YDLib::getES ( 'elasticsearch' );
		
		$fileds = " a.id,a.name,a.self_code,a.market_price,a.category_id,a.category_name,a.brand_id,
				b.name brand_name,a.logo_url,a.stock,c.id is_id,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.sale_price ELSE c.sale_price END sale_price,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.now_at ELSE c.now_at END now_at,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.sale_num ELSE c.sale_num END sale_num";
		
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
		       		c.supplier_id = ' . SUPPLIER_ID . '    
		       	AND
		       		c.is_del = ' . self::DELETE_SUCCESS . '    		       		  
		        WHERE
        		    a.is_del = 2
        		AND 
        			( a.supplier_id = ' . SUPPLIER_ID . '
        			OR
        			  c.id is not null 
        			)
                AND (
                        (
                            a.supplier_id = ' . SUPPLIER_ID . ' 
                        AND 
                            a.on_status = 2
                        )
                        OR
                        (
                            a.supplier_id != ' . SUPPLIER_ID . ' 
                        AND 
                            c.on_status = 2 
                        AND 
                            a.channel_status = 3
                        )
                     )
        		AND 
        			a.stock > 0
        		';
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$list = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		
		/**
		 * $params = [
		 * 'index' => 'my_index', //索引名（相当于mysql的数据库）
		 * 'body' => [
		 * 'settings' => [
		 * 'number_of_shards' => 5, #分片数
		 * ],
		 * 'mappings' => [
		 * 'my_type' => [ //类型名（相当于mysql的表）
		 * '_all' => [
		 * 'enabled' => 'false'
		 * ],
		 * '_routing' => [
		 * 'required' => 'true'
		 * ],
		 * 'properties' => [ //文档类型设置（相当于mysql的数据类型）
		 * 'id' => [
		 * 'type' => 'integer'
		 * ],
		 * 'name' => [
		 * 'type' => 'string',
		 * 'store' => 'true'
		 * ],
		 * 'self_code' => [
		 * 'type' => 'string',
		 * 'store' => 'true'
		 * ],
		 * 'market_price' => [
		 * 'type' => 'string',
		 * 'store' => 'true'
		 * ],
		 * 'category_id' => [
		 * 'type' => 'integer'
		 * ],
		 * 'category_name' => [
		 * 'type' => 'string',
		 * 'store' => 'true'
		 * ],
		 * 'brand_id' => [
		 * 'type' => 'integer'
		 * ],
		 * 'brand_name' => [
		 * 'type' => 'string',
		 * 'store' => 'true'
		 * ],
		 * 'logo_url' => [
		 * 'type' => 'string',
		 * 'store' => 'true'
		 * ],
		 * 'stock' => [
		 * 'type' => 'integer'
		 * ],
		 * 'is_id' => [
		 * 'type' => 'integer'
		 * ],
		 * 'sale_price' => [
		 * 'type' => 'string',
		 * 'store' => 'true'
		 * ],
		 * 'now_at' => [
		 * 'type' => 'string',
		 * 'store' => 'true'
		 * ],
		 * 'sale_num' => [
		 * 'type' => 'integer'
		 * ]
		 * ]
		 * ]
		 * ]
		 * ]
		 * ];
		 * $el->createIndex($params);
		 * exit;
		 */
		
		// $el->deleteIndex('my_index');
		foreach ( $list as $key => $value ) {
			$params = array ();
			$params ['body'] = $value;
			$params ['index'] = 'my_index';
			$params ['type'] = 'my_product';
			$params ['id'] = $value ['id'];
			print_r ( $params );
			$el->addIndex ( $params );
		}
	}
	
	/* 获取用于更新金价 */
	public static function getGoldValue($page = 1, $rows = 10) {
		$limit = ($page - 1) * $rows;
		
		$fileds = " a.id,a.name,a.self_code,a.market_price,a.category_id,a.category_name,a.brand_id,a.sale_is_up,a.weight,a.channel_up_price product_up_price,
				b.name brand_name,a.logo_url,a.stock,c.id is_id,a.sale_up_price,a.channel_up_price,a.channel_is_up,c.sale_is_up is_up,c.sale_up_price up_price ,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.sale_price ELSE c.sale_price END sale_price,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.now_at ELSE c.now_at END now_at,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.collect_num ELSE c.collect_num END collect_num,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.browse_num ELSE c.browse_num END browse_num,
                CASE WHEN a.supplier_id = " . SUPPLIER_ID . " THEN a.sale_num ELSE c.sale_num END sale_num";
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
		       		c.supplier_id = ' . SUPPLIER_ID . '
		       	AND
		       		c.is_del = ' . self::DELETE_SUCCESS . '
		        WHERE
        		    a.is_del = 2
        		AND
        			( a.supplier_id = ' . SUPPLIER_ID . '
        			OR
        			  c.id is not null
        			)
                AND (
                        (
                            a.supplier_id = ' . SUPPLIER_ID . '
                        AND
                            a.on_status = 2
                        )
                        OR
                        (
                            a.supplier_id != ' . SUPPLIER_ID . '
                        AND
                            c.on_status = 2
                        AND
                            a.channel_status = 3
                        )
                     )
        		AND
        			a.stock > 0
        		';
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$result = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		
		$gold_price = GoldPrice::getGoldPrice ();
		foreach ( $result as $key => $val ) {
			// 自由商品
			if ($val ['sale_is_up'] == '2') {
				$data ['sale_price'] = bcmul ( $val ['weight'], ($gold_price + $val ['sale_up_price']), 2 );
				self::updateByID ( $data, $val ['id'] );
			}
			// 渠道价
			if ($val ['channel_is_up'] == '2') {
				$channelData ['channel_price'] = bcmul ( $val ['weight'], ($gold_price + $val ['channel_up_price']), 2 );
				self::updateByID ( $channelData, $val ['id'] );
			}
			// 供应价
			if ($val ['is_up'] == '2') {
				$ProductChannelData ['sale_price'] = bcadd ( bcmul ( $val ['weight'], ($gold_price + $val ['product_up_price']), 2 ), $val ['up_price'], 2 );
				ProductChannelModel::updateByID ( $ProductChannelData, $val ['id'] );
			}
		}
		
		return $result;
	}
	
	/**
	 * 取商品所有浏览量和收藏量
	 */
	public static function GetAllSum() {
		$pdo = self::_pdo ( 'db_r' );
		$sql = "
        	SELECT  sum(collect_num) as collect_num,sum(browse_num) as browse_num 
        	FROM " . CommonBase::$_tablePrefix . self::$_tableName . "
        	WHERE is_del = 2
        	AND supplier_id = " . SUPPLIER_ID . "
        ";
		$info = $pdo->YDGetRow ( $sql );
		
		$sql = "
        	SELECT  sum(collect_num) as collect_num,sum(browse_num) as browse_num
        	FROM " . CommonBase::$_tablePrefix . "product_channel
        	WHERE is_del = 2
        	AND supplier_id = " . SUPPLIER_ID . "
        ";
		$channeIinfo = $pdo->YDGetRow ( $sql );
		
		if ($channeIinfo) {
			$info ['collect_num'] = intval($channeIinfo['collect_num']+$info['collect_num']);
    	$info['browse_num'] =intval($channeIinfo['browse_num']+$info['browse_num']);
    	}
    	
    	return $info;
    
    }

}