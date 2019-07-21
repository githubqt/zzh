<?php
/**
 * 分类model
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
namespace Category;
use Overtrue\Pinyin;
use Custom\YDLib;
use Common\CommonBase;
 
class CategoryModel extends \Common\CommonBase 
{
    protected static $_tableName = 'category';
    private function __construct() {
        parent::__construct();
    }
    
    /* 获取列表*/
    public static function getList($attribute = array(),$page = 0,$rows = 10)
    {
        
        if (!empty($attribute['info']) && is_array($attribute['info']) && count($attribute['info']) > 0) {
            extract($attribute['info']);
        }
       
		$pdo = YDLib::getPDO('db_r');
		$fileds = " a.* ";
		$sql = 'SELECT 
        		   [*]
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a 
		        WHERE
        		    a.is_del = 2
        		';
		
		if (isset($name) && !empty($name)) {
			$sql .= " AND a.name like '%".$name."%' ";
		}
		
		if (isset($en_name) && !empty($en_name)) {
			$sql .= " AND a.en_name like '%".$en_name."%' ";
		}
		
		if (isset($alias_name) && !empty($alias_name)) {
			$sql .= " AND a.alias_name like '%".$alias_name."%' ";
		}
     
    
        if (isset($start_time) && !empty($start_time)) {
            $sql .= " AND a.created_at >= '".$start_time." 00:00:00'";
        }
        
        if (isset($end_time) && !empty($end_time)) {
            $sql .= " AND a.created_at <= '".$end_time." 23:59:59'";
        }
     
        $result['list'] = $pdo ->YDGetAll(str_replace("[*]", $fileds, $sql));
        
     	$result['total'] = $pdo ->YDGetOne(str_replace("[*]", "count(*) as num", $sql));

        if ($result) {
            return $result;
        } else {
            return false;
        }
        
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
        		   id,name,parent_id,root_id
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a 
		        WHERE
        		    a.is_del = 2
        		ORDER BY 
        			a.sort DESC
        		';
    	$result = $pdo ->YDGetAll($sql);
        return $result;
    }
    
    
    /**
     * 获得全部数据根据id
     *
     * @return mixed
     */
    public static function getAllByids($ids)
    {
    
        $pdo = YDLib::getPDO('db_r');
        $sql = 'SELECT
        		   id,name,parent_id,root_id
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.id IN ('.$ids.')
        		ORDER BY
        			a.sort DESC
        		';
        $result = $pdo ->YDGetAll($sql);
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
     * 根据一条自增ID更新表记录
     * @param array $data 更新字段作为key的数组
     * @param array $id 表自增id
     * @return boolean 更新结果
     */
    public static function updateByID($data, $id)
    {
    	$pinyin = new Pinyin();
		$firstChar  = $pinyin->getFirstChar($data['name']);
		$data['first_letter'] = $firstChar;		
    	$data['updated_at'] = date("Y-m-d H:i:s");
		
		$pdo = self::_pdo('db_w');			 
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
    }
    
    
    /**
     * 获取顶级分类
     * @param string $type
     * @return array
     */
    public static function getParentList($type='')
    {
        $where = [
            'parent'=>0,
            'is_del'=>'2'
        ];
    
        if ($type) {
            $where['type'] = $type;
        }
        $pdo = YDLib::getPDO('db_r');
        $ret = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->order('order_num asc')->getAll();
    
        return $ret?$ret:[];
    }
    
    /**
     * 获取子级分类
     * @param string $parent_id
     * @return array
     */
    public static function getParentTwoList($parent_id)
    {
        $pdo = YDLib::getPDO('db_r');
        $ret = $pdo->clear()->select('*')->from(self::$_tableName)->where(['parent_id'=>$parent_id,'is_del'=>'2'])->getAll();
    
        return $ret?$ret:[];
    }
    
    /**
     * 组装分类
     * @param $category_id
     * @return string
     */
    public static function getCategoryName($category_id)
    {
        $c_name = self::getInfoByID($category_id);
        $b_name = self::getInfoByID($c_name['parent_id']);
        $a_name = self::getInfoByID($b_name['parent_id']);
        return $a_name['name'].'|'.$b_name['name'].'|'.$c_name['name'];
    }

}