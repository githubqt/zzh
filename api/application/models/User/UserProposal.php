<?php

/**
 * 用户建议model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-15
 */
namespace User;

use Custom\YDLib;
use Common\CommonBase;
use Image\ImageModel;
use ErrnoStatus;

class UserProposalModel extends \Common\CommonBase {
	protected static $_tableName = 'user_proposal';
	
	/**
	 * 获取表名
	 */
	public static function getTb() {
		return self::$_tablePrefix . self::$_tableName;
	}
	
	/**
	 * 添加信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addData($info, $img) {
		$db = YDLib::getPDO ( 'db_w' );
		$db->beginTransaction ();
		try {
			$info ['supplier_id'] = SUPPLIER_ID;
			$info ['is_del'] = '2';
			$info ['created_at'] = date ( "Y-m-d H:i:s" );
			$info ['updated_at'] = date ( "Y-m-d H:i:s" );
			$last_id = $db->insert ( self::$_tableName, $info );
			
			if ($last_id) {
				// 插入图片信息
				if ($img) {
					foreach ( $img as $key => $img ) {
						$imgItems = [ ];
						$imgItems ['supplier_id'] = SUPPLIER_ID;
						$imgItems ['obj_id'] = $last_id;
						$imgItems ['img_url'] = $img;
						$imgItems ['type'] = 'proposal';
						$imgItems ['img_type'] = substr ( $img, strrpos ( $img, '.' ) + 1 );
						$childImgId = ImageModel::addData ( $imgItems );
						if ($childImgId == FALSE) {
							
							YDLib::output ( ErrnoStatus::STATUS_60553 );
						}
					}
				}
			} else {
				$db->rollback ();
				return FALSE;
			}
			
			$db->commit ();
			return $last_id;
		} catch ( \Exception $e ) {
			$db->rollback ();
			return FALSE;
		}
	}
	
	/**
	 * 根据一条自增ID更新表记录
	 * 
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateByID($data, $id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
	
	/**
	 * 根据表自增 ID删除记录
	 * 
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID($id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
	
	
	/**
	 * 获取单条数据
	 *
	 * @param interger $id
	 * @return mixed
	 *
	 */
	public static function getSuggestAll($user_id,$page = 1,$rows = 10) {
		
		$limit = ($page-1) * $rows;
		
		$pdo = YDLib::getPDO('db_r');
        $fields = " a.*,CASE WHEN a.`status` in (2,3) THEN 2 ELSE 1 END type";
		$sql = 'SELECT
        		   [*]
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a
		        WHERE
        		    a.is_del = 2
		        AND
		            a.supplier_id = '.SUPPLIER_ID.'
		        AND
		            a.user_id = '.intval ( $user_id ).'
		        ';
					
		$result['total'] = $pdo->YDGetOne(str_replace('[*]', 'COUNT(1) num', $sql));		
		$sql .= " ORDER BY field(type,1,2)  ASC , a.created_at DESC  LIMIT {$limit},{$rows}";
		$result['rows'] = $pdo->YDGetAll(str_replace('[*]', $fields, $sql));
		
		foreach ($result['rows'] as $key => $val){
		    $img_url = ImageModel::getInfoByTypeOrID($result['rows'][$key]['id'],'proposal');
		    
		  	$year	= substr($val['created_at'],0,strpos($val['created_at'], '-'));
		    $month	= substr($val['created_at'],5,strpos($val['created_at'],'/')+2);
		    $day	= substr($val['created_at'],8,strpos($val['created_at'],'/')+2);
		    $h	= substr($val['created_at'],-8,5);
			if($year <  date('Y')){
				$result['rows'][$key]['time_txt'] = $year."年".$month."月".$day."日".$h;
			}else {
				$result['rows'][$key]['time_txt'] = $month."月".$day."日".$h;
			}
		   
		    foreach ($img_url as $k => $v){
		    	  
		    	   if (! empty ( $img_url[$k]['img_url'] )) {
		    	   	$img_url[$k] ['log_url'] = HOST_FILE . self::imgSize ( $img_url[$k]['img_url'], 4 );
		    	   } else {
		    	   	$img_url[$k] ['log_url'] = HOST_STATIC . 'common/images/common.png';
		    	   }
		    }
		    
		    
		    $result['rows'][$key]['imgList'] = $img_url;
		}
		
		 return $result;
	}
	
	
}