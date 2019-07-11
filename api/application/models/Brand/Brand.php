<?php

/**
 * 权限model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-05
 */
namespace Brand;

use Overtrue\Pinyin;
use Custom\YDLib;
use Common\CommonBase;
use Product\ProductModel;

class BrandModel extends \BaseModel
{
    protected static $_tableName = 'brand';

    /* 获取列表 */
    public static function getList($attribute = array(), $page = 0, $rows = 10)
    {
        if (!empty ($attribute ['info']) && is_array($attribute ['info']) && count($attribute ['info']) > 0) {
            extract($attribute ['info']);
        }

        $pdo = YDLib::getPDO('db_r');
        $fields = " a.* ";
        $sql = 'SELECT 
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        WHERE
        		    a.is_del = 2
        		';

        if (isset ($name) && !empty ($name)) {
            $sql .= " AND a.name like '%" . $name . "%' ";
        }

        if (isset ($en_name) && !empty ($en_name)) {
            $sql .= " AND a.en_name like '%" . $en_name . "%' ";
        }

        if (isset ($alias_name) && !empty ($alias_name)) {
            $sql .= " AND a.alias_name like '%" . $alias_name . "%' ";
        }

        if (isset ($start_time) && isset ($end_time) && !empty ($start_time) && !empty ($end_time)) {
            $sql .= " AND a.created_at >= '" . $start_time . " 00:00:00' ";
            $sql .= " AND a.created_at <= '" . $end_time . " 23:59:59' ";
        }

        $result ['list'] = $pdo->YDGetAll(str_replace("[*]", $fields, $sql));

        $result ['total'] = $pdo->YDGetOne(str_replace("[*]", "count(*) as num", $sql));
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /* 获取列表 */
    public static function getBrandList($attribute = array())
    {
        if (!empty ($attribute) && is_array($attribute) && count($attribute) > 0) {
            extract($attribute);
        }

        $fields = " a.* ";
        $sql = 'SELECT 
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        WHERE
        		    a.is_del = 2
        		';

        if (isset ($is_hit) && !empty ($is_hit)) {
            $sql .= " AND a.is_hit = '" . $is_hit . "'";
        }

        $sql .= " ORDER BY first_letter ASC";

        $mem = YDLib::getMem('memcache');
        $data = $mem->get('brand_is_hit_' . $is_hit);
        if (!$data) {

            $pdo = YDLib::getPDO('db_r');
            $data = $pdo->YDGetAll(str_replace("[*]", $fields, $sql));

            $mem->delete('brand_is_hit_' . $is_hit);
            $mem->set('brand_is_hit_' . $is_hit, $data);
        }

        return $data;
    }

    /**
     * 添加
     *
     * @param array $info
     * @return mixed
     *
     */
    public static function add($info)
    {
        $db = YDLib::getPDO('db_w');
        $pinyin = new Pinyin ();
        $firstChar = $pinyin->getFirstChar($info ['name']);
        $info ['first_letter'] = $firstChar;
        $info ['is_del'] = '2';
        $result = $db->insert(self::$_tableName, $info, [
            'ignore' => true
        ]);

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
        $where ['is_del'] = self::DELETE_SUCCESS;
        $where ['id'] = intval($id);

        $pdo = self::_pdo('db_r');
        return $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();
    }

    /**
     * 根据一条自增ID更新表记录
     *
     * @param array $data
     *            更新字段作为key的数组
     * @param array $id
     *            表自增id
     * @return boolean 更新结果
     */
    public static function updateByID($data, $id)
    {
        $pinyin = new Pinyin ();
        $firstChar = $pinyin->getFirstChar($data ['name']);
        $data ['first_letter'] = $firstChar;
        $data ['updated_at'] = date("Y-m-d H:i:s");

        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array(
            'id' => intval($id)
        ));
    }

    /**
     * 根据表自增 ID删除记录
     *
     * @param int $id
     *            表自增 ID
     * @return boolean 删除是否成功
     */
    public static function deleteByID($id)
    {
        $data ['is_del'] = self::DELETE_FAIL;
        $data ['updated_at'] = date("Y-m-d H:i:s");
        $data ['deleted_at'] = date("Y-m-d H:i:s");

        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array(
            'id' => intval($id)
        ));
    }

    public static function getProductNum($brand_id)
    {
        $where ['is_del'] = self::DELETE_SUCCESS;
        $where ['on_status'] = 2;
        $where ['supplier_id'] = SUPPLIER_ID;
        $where ['brand_id'] = intval($brand_id);

        $pdo = self::_pdo('db_r');
        $return = $pdo->clear()->select(' count(*) as num ')->from(ProductModel::table())->where($where)->getRow();
        return $return['num'];
    }

    /**
     * 转载商品数
     * @param $brand_id
     * @return mixed
     */
    public static function getChannelProductNum($brand_id)
    {
        $prefix = self::$_tablePrefix;
        $sql = "SELECT COUNT(*) AS num FROM {$prefix}product_channel a 
                LEFT JOIN {$prefix}product b ON a.product_id = b.id 
                WHERE a.is_del = " . self::DELETE_SUCCESS . "
                AND a.on_status = 2 
                AND b.channel_status = 3  
                AND a.supplier_id = " . SUPPLIER_ID . " 
                AND b.brand_id = '{$brand_id}'";
		
		$return = self::newRead ()->YDGetRow ( $sql );
		return ( int ) $return ['num'];
	}
	
	/**
	 * 根据id更新常用数量
	 *
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateCommon($data, $id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
	
	/**
	 * 获取全部常用品牌
	 */
	public static function getCommonList() {
		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = " a.id,a.name,a.en_name,a.alias_name,a.first_letter,a.logo_url ";
		$sql = 'SELECT
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND 
		            a.common > 0
        		';
		$sql .= " ORDER BY a.common DESC";
		$data = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		return $data;
	}
	
	/**
	 *
	 * 获取搜索品牌内容
	 *
	 * @param array $attribute        	
	 */
	public static function getSearcList($val) {
		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = "a.id,a.name,a.en_name,a.alias_name,a.first_letter,a.logo_url";
		$sql = 'SELECT
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
        		';
		
		if (isset ( $val ) && ! empty ( $val )) {
		$sql .= " AND a.name like'%".$val."%' ";
		}
		$sql .= " ORDER BY first_letter ASC";
		$data = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		return $data;
	}
}