<?php
/**
 * 渠道商品列表model
 * @version v0.01
 * @author lqt
 * @time 2018-08-21
 */
namespace Product;

class ProductChannelModel extends \BaseModel
{

    /** 是否上架 */
    const  ON_STATUS_1			= 1;//未上架
    const  ON_STATUS_2			= 2;//已上架

    const  ON_STATUS_VALUE = [self::ON_STATUS_1=>'未上架',self::ON_STATUS_2=>'已上架'];

    /**
     * 条件查询
     */
	public static function findOneWhere($where)
    {
        $pdo = self::_pdo('db_r');
        $channelDetail =  $pdo->clear()->select('*')->from(self::table())->where($where)->getRow();
        if ($channelDetail) {
            return $channelDetail;
        }
        return FALSE;
    }

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
        return $pdo->insert(self::table(), $data);
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
        return $pdo->update(self::table(), $data, array('product_id' => intval($id)));
    }

    /**
     * 字段自更新
     *
     * @param array $data
     *        	更新字段作为key的数组
     * @param array $id
     *        	表自增id
     * @return boolean 更新结果
     */
    public static function autoUpdateByID($data, $id) {
        $sql = "UPDATE " . self::getFullTable() . " SET ";
        foreach ( $data as $key => $val ) {
            if ($val > 0)
                $val = '+' . $val;
            $sql .= "`{$key}` = (`{$key}` {$val}),";
        }
        $sql = substr ( $sql, 0, - 1 );

        $sql .= " WHERE id = " . $id;

        $pdo = self::_pdo ( 'db_w' );

        return $pdo->YDExecute ( $sql );
    }
    
    
    
    
    /**
     * 获取单条数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getInfoByID($id) {
    
    	$where ['is_del'] = self::DELETE_SUCCESS;
    	$where ['supplier_id'] = SUPPLIER_ID;
    	//$where ['on_status'] = self::ON_STATUS_2;
    	$where ['product_id'] = intval ( $id );
    	$pdo = self::_pdo ( 'db_r' );
    	$info = $pdo->clear ()->select ('*')->from (self::table() )->where ( $where )->getRow ();
    	return $info;
    }
    
    
    
    /**
     * 获取已上架单条数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getSingleStatus($id) {
    
    	$where ['is_del'] = self::DELETE_SUCCESS;
    	$where ['supplier_id'] = SUPPLIER_ID;
    	$where ['on_status'] = self::ON_STATUS_2;
    	$where ['product_id'] = intval ( $id );
    	$pdo = self::_pdo ( 'db_r' );
    	$info = $pdo->clear ()->select ('*')->from (self::table() )->where ( $where )->getRow ();
    	return $info;
    }
    
    
    
    
}