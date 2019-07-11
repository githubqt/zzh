<?php
/**
 * 图片model
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */

namespace Image;

use Overtrue\Pinyin;
use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;

class ImageModel extends \BaseModel
{
    protected static $_tableName = 'img';

    /**
     * 定义表名称
     * @var string
     */
    public $table = 'img';

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
        return $pdo->insert(self::$_tableName, $data);
    }

    /**
     * 获取单条数据
     *
     * @param int $id
     * @return mixed
     *
     */
    public static function getInfoByTypeOrID($id, $type = 'product')
    {
//        $adminId = AdminModel::getAdminID();
//        $adminInfo = AdminModel::getAdminLoginInfo($adminId);
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['obj_id'] = $id;
        $where['type'] = $type;
//        $where['supplier_id'] = $adminInfo['supplier_id'];
        $pdo = self::_pdo('db_r');
        $list = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getAll();
        foreach (($list) as $k => $v){
            $list[$k]['full_img_url'] = HOST_FILE . ltrim($v['img_url'], '/');
        }
        return $list;
    }


    /**
     * 获取单条数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getInfoByTypeOrIDUse($id, $type = 'product')
    {
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['obj_id'] = intval($id);
        $where['type'] = $type;
        $pdo = self::_pdo('db_r');
        return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getAll();
    }


    /**
     * 获取单条数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getBlackByTypeOrID($id, $type = 'blacklist')
    {
//        $adminId = AdminModel::getAdminID();
//        $adminInfo = AdminModel::getAdminLoginInfo($adminId);
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['obj_id'] = $id;
        $where['type'] = $type;
//        $where['supplier_id'] = $adminInfo['supplier_id'];
        $pdo = self::_pdo('db_r');
        return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getAll();
    }


    /**
     * 根据表自增 ID删除记录
     * @param int $id 表自增 ID
     * @return boolean 删除是否成功
     */
    public static function deleteByID($id,$type='blacklist')
    {
        $adminId = AdminModel::getAdminID();
        $adminInfo = AdminModel::getAdminLoginInfo($adminId);
        $data['is_del'] = self::DELETE_FAIL;
        $data['updated_at'] = date("Y-m-d H:i:s");
        $data['deleted_at'] = date("Y-m-d H:i:s");
        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('obj_id' => intval($id), 'type' => $type, 'supplier_id' => $adminInfo['supplier_id']));
    }

    
    
    
    /**
     * 根据表自增 ID删除记录
     * @param int $id 表自增 ID
     * @return boolean 删除是否成功
     */
    public static function deleteReturnByID($id)
    {
    	$data['is_del'] = self::DELETE_FAIL;
    	$data['updated_at'] = date("Y-m-d H:i:s");
    	$data['deleted_at'] = date("Y-m-d H:i:s");
    	$pdo = self::_pdo('db_w');
    	return $pdo->update(self::$_tableName, $data, array('obj_id' => intval($id), 'type' => 'salesreturn', ));
    }
    /**
     * 添加图片
     * @param $type
     * @param $obj_id
     * @param $supplier_id
     * @param $img_url
     * @return int
     */
    public static function add($type, $obj_id, $img_url, $supplier_id = 0)
    {

        if (!$supplier_id) {
            $supplier_id = self::auth()['supplier_id'];
        }

        if (is_array($img_url) && count($img_url) > 0) {
            $ret = 0;
            foreach ($img_url as $img) {
                $data = [];
                $data['supplier_id'] = $supplier_id;
                $data['obj_id'] = $obj_id;
                $data['img_url'] = $img;
                $data['type'] = $type;
                $data['img_type'] = substr($img, strrpos($img, '.') + 1);
                if (self::addData($data)) {
                    $ret++;
                }
            }
            return count($img_url) === $ret;
        } else {
            $data = [];
            $data['supplier_id'] = $supplier_id;
            $data['obj_id'] = $obj_id;
            $data['img_url'] = $img_url;
            $data['type'] = $type;
            $data['img_type'] = substr($img_url, strrpos($img_url, '.') + 1);
            return self::addData($data);
        }
    }

    /**
     * 获取采购支付汇款图片
     * @param $id
     * @return array
     */
    public static function getPurchasePayImages($id)
    {
        $result = [];
        $list = self::getBlackByTypeOrID($id, 'purchase_pay');
        foreach ($list as $k => $item) {
            $result[] = HOST_FILE . ltrim($item['img_url'], '/');
        }
        return $result;
    }

    
    
    
    
    /*
     * 写入重新发货图片
     * 
     */
    
    public static function addDelivery($item,$addReturnInfo,$supplier_id){
    	$pdo = self::_pdo('db_r');
		foreach ( $item as $key => $value ) {
			$imgList ['supplier_id'] = $supplier_id;
			$imgList ['img_url'] = $value;
			$imgList ['obj_id'] = $addReturnInfo;
			$imgList ['type'] = 'salesreturn';
			$imgList ['img_type'] = pathinfo ( $value, PATHINFO_EXTENSION );
			$imgList ['is_del'] = 2;
			$lastId = $pdo->insert ( 'img', $imgList, [ 
					'ignore' => true 
			] );
		}
		return true;
	}
	
	
	
	/**
	 * 获取多天数据
	 *
	 * @param interger $id
	 * @param interger $supplier_id
	 * @return mixed
	 *
	 */
	public static function getReturnInfo($id,$supplier_id,$type = 'salesreturn')
	{
		$where['is_del'] = self::DELETE_SUCCESS;
		$where['obj_id'] = intval($id);
		$where['type'] = $type;
		$where['supplier_id'] = $supplier_id;
		$pdo = self::_pdo('db_r');
		return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getAll();
	}
	
	
}