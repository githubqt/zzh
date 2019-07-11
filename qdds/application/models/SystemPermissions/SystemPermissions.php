<?php
namespace SystemPermissions;
use Custom\YDLib;
/**
 * 基本设置 Model
 * @version v0.01
 * @package admin\model\dcs\DcsSystemModel
 * @author fuzuchang <fuzuchang@zhahehe.com>
 * @time  2018-11-28
 */
class SystemPermissionsModel extends \Common\CommonBase
{
    // 当前Model文件操作的表名
    public static $_tableName = 'system_permissions_config';
    
    /**
     * 获取给PDO方式下操作的当前操作表的表名
     * 子类使用
     *
     * @code ->query('SELECT * FROM ' . $this->getTb())
     *
     * @return string
     */
    // getTableName() 别名
    public static function getTb()
    {
        return self::$_tablePrefix . self::$_tableName;
    }
    
    /**
     * 获取配置
     * @method getConfig
     * @param null $key
     * @return array
     * @throws
     * @author fuzuchang <fuzuchang@zhahehe.com>
     */
    public static function getConfig($key = null)
    {
        $filed = "config_key,config_value,config_options";

        $sql = "SELECT [*]  FROM " . self::getTb();

        $pdo = YDLib::getPDO('db_r');
        $configs = $pdo->YDGetAll(str_replace('[*]', $filed, $sql));

        $configMaps = [];

        if ($configs) {
            foreach ($configs as $config) {
                $configMaps[$config['config_key']]['value'] = $config['config_value'];
                $configMaps[$config['config_key']]['options'] = json_decode($config['config_options'], true);
            }
        }

        if ($key !== null) {

            if ($configMaps && isset($configMaps[$key])) {
                return $configMaps[$key];
            }

            return null;
        }

        return $configMaps;
    }

    /**
     * 创建或更新
     * @method save
     * @param array $data
     * @return bool|null
     * @throws * @throws Exception
     * @author fuzuchang <fuzuchang@zhahehe.com>
     */
    public static function save($data = [])
    {
        try {

            foreach ($data as $k => $v) {
                $tbData = [];
                $tbData['config_key'] = $k;
                $tbData['config_value'] = $v['value'] ? $v['value'] : '';
                
                //打印单只存储value值
                if ($k != 'is_open_print_set') {
                    $tbData['config_options'] = json_encode($v['options'], JSON_UNESCAPED_UNICODE);
                }
                
                if (self::isKeyExists($k)) {
                    self::_updateData(self::getTb(), $tbData, "`config_key` = '{$k}'");
                } else {
                    if ($k == 'is_open_print_set') { 
                        $tbData['config_options'] = '{"is_open_print_user_return":"1","is_open_print_user_recharge":"1","is_open_print_user_recharge_return":"1","is_open_print_jici_recharge":"1","is_open_print_jici_consumption":"1","is_open_print_jici_return":"1","is_open_print_user_deposit":"1","is_open_print_user_Send_out":"1","is_open_print_user_change_shifts":"1","is_open_print_cashier_reconciliation":"1","print_user_return_num":"1","print_user_recharge_num":"1","print_user_recharge_return_num":"1","print_jici_recharge_num":"1","print_jici_consumption_num":"1","print_jici_return_num":"1","print_user_deposit_num":"1","user_Send_out_num":"1","print_user_change_shifts_num":"1","cashier_reconciliation_num":"1"}';
                    }
                    $pdo = YDLib::getPDO('db_w');
                    $pdo->insert(self::getTb(), $tbData);
                }
            }
            return true;
        } catch (\Exception $e) {
            print_r($e->getMessage());
            throw new \Exception($e->getMessage());
        }

    }
    
    
    /**
     * 创建或更新
     * @method save
     * @param array $data
     * @return bool|null
     * @throws * @throws Exception
     * @author fuzuchang <fuzuchang@zhahehe.com>
     */
    public static function saveValue($data = [])
    {
        try {
            foreach ($data as $k => $v) {
                if (self::isKeyExists($k)) {
                        $tbData = [];
                        $tbData['config_key'] = $k;
                        $tbData['config_options'] = json_encode($v['options'], JSON_UNESCAPED_UNICODE);
                        self::_updateData(self::getTb(), $tbData, "`config_key` = '{$k}'");
                  
                } else {
                        $tbData = [];
                        $tbData['config_key'] = $k;
                        $tbData['config_value'] = $v['value'] ? $v['value'] : '1';
                        $tbData['config_options'] = '{"is_open_print_user_return":"1","is_open_print_user_recharge":"1","is_open_print_user_recharge_return":"1","is_open_print_jici_recharge":"1","is_open_print_jici_consumption":"1","is_open_print_jici_return":"1","is_open_print_user_deposit":"1","is_open_print_user_Send_out":"1","is_open_print_user_change_shifts":"1","is_open_print_cashier_reconciliation":"1","print_user_return_num":"1","print_user_recharge_num":"1","print_user_recharge_return_num":"1","print_jici_recharge_num":"1","print_jici_consumption_num":"1","print_jici_return_num":"1","print_user_deposit_num":"1","user_Send_out_num":"1","print_user_change_shifts_num":"1","cashier_reconciliation_num":"1"}';
                        $pdo = YDLib::getPDO('db_w');
                        $pdo->insert(self::getTb(), $tbData);
                      
                }
            }
            return true;
        } catch (\Exception $e) {
            print_r($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    
    }
    
  
    

    /**
     * 判断配置项是否存在
     * @method isKeyExists
     * @param $key
     * @return bool
     * @throws
     * @author fuzuchang <fuzuchang@zhahehe.com>
     */
    public static function isKeyExists($key)
    {
        return self::countBy(self::getTb(), "`config_key` = '{$key}'") > 0;
    }
    
    /**
     * 根据where获取数量
     *
     * @param unknown $where
     * @date 2018-11-27
     */
    public static function countBy($tb, $where)
    {
        $sql = "SELECT COUNT(1) as num FROM {$tb} WHERE  {$where}";
        $pdo = YDLib::getPDO('db_r');
       
        return $pdo->YDGetOne($sql)['num'];
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

}				
		