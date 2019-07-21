<?php
/**
 * 设备model
 * @version v0.01
 * @author lqt
 * @time 2019-07-03
 */

namespace Machine;

use Assemble\Builder;
use Assemble\Support\Arr;
use Assemble\Support\Date;

class MachineModel extends \BaseModel
{
    protected static $_tableName = 'machine';

    /**
     * 查询需要显示的列
     *
     * @var array
     */
    public static $showColumns = [
        'id',
        'parent_id',
        'name',
        'self_code',
        'custom_code',
        'introduction',
        'logo_url',
        'note',
        'created_at'
    ];

    public static function getList(array $search = [])
    {
        $auth = self::auth();
        $builder = new Builder ();
        $fileds = self::$showColumns;
        $builder->select($fileds);
        $form = self::getFullTable();
        $builder->from($form);
        $builder->where('is_del', self::DELETE_SUCCESS);
        $builder->where('supplier_id', $auth['supplier_id']);
        $name = Arr::value($search, 'name');
        $name and $builder->where('name', 'like', "%$name%");
        $self_code = Arr::value($search, 'self_code');
        $self_code and $builder->where('self_code', $self_code);
        $custom_code = Arr::value($search, 'custom_code');
        $custom_code and $builder->where('custom_code', $custom_code);
        $start_time = Arr::value($search, 'start_time');
        $start_time and $builder->where('created_at', '>=', Date::startOfDay($start_time));
        $end_time = Arr::value($search, 'end_time');
        $end_time and $builder->where('created_at', '<=', Date::startOfDay($end_time));
        $list = static::paginate($builder);
        if (!empty($list)) {
            foreach ($list as $key => $value) {
                $list[$key]['parent_name'] = '顶级设备';
                if ($value['parent_id'] != 0) {
                    $parent = self::findOneWhere(['id'=>$value['parent_id'] ]);
                    $list[$key]['parent_name'] = $parent['name'];
                }
            }
        }
        return $list;
    }

    /**
     * 条件查询
     */
    public static function findOneWhere($where)
    {
        $pdo = self::_pdo('db_r');
        $detail =  $pdo->clear()->select('*')->from(self::table())->where($where)->getRow();
        if ($detail) {
            return $detail;
        }
        return FALSE;
    }

    /**
     * 条件查询
     */
    public static function findMoreWhere($where)
    {
        $pdo = self::_pdo('db_r');
        $detail =  $pdo->clear()->select('*')->from(self::table())->where($where)->getALl();
        if ($detail) {
            return $detail;
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
        $detail =  $pdo->clear()->select('*')->from(self::table())->where($where)->getRow();
        if ($detail) {
            return $detail;
        }
        return FALSE;
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

    /**
     * 根据表自增 ID删除记录
     * @param int $id 表自增 ID
     * @return boolean 删除是否成功
     */
    public static function deleteByID($id)
    {
        $data['is_del'] = self::DELETE_FAIL;
        $data['updated_at'] = date("Y-m-d H:i:s");
        $data['deleted_at'] = date("Y-m-d H:i:s");

        $pdo = self::_pdo('db_w');
        return $pdo->update(self::$_tableName, $data, array('id' => intval($id)));
    }

    /**
     * 获得设备编号
     * @return integer
     */
    public static function getSelfCode()
    {
        $auth = self::auth();
        $pdo = self::_pdo('db_r');
        $num = $pdo->clear()->select('count(*) as num')->from(self::$_tableName)->where(['supplier_id' => $auth['supplier_id']])->getOne();
        return sprintf("%05d%04d%05d", $auth['supplier_id'], mt_rand(1000, 9999), ($num + 1));
    }

}