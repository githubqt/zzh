<?php
/**
 * 多网点model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-07-10
 */
namespace Weixin;
use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
 
class WexinAuthorizationModel extends \Common\CommonBase 
{
    protected static $_tableName = 'wexin_authorization';
    private function __construct() {
        parent::__construct();
    }
    
    
    /**
     * 添加信息
     * @param array $info
     * @return mixed
     */
    public static function addData($info)
    {
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        $db = YDLib::getPDO('db_w');
        $info['is_del'] = '2';
        $info['created_at'] = date("Y-m-d H:i:s");
		$info['updated_at'] = date("Y-m-d H:i:s");
		$info['supplier_id'] = $adminInfo['supplier_id'];
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
		$info = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
		
		return $info;				 
   
	}
	
	/**
	 * 获取单条数据
	 *
	 * @param interger $id
	 * @return mixed
	 *
	 */
	public static function getInfoByAppID($appid)
	{
	    $where['is_del'] = self::DELETE_SUCCESS;
	    $where['authorizer_appid'] = $appid;
	
	    $pdo = self::_pdo('db_r');
	    $info = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
	
	    return $info;
	     
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
	    $up = $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
	    if ($up) {
	        return $up;
	    }
	    return false;
	}
	
	
	/**
	 * 根据一条自增ID更新表记录
	 * @param array $data 更新字段作为key的数组
	 * @param array $id 表自增id
	 * @return boolean 更新结果
	 */
	public static function updateBySupplierID($data)
	{
	    $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
	    $data['updated_at'] = date("Y-m-d H:i:s");
	
	    $pdo = self::_pdo('db_w');
	    $up = $pdo->update(self::$_tableName, $data, array('supplier_id' => intval($adminInfo['supplier_id'])));
	    if ($up) {
	        return $up;
	    }
	    return false;
	}
	
	
	/**
	 * 根据表自增 ID删除记录
	 * @param int $id 表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID()
	{
	    $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
	    $data['is_del'] = self::DELETE_FAIL;
	    $data['updated_at'] = date("Y-m-d H:i:s");
	    $data['deleted_at'] = date("Y-m-d H:i:s");
	
	    $pdo = self::_pdo('db_w');
	    return $pdo->update(self::$_tableName, $data, array('supplier_id'=>$adminInfo['supplier_id']));
	}
	
	
	/**
	 * 获得一条数据
	 *
	 * @return mixed
	 */
	public static function getOneBySupplierId()
	{

	    $pdo = YDLib::getPDO('db_r');
	    $sql = 'SELECT
        		   a.*
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.supplier_id = '.SUPPLIER_ID;
	    $result = $pdo ->YDGetRow($sql);
	    return $result;
	}
	
	
	/**
	 * 获得一条数据
	 *
	 * @return mixed
	 */
	public static function getInfoBySupplierId($supplier_id)
	{
	   
	    $pdo = YDLib::getPDO('db_r');
	    $sql = 'SELECT
        		   a.*
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.supplier_id = '.$supplier_id;
	    $result = $pdo ->YDGetRow($sql);
	    return $result;
	}

}