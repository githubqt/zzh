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
        return $pdo->update(self::table(), $data, array('id' => intval($id)));
    }
}