<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/14 Time:21:25
// +----------------------------------------------------------------------


namespace Assemble;


use http\Exception\InvalidArgumentException;

class Builder
{
    /**
     * 选择的字段列
     * @var string
     */
    protected $columns;
    /**
     * 连接表、及关联的表
     *
     * @var string
     */
    protected $from;
    /**
     * 连接字符串查询
     *
     * @var string
     */
    protected $whereRaw;
    /**
     * 连接分组语句
     *
     * @var string
     */
    protected $group;
    /**
     * 连接分组条件
     *
     * @var string
     */
    protected $having;

    /**
     * 连接排序
     *
     * @var string
     */
    protected $order;
    /**
     * 排序字段表别名
     *
     * @var string
     */
    protected $orderAlias;

    /**
     * 偏移量
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * 连接查询列数
     *
     * @var int
     */
    protected $limit = 0;

    /**
     * sql语句
     *
     * @var string
     */
    protected $querySql;

    /**
     * 统计count sql语句
     *
     * @var string
     */
    protected $countSql;

    /**
     * 连接的sql属性
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * 连接数组查询
     *
     * @var string
     */
    protected $wheres = [];


    /**
     * All of the available clause operators.
     *
     * @var array
     */
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
        'is null', 'is not null','in',
    ];


    public function select(array $column = [])
    {
        $this->columns = implode(',', array_unique($column));
        $this->attributes ['columns'] = $this->columns;
        return $this;
    }

    public function from(string $from = null)
    {
        $this->from = $from;
        $this->attributes ['from'] = $this->from;
        return $this;
    }

    public function whereRaw(string $whereRaw = null)
    {
        $this->whereRaw = $whereRaw;
        $this->attributes ['whereRaw'] = $this->whereRaw;
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        list($value, $operator) = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() == 2
        );

        return $this->where($column, $operator, $value, 'or');
    }

    public function where(string $column, $operator = null, $value = null, $boolean = 'and')
    {
        list($value, $operator) = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() == 2
        );

        if ($this->invalidOperator($operator)) {
            list($value, $operator) = [$operator, '='];
        }

        if (is_null($value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        $this->wheres[] = compact(
            'column', 'operator', 'value', 'boolean'
        );

        return $this;
    }

    protected function invalidOperator($operator)
    {
        return !in_array(strtolower($operator), $this->operators, true);
    }

    public function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
        	if(in_array($operator, $this->operators))return [$operator, $operator];
            return [$operator, '='];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }
        return [$value, $operator];
    }

    protected function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators) &&
            !in_array($operator, $this->operators);
    }

    public function groupBy(string $group = null)
    {
        $this->group = $group;
        $this->attributes ['group'] = $group;
        return $this;
    }

    public function having(string $having = null)
    {
        $this->having = $having;
        $this->attributes ['having'] = $this->having;
        return $this;
    }

    /**
     * 排序
     *
     * @param string|null $order
     * @return $this
     */
    public function orderBy(string $order = null)
    {
        $this->order = $order;
        $this->attributes ['order'] = $this->order;

        return $this;
    }

    /**
     * 多表连表时排序字段增加别名前缀，单表不需要此操作
     *
     * @param string|null $alias
     * @return $this
     */
    public function orderAlias(string $alias = null)
    {
        $this->orderAlias = $alias;
        $this->attributes ['orderAlias'] = $this->orderAlias;

        return $this;
    }

    public function offset(int $offset = 0)
    {
        $this->offset = $offset;
        $this->attributes ['offset'] = $this->offset;
        return $this;
    }

    public function limit(int $limit = 10)
    {
        $this->limit = $limit;
        $this->attributes ['limit'] = $this->limit;
        return $this;
    }

    public function paginate(int $limit = 0)
    {
        if ($limit){
            $this->limit = $limit;
            $this->attributes ['limit'] = $this->limit;
        }

        try {
            $this->querySql = $this->build($this->query());
            $this->countSql = $this->build($this->count());

            $this->attributes ['sql'] = [
                'query' => $this->querySql,
                'count' => $this->countSql,
            ];

        } catch (\Exception $exception) {
            var_dump($exception);
        }

        return $this;
    }

    public function showSql(){
        return $this->paginate()->sql;
    }

    public function toArray()
    {
        return $this->attributes;
    }

    protected function query()
    {
        $sql = '';
        if (empty($this->columns)) {
            $sql .= " SELECT * ";
        } else {
            $sql .= " SELECT {$this->columns} ";
        }
        return $sql;
    }


    protected function count()
    {
        $sql = " SELECT count(*) as aggregate ";

        if (empty($this->from)) {
            throw new \Exception(' Wow !! method of form() invalid ');
        }
        return $sql;
    }

    /**
     * 构建sql语句
     *
     * @param $sql
     * @return string
     */
    protected function build($sql)
    {
        $sql .= " FROM {$this->from} ";

        /**
         * 存在查询字符串且存在查询数组
         */
        if ($this->whereRaw && !empty($this->wheres)) {
            $sql .= " WHERE 1=1  {$this->whereRaw} " . $this->compileWheres();
        }
        /**
         *  存在查询字符串且查询数组为空
         */
        elseif ($this->whereRaw && empty($this->wheres)) {
            $sql .= " WHERE 1=1  {$this->whereRaw} ";
        }

        /**
         *  存在查询数组且不存在查询字符串
         */
        elseif (!$this->whereRaw && !empty($this->wheres)) {
            $sql .= " WHERE 1=1 " . $this->compileWheres();
        }

        if (!is_null($this->group)) {
            $sql .= " GROUP BY  {$this->group} ";
        }

        if (!is_null($this->having)) {
            $sql .= " HAVING  {$this->having} ";
        }

        if (!is_null($this->order)) {
            $alias = '';
            if ($this->orderAlias){
                $alias = "{$this->orderAlias}.";
            }
            $sql .= " ORDER BY {$alias}{$this->order} ";
        }

        if (false === strpos($sql,'as aggregate')){
            if (abs(intval($this->offset)) >= 0 && intval($this->limit)) {
                $sql .= " LIMIT  {$this->offset},{$this->limit} ";
            }
        }

        return $sql;
    }

    /**
     * 构建where条件
     *
     * @return string
     */
    protected function compileWheres()
    {
        $whereRaw = '';

        if (is_null($this->wheres) || empty($this->wheres)) {
            return $whereRaw;
        }
        foreach ($this->wheres as $where) {
            $column = $where['column'];
            $operator = $where['operator'];
            $value = $where['value'];
            $boolean = $where['boolean'];

            switch (strtolower($operator)) {
                case "between":
                    if (is_array($value) && count($value) == 2) {
                        $whereRaw .= " {$boolean} {$column}  BETWEEN '{$value[0]}' AND '{$value[1]}' ";
                    } else {
                        throw  new InvalidArgumentException(' value 必须是有两个元素的数组 ');
                    }
                    break;
                case "not between":
                    if (is_array($value) && count($value) == 2) {
                        $whereRaw .= " {$boolean} {$column} NOT BETWEEN '{$value[0]}' AND '{$value[1]}' ";
                    } else {
                        throw  new InvalidArgumentException(' value 必须是有两个元素的数组 ');
                    }
                    break;
                case "in":
                    if (is_array($value) && !empty($value)) {
                        $whereRaw .= " {$boolean} {$column} IN (" . implode(', ', $value) . ") ";
                    } else {
                        throw  new InvalidArgumentException(' value 必须是数组 ');
                    }
                    break;
                case "not in":
                    if (is_array($value) && !empty($value)) {
                        $whereRaw .= " {$boolean} {$column} NOT IN (" . implode(', ', $value) . ") ";
                    } else {
                        throw  new InvalidArgumentException(' value 必须是数组 ');
                    }
                    break;
                case "like":
                    $whereRaw .= " {$boolean} {$column} LIKE '%{$value}%' ";
                    break;
                case "not like":
                    $whereRaw .= " {$boolean} {$column} NOT LIKE '%{$value}%' ";
                    break;
                case "is null":
                    $whereRaw .= " {$boolean} {$column} IS NULL ";
                    break;
                case "is not null":
                    $whereRaw .= " {$boolean} {$column} IS NOT NULL ";
                    break;
                case "=":
                case "!=":
                case "<>":
                case ">":
                case ">=":
                case "<":
                case "<=":
                    $whereRaw .= " {$boolean} {$column} {$operator} '{$value}' ";
                    break;
                default:
                    throw  new InvalidArgumentException(' operator 不是有效的操作符 ');
                    break;
            }
        }
        return $whereRaw;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        if (method_exists(self::class, $name)) {
            return null;
        }
    }

}