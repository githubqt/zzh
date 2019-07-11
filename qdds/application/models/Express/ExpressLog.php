<?php
/**
 * 快递日志model
 * @time 2018-05-08
 */
namespace Express;

class ExpressLogModel extends \BaseModel
{
    /**
     * 定义表名后缀
     */	
    protected static $_tableName = 'express_log';
	
    /**
     * 获取表名
     */	
    public static function getTb()
    {
        return self::$_tablePrefix . self::$_tableName;
    }
	
    
    
    
    /**
     * 获取对应的list列表
     * @param array  $attribute 获取对应的参数
     * @param integer $page 对应的页
     * @param integer $rows 取出的行数
     * @return array
     */
    public static function getList($attribute = array())
    {
    	if (!empty($attribute['info']) && is_array($attribute['info']) && count($attribute['info']) > 0) {
    		extract($attribute['info']);
    	}
    	
    	$pdo = self::_pdo('db_r');
    	
    	$sql = "SELECT
        		    *
        		FROM
		            ".self::getTb()."
		        WHERE
        		    is_del=".self::DELETE_SUCCESS
    			;
    
    	if (isset($status) && !empty($status)) {
    		$sql .= " AND source = '".$status."' ";
    	}	
    		
    	if (isset($inquirer) && !empty($inquirer)) {
    			$sql .= " AND inquirer like '%".$inquirer."%' ";
    	}
    	
    	if (isset($express_name) && !empty($express_name)) {
    		$sql .= " AND express_id like '%".$express_name."%' ";
    	}
    	
    	if (isset($express_no) && !empty($express_no)) {
    		$sql .= " AND express_no = '".$express_no."' ";
    	}
    
    	if (isset($start_time) && isset($end_time) && !empty($start_time) && !empty($end_time)) {
    		$sql .= " AND query_time >= '" . $start_time . " 00:00:00' ";
    		$sql .= " AND query_time <= '" . $end_time . " 23:59:59' ";
    	}
    	$resInfo = array();
    	$resInfo['total'] = $pdo->YDGetOne(str_replace('*', 'COUNT(*) num', $sql));
    
    	$sort = isset($sort)?$sort:'id';
    	$order = isset($order)?$order:'DESC';
    
    	$sql .= " ORDER BY {$sort} {$order} LIMIT 0,10";
    	$resInfo['list'] = $pdo->YDGetAll($sql);
    
    	foreach ($resInfo['list'] as $key  => $val){
    		
    		if($val['source'] == 1){
    			$resInfo['list'][$key]['source_txt'] = '商户';
    		}else if($val['source'] == 2){
    			$resInfo['list'][$key]['source_txt'] = '用户';
    		}
    	}
    	
    	return $resInfo;
    
    }
    
    
    
	
	 /**
     * 记录入库
     * @param array $data 表字段名作为key的数组
     * @return int 入库成功则返回入库记录的自增ID，否则返回FALSE
     */	
    public static function addData($data)
    {
        $data['is_del'] = self::DELETE_SUCCESS;
		$data['status'] = self::STATUS_SUCCESS;
		$data['created_at'] = date("Y-m-d H:i:s");
		$data['updated_at'] = date("Y-m-d H:i:s");
		
		$pdo = self::_pdo('db_w');
		return $pdo->insert(self::$_tableName, $data);
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