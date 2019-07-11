<?php

/**
 * Created by PhpStorm.
 * User: lqt
 * Date: 2018/4/23
 * Time: 下午12:51
 */

use Custom\YDLib;
use Core\Queue;
use Common\DataType;
 
class AreaModel extends BaseModel
{
    protected static $_tableName = 'areas';
	
    public static function getTb()
    {
        return self::$_tablePrefix . self::$_tableName;
    }		
	
	/**
	 * 获取对应的列表
	 * @param interger  $pid
	  *  		获取对应的参数    
	 * @return array 
	 */
	public static function getChild($pid = 0)
	{
		
		$sql = "SELECT 
					  area_id,parent_id,area_name  
				FROM 
					".self::getTb()." 
				WHERE
					parent_id = {$pid}
			";
		$pdo = self::_pdo('db_r');
		return $pdo->YDGetAll($sql);
	}
	
	
	/**
	 * 获取对应的列表
	 * @param interger  $pid
	 *  		获取对应的参数
	 * @return array
	 */
	public static function getChildByName($pname = false,$p_id = false)
	{
	
	    $sql = "SELECT
					  c.area_id,c.parent_id,c.area_name
				FROM
					".self::getTb()." c
				LEFT JOIN
				    ".self::getTb()." p
				ON
				    c.parent_id = p.area_id
						WHERE
						  1=1
						";
	    if($pname) {
	        $sql .= "AND p.area_name like '%{$pname}%'";
	    }
	    if($p_id || $p_id == '0') {
	        $sql .= "AND c.parent_id = {$p_id}";
	    }
	    
	    $pdo = self::_pdo('db_r');
	    return $pdo->YDGetAll($sql);
	}
	
	/**
	 * 获取省自治区列表
	 * @param bool $ref 是否返回所有数据
	 * @return array
	 */
	public static function getProvince()
	{
	    $sql  = "SELECT
                     *
                 FROM
                     ".self::getTb()."
                 WHERE parent_id = '0'";
	
		$pdo = self::_pdo('db_r');
		return $pdo->YDGetAll($sql);
	}
	 /**
     * 根据表自增ID获取该条记录信息
     * @param int $id 表自增ID
     */
    public static function getInfoByID($id)
    { 
		$where['area_id'] = intval($id);

		$pdo = self::_pdo('db_r');	
		return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();	
    }

    /**
     * 获取省市县
     * @param integer  $province
     *  	省id
     * @param integer $city
     * 		市id
     * @param integer $area
     *  	县id
     * @return string
     */
    public static function getPca($province,$city,$area)
    {
        $province = $province ? $province : 0;
        $city = $city ? $city : 0;
        $area = $area ? $area : 0;
        $sql = "SELECT area_name FROM ".self::getTb()." WHERE area_id={$province} OR area_id={$city} OR area_id={$area} ORDER BY level";
		$pdo = self::_pdo('db_r');
		$resInfo = $pdo->YDGetAll($sql);        
        $result = '';
        foreach ($resInfo as $value) {
            $result .= $value['area_name'].' ';
        }
        return $result;
    }
    
    
    /**
     * 根据拼音获取省自治区
     *
     * @param bool $ref
     *        	是否返回所有数据
     * @return array
     */
    public static function getProvinceByPinyin($pinyin, $parent_id = '0') {
        $sql = "SELECT
                     *
                 FROM
                     " . self::getTb () . "
                 WHERE parent_id = '" . $parent_id . "'
                 AND area_pinyin = '" . $pinyin . "' ";
    
        $pdo = self::_pdo ( 'db_r' );
        return $pdo->YDGetRow ( $sql );
    }

    public static function findOneWhere($where)
    {
        $sql = "SELECT * FROM ".self::getTb()." WHERE 1=1";
        if (isset($where['parent_id'])) {
            $sql .= ' AND parent_id= '.$where['parent_id'];
        }
        if (isset($where['area_name'])) {
            $sql .= " AND area_name like '%".$where['area_name']."%'";
        }
        $pdo = self::_pdo('db_r');
        return $pdo->YDGetRow($sql);
    }
    
}