<?php

/**
 * PDO数据库连接
 *
 * @package lib
 * @subpackage plugins.pdo
 * @author 苏宁 <snsnky@126.com>
 *
 * $Id: PDOQuery.class.php 2 2014-02-19 09:52:07Z suning $
 */

namespace Core;

use \PDO;
use \PDOStatement;
use \PDOException;
use Custom\YDLib;
use Common\CommonBase;

final class PDOQuery extends PDO
{
    /**
     * DSN 连接信息
     *
     * @var string
     */
    private $_dsn;

    /**
     * 数据库 用户名
     *
     * @var string
     */
    private $_user;

    /**
     * 数据库 密码
     *
     * @var string
     */
    private $_pwd;

    /**
     * PDO 集
     *
     * @var PDOStatement
     */
    private $_statement = null;

    /**
     * 默认选项
     * @var array
     */
    private $_opt = array(
        // 字符集
        'charset' => 'utf8',
        // 长连接
        'persistent' => false,
        // 结果集方式
        'fetchMode' => parent::FETCH_ASSOC,
        // 异常方式
        'errorMode' => parent::ERRMODE_WARNING
    );

    private $_chained = array(
        'fields' => '*',
        'table' => '',
        'group' => null,
        'order' => null,
        'limit' => null,
        'where' => '',
        'index' => null,
        'bind' => array()
    );

    /**
     * 构造函数
     *
     * @param array $params 配置信息
     * @param array $options 选项, @see self::$_opt
     */
    public function __construct($params, $options)
    {
        if (is_array($options)) {
            $this->_opt = array_merge($this->_opt, $options);
        }

        $params['port'] = empty($params['port']) ? 3306 : $params['port'];

        $this->_dsn = $params['driver'] . ':dbname=' . $params['name'] . ';host=' . $params['host'] . ';port=' . $params['port'] . ';charset=' . $this->_opt['charset'];

        $this->_user = $params['user'];
        $this->_pwd = $params['password'];

        $opt = array();
        $opt[parent::ATTR_PERSISTENT] = $this->_opt['persistent'];
        //$opt[parent::ATTR_EMULATE_PREPARES] = false;
        //$opt[parent::ATTR_STRINGIFY_FETCHES] = false;
        if (\Yaf_Application::app()->getConfig()->PDOParam) $opt[parent::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
        //$opt[parent::ATTR_ORACLE_NULLS] = parent::NULL_TO_STRING;
        $opt[parent::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $this->_opt['charset'];

        parent::__construct($this->_dsn, $this->_user, $this->_pwd, $opt);
        $this->setAttribute(\PDO::ATTR_ERRMODE, $this->_opt['errorMode']);
        //$this->setAttribute(parent::ATTR_STRINGIFY_FETCHES, false);
        //$this->setAttribute(parent::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * 关闭连接并销毁此对象
     */
    public function close()
    {
        $this->_statement = null;
    }

    /**
     *
     * @see PDO::prepare()
     *
     * @param string $statement
     * @param array $driver_options
     * @return PDOStatement
     */
    public function prepare($statement, $driver_options = array())
    {
        $this->_statement = parent::prepare($statement, $driver_options);
        $this->_statement->setFetchMode($this->_opt['fetchMode']);
        return $this->_statement;
    }


    /**
     * 事物
     * @var unknown
     */
    protected $transactionCounter = 0;

    function beginTransaction()
    {
        if (!$this->transactionCounter++)
            return parent::beginTransaction();
        return $this->transactionCounter >= 0;

    }

    function commit()
    {
        if (!--$this->transactionCounter)
            return parent::commit();
        return $this->transactionCounter >= 0;
    }

    function rollback()
    {
        if ($this->transactionCounter >= 0) {
            $this->transactionCounter = 0;
            return parent::rollback();
        }
        $this->transactionCounter = 0;
        return false;
    }


    /**
     * @see PDO::query()
     *
     * @param string $statement
     * @return PDOStatement
     */
    public function query($statement)
    {
        $this->_statement = parent::query($statement);
        $this->_statement->setFetchMode($this->_opt['fetchMode']);
        return $this->_statement;
    }

    /**
     * SELECT 语句的快捷方式
     *
     * @param string $tableName 表名
     * @param array $where WHERE 条件数组，仅支持 AND 连接
     * @param string $fields 要查询的字段，半角逗号分隔，如：field1, field2
     * @param null $order 排序方法，如：someField DESC
     * @param null $limit 限制条数，可以是单个数字或者 array($offset, $num) 格式的数组
     * @param null $group
     * @param int $style
     *
     * @return mixed
     */
    public function find($tableName, $where = array(), $fields = '*', $order = null, $limit = null, $group = null, $style = PDO::FETCH_ASSOC)
    {
        $tableName = str_replace(array('`', '.'), array('', '`.`'), CommonBase::$_tablePrefix . $tableName);
        $bindVals = array();
        if (is_string($where) && !empty($where))
            $_where = 'WHERE ' . $where;
        elseif (is_array($where) && !empty($where))
            $_where = 'WHERE ' . implode(' AND ', $this->_parseWhere($where, $bindVals));
        else
            $_where = '';
        $_group = is_null($group) ? '' : 'GROUP BY ' . $group;
        $_order = is_null($order) ? '' : 'ORDER BY ' . $order;
        if (is_numeric($limit))
            $_limit = 'LIMIT ' . intval($limit);
        elseif (is_array($limit) && count($limit) == 2)
            $_limit = sprintf('LIMIT %s, %s', $limit[0], $limit[1]);
        else
            $_limit = '';

        $sql = sprintf("SELECT %s FROM `%s` %s %s %s %s", $fields, $tableName, $_where, $_group, $_order, $_limit);
        $query = $this->prepare($sql);
        $this->dumpSQL($sql, $bindVals);
        if (!($query instanceof PDOStatement)) return false;
        $res = $query->execute($bindVals);
        if ($res !== true) return false;

        $data = $query->fetchAll($style);
        $this->clear();
        return $data;
    }

    /**
     * 链式查询 - 选择字段
     * @param string $fields 字段列表
     * @return $this
     */
    public function select($fields)
    {
        if (!is_array($fields))
            $fields = explode(',', $fields);
        foreach ($fields as &$field) {
            $field = trim($field);
            if (preg_match('/^[a-zA-Z0-9_]+$/', $field))
                $field = '`' . $field . '`';
        }
        unset($field);
        $this->_chained['fields'] = implode(',', $fields);
        return $this;
    }

    /**
     * 链式查询 - 选择表名
     * @param string $tableName 表名
     * @return $this
     */
    public function from($tableName)
    {
        $this->_chained['table'] = str_replace(
            array('`', '.'),
            array('', '`.`'),
            CommonBase::$_tablePrefix . $tableName
        );
        return $this;
    }

    /**
     * 链式查询 - 指定查询条件
     * @param array $where 查询条件
     * @return $this
     */
    public function where($where)
    {
        if (is_string($where))
            $info = $this->_chained['where'] = 'WHERE ' . $where;
        elseif (is_array($where) && !empty($where))
            $info = $this->_chained['where'] = 'WHERE ' . implode(' AND ', $this->_parseWhere($where, $this->_chained['bind']));
        else
            $info = $this->_chained['where'] = '';

        return $this;
    }

    /**
     * 链式查询 - 指定排序方法
     * @param string $order 排序方法
     * @return $this
     */
    public function group($group)
    {
        $this->_chained['group'] = $group;
        return $this;
    }


    /**
     * 链式查询 - 指定排序方法
     * @param string $order 排序方法
     * @return $this
     */
    public function order($order)
    {
        $this->_chained['order'] = $order;
        return $this;
    }

    /**
     * 链式查询 - 指定 LIMIT 参数
     * @param mixed $offset LIMIT 第一个参数，也可以写成 array($offset, $num) 以省略第二个参数
     * @param null $num LIMIT 第二个参数
     * @return $this
     */
    public function limit($offset, $num = null)
    {
        $limit = is_null($num) ? $offset : array($offset, $num);
        if (is_numeric($limit))
            $this->_chained['limit'] = intval($limit);
        elseif (is_array($limit) && count($limit) == 2)
            $this->_chained['limit'] = implode(',', $limit);

        return $this;
    }

    /**
     * 链式查询 - 指定索字段
     * @param mixed $index 字段名，或者字段序号
     * @return $this
     */
    public function indexBy($index)
    {
        $this->_chained['index'] = $index;
        return $this;
    }

    /**
     * 链式查询 - 获取结果集
     * @param int $style 记录格式，PDO::FETCH_*
     * @param null $arg 与 $style 对应的参数，目前只有 PDO::FETCH_COLUMN 需要
     * @return array|bool
     */
    public function getAll($style = PDO::FETCH_ASSOC, $arg = null)
    {
        $opt = $this->_chained;
        $sql = sprintf("SELECT %s FROM `%s` %s %s %s %s",
            $opt['fields'],
            $opt['table'],
            $opt['where'],
            is_null($opt['group']) ? '' : 'GROUP BY ' . $opt['group'],
            is_null($opt['order']) ? '' : 'ORDER BY ' . $opt['order'],
            is_null($opt['limit']) ? '' : 'LIMIT ' . $opt['limit']
        );

        $query = $this->prepare($sql);
        $this->dumpSQL($sql, $opt['bind']);

        if (!($query instanceof PDOStatement)) return false;
        $res = $query->execute($opt['bind']);
        if ($res !== true) return false;

        $index = $opt['index'];
        $data = ($style == PDO::FETCH_COLUMN)
            ? $query->fetchAll($style, $arg)
            : $query->fetchAll($style);

        $this->clear();

        if (empty($data))
            return array();

        if (is_null($index))
            return $data;
        else {
            $ret = array();
            if (is_int($index)) {
                $fields = ($opt['fields'] == '*')
                    ? array_keys($data[0])
                    : explode(',', $opt['fields']);
                $field = $fields[$index];
            } else
                $field = $index;

            foreach ($data as $row)
                $ret[$row[$field]] = $row;

            return $ret;
        }
    }

    /**
     * 链式查询 - 获取第一条数据
     * @param int $style 记录格式，PDO::FETCH_*
     * @return null
     */
    public function getRow($style = PDO::FETCH_ASSOC)
    {
        $res = $this->limit('1')->indexBy(null)->getAll($style);
        return (is_array($res) && !empty($res)) ? $res[0] : null;
    }

    /**
     * 链式查询 - 获取第一条数据的第一个字段值
     * @return null
     */
    public function getOne()
    {
        $res = $this->getRow(PDO::FETCH_NUM);
        return is_array($res) ? $res[0] : null;
    }

    /**
     * 链式查询 - 获取某列数据
     * @param int $col
     * @return array|bool
     */
    public function getColumn($col = 0)
    {
        $index = $this->_chained['index'];
        if (is_null($index) && is_numeric($col))
            return $this->getAll(PDO::FETCH_COLUMN, intval($col));

        $ret = array();
        $data = $this->getAll(PDO::FETCH_BOTH);
        if (is_null($index)) {
            foreach ($data as $row)
                $ret[] = $row[$col];
        } else {
            foreach ($data as $row)
                $ret[$row[$index]] = $row[$col];
        }

        return $ret;
    }

    /**
     * 链式查询 - 重置查询参数
     */
    public function clear()
    {
        $this->_chained = array(
            'fields' => '*',
            'table' => '',
            'group' => null,
            'order' => null,
            'limit' => null,
            'where' => '',
            'index' => null,
            'bind' => array()
        );
        return $this;
    }

    /**
     * INSERT 语句的快捷方式
     * @param string $tableName 表名
     * @param array $row 要写入的数组
     * @param array $options (可选) INSERT 选项，目前只支持 ignore
     * @return bool|int 成功则返回插入的主键ID，失败则返回 false
     */
    public function insert($tableName, $row, $options = array())
    {
        // 表名
        $tableName = str_replace(array('`', '.'), array('', '`.`'), CommonBase::$_tablePrefix . $tableName);
        // 带`的字段名称列表
        $fields = array();
        // INSERT 选项
        $option = '';
        if (isset($options['ignore']) && $options['ignore'] == true) {
            $option = 'IGNORE';
        }
        // 绑定数据
        $bindVals = array();
        $bindKeys = array();
        foreach ($row as $key => $val) {
            $bindKeys[] = ":$key";
            $fields[] = "`{$key}`";
            $bindVals[":$key"] = $val;
        }

        $sql = sprintf("INSERT %s INTO  `%s` (%s) VALUES (%s)", $option, $tableName, implode(', ', $fields), implode(', ', $bindKeys));
        $query = $this->prepare($sql);
        $this->dumpSQL($sql, $bindVals);
        if (!($query instanceof PDOStatement)) return false;
        $res = $query->execute($bindVals);
        if ($res !== true) return false;

        $last_id = $this->YDParseSql('SELECT LAST_INSERT_ID() last_id', [])->fetchAll(PDO::FETCH_ASSOC);
        return $last_id[0]['last_id'];
    }

    /**
     * 唯一键不存在则插入, 存在则在原有数据基础上做更新操作
     *
     * @param string $tableName 表名
     * @param array $rows 要写入的数组(支持一次性接入多行)
     * @param array $updates 做更新操作时要更新的字段名列表
     * @return bool|int                 成功则返回影响的行数，失败则返回 false
     */
    public function upsert($tableName, $rows, $updates)
    {
        $tableName = str_replace(['`', '.'], ['', '`.`'], CommonBase::$_tablePrefix . $tableName);
        $updateKeys = $fieldKeys = $bindVals = $inserts = [];

        $row = current($rows);
        if (!is_array($row)) {
            $row = $rows;
            $rows = [$row];
        }

        $fields = array_keys($row);
        foreach ($fields as $key) {
            $fieldKeys[$key] = "`{$key}`";
        }

        $count = 0;

        foreach ($rows as $row) {
            ++$count;
            $bindKeys = [];

            foreach ($fields as $key) {
                $bindKey = ":{$key}{$count}";
                $bindKeys[$key] = $bindKey;
                $bindVals[$bindKey] = $row[$key];
            }

            $inserts[] = '(' . implode(',', $bindKeys) . ')';
        }

        foreach ($updates as $key) {
            if (isset($row[$key])) {
                $updateKeys[$key] = "`{$key}` = VALUES(`{$key}`)";
            }
        }

        if (empty($updateKeys)) {
            $update_clause = '';
            $ignore = 'IGNORE';
        } else {
            $update_clause = "ON DUPLICATE KEY UPDATE " . implode(', ', $updateKeys);
            $ignore = '';
        }

        $sql = sprintf("INSERT %s INTO `%s` (%s) VALUES %s {$update_clause}", $ignore, $tableName, implode(', ', $fieldKeys), implode(', ', $inserts));
        $query = $this->prepare($sql);
        $this->dumpSQL($sql, $bindVals);
        if (!($query instanceof PDOStatement)) {
            return false;
        }

        $res = $query->execute($bindVals);
        if ($res !== true) {
            return false;
        }

        return $query->rowCount();
    }

    /**
     * UPDATE 语句的快捷方式
     *
     * @param string $tableName 表名
     * @param array $row 要更新的字段及
     * @param array $where 一个 WHERE 条件数组，仅支持 AND
     * @return bool|int 成功执行则返回影响的行数，失败则返回 false
     */
    public function update($tableName, $row, $where)
    {
        if (empty($row) || empty($where)) return false;
        // 表名
        $tableName = str_replace(array('`', '.'), array('', '`.`'), CommonBase::$_tablePrefix . $tableName);
        // 字段更新列表
        $fields = array();
        // 绑定数据
        $bindVals = array();
        foreach ($row as $key => $val) {
            if (is_array($val)) {
                $val[1] = (float)$val[1];
                if ($val[1] > 0) {
                    $val[1] = abs($val[1]);
                    $fields[] = "`$key`=`{$val[0]}` + {$val[1]}";
                } else if ($val[1] < 0) {
                    $val[1] = abs($val[1]);
                    $fields[] = "`$key`= IF(`$key` >= $val[1], `$key`-$val[1], 0)";
//                    $fields[] = "`$key`=`{$val[0]}` - GREATEST({$val[1]},0)";
                }
                //$fields[] = "$key= {$val[0]} {$val[1]}";
                continue;
            }
            $fields[] = "`$key`=:$key";
            $bindVals[":$key"] = $val;
        }
        // WHERE 条件
        $whereSQL = $this->_parseWhere($where, $bindVals);


        $sql = sprintf("UPDATE `%s` SET %s WHERE %s", $tableName, implode(', ', $fields), implode(' AND ', $whereSQL));
        $query = $this->prepare($sql);
        $this->dumpSQL($sql, $bindVals);
        if (!($query instanceof PDOStatement)) return false;
        $res = $query->execute($bindVals);
        return $res;
        //if ($res !== true) return false;
        //return $query->rowCount();

    }

    /**
     * DELETE 语句的快捷方式
     * @param string $tableName 表名
     * @param array $where 一个 WHERE 条件数组，仅支持 AND 连接
     * @return bool|int 成功执行则返回影响的行数，失败则返回 false
     */
    public function delete($tableName, $where)
    {
        if (empty($where)) return false;
        // 表名
        $tableName = str_replace(array('`', '.'), array('', '`.`'), CommonBase::$_tablePrefix . $tableName);
        // WHERE 条件
        $bindVals = array();
        $whereSQL = $this->_parseWhere($where, $bindVals);

        $sql = sprintf("DELETE FROM `%s` WHERE %s", $tableName, implode(' AND ', $whereSQL));
        $query = $this->prepare($sql);
        $this->dumpSQL($sql, $bindVals);
        if (!($query instanceof PDOStatement)) return false;
        $res = $query->execute($bindVals);
        if ($res !== true) return false;

        return $query->rowCount();
    }

    public function YDExecute($sql, $data = [])
    {
        $sql = trim($sql);
        return $this->YDParseSql($sql, $data);
    }

    public function YDGetOne($sql, $data = [])
    {
        $sql = trim($sql);
        if (stripos($sql, 'limit 1') === false) $sql = $sql . ' LIMIT 1';
        $res = $this->YDParseSql($sql, $data)->fetchAll(PDO::FETCH_COLUMN, 0);
        return is_array($res) ? $res[0] : null;
    }

    public function YDGetRow($sql, $data = [])
    {
        $sql = trim($sql);
        if (stripos($sql, 'limit 1') === false) $sql = $sql . ' LIMIT 1';
        $res = $this->YDParseSql($sql, $data)->fetchAll(PDO::FETCH_ASSOC);
        return $res ? $res[0] : [];
    }

    public function YDGetColumn($sql, $data = [], $arg = 0)
    {
        $sql = trim($sql);
        return $this->YDParseSql($sql, $data)->fetchAll(PDO::FETCH_COLUMN, intval($arg));
    }

    public function YDGetAll($sql, $data = [])
    {
        $sql = trim($sql);
        return $this->YDParseSql($sql, $data)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function YDParseSql($sql, $data = [])
    {
        $command = parent::prepare($sql, ['fetchMode' => parent::FETCH_ASSOC]);
        if (!($command instanceof PDOStatement)) return false;

        /*  
            $pdo = YDLib::getPDO('db_passport_r');
            $pdo->prepare('SELECT UserId FROM UserIndex WHERE Mobile=:mobile LIMIT 1');
            $pdo->bindValue(':mobile', $mobile);
            $pdo->execute();
            $userid = $pdo->fetchColumn();
            if($data !== '' && count($data) > 0) foreach ($data as $key=>&$value) if($value !== '') $command->bindParam(":$key",$value,PDO::PARAM_STR);
        */
        $command->execute($data);

        $this->dumpSQL($sql,$data);

        return $command;
    }


    public function YDGetTableFields($tableName)
    {
        if (!$tableName) return false;
        return $this->YDGetColumn('desc ' . CommonBase::$_tablePrefix . $tableName);
    }


    /**
     * 魔术call
     *
     * @param string $name 方法名
     * @param mixed $arguments 参数
     * @throws PDOException
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->_statement, $name)) {
            return call_user_func_array(array($this->_statement, $name), $arguments);
        }

        throw new PDOException("Fatal error: Call to undefined method PDOQuery::{$name}", 100);
    }

    private function _parseWhere($where, &$bindVals)
    {
        // 返回值
        $whereSQL = array();
        // 绑定符号计数器
        $bindCnts = array();
        // 支持的比较符号
        $operators = array('=', '<', '>', '<>', '>=', '<=', 'IN', 'LIKE');

        foreach ($where as $key => $val) {
            if (gettype($key) == "integer") {
                $whereSQL[] = $val;
                continue;
            }

            $tmp = explode(' ', $key);
            if (count($tmp) == 1) $tmp[1] = '=';
            list($field, $operator) = $tmp;
            if (!in_array($operator, $operators)) continue;

            // 特殊处理 IN 查询
            if (strtoupper($operator) == 'IN') {
                if (gettype($val) == 'array') {
                    if (!empty($val)) {
                        $val = array_map(array($this, 'quote'), $val);
                        $whereSQL[] = sprintf("`%s` IN (%s)", $field, implode(",", $val));
                    }
                } elseif (gettype($val) == 'string') {
                    $whereSQL[] = sprintf("`%s` IN (%s)", $field, $val);
                }
                continue;
            }

            // 特殊处理 LIKE 查询
            if (strtoupper($operator) == 'LIKE') {
                if (gettype($val) == 'array') {
                    if (!empty($val)) {
                        $val = array_map(array($this, 'quote'), $val);
                        foreach ($val as $value) {
                            $whereSQL[] = sprintf("`%s` LIKE '%%%s%%'", $field, $value);
                        }
                    }
                } elseif (gettype($val) == 'string') {
                    $whereSQL[] = sprintf("`%s` LIKE '%%%s%%'", $field, $val);
                }
                continue;
            }

            if (isset($bindCnts[$field])) {
                $bindNum = ++$bindCnts[$field];
            } else {
                $bindNum = $bindCnts[$field] = 1;
            }
            $bindKey = sprintf(':%s_%s', $field, $bindNum);
            $whereSQL[] = sprintf("`%s` %s %s", $field, $operator, $bindKey);
            $bindVals[$bindKey] = $val;
        }

        return $whereSQL;
    }

    /**
     * 输出sql
     * @param $sql
     * @param array $bind
     */
    private function dumpSQL($sql, array $bind = [])
    {
        if (!empty($bind)) {
            $sql = str_replace(array_keys($bind), array_values($bind), $sql);
        }
        sqlDump($sql);
    }

}
