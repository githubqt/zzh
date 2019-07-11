<?php
namespace Services\SystemPermissions;
/**
 * 权限设置
 * @version v0.01
 * @package admin\service\dcs\DcsSystemService
 * @author fuzuchang <fuzuchang@houhouyun.com>
 * @time  2017-07-17
 */


use Services\BaseService;
use SystemPermissions\SystemPermissionsModel; 
class SystemPermissionsService extends BaseService
{
   

    const UNIT_JIAO = 1;
    const UNIT_FEN = 2;

    public static $_config = NULL;

    function __construct()
    {

    }


    /**
     * 创建或者更新配置项
     * @method save
     * @param $data
     * @return bool|null
     * @throws
     */
    public static function save($data)
    {
        return SystemPermissionsModel::save($data);
    }

    /**
     * 创建或者更新配置项
     * @method save
     * @param $data
     * @return bool|null
     * @throws
     */
    public static function saveValue($data)
    {
        return SystemPermissionsModel::saveValue($data);
    }

    /**
     * 获取全部配置或指定配置项
     * @method getConfig
     * @param null $key
     * @return $this|null
     * @throws
     */
    public static function getConfig($key = NULL)
    {
        if ($key) {
            self::$_config = SystemPermissionsModel::getConfig($key);
        }

        return self::$_config;

    }

    /**
     * 获取配置项的值
     * @method getValue
     * @param bool $toBool 是否转换为bool值
     * @return null
     * @throws
     */
    public static function getValue($toBool = false)
    {
        if (self::$_config) {
            if ($toBool) {
                return ((int)self::$_config['value'] === 1);
            } else {
                return self::$_config['value'];
            }
        } else {
            return NULL;
        }
    }


    /**
     * 获取配置选项或制定选项的值
     * @method getOption
     * @param null $key
     * @return null
     * @throws
     */
    public static function getOption($key = NULL)
    {
        if (self::$_config) {
            if ($key) {
                if (isset(self::$_config['options'][$key])) {
                    return self::$_config['options'][$key];
                }
            } else {
                return self::$_config['options'];
            }
        }
        return NULL;
    }
    
    

    
    

    /**
     * 通过对应的ID获取详情所有数据
     * @method getInfoByID
     * @param int $id
     * @return array|bool   array 对应的详情数据
     * @throws
     */
    public static function getInfoByID($id)
    {
        $detailInfo = SystemPermissionsModel::getInfoByID($id);
        if (is_array($detailInfo) && count($detailInfo) > 0) {
            return $detailInfo;
        }
        return FALSE;
    }

    /**
     * 更新对应值
     * @method updateByID
     * @param array $data
     * @param int $id
     * @return bool
     * @throws
     */
    public static function updateByID($data, $id)
    {
        $info = self::filerAttribute(self::_filterAttribute, $data);
        if ($info !== FALSE) {
            $res = SystemPermissionsModel::updateByID($info, $id);
            if ($res == FALSE) {
                return FALSE;
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 删除对应的参数
     * @method deleteByID
     * @param int $id
     * @return bool
     * @throws
     */
    public static function deleteByID($id)
    {
        if (is_numeric($id)) {
            $atrVal = SystemPermissionsModel::deleteByID($id);
            if (!$atrVal) {
                return FALSE;
            }
            return TRUE;
        }
        return FALSE;
    }

}