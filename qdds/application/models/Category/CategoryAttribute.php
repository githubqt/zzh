<?php
/**
 * 分类属性model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-28
 */
namespace Category;
use Custom\YDLib;
use Core\Queue;
use Common\DataType;

class CategoryAttributeModel extends \Common\CommonBase 
{
    /**
     * 定义表名后缀
     */	
    protected static $_tableName = 'category_attribute';
	
    /**
     * 获取表名
     */	
    public static function getTb()
    {
        return self::$_tablePrefix . self::$_tableName;
    }	
	
	 /**
     * 记录入库
     * @param array $data 表字段名作为key的数组
     * @return int 入库成功则返回入库记录的自增ID，否则返回FALSE
     */	
    public static function addData($data)
    {
        $data['is_del'] = self::DELETE_SUCCESS;
		$data['created_at'] = date("Y-m-d H:i:s");
		$data['updated_at'] = date("Y-m-d H:i:s");			
	  
		$pdo = self::_pdo('db_w');
		$id = $pdo->insert(self::$_tableName, $data);
		
		return $id;
    }		

    /**
     * 根据表自增ID获取该条记录信息
     * @param int $id 表自增ID
     */
    public static function getInfoByID($id)
    {   	
        $where['is_del'] = self::DELETE_SUCCESS;
		$where['id'] = intval($id);

		$pdo = self::_pdo('db_r');	
		return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();		 
    }
    
    
    /**
     * 根据分类ID获取该条记录信息
     * @param int $id 表自增ID
     */
    public static function getInfoByCategoryID($id)
    {
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['category_id'] = intval($id);
    
        $pdo = self::_pdo('db_r');
        return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getAll();
    }
    
    /**
     * 根据一条自增ID更新表记录
     * @param array $data 更新字段作为key的数组
     * @param array $id 表自增id
     * @return boolean 更新结果
     */
    public static function updateByID($data, $id)
    {
    	$info = self::getInfoByID($id);
		
		$data['updated_at'] = date("Y-m-d H:i:s");			
	  
		$pdo = self::_pdo('db_w');			 
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
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
        return $pdo->update(self::$_tableName, $data, array('category_id' => intval($id)));
    }	
	
	/**
	 * 获取对应的list列表
	 * @param array  $attribute 获取对应的参数    
	 * @param integer $page 对应的页
	 * @param integer $rows 取出的行数
	 * @return array 
	 */
    public static function getList($attribute = array(),$page = 1,$rows = 10)
    {
    	
		//一级分类：parent_id=0
		//二级分类：parent_id>0,parent_id=root_id
		//三级分类：parent_id>0,parent_id!=root_id

        $limit = ($page-1) * $rows;
		if (is_array($attribute) && count($attribute) > 0) {
			extract($attribute);
		}
		
		$filed = "*";		
		
		$sql = "SELECT 
        		    [*] 
        		FROM
		            ".self::getTb()."    
		        WHERE
        		    is_del=".self::DELETE_SUCCESS;	
								
		if (isset($id) && !empty(intval($id))) {
		    $sql .= " AND id = '".intval($id)."'";
		}
		if (isset($name) && !empty(trim($name))) {
		    $sql .= " AND name LIKE '%".trim($name)."%'";
		}
		if (isset($parent) && !empty(intval($parent))) {
		    $sql .= " AND parent_id = '".intval($parent)."'";
		}		
		if (isset($status) && !empty(intval($status))) {
		    $sql .= " AND status = '".intval($status)."'";
		}	
		//是否查询只查询三级分类
		if (isset($is_three)) {
		    $sql .= " AND parent_id<>root_id";
		}						
		
		$pdo = self::_pdo('db_r');	
		$resInfo = array();
		$resInfo['total'] = $pdo->YDGetOne(str_replace('[*]', 'COUNT(1) num', $sql));
						
		$sort = isset($sort)?$sort:'id';	
		$order = isset($order)?$order:'DESC';
		
		$sql .= " ORDER BY {$sort} {$order} LIMIT {$limit},{$rows}";
		$resInfo['rows'] = $pdo->YDGetAll(str_replace('[*]', $filed, $sql));
		return $resInfo;
        
    }	
    
    
    /**
     * 根据属性id获取
     * @param array $product_id
     * @return array
     */
    public static function getattrInfoByCategoryId($category_id)
    {
         
        $sql = "
			SELECT
				a.attr_id as attribute_id,a.attr_name as attribute_name,b.input_type
			FROM
                ".self::getTb()." as a
            LEFT JOIN
				".self::$_tablePrefix."attribute as b
    				ON
    				b.id=a.attr_id
    				WHERE
    				a.category_id = {$category_id}
    				AND
    				a.is_del = 2
    				";
        $pdo = self::_pdo('db_r');
        return $pdo ->YDGetAll($sql);
    }
	

}