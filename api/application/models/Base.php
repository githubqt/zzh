<?php
// +----------------------------------------------------------------------
// | 基模型
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/14 Time:11:39
// +----------------------------------------------------------------------


use Custom\YDLib;
use \Assemble\Support\Str;
use \Assemble\Support\Arrayable;
use \Assemble\Support\Arr;
use \Assemble\Builder;

abstract class BaseModel extends \Common\CommonBase implements Arrayable, ArrayAccess
{
    use \Assemble\Support\Traits\Model\HasColumns;
    use \Assemble\Support\Traits\Model\HasValidation;
    /**
     * 关联模型的表名
     * @var string
     */
    protected $table;

    /**
     * 模型主键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 每页显示数
     *
     * @var int
     */
    protected $perPage = 15;


    /**
     * created_at
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * updated at
     *
     * @var string
     */
    const UPDATED_AT = 'updated_at';

    /**
     * deleted at
     *
     * @var string
     */
    const DELETED_AT = 'deleted_at';

    /**
     * is del
     *
     * @var string
     */
    const IS_DEL = 'is_del';


    /**
     * 模型是否存在
     * @var bool
     */
    public $exists = false;

    /**
     * 模型属性
     * @var array
     */
    protected $attributes = [];

    /**
     * 原属性
     * @var array
     */
    protected $original = [];

    /**
     * 改变的属性.
     * @var array
     */
    protected $changes = [];

    /**
     * 追加属性
     * @var array
     */
    protected $appends = [];

    /**
     * 蛇形属性
     * @var bool
     */
    public static $snakeAttributes = true;

    /**
     * 是否更新时间
     *
     * @var bool
     */
    public $timestamps = true;
    /**
     * 缓存可变属性
     * @var array
     */
    protected static $mutatorCache = [];

    /**
     * 查询显示的列
     *
     * @var array
     */
    public static $showColumns = ['*'];

    /**
     * 数据库读操作
     * @return \Core\PDOQuery
     */
    protected static function newRead()
    {
        return YDLib::getPDO('db_r');
    }

    /**
     * 数据库写操作
     * @return \Core\PDOQuery
     */
    protected static function newWrite()
    {
        return YDLib::getPDO('db_w');
    }

    /**
     * 获取模型中对象属性。
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (!$key) {
            return null;
        }

        if (array_key_exists($key, $this->attributes)) {
            return $this->getAttributeValue($key);
        }

        if (method_exists(self::class, $key)) {
            return null;
        }

    }

    /**
     * 获取普通属性
     * @param  string $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        return $value;
    }

    /**
     * 从 $attributes数组中获取属性
     * @param  string $key
     * @return mixed
     */
    protected function getAttributeFromArray($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
    }

    /**
     * 更新updated_at、created_at时间
     *
     * @return void
     */
    protected function updateTimestamps()
    {
        $time = date('Y-m-d H:i:s');

        if (!is_null(static::UPDATED_AT) && !$this->isDirty(static::UPDATED_AT)) {
            $this->setUpdatedAt($time);
        }

        if (!$this->exists && !$this->isDirty(static::CREATED_AT)) {
            $this->setCreatedAt($time);
        }
    }

    /**
     * 更新deleted_at时间
     *
     * @return void
     */
    protected function updateDeleteState()
    {
        $time = date('Y-m-d H:i:s');

        if (!is_null(static::DELETED_AT) && !$this->isDirty(static::DELETED_AT)) {
            $this->setDeletedAt($time);
            $this->setIsDelAt(self::DELETE_FAIL);
        }

    }

    /**
     * @param  mixed $value
     * @return $this
     */
    public function setCreatedAt($value)
    {
        $this->{static::CREATED_AT} = $value;

        return $this;
    }

    /**
     * @param  mixed $value
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        $this->{static::UPDATED_AT} = $value;

        return $this;
    }

    /**
     * @param  mixed $value
     * @return $this
     */
    public function setDeletedAt($value)
    {
        $this->{static::DELETED_AT} = $value;

        return $this;
    }

    /**
     * @param  mixed $value
     * @return $this
     */
    public function setIsDelAt($value)
    {
        $this->{static::IS_DEL} = $value;

        return $this;
    }

    /**
     * Determine if the model uses timestamps.
     *
     * @return bool
     */
    public function usesTimestamps()
    {
        return $this->timestamps;
    }

    /**
     * 确定属性是否存在。
     *
     * @param  string $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get' . Str::studly($key) . 'Attribute');
    }

    /**
     * 获取属性值
     *
     * @param  string $key
     * @param  mixed $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        return $this->{'get' . Str::studly($key) . 'Attribute'}($value);
    }

    /**
     * Get the value of an attribute using its mutator for array conversion.
     *
     * @param  string $key
     * @param  mixed $value
     * @return mixed
     */
    protected function mutateAttributeForArray($key, $value)
    {
        $value = $this->mutateAttribute($key, $value);

        return $value instanceof Arrayable ? $value->toArray() : $value;
    }


    /**
     * 绑定属性到模型.
     *
     * @param  string $key
     * @param  mixed $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {

        if ($this->hasSetMutator($key)) {
            $method = 'set' . Str::studly($key) . 'Attribute';

            return $this->{$method}($value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * 判断可变属性是否存在
     *
     * @param  string $key
     * @return bool
     */
    public function hasSetMutator($key)
    {
        return method_exists($this, 'set' . Str::studly($key) . 'Attribute');
    }

    /**
     * 获取模型属性
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }


    /**
     * 设置数组属性为模型属性
     *
     * @param  array $attributes
     * @param  bool $sync
     * @return $this
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        $this->attributes = $attributes;

        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }

    /**
     * 获取模型的原始属性
     *
     * @param  string|null $key
     * @param  mixed $default
     * @return mixed|array
     */
    public function getOriginal($key = null, $default = null)
    {
        return Arr::get($this->original, $key, $default);
    }

    /**
     * 同步属性到原始属性
     *
     * @return $this
     */
    public function syncOriginal()
    {
        $this->original = $this->attributes;

        return $this;
    }


    /**
     * 同步单个属性到原始属性
     *
     * @param  string $attribute
     * @return $this
     */
    public function syncOriginalAttribute($attribute)
    {
        $this->original[$attribute] = $this->attributes[$attribute];

        return $this;
    }

    /**
     * 同步改变的属性
     *
     * @return $this
     */
    public function syncChanges()
    {
        $this->changes = $this->getDirty();

        return $this;
    }

    /**
     * 判断模型属性是否更改
     *
     * @param  array|string|null $attributes
     * @return bool
     */
    public function isDirty($attributes = null)
    {
        return $this->hasChanges(
            $this->getDirty(), is_array($attributes) ? $attributes : func_get_args()
        );
    }

    /**
     * 判断模型属性是否重复
     *
     * @param  array|string|null $attributes
     * @return bool
     */
    public function isClean($attributes = null)
    {
        return !$this->isDirty(...func_get_args());
    }


    /**
     * 判断属性是否更改
     *
     * @param  array|string|null $attributes
     * @return bool
     */
    public function wasChanged($attributes = null)
    {
        return $this->hasChanges(
            $this->getChanges(), is_array($attributes) ? $attributes : func_get_args()
        );
    }

    /**
     * 判断属性是否变化
     *
     * @param  array $changes
     * @param  array|string|null $attributes
     * @return bool
     */
    protected function hasChanges($changes, $attributes = null)
    {

        if (empty($attributes)) {
            return count($changes) > 0;
        }

        foreach (Arr::wrap($attributes) as $attribute) {
            if (array_key_exists($attribute, $changes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取最后操作更新的属性
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];

        foreach ($this->getAttributes() as $key => $value) {
            if (!$this->originalIsEquivalent($key, $value)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * 获取变更的属性
     *
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * 判断新旧属性的值是否相等
     *
     * @param  string $key
     * @param  mixed $current
     * @return bool
     */
    protected function originalIsEquivalent($key, $current)
    {
        if (!array_key_exists($key, $this->original)) {
            return false;
        }

        $original = $this->getOriginal($key);

        if ($current === $original) {
            return true;
        } elseif (is_null($current)) {
            return false;
        }

        return is_numeric($current) && is_numeric($original)
            && strcmp((string)$current, (string)$original) === 0;
    }

    /**
     * 追加属性
     *
     * @param  array|string $attributes
     * @return $this
     */
    public function append($attributes)
    {
        $this->appends = array_unique(
            array_merge($this->appends, is_string($attributes) ? func_get_args() : $attributes)
        );

        return $this;
    }

    /**
     * 设置追加属性
     *
     * @param  array $appends
     * @return $this
     */
    public function setAppends(array $appends)
    {
        $this->appends = $appends;

        return $this;
    }

    /**
     * 获取可变属性
     *
     * @return array
     */
    public function getMutatedAttributes()
    {
        $class = static::class;

        if (!isset(static::$mutatorCache[$class])) {
            static::cacheMutatedAttributes($class);
        }

        return static::$mutatorCache[$class];
    }

    /**
     * 缓存可变属性
     *
     * @param  string $class
     * @return void
     */
    public static function cacheMutatedAttributes($class)
    {
        $data = [];
        foreach (static::getMutatorMethods($class) as $k => $match) {
            $data[] = lcfirst(static::$snakeAttributes ? Str::snake($match) : $match);
        }
        static::$mutatorCache[$class] = $data;

    }

    /**
     * 获取所以可变属性的方法
     *
     * @param  mixed $class
     * @return array
     */
    protected static function getMutatorMethods($class)
    {
        preg_match_all('/(?<=^|;)get([^;]+?)Attribute(;|$)/', implode(';', get_class_methods($class)), $matches);

        return $matches[1];
    }

    /**
     * 插入或更新数据
     * @return bool
     */
    public function save()
    {
        $query = self::newWrite();

        try {
            // 数据自动验证
            if (!$this->validateData($this->getDirty())) {
                return false;
            }
        } catch (Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }


        if ($this->exists) {
            $saved = $this->isDirty() ?
                $this->performUpdate($query) : true;
        } else {
            $saved = $this->performInsert($query);
        }

        if ($saved) {
            $this->finishSave();
        }

        return $saved;
    }

    /**
     * 插入数据
     * @param array $options
     * @return BaseModel
     */
    public static function create(array $options = [])
    {
        $model = new static();
        $model->setRawAttributes($options);
        $query = self::newWrite();
        $saved = $model->performInsert($query);

        if ($saved) {
            $model->finishSave();
        }
        return $model;
    }

    /**
     * 同步原属性
     * @return void
     */
    protected function finishSave()
    {
        $this->syncOriginal();
    }


    /**
     * 执行插入操作
     * @param $query
     * @return bool
     */
    protected function performInsert($query)
    {

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
            $this->setIsDelAt(self::DELETE_SUCCESS);
        }

        $attributes = $this->attributes;

        if (empty($attributes)) {
            return true;
        }

        $id = $query->insert($this->getTable(), $attributes);

        $this->setAttribute($this->getKeyName(), $id);

        $this->exists = true;

        return true;
    }


    /**
     * 执行更新操作
     * @param $query
     * @return bool
     */
    protected function performUpdate($query)
    {

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $dirty = $this->getDirty();

        if (count($dirty) > 0) {

            $id = $this->getKeyForSaveQuery();

            $query->update($this->getTable(), $dirty, array($this->getKeyName() => intval($id)));

            $this->syncChanges();
        }

        return true;
    }

    /**
     * 更新模型
     * @param array $attributes
     * @param array $where
     * @return $this
     */
    public static function update(array $attributes = [], array $where = [])
    {
        $model = new static();

        if ($model->usesTimestamps()) {
            $model->updateTimestamps();
        }

        $model->setRawAttributes($attributes);

        $query = self::newWrite();

        $query->update($model->getTable(), $attributes, $where);

        $model->syncChanges();

        return $model;
    }

    /**
     * 返回未删除状态下的单条记录
     * @param $id
     * @param array $columns
     * @param array $where
     * @return BaseModel
     */
    public static function find($id, array $columns = [], array $where = [])
    {
        $select = $columns ? implode(',', $columns) : '*';
        $model = new static();
        $query = self::newRead();

        // 默认where条件
        $wheres = [
            $model->getKeyName() => $id,
            self::IS_DEL => self::DELETE_SUCCESS
        ];

        // 增加的where条件
        if (is_array($where) && !empty($where)) {
            foreach ($where as $field => $value) {
                $wheres[$field] = $value;
            }
        }

        $row = $query->clear()
            ->select($select)
            ->from($model->getTable())
            ->where($wheres)
            ->getRow();

        if (is_null($row)) {
            return null;
        }

        $model->setRawAttributes($row ?: []);
        $model->syncOriginal();
        $model->exists = true;
        return $model;
    }

    /**
     * 获取单条记录 不限制删除状态
     * @param $id
     * @param array $columns
     * @return BaseModel
     */
    public static function findOnDeleted($id, array $columns = [])
    {
        $select = $columns ? implode(',', $columns) : '*';
        $model = new static();
        $query = self::newRead();
        $row = $query->clear()
            ->select($select)
            ->from($model->getTable())
            ->where([
                $model->getKeyName() => $id])
            ->getRow();

        if (is_null($row)) {
            return null;
        }

        $model->setRawAttributes($row ?: []);
        $model->syncOriginal();
        $model->exists = true;
        return $model;
    }

    /**
     * 返回未删除状态下的多条记录
     * @param array $columns
     * @param string|null $order
     * @return null
     */
    public static function all(array $columns = [], string $order = null)
    {
        $select = $columns ? implode(',', $columns) : '*';
        $query = self::newRead();
        $q = $query->clear();
        $q->select($select);
        $q->from(self::table());
        $q->where([
            self::IS_DEL => self::DELETE_SUCCESS
        ]);
        if ($order) {
            $q->order($order);
        }
        return $q->getAll();
    }


    protected function getKeyForSaveQuery()
    {
        return $this->original[$this->getKeyName()]
            ?? $this->getKey();
    }

    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function delete()
    {
        if (is_null($this->getKeyName())) {
            throw new Exception('No primary key defined on model.');
        }

        if (!$this->exists) {
            return;
        }

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
            $this->updateDeleteState();
        }

        $this->save();

        $this->exists = false;

        return true;
    }

    /**
     * 强制删除
     * @return bool|null
     * @throws Exception
     */
    public function forceDelete()
    {
        return $this->delete();
    }

    public static function pagination()
    {

    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Set the primary key for the model.
     *
     * @param  string $key
     * @return $this
     */
    public function setKeyName($key)
    {
        $this->primaryKey = $key;

        return $this;
    }


    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        $className = $this->class_basename($this);
        if (!isset($this->table)) {
            return str_replace(
                [
                    '\\',
                    '_model'
                ], '', Str::snake($className)
            );
        }
        return $this->table;
    }

    public static function table()
    {
        return (new static())->getTable();
    }

    public static function getFullTable()
    {
        $model = new static();
        $className = $model->class_basename($model);
        if (!isset($model->table)) {
            return self::$_tablePrefix . str_replace(
                    [
                        '\\',
                        '_model'
                    ], '', Str::snake($className)
                );
        }
        return self::$_tablePrefix . $model->table;
    }

    protected function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }

    /**
     * 获取模型属性
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * 动态设置属性
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * 判断属性是否存在
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return !is_null($this->getAttribute($offset));
    }

    /**
     * 获取模型属性
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * 设置模型属性
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * 删除模型属性
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * 判断模型属性是否存在
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * 删除模型属性
     *
     * @param  string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * 处理模型动态函数调用
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->$method(...$parameters);
    }

    /**
     * 处理动静态函数调用
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    public function toArray()
    {
        return array_merge($this->attributesToArray());
    }

    protected function getArrayableAttributes()
    {
        return $this->getArrayableItems($this->attributes);
    }

    protected function getArrayableItems(array $values)
    {
        return $values;
    }

    public function attributesToArray()
    {
        $attributes = $this->getArrayableAttributes();

        $attributes = $this->addMutatedAttributesToArray(
            $attributes, $mutatedAttributes = $this->getMutatedAttributes()
        );

        return $attributes;
    }

    protected function addMutatedAttributesToArray(array $attributes, array $mutatedAttributes)
    {
        foreach ($mutatedAttributes as $key) {

            if (!array_key_exists($key, $attributes)) {
                continue;
            }

            $attributes[$key] = $this->mutateAttributeForArray(
                $key, $attributes[$key]
            );
        }

        return $attributes;
    }

    /**
     * Get the number of models to return per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * 分页
     * @param Builder $builder
     * @param string $pageName
     * @param string $pageRowName
     * @param null $page
     * @return array
     */
    protected static function paginate(Builder $builder, $pageName = 'page', $pageRowName = 'rows', $page = null)
    {
        $model = new static();

        $request = (new \Yaf_Request_Http())->getRequest();

        $query = self::newRead();

        $page = $page ?: ((int)$request[$pageName] ?: 1);

        $perPage = (int)$request[$pageRowName] ?: $model->getPerPage();

        // 是否有排序了?，没有排序使用默认排序方式
        if (is_null($builder->order)) {
            $sort = Arr::get($request, 'sort', $model->getKeyName());
            $order = Arr::get($request, 'order', 'DESC');
            $order = " {$sort} {$order} ";
            $builder->orderBy($order);
        }

        $paginate = $builder->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->paginate()
            ->toArray();

        $querySql = Arr::get($paginate, 'sql.query');
        $countSql = Arr::get($paginate, 'sql.count');

        $result = [];
        $result['rows'] = $query->YDGetAll($querySql);
        $result['total'] = (int)$query->YDGetAll($countSql)[0]['aggregate'];
        $result['perPage'] = (int)$perPage;
        $result['currentPage'] = (int)$page;
        return $result;
    }

    /**
     * 当前登录用户信息
     * @return array
     */
    protected static function auth()
    {
        return \Admin\AdminModel::getCurrentLoginInfo();
    }

    public function validate($rule = true, $msg = [], $batch = false)
    {
        if (is_array($rule)) {
            $this->validate = [
                'rule' => $rule,
                'msg' => $msg,
            ];
        } else {
            $this->validate = true === $rule ? $this->getTable() : $rule;
        }
        $this->batchValidate = $batch;

        $this->isCallValidate = true;

        return $this;
    }

    /**
     * 自动验证数据
     * @param array $data 验证数据
     * @param null $rule 验证规则
     * @param null $batch 批量验证
     * @return bool
     * @throws Exception
     */
    public function validateData(array $data, $rule = null, $batch = null)
    {
        $info = is_null($rule) ? $this->validate : $rule;
        if (!empty($info)) {
            if (is_array($info)) {
                $validate = new \Assemble\Support\Validate();
                $validate->rule($info['rule']);
                $validate->message($info['msg']);
            } else {
                $name = is_string($info) ? $info : $this->getTable();


                if (strpos($name, '.')) {
                    list($name, $scene) = explode('.', $name);
                }
                $modelValidate = "\Validate\\" . $this->getValidateName($name);

                if (!class_exists($modelValidate)) {
                    throw new \InvalidArgumentException("$modelValidate Not Found,请创建");
                }

                $validate = new \Assemble\Support\Validate($modelValidate::$rule, $modelValidate::$message);
                if (!empty($scene)) {
                    $validate->scene($scene, $modelValidate::$scene[$scene]);
                }
            }
            $batch = is_null($batch) ? $this->batchValidate : $batch;

            if (!$validate->batch($batch)->check($data)) {
                $this->error = $validate->getError();
                if (true) {
                    throw new Exception($this->error);
                } else {
                    return false;
                }
            }
            $this->validate = null;
        }
        return true;
    }

    /**
     * 返回模型的错误信息
     * @access public
     * @return string|array
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 验证输入数据
     * @param array $data
     * @return bool
     */
    public function check(array $data)
    {

        if (!$this->isCallValidate) {
            $this->validate();
        }

        try {
            // 数据自动验证
            if (!$this->validateData($data)) {
                return false;
            }
            return true;
        } catch (Exception $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    /**
     * 获取验证器名称
     * @param $model
     * @return string
     */
    protected function getValidateName($model)
    {
        $names = explode('_', $model);
        $names = array_map(function ($val) {
            return ucfirst($val);
        }, $names);
        $model_name = implode('', $names);
        $model = "{$model_name}";

        $className = static::class;
        $split = explode('\\',$className);
        $table = str_replace('Model','',$split[count($split)-1]);
        if ($table == $model){
            return ucfirst($model);
        }
        return ucfirst($table);
    }

}