<?php
/**
 * 设备维护日志model
 * @version v0.01
 * @author lqt
 * @time 2017-07-03
 */
namespace Machine;

use Assemble\Builder;
use Assemble\Support\Arr;
use Assemble\Support\Date;

class MachineservicelogModel extends \BaseModel
{
    public $table = 'machine_service_log';

    protected static $_tableName = 'machine_service_log';

    /**
     * 查询需要显示的列
     *
     * @var array
     */
    public static $showColumns = [
        'id',
        'machine_id',
        'machine_name',
        'admin_id',
        'admin_name',
        'note',
        'created_at'
    ];

    public static function getList(array $search = [])
    {
        $auth = self::auth();
        $builder = new Builder ();
        $fileds = self::aliasColumn(self::$_tableName);
        $builder->select($fileds);
        // 表和表连接语句
        $form = sprintf(" %s machine_service_log LEFT JOIN %s machine ON machine_service_log.machine_id = machine.id ", self::getFullTable(), MachineModel::getFullTable());
        $builder->from($form);
        $builder->where('machine_service_log.is_del', self::DELETE_SUCCESS);
        $builder->where('machine_service_log.supplier_id', $auth['supplier_id']);
        $machine_id = Arr::value($search, 'machine_id');
        $machine_id and $builder->where('machine_service_log.machine_id', $machine_id);
        $machine_name = Arr::value($search, 'machine_name');
        $machine_name and $builder->where('machine_service_log.machine_name', 'like', "%$machine_name%");
        $admin_id = Arr::value($search, 'admin_id');
        $admin_id and $builder->where('machine_service_log.admin_id', $admin_id);
        $admin_name = Arr::value($search, 'admin_name');
        $admin_name and $builder->where('machine_service_log.admin_name', 'like', "%$admin_name%");
        $self_code = Arr::value($search, 'self_code');
        $self_code and $builder->where('machine.self_code', $self_code);
        $custom_code = Arr::value($search, 'custom_code');
        $custom_code and $builder->where('machine.custom_code', $custom_code);
        $start_time = Arr::value($search, 'start_time');
        $start_time and $builder->where('v.created_at', '>=', Date::startOfDay($start_time));
        $end_time = Arr::value($search, 'end_time');
        $end_time and $builder->where('machine_service_log.created_at', '<=', Date::startOfDay($end_time));
        $builder->orderAlias('machine_service_log');
        return static::paginate($builder);
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
}