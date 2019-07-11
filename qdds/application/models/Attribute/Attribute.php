<?php
/**
 * 属性model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-09
 */
namespace Attribute;
use Overtrue\Pinyin;
use Custom\YDLib;
use Common\CommonBase;
 
class AttributeModel extends \Common\CommonBase 
{
    protected static $_tableName = 'attribute';
    private function __construct() {
        parent::__construct();
    }
    
    /* 获取列表*/
    public static function getList($attribute = array(),$page = 0,$rows = 10)
    {
        $limit = ($page) * $rows;
        if (!empty($attribute['info']) && is_array($attribute['info']) && count($attribute['info']) > 0) {
            extract($attribute['info']);
        }
       
		$pdo = YDLib::getPDO('db_r');
		$fileds = " a.* ";
		$sql = 'SELECT 
        		   [*]
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a 
		        LEFT JOIN
		              '.CommonBase::$_tablePrefix.'attribute_value b 
		        ON
		               a.id = b.attribute_id
        		AND    
        		    b.is_del = 2			               
		        WHERE
        		    a.is_del = 2
        		';
		
		if (isset($name) && !empty($name)) {
			$sql .= " AND a.name like '%".$name."%' ";
		}
		
		if (isset($alias) && !empty($alias)) {
			$sql .= " AND a.alias like '%".$alias."%' ";
		}
		
		if (isset($value) && !empty($value)) {
			$sql .= " AND b.`value` like '%".$value."%' ";
		}
		if (isset($value_alias) && !empty($value_alias)) {
		    $sql .= " AND b.`value_alias` like '%".$value_alias."%' ";
		}
     
        if (isset($start_time) && !empty($start_time)) {
            $sql .= " AND a.created_at >= '".$start_time." 00:00:00'";
        }
        
        if (isset($end_time) && !empty($end_time)) {
            $sql .= " AND a.created_at <= '".$end_time." 23:59:59'";
        }
		$sql.=" GROUP BY a.id";

		$result['total'] = $pdo ->YDGetOne(str_replace("[*]", "count(DISTINCT a.id) as num", $sql));
		
		$sql .= " ORDER BY a.id DESC limit {$limit},{$rows}";
        $result['list'] = $pdo ->YDGetAll(str_replace("[*]", $fileds, $sql));
        if ($result) {
            return $result;
        } else {
            return false;
        }
        
    }
  

    /**
     * 添加信息
     * @param array $info
     * @return mixed
     */
    public static function addData($info)
    {
        $db = YDLib::getPDO('db_w');
        $info['is_del'] = '2';
        $info['created_at'] = date("Y-m-d H:i:s");
		$info['updated_at'] = date("Y-m-d H:i:s");
        $result = $db->insert(self::$_tableName, $info);
    
        return $result;
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
		return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();		 
   
	}
	
	/**
	 * 获得全部数据
	 *
	 * @return mixed
	 */
	public static function getAll()
	{
	
	    $pdo = YDLib::getPDO('db_r');
	    $sql = 'SELECT
        		   id,name
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a
		        WHERE
        		    a.is_del = 2
        		';
	    $result = $pdo ->YDGetAll($sql);
	    return $result;
	}
	
	
	 /**
     * 根据一条自增ID更新表记录
     * @param array $data 更新字段作为key的数组
     * @param array $id 表自增id
     * @return boolean 更新结果
     */
    public static function updateByID($data, $id)
    {
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
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
    }
}