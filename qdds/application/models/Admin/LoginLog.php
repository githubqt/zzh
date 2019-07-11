<?php
/**
 * 登陆日志model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
namespace Admin;

use Custom\YDLib;
use Core\Queue;
use Common\DataType;

class LoginLogModel extends \Common\CommonBase 
{
    protected static $_tableName = 'login_log';
    private function __construct() {
        parent::__construct();
    }
    
    /* 获取登陆次数*/
    public static function getLoginNum($adminID)
    {
        $pdo = YDLib::getPDO('db_r');
        $ret = $pdo->clear()->select('*')->from(self::$_tableName)->where(['admin_id'=>$adminID])->order('id DESC')->getRow();
        
        if(!$ret){
            $num = 0;
        }else{
            $num = $ret['num'];
        }
        return $num;
    }
  

    /**
     * 添加登陆日志信息
     *
     * @param array $info
     * @return mixed
     *   
     */
    public static function addLogin($info)
    {
        
        $db = YDLib::getPDO('db_w');
        $result = $db->insert(self::$_tableName, $info, ['ignore' => true]);
    
        return $result;
    }
    
}