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
use Publicb;
 
class WexinUserMsgModel extends \Common\CommonBase 
{
    protected static $_tableName = 'weixin_user_msg';
    private function __construct() {
        parent::__construct();
    }
    
  
    
    /**
     * 添加信息
     * @return mixed
     */
    public static function addData($access_token,$openid,$wechat,$supplier_id) {
        $userinfo = $wechat->getOauthUserinfo($access_token,$openid);
        if ($userinfo) {
            //存数据库
            $user = [];
            $user['nickname']             = $userinfo['nickname'];
            $user['head_url']             = Publicb::getImage($userinfo['headimgurl']);
            $user['openid']               = $userinfo['openid'];
            $user['sex']                  = $userinfo['sex'];
            $user['supplier_id']          = $supplier_id;
            $user['is_del']               = '2';
            $user['created_at'] = date("Y-m-d H:i:s");
            $user['updated_at'] = date("Y-m-d H:i:s");
            if (isset($userinfo['unionid'])) {
                $user['unionid']          = $userinfo['unionid'];
            }
            $user['country_name']         = $userinfo['country'];
            $user['province_name']        = $userinfo['province'];
            $user['city_name']            = $userinfo['city'];
            
            $db = YDLib::getPDO('db_w');
            $last_id = $db->insert(self::$_tableName, $user);
            return $last_id;
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
    public static function getInfoByOpenID($openid)
    {
        $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
        $where['supplier_id'] = $adminInfo['supplier_id'];
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['openid'] = $openid;
    
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
    public static function getInfoByID($id)
    {
        $where['is_del'] = self::DELETE_SUCCESS;
        $where['id'] = intval($id);
    
        $pdo = self::_pdo('db_r');
        $result = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
        if ($result) {
            //查询会员等级
            $result['old_head_url'] = $result['head_url'];
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
            
            
        }
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
    
        if ($desc) {
            $sql .=" order By created_at ".$desc;
        }
         
        $pdo = self::_pdo('db_r');
        $info = $pdo ->YDGetRow($sql);
         
        return $info;
    
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}