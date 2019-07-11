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

class ImageModel extends \BaseModel {
	protected static $_tableName = 'img';
	
	/**
	 * 记录入库
	 * 
	 * @param array $data
	 *        	表字段名作为key的数组
	 * @return int 入库成功则返回入库记录的自增ID，否则返回FALSE
	 */
	public static function addData($data) {
		$data ['is_del'] = self::DELETE_SUCCESS;
		$data ['created_at'] = date ( "Y-m-d H:i:s" );
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->insert ( self::$_tableName, $data );
	}
	
	/**
	 * 获取单条数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByTypeOrID($id, $type = 'product') {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['obj_id'] = intval ( $id );
		$where ['type'] = $type;
		//$where ['supplier_id'] = SUPPLIER_ID;
		$pdo = self::_pdo ( 'db_r' );
		
		return $pdo->clear ()->select ( 'id,obj_id,type,img_url,img_type,img_note' )->from ( self::$_tableName )->where ( $where )->getAll ();
	}

    /**
     * 根据回收id删除回收图片
     * @param $obj_id
     * @return mixed
     */
	public static function deleteRecoveryItem($obj_id){
        $data['is_del'] = self::DELETE_FAIL;
        $data['updated_at'] = date("Y-m-d H:i:s");
        $data['deleted_at'] = date("Y-m-d H:i:s");

        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('obj_id' => intval($obj_id),'type'=>'recovery'));
    }
    
    
    /**
     * 获取证书单条数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getInfoByCertificateID($id, $type ,$appraisal_flaw) {
    	
    	
    	$pdo = self::_pdo ( 'db_r' );
    	$sql = "SELECT
        		   a.id,a.obj_id,a.type,a.img_url,a.img_type,a.img_note
        		FROM
		             ". CommonBase::$_tablePrefix . self::$_tableName . " a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.obj_id = ".intval ( $id ) ."
		         AND
		            a.type =  '".$type."'
		        AND 
		            a.img_note IN (".$appraisal_flaw.")
		           ";
    	
    	return $pdo->YDGetAll ($sql);
    }
    
    
    
    
}