<?php

/**
 * 分类model
 * @version v0.01
 * @author laiqingtao
 * @time 2018-05-08
 */
namespace Category;

use Overtrue\Pinyin;
use Custom\YDLib;
use Core\Queue;
use Common\DataType;

class CategoryModel extends \BaseModel {
	/**
	 * 定义表名后缀
	 */
	protected static $_tableName = 'category';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
	}
	
	/**
	 * 记录入库
	 * 
	 * @param array $data
	 *        	表字段名作为key的数组
	 * @return int 入库成功则返回入库记录的自增ID，否则返回FALSE
	 */
	public static function addData($data) {
		$data ['is_del'] = self::DELETE_SUCCESS;
		$data ['status'] = self::STATUS_SUCCESS;
		$data ['created_at'] = date ( "Y-m-d H:i:s" );
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$pinyin = new Pinyin ();
		$firstChar = $pinyin->getFirstChar ( $data ['name'] );
		$data ['first_letter'] = $firstChar;
		
		// 获取顶级分类
		$data ['root_id'] = 0;
		if ($data ['parent_id'] > 0) {
			$parent = self::getInfoByID ( $data ['parent_id'] );
			if ($parent ['root_id'] == 0) {
				$data ['root_id'] = $data ['parent_id'];
			} else {
				$data ['root_id'] = $parent ['root_id'];
			}
		}
		// 获取父级分类下节点的个数
		$count = self::countChild ( $data ['parent_id'] );
		$data ['sort'] = $count + 1;
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->insert ( self::$_tableName, $data );
	}
	
	/**
	 * 获取子级数量
	 * 
	 * @param int $parent_id
	 *        	父级ID
	 * @param int $id
	 *        	不包含的子id 【0表示不限】
	 */
	public static function countChild($parent_id, $id = 0) {
		$pdo = self::_pdo ( 'db_r' );
		$sql = "SELECT 
        		    COUNT(1) num
        		FROM
		            " . self::getTb () . "    
		        WHERE
        		    is_del=" . self::DELETE_SUCCESS . "
		        AND
        		    parent_id={$parent_id}";
		if ($id > 0) {
			$sql .= " AND id <> {$id}";
		}
		return $pdo->YDGetOne ( $sql );
	}
	
	/**
	 * 根据表自增ID获取该条记录信息
	 * 
	 * @param int $id
	 *        	表自增ID
	 */
	public static function getInfoByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	/**
	 * 根据表自增ID获取该条记录信息
	 * 
	 * @param int $id
	 *        	表自增ID
	 */
	public static function getTopInfoBYID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		$where ['parent_id'] = '0';
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( 'id,name,logo_url,parent_id,first_letter,children,root_id,sort,description' )->from ( self::$_tableName )->where ( $where )->getRow ();
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
		
		$pinyin = new Pinyin ();
		$firstChar = $pinyin->getFirstChar ( $data ['name'] );
		$data ['first_letter'] = $firstChar;
		
		// 获取顶级分类
		$data ['root_id'] = 0;
		if ($data ['parent_id'] > 0) {
			$parent = self::getInfoByID ( $data ['parent_id'] );
			if ($parent ['root_id'] == 0) {
				$data ['root_id'] = $data ['parent_id'];
			} else {
				$data ['root_id'] = $parent ['root_id'];
			}
		}
		// 获取父级分类下节点的个数
		$count = self::countChild ( $data ['parent_id'], $id );
		$data ['sort'] = $count + 1;
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
	
	/**
	 * 根据表自增 ID删除记录
	 * 
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID($id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
	
	/**
	 * 获取对应的list列表
	 * 
	 * @param array $attribute
	 *        	获取对应的参数
	 * @param integer $page
	 *        	对应的页
	 * @param integer $rows
	 *        	取出的行数
	 * @return array
	 */
	public static function getList($attribute = array(), $page = 1, $rows = 10) {
		
		// 一级分类：parent_id=0
		// 二级分类：parent_id>0,parent_id=root_id
		// 三级分类：parent_id>0,parent_id!=root_id
		$limit = ($page - 1) * $rows;
		if (is_array ( $attribute ) && count ( $attribute ) > 0) {
			extract ( $attribute );
		}
		
		$filed = "*";
		
		$sql = "SELECT 
        		    [*] 
        		FROM
		            " . self::getTb () . "    
		        WHERE
        		    is_del=" . self::DELETE_SUCCESS;
		
		if (isset ( $id ) && ! empty ( intval ( $id ) )) {
			$sql .= " AND id = '" . intval ( $id ) . "'";
		}
		if (isset ( $name ) && ! empty ( trim ( $name ) )) {
			$sql .= " AND name LIKE '%" . trim ( $name ) . "%'";
		}
		if (isset ( $parent ) && ! empty ( intval ( $parent ) )) {
			$sql .= " AND parent_id = '" . intval ( $parent ) . "'";
		}
		if (isset ( $status ) && ! empty ( intval ( $status ) )) {
			$sql .= " AND status = '" . intval ( $status ) . "'";
		}
		// 是否查询只查询三级分类
		if (isset ( $is_three )) {
			$sql .= " AND parent_id<>root_id";
		}
		
		$pdo = self::_pdo ( 'db_r' );
		$resInfo = array ();
		$resInfo ['total'] = $pdo->YDGetOne ( str_replace ( '[*]', 'COUNT(1) num', $sql ) );
		
		$sort = isset ( $sort ) ? $sort : 'id';
		$order = isset ( $order ) ? $order : 'DESC';
		
		$sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
		$resInfo ['rows'] = $pdo->YDGetAll ( str_replace ( '[*]', $filed, $sql ) );
		return $resInfo;
	}
	
	/**
	 * 获取顶级分类
	 * 
	 * @param string $type        	
	 * @return array
	 */
	public static function getParentList($type = '') {
		$where = [ 
				'parent' => 0,
				'is_del' => '2' 
		];
		
		if ($type) {
			$where ['type'] = $type;
		}
		$pdo = YDLib::getPDO ( 'db_r' );
		$ret = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->order ( 'order_num asc' )->getAll ();
		
		return $ret ? $ret : [ ];
	}
	
	/**
	 * 获取子级分类
	 * 
	 * @param string $parent_id        	
	 * @return array
	 */
	public static function getParentTwoList($parent_id) {
		$pdo = YDLib::getPDO ( 'db_r' );
		$ret = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( [ 
				'parent' => $parent_id,
				'is_del' => '2' 
		] )->order ( 'order_num asc' )->getAll ();
		
		return $ret ? $ret : [ ];
	}
	
	/**
	 * 获取所有分类
	 * 
	 * @param        	
	 *
	 * @return array
	 */
	public static function getAllList() {
		$mem = YDLib::getMem ( 'memcache' );
		$data = $mem->get ( 'category_all' );
		if (! $data) {
			
			$pdo = YDLib::getPDO ( 'db_r' );
			$data = $pdo->clear ()->select ( 'id,name,logo_url,parent_id,first_letter,root_id,sort,description' )->from ( self::$_tableName )->where ( [ 
					'is_del' => '2' 
			] )->getAll ();
			
			$mem->delete ( 'category_all' );
			$mem->set ( 'category_all', $data );
		}
		
		return $data ? $data : [ ];
	}
	
	/**
	 * 获取对应的列表
	 * 
	 * @param interger $pid
	 *        	获取对应的参数
	 * @return array
	 */
	public static function getChild($pid = 0) {
		$sql = "SELECT 
					id,name,logo_url,parent_id,first_letter,root_id,sort,description
				FROM 
					" . self::getTb () . " 
				WHERE
					parent_id = {$pid}
				AND
					is_del = 2						
				ORDER BY sort	
			";
		
		$mem = YDLib::getMem ( 'memcache' );
		$data = $mem->get ( 'category_child_' . $pid );
		if (! $data) {
			
			$pdo = self::_pdo ( 'db_r' );
			$data = $pdo->YDGetAll ( $sql );
			
			$mem->delete ( 'category_child_' . $pid );
			$mem->set ( 'category_child_' . $pid, $data );
		}
		
		return $data;
	}
}