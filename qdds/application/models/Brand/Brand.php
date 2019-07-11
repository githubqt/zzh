<?php
/**
 * 权限model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-05
 */
namespace Brand;
use Overtrue\Pinyin;
use Custom\YDLib;
use Common\CommonBase;
 
class BrandModel extends \BaseModel 
{
    protected static $_tableName = 'brand';
	
    /**
     * 查询需要显示的列
     * @var array
     */
    public static $showColumns = [
        'id', 'name', 'en_name', 'alias_name',
    ];

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

		$result['total'] = $pdo ->YDGetOne(str_replace("[*]", "count(*) as num", $sql));
        $sql .= " ORDER BY a.id DESC limit {$limit},{$rows}";
        $result['list'] = $pdo ->YDGetAll(str_replace("[*]", $fileds, $sql));
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
        		   id,name,en_name
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a 
		        WHERE
        		    a.is_del = 2
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
        		   id,name,en_name
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.id IN ('.$ids.')
        		
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

}