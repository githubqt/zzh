<?php
/**
 * 微信消息记录内容model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-08-28
 */
namespace Weixin;
use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
 
class WexinUserMsgContentModel extends \Common\CommonBase 
{
    protected static $_tableName = 'weixin_user_msg_content'; 
    private function __construct() {
        parent::__construct();
    }
    
    
    
    /**
     * 获取对应的list列表
     * @param array  $attribute 获取对应的参数
     * @param integer $page 对应的页
     * @param integer $rows 取出的行数
     * @return array
     */
    public static function getList($attribute = array(),$page = 1,$rows = 10)
    {
        $limit = ($page) * $rows;
        if (!empty($attribute['info']) && is_array($attribute['info']) && count($attribute['info']) > 0) {
            extract($attribute['info']);
        }
         
        $pdo = YDLib::getPDO('db_r');
        $fileds = " a.*,b.nickname,b.head_url,b.sex,b.country_name,b.province_name,b.city_name";
        $sql = 'SELECT
        		   [*]
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a
		        LEFT JOIN
		             '.CommonBase::$_tablePrefix.'weixin_user_msg b
		        ON
		             a.user_msg_id = b.id
		        WHERE
        		    a.is_del = 2
		        AND
        		    b.is_del = 2
		        AND 
		           a.MsgType != "event"
		        AND
		            a.supplier_id="'.AdminModel::getAdminLoginInfo(AdminModel::getAdminID())['supplier_id'].'"';
    
        if (isset($nickname) && !empty($nickname)) {
            $sql .= " AND b.nickname like '%".$nickname."%' ";
        }
    
        if (isset($msg_type) && !empty($msg_type)) {
            $sql .= " AND a.msg_type = '".$msg_type."' ";
        }
        
        if (isset($is_reply) && !empty($is_reply)) {
            $sql .= " AND a.is_reply = '".$is_reply."' ";
        }
        
        if (isset($is_star) && !empty($is_star)) {
            $sql .= " AND a.is_star = '".$is_star."' ";
        }
        
        if (isset($is_note) && !empty($is_note)) {
            if ($is_note == '1') {
                $sql .= " AND a.note is null ";
            } elseif ($is_note == '2') {
                $sql .= " AND a.note is not null ";
            }
        }
    
        if (isset($start_time) && !empty($start_time)) {
            $sql .= " AND a.created_at >= '".$start_time." 00:00:00'";
        }
        
        if (isset($end_time) && !empty($end_time)) {
            $sql .= " AND a.created_at <= '".$end_time." 23:59:59'";
        }
   
        $result['total'] = $pdo ->YDGetOne(str_replace("[*]", "count(*) as num", $sql));
        $sql .= " GROUP BY a.id ORDER BY a.id DESC limit {$limit},{$rows}";
        $result['list'] = $pdo ->YDGetAll(str_replace("[*]", $fileds, $sql));
    
        foreach ( $result ['list'] as $key => $value ) {
            //查询会员等级
            if ($value['head_url']) {
                $result ['list'][$key]['head_url'] = '<img src="'.HOST_FILE.$value['head_url'].'" style="width:70px">';
            }
            if ($value['sex'] == '1') {
                $result ['list'][$key]['sex_name'] = '男';
            } elseif ($value['sex'] == '2') {
                $result ['list'][$key]['sex_name'] = '女';
            }elseif ($value['sex'] == '0') {
                $result ['list'][$key]['sex_name'] = '未知';
            }
            
            $result ['list'][$key]['address'] = $value['country_name'].' '.$value['province_name'].' '.$value['city_name'];
            $result ['list'][$key]['content'] = CommonBase::getemojiname($value['msg_content']);
            $result ['list'][$key]['content'] = CommonBase::getemojicode($result ['list'][$key]['content']);
            $result ['list'][$key]['reply_txt'] = $value['is_reply'] == '1'?'未回复':'已回复';
            
            if ($value['MsgType'] == 'text') {
                $result ['list'][$key]['msg_type_txt'] = '文本';
            } elseif ($value['MsgType'] == 'image') {
                $result ['list'][$key]['msg_type_txt'] = '图片';
            } elseif ($value['MsgType'] == 'voice') {
                $result ['list'][$key]['msg_type_txt'] = '语音';
            } elseif ($value['MsgType'] == 'video') {
                $result ['list'][$key]['msg_type_txt'] = '视频';
            } elseif ($value['MsgType'] == 'shortvideo') {
                $result ['list'][$key]['msg_type_txt'] = '小视频';
            } elseif ($value['MsgType'] == 'location') {
                $result ['list'][$key]['msg_type_txt'] = '位置信息';
            } elseif ($value['MsgType'] == 'link') {
                $result ['list'][$key]['msg_type_txt'] = '链接';
            } elseif ($value['MsgType'] == 'event') {
                $json = json_decode($value['resources_info'],true);
                if ($json['Event'] == 'subscribe') {
                    $result ['list'][$key]['msg_type_txt'] = '关注';
                } else if ($json['Event'] == 'unsubscribe') {
                    $result ['list'][$key]['msg_type_txt'] = '取消关注';
                }
            }
            
            if ($value['is_star'] == '1') {
                $result ['list'][$key]['is_star_txt'] = '未加星';
            } else {
                $result ['list'][$key]['is_star_txt'] = '已加星';
            }
            $result ['list'][$key]['is_ok_reply'] = '0';
            if (time()-strtotime($value['created_at']) >= '86400') {
                $result ['list'][$key]['is_ok_reply'] = '1';
            }
            $result ['list'][$key]['note'] = $value['note']?$value['note']:'';
        }
         
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
    public static function updateByUserMsgID($data, $id)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
    
        $pdo = self::_pdo('db_w');
        $up = $pdo->update(self::$_tableName, $data, array('user_msg_id' => intval($id)));
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
    public static function getInfoByWhere($where=[],$desc='',$like = '')
    {
         $sql = 'SELECT
    		   *
    		FROM
	             '.CommonBase::$_tablePrefix.self::$_tableName.' a
	        WHERE
    		    a.is_del = 2';
	     
	     if ($where) {
	         foreach ($where as $key=>$val) {
	             $sql .= " AND ".$key." = '".$val."'";
	         }
	     }
	     
	     if ($like) {
	         foreach ($like as $key=>$val) {
	           $sql .= " AND ".$key." like '%".$val."%'";
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
     * 获取对应的list列表
     * @param array  $attribute 获取对应的参数
     * @param integer $page 对应的页
     * @param integer $rows 取出的行数
     * @return array
     */
    public static function getinfoByID($id)
    {
        
        $pdo = YDLib::getPDO('db_r');
        $fileds = " a.*,b.nickname,b.head_url,b.sex,b.country_name,b.province_name,b.city_name";
        $sql = 'SELECT
        		   [*]
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a
		        LEFT JOIN
		             '.CommonBase::$_tablePrefix.'weixin_user_msg b
		        ON
		             a.user_msg_id = b.id
		        WHERE
        		    a.is_del = 2
		        AND
        		    b.is_del = 2
		        AND
		           a.id = '.$id.'
		        AND
		            a.supplier_id="'.AdminModel::getAdminLoginInfo(AdminModel::getAdminID())['supplier_id'].'"';
    
        $result = $pdo ->YDGetRow(str_replace("[*]", $fileds, $sql));
    
        //查询会员等级
        if ($result['head_url']) {
            $result['head_url'] = '<img src="'.HOST_FILE.$result['head_url'].'" style="width:70px">';
        }
        if ($result['sex'] == '1') {
            $result['sex_name'] = '男';
        } elseif ($result['sex'] == '2') {
            $result['sex_name'] = '女';
        } elseif ($result['sex'] == '0') {
            $result['sex_name'] = '未知';
        }

        $result['address'] = $result['country_name'].' '.$result['province_name'].' '.$result['city_name'];
        $result['content'] = CommonBase::getemojiname($result['msg_content']);
        $result['content'] = CommonBase::getemojicode($result['content']);
        $result['reply_txt'] = $result['is_reply'] == '1'?'未回复':'已回复';

        if ($result['MsgType'] == 'text') {
            $result['msg_type_txt'] = '文本';
        } elseif ($result['MsgType'] == 'image') {
            $result['msg_type_txt'] = '图片';
        } elseif ($result['MsgType'] == 'voice') {
            $result['msg_type_txt'] = '语音';
        } elseif ($result['MsgType'] == 'video') {
            $result['msg_type_txt'] = '视频';
        } elseif ($result['MsgType'] == 'shortvideo') {
            $result['msg_type_txt'] = '小视频';
        } elseif ($result['MsgType'] == 'location') {
            $result['msg_type_txt'] = '位置信息';
        } elseif ($result['MsgType'] == 'link') {
            $result['msg_type_txt'] = '链接';
        } elseif ($result['MsgType'] == 'event') {
            $json = json_decode($result['resources_info'],true);
            if ($json['Event'] == 'subscribe') {
                $result['msg_type_txt'] = '关注';
            } else if ($json['Event'] == 'unsubscribe') {
                $result['msg_type_txt'] = '取消关注';
            }
        }

        if ($result['is_star'] == '1') {
            $result['is_star_txt'] = '未加星';
        } else {
            $result['is_star_txt'] = '已加星';
        }
        $result['is_ok_reply'] = '0';
        if (time()-strtotime($result['created_at']) >= '172800') {
            $result['is_ok_reply'] = '1';
        }
        $result['note'] = $result['note']?$result['note']:'';
        
         
        if ($result) {
            return $result;
        } else {
            return false;
        }
    
    }
    
    
    
    /**
     * 获取多条数据
     *
     * @param interger $user_msg_id
     * @return mixed
     *
     */
    public static function getInfoByMsgID($user_msg_id)
    {
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['user_msg_id'] = intval($user_msg_id);
    
        $pdo = self::_pdo('db_r');
        $result = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getAll();
        
        if ($result) {
            foreach ($result as $key=>$val) {
                $result[$key]['content'] = CommonBase::getemojiname($val['msg_content']);
                $result[$key]['content'] = CommonBase::getemojicode($result[$key]['content']);
                $result[$key]['reply_txt'] = $val['is_reply'] == '1'?'未回复':'已回复';
                if ($val['MsgType'] == 'event') {
                    $json = json_decode($val['resources_info'],true);
                    $result[$key]['option_type'] = '1';
                    if ($json['Event'] == 'subscribe') {
                        $result[$key]['option_type'] = '2';
                        $result[$key]['content'] = '用户关注公众号';
                    } else if ($json['Event'] == 'unsubscribe') {
                        $result[$key]['option_type'] = '2';
                        $result[$key]['content'] = '用户取消关注公众号';
                    }
                }
            }
        }
        return $result;
    }
    
    
    
    public static function getInfoByMsgIDDESC($user_msg_id)
    {
    
        $pdo = YDLib::getPDO('db_r');
        $fileds = " a.*";
        $sql = 'SELECT
        		   [*]
        		FROM
		             '.CommonBase::$_tablePrefix.self::$_tableName.' a
		        WHERE
        		    a.is_del = 2
		        AND
		           a.user_msg_id = '.$user_msg_id.'
		        AND 
		           a.msg_type = 1
		        AND
		            a.supplier_id="'.AdminModel::getAdminLoginInfo(AdminModel::getAdminID())['supplier_id'].'"
		        ORDER BY a.id DESC';
    
        $result = $pdo ->YDGetRow(str_replace("[*]", $fileds, $sql));
        return $result;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
  
}