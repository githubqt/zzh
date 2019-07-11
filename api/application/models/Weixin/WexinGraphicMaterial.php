<?php
/**
 * 微信消息记录model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-08-28
 */
namespace Weixin;
use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
use AreaModel;
use Weixin\WeixinFunction;
use Weixin\WexinReplyInfoModel;
 
class WexinGraphicMaterialModel extends \Common\CommonBase 
{
    protected static $_tableName = 'wexin_graphic_material';
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
		        WHERE
        		    a.is_del = 2
		        AND
		            a.is_more != "0"
		        AND
	                 a.supplier_id='.AdminModel::getAdminLoginInfo(AdminModel::getAdminID())['supplier_id'];
    
    
        if (isset($title) && !empty($title)) {
            $sql .= " AND a.title like '%".$title."%' ";
        }
    
        /*if (isset($id) && !empty($id)) {
            $sql .= " AND a.id like '%".$id."%' ";
        }
    
        if (isset($note) && !empty($note)) {
            $sql .= " AND a.note like '%".$note."%' ";
        }
        if (isset($status) && !empty($status)) {
            $sql .= " AND a.status = ".$status." ";
        }
         
        if (isset($start_time) &&  isset($end_time) && !empty($start_time) && !empty($end_time)) {
            $sql .= " AND a.created_at >= '" .$start_time. " 00:00:00' ";
            $sql .= " AND a.created_at <= '" .$end_time. " 23:59:59' ";
        } */
        $result['total'] = $pdo ->YDGetOne(str_replace("[*]", "count(*) as num", $sql));
        $sort = isset($sort)?$sort:'id';
        $order = isset($order)?$order:'DESC';
    
        $sql .= " ORDER BY a.{$sort} {$order} LIMIT {$limit},{$rows}";
    
        $result['list'] = $pdo ->YDGetAll(str_replace("[*]", $fileds, $sql));
        if ($result['list']) {
            foreach ($result['list'] as $key=>$val) {
                if ($val['is_more'] == '1') {
                    $result['list'][$key]['is_more_text'] = '单图文';
                } else {
                    $result['list'][$key]['is_more_text'] = '多图文';
                }   
            }
            
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
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        $pdo = self::_pdo('db_w');
        $pdo->beginTransaction();
        try {
            $info['supplier_id']    = $adminInfo['supplier_id'];
            $info['is_del']         = '2';
            $info['created_at']     = date("Y-m-d H:i:s");
    		$info['updated_at']     = date("Y-m-d H:i:s");
    		
            $last_id = $pdo->insert(self::$_tableName, $info);
               
            $pdo->commit();
            return $last_id;
        } catch(\Exception $e) {
            $pdo->rollback();
            return FALSE;
        }
    }
    
    
    
    /**
     * 添加多图文消息
     * @param array $data 单图文数组
     * @param array $datamore 多图文信息
     */
    public static function addMsgMore($data)
    {
        if ($data !== FALSE) {
            $pdo = self::_pdo('db_w');	
	        $pdo->beginTransaction();
            try {
                /*循环添加多图文*/
                foreach ($data as $key => $value) {
                    
                    $value['wechat_content'] = CommonBase::contrastImg($value['content'], '1');
                    
                    if ($key == '0') {
                        $value['is_more'] = '2';
                        $lastID = self::addData($value);
                        if ($lastID == FALSE) {
                            $pdo->rollback();
                            return FALSE;
                        }
                    } else {
                        $value['is_more'] = '0';
                        $value['graphic_id'] = $lastID;
                        $add = self::addData($value);
                        if ($add == FALSE) {
                            $pdo->rollback();
                            return FALSE;
                        }
                    } 
                }
                
                $pdo->commit();
                return $lastID;
            } catch(\Exception $e) {
                $pdo->rollback();
                return FALSE;
            }
        }
    }
    
    
    
    /**
     * 编辑多图文消息
     * @param array $data 单图文数组
     * @param array $datamore 多图文信息
     */
    public static function editMsgMore($data,$id)
    {
        if ($data !== FALSE) {
            $pdo = self::_pdo('db_w');
            $pdo->beginTransaction();
            try {
                
                //先删除
                $del = self::deleteByMoreID($id);
                if ($del == FALSE) {
                    $pdo->rollback();
                    return FALSE;
                }
                
                /*循环编辑多图文*/
                foreach ($data as $key => $value) {
                    $now_id = $value['id'];
                    unset($value['id']);
                    
                    $value['wechat_content'] = CommonBase::contrastImg($value['content'], '1');
                    $value['is_del'] = '2';
                    if ($now_id) {
                        $lastID = self::updateByID($value,$now_id);
                        if ($lastID == FALSE) {
                            $pdo->rollback();
                            return FALSE;
                        }
                    } else {
                        $value['is_more'] = '0';
                        $value['graphic_id'] = $id;
                        $lastID = self::addData($value);
                        if ($lastID == FALSE) {
                            $pdo->rollback();
                            return FALSE;
                        }
                    }
                }
                
    
                $pdo->commit();
                return TRUE;
            } catch(\Exception $e) {
                $pdo->rollback();
                return FALSE;
            }
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
        $result = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
        
        return $result;
    }
    
    
    /**
     * 获取单条数据根据多图文id
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getInfoByGraphicID($graphic_id)
    {
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());

        $where['supplier_id'] = $adminInfo['supplier_id'];
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['graphic_id'] = intval($graphic_id);
        $where['is_more'] = '0';
    
        $pdo = self::_pdo('db_r');
        $result = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getAll();
        
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
        $up = $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
        if ($up) {
            return $up;
        }
        return false;
    }
    
    
    
    /**
     * 获取单条数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getInfoByWhere($where=[],$desc='')
    {
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        
        $sql = 'SELECT
    		   *
    		FROM
	             '.CommonBase::$_tablePrefix.self::$_tableName.' a
	        WHERE
    		    a.is_del = 2
	        AND      
	            a.supplier_id ='.$adminInfo['supplier_id'];
    
        if ($where) {
            foreach ($where as $key=>$val) {
                $sql .= " AND ".$key." = '".$val."'";
            }
        }
    
        if ($desc) {
            $sql .=" order By created_at ".$desc;
        }
         
        $pdo = self::_pdo('db_r');
        $info = $pdo ->YDGetRow($sql);
         
        return $info;
    
    }
    
    
    
    
    /**
     * 根据表自增 ID删除记录(单图文)
     * @param int $id 表自增 ID
     * @return boolean 删除是否成功
     */
    public static function deleteByID($id)
    {
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        
        $data['is_del'] = self::DELETE_FAIL;
        $data['updated_at'] = date("Y-m-d H:i:s");
        $data['deleted_at'] = date("Y-m-d H:i:s");
    
        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id),'supplier_id'=>$adminInfo['supplier_id']));
    }
    
    
    /**
     * 根据表自增 ID删除记录（多图文）
     * @param int $id 表自增 ID
     * @return boolean 删除是否成功
     */
    public static function deleteByMoreID($id)
    {
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        
        $data['is_del'] = self::DELETE_FAIL;
        $data['updated_at'] = date("Y-m-d H:i:s");
        $data['deleted_at'] = date("Y-m-d H:i:s");
    
        $pdo = self::_pdo('db_w');
        
        $pdo->update(self::$_tableName, $data, array('graphic_id' => intval($id),'supplier_id'=>$adminInfo['supplier_id']));
        
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id),'supplier_id'=>$adminInfo['supplier_id']));
    }
    
    
    
    
    
    
    
    
    
    
    
}