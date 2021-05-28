<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11-21:21
 */

namespace VM;

/**
 * Class Model
 *
 * @package VM
 *
 * @method static \VM\Model|\Illuminate\Database\Eloquent\Model create(array $attributes = [])
 * @method static \VM\Model|\Illuminate\Database\Eloquent\Collection|null find(mixed $id, array $columns = array())
 * @method static \Illuminate\Database\Eloquent\Collection findMany(mixed $id, array $columns = array())
 * @method static \VM\Model|\Illuminate\Database\Eloquent\Collection findOrFail(mixed $id, array $columns = array())
 * @method static \VM\Model findOrNew(mixed $id, array $columns = array())
 * @method static \VM\Model firstOrNew(array $attributes)
 * @method static \VM\Model firstOrCreate(array $attributes, array $values = [])
 * @method static \VM\Model updateOrCreate(array $attributes, array $values = array())
 * @method static \VM\Model|null first(array $columns = array())
 * @method static \VM\Model firstOrFail(array $columns = array())
 * @method static \Illuminate\Database\Eloquent\Collection get(array $columns = array())
 * @method static mixed value(string $columns)
 * @method static mixed pluck(string $columns)
 * @method static bool chunk(int $count, callable $callback)
 * @method static \Illuminate\Support\Collection lists(string $column, string|null $key)
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection paginate(int $perPage = null, array $columns = array('*'), string $pageName = 'page', int|null $page = null)
 * @method $this \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection paginate(int $perPage = null, array $columns = array('*'), string $pageName = 'page', int|null $page = null)
 * @method static \Illuminate\Contracts\Pagination\Paginator simplePaginate(int $perPage = null, array $columns = array('*'), string $pageName = 'page')
 * @method static void onDelete(\Closure $callback)
 * @method static \Illuminate\Database\Eloquent\Model[] getModels(array $columns = array())
 * @method static array eagerLoadRelations(array $models)
 * @method static \VM\Model when($value, $callback)
 * @method static \VM\Model where(string $column, string $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder orWhere(string $column, string $operator = null, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder has(string $relation, string $operator = '>=', int $count = 1, string $boolean = 'and', \Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder doesntHave(string $relation, string $boolean = 'and', \Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder whereHas(string $relation, \Closure $callback, string $operator = '>=', int $count = 1)
 * @method static \Illuminate\Database\Eloquent\Builder whereDoesntHave(string $relation, \Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder orHas(string $relation, string $operator = '>=', int $count = 1)
 * @method static \Illuminate\Database\Eloquent\Builder orWhereHas(string $relation, \Closure $callback, string $operator = '>=', int $count = 1)
 * @method static \Illuminate\Database\Query\Builder getQuery()
 * @method static \VM\Model setQuery(\Illuminate\Database\Query\Builder $query)
 * @method static array getEagerLoads()
 * @method static \VM\Model setEagerLoads(array $eagerLoad)
 * @method static \VM\Model getModel()
 * @method static setModel(\Illuminate\Database\Eloquent\Model $model)
 * @method static void macro(string $name, \Closure $callback)
 * @method static \Closure getMacro(string $name)
 * @method static \VM\Model select(array|mixed $columns = array())
 * @method static \Illuminate\Database\Query\Builder selectRaw(string $expression, array $bindings = array())
 * @method static \Illuminate\Database\Query\Builder selectSub(\Closure|\Illuminate\Database\Query\Builder|string $query, string $as)
 * @method static \VM\Model addSelect(array|mixed $column)
 * @method static \VM\Model distinct()
 * @method static \VM\Model from(string $table)
 * @method static \VM\Model join(string $table, string $one, string $operator = null, string $two = null, string $type = 'inner', bool $where = false)
 * @method static \Illuminate\Database\Query\Builder joinWhere(string $table, string $one, string $operator, string $two, string $type = 'inner')
 * @method static \Illuminate\Database\Query\Builder leftJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static \Illuminate\Database\Query\Builder rightJoin(string $table, string $first, string $operator = null, string $second = null)
 * @method static \Illuminate\Database\Query\Builder rightJoinWhere(string $table, string $one, string $operator, string $two)
 * @method static \VM\Model whereRaw(string $sql, array $bindings = array(), string $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder orWhereRaw(string $sql, array $bindings = array())
 * @method static \VM\Model whereBetween(string $column, array $values, string $boolean = 'and', bool $not = false)
 * @method static \Illuminate\Database\Query\Builder orWhereBetween(string $column, array $values)
 * @method static \Illuminate\Database\Query\Builder whereNotBetween(string $column, array $values, string $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder whereNested(\Closure $callback, string $boolean = 'and')
 * @method static \VM\Model addNestedWhereQuery(\Illuminate\Database\Query\Builder|\Illuminate\Database\Query\Builder $query, string $boolean = 'and')
 * @method static \VM\Model whereExists(\Closure $callback, string $boolean = 'and', bool $not = false)
 * @method static \Illuminate\Database\Query\Builder orWhereExists(\Closure $callback, bool $not = false)
 * @method static \Illuminate\Database\Query\Builder whereNotExists(\Closure $callback, string $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder orWhereNotExists(\Closure $callback)
 * @method static \VM\Model whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method static \Illuminate\Database\Query\Builder orWhereIn(string $column, mixed $values)
 * @method static \Illuminate\Database\Query\Builder whereNotIn(string $column, mixed $values, string $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder orWhereNotIn(string $column, mixed $values)
 * @method static \Illuminate\Database\Query\Builder whereNull(string $column, string $boolean = 'and', bool $not = false)
 * @method static \Illuminate\Database\Query\Builder orWhereNull(string $column)
 * @method static \Illuminate\Database\Query\Builder whereNotNull(string $column, string $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder orWhereNotNull(string $column)
 * @method static \Illuminate\Database\Query\Builder whereDate(string $column, string $operator, int $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder whereDay(string $column, string $operator, int $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder whereMonth(string $column, string $operator, int $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder whereYear(string $column, string $operator, int $value, string $boolean = 'and')
 * @method static \VM\Model dynamicWhere(string $method, string $parameters)
 * @method static \VM\Model groupBy(mixed $args)
 * @method static \VM\Model having(string $column, string $operator = null, string $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder orHaving(string $column, string $operator = null, string $value = null)
 * @method static \VM\Model havingRaw(string $sql, array $bindings = array(), string $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder orHavingRaw(string $sql, array $bindings = array())
 * @method static \VM\Model orderBy(string $column, string $direction = 'asc')
 * @method static \Illuminate\Database\Query\Builder latest(string $column = 'created_at')
 * @method static \Illuminate\Database\Query\Builder oldest(string $column = 'created_at')
 * @method static \VM\Model orderByRaw(string $sql, array $bindings = array())
 * @method static \VM\Model offset(int $value)
 * @method static \VM\Model limit(int $value)
 * @method static \Illuminate\Database\Query\Builder skip(int $value)
 * @method static \Illuminate\Database\Query\Builder take(int $value)
 * @method static \Illuminate\Database\Query\Builder forPage(int $page, int $perPage = 15)
 * @method static \Illuminate\Database\Query\Builder union(\Illuminate\Database\Query\Builder|\Closure $query, bool $all = false)
 * @method static \Illuminate\Database\Query\Builder unionAll(\Illuminate\Database\Query\Builder|\Closure $query)
 * @method static \VM\Model lock(bool $value = true)
 * @method static $this|\Illuminate\Database\Query\Builder lockForUpdate()
 * @method static $this|\Illuminate\Database\Query\Builder sharedLock()
 * @method static int getCountForPagination(array $columns = array('*'))
 * @method static string implode(string $column, string $glue = '')
 * @method static bool exists()
 * @method static int count(string $columns = '*')
 * @method static mixed min(string $column)
 * @method static mixed max(string $column)
 * @method static mixed sum(string $column)
 * @method static mixed avg(string $column)
 * @method static mixed average(string $column)
 * @method static mixed aggregate(string $function, array $columns = array('*'))
 * @method static float|int numericAggregate(string $function, array $columns = array('*'))
 * @method static bool insert(array $values)
 * @method static int insertGetId(array $values, string $sequence = null)
 * @method static void truncate()
 * @method static void mergeWheres(array $wheres, array $bindings)
 * @method static \Illuminate\Database\Query\Expression raw(mixed $value)
 * @method static array getBindings()
 * @method static array getRawBindings()
 * @method static \VM\Model setBindings(array $bindings, string $type = 'where')
 * @method static \VM\Model addBinding(mixed $value, string $type = 'where')
 * @method static \VM\Model mergeBindings(\Illuminate\Database\Query\Builder $query)
 * @method static \Illuminate\Database\Query\Processors\Processor getProcessor()
 * @method static \Illuminate\Database\Query\Grammars\Grammar getGrammar()
 * @method static \VM\Model useWritePdo()
 * @method static bool hasMacro(string $name)
 * @method static mixed macroCall($method, $parameters)
 * @method static inRandomOrder()
 * @method int increment($column, $amount = 1, array $extra = [])
 * @method int decrement($column, $amount = 1, array $extra = [])
 * @method \VM\Model array toArray()
 * @method \VM\Model withIn(string $relation, array $column = array())
 * @method static|\VM\Model atDate(string $column, string $date ) [range date with symbol ~]
 */

abstract class Model extends \Illuminate\Database\Eloquent\Model
{

    /**
     * 父ID
     * @var string
     */
    protected $pid = 'pid';

    /**
     * Hash Id
     * @var array
     */
    protected $hashId;

    /**
     * 表前缀
     * @var
     */
    protected $prefix;

    /**
     * 字段黑名单(可以阻止被批量赋值)
     *
     * @var array
     */
    protected $guarded = ['id','created_at','updated_at','deleted_at'];

    /**
     * 字段白名单  属性指定了哪些字段支持批量赋值 。可以设定在类的属性里或是实例化后设定。
     *
     * @var null
     */
    protected $fillable = [];


    /**
     * 默认日期格式
     * @var string
     */
    protected $dateFormat;

    /**
     * 预定义分页数
     * @var int
     */
    protected $perPage = 20;


    /**
     * 预定义联查
     * @var array
     */
    protected $with = [];


    /**
     *
     * 数组转换 把数组转化成JSON格式存入数据库 读取时自动转化成数组
     * @var array
     */
    protected $casts = [];

    /**
     * 追加字段到返回数组中 而且是数据库没有的字段 而且需要访问器的帮忙
     * 但这个不理解有什么用处 他其实是通过已有字段经过判断后输出 两个字段都能返回 只不过这个返回是布尔值
     * @var array
     */
    protected $appends = [];

    /**
     * 隐藏模型的一些属性 直接输出的时候是无法看见的
     * @var array
     */
    protected $hidden = [];


    /**
     * 显示白名单 那些字段直接输出是可以被看到的
     * @var array
     */
    protected $visible = [];

    /**
     * updated_at 和 created_at 数据库是否包含该两个字段，默认无false
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Composite table
     *
     * @var array
     */
    static $tables = array();

    /**
     * Set the table associated with the model.
     *
     * @param  string  $table
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function setTable($table)
    {
        return parent::setTable($this->prefix?$this->prefix.$table:$table);
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        if (isset($this->table)) {
            if(isset($this->prefix)){
                $this->getConnection()->setTablePrefix($this->prefix);
            }
            return $this->table;
        }

        return str_replace('\\', '', \Str::snake(\Str::plural(class_basename($this))));
    }

    /**
     * [table]
     *
     * @return mixed
     */
    public static function table($table = null)
    {
        if($table){
            return (new static)->setTable($table);
        }
        return (new static)->getTable();
    }

    /**
     * @return Model
     */
    public function model()
    {
        return self::getModel();
    }

    /**
     * toArray
     * @return array
     */
    public function toArray($relations = true)
    {
        return $relations ?  array_merge($this->attributesToArray(), $this->relationsToArray()) : $this->attributesToArray();
    }

    /**
     * scopeWithOnly
     * @param $query $this
     * @param $relation
     * @param array $columns
     * @return mixed
     */
    public function scopeWithIn($query, $relation, array $columns)
    {
        return $query->with([$relation => function ($query) use ($columns){
            $query->select(array_merge([$this->param], $columns));
        }]);
    }

    /**
     * 上级
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function upper()
    {
        return $this->belongsTo(static::class, $this->pid);
    }

    /**
     * 下级
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lower()
    {
        return $this->hasMany(static::class, $this->pid, $this->getKeyName());
    }

    /**
     * 所有上级
     * @return mixed
     */
    public function uppers() {
        return $this->belongsTo(static::class, $this->pid)->with(__FUNCTION__);
    }

    /**
     * 所有下级(含同级)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lowers()
    {
        return $this->lower()->with(__FUNCTION__);
    }

    /**
     * [bootstrap]
     */
    protected static function boot()
    {
        //Database Bootstrap
        make('db');

        //加载Traits
        static::bootTraits();

        /**
         * 创建事件
         */
        static::creating(function($model){});

        /**
         * 已创建事件
         */
        static::created(function($model){});

        /**
         * 更新事件
         */
        static::updating(function($model){
        });

        /**
         * 已更新事件
         */
        static::updated(function($model){});

        /**
         * 保存事件
         */
        static::saving(function($model){});

        /**
         * 已保存事件
         */
        static::saved(function ($model){});

        /**
         * 删除事件
         */
        static::deleting(function($model){});

        /**
         * 已删除事件
         */
        static::deleted(function($model){});


        /**
         * call model hooks
         */
        $class   = get_called_class();
        $hooks    = array('on' => 'ing', 'off' => 'ed');
        $radicals = array('sav', 'validat', 'creat', 'updat', 'delet');
        foreach ($radicals as $rad) {
            foreach ($hooks as $hook => $event) {
                $method = $hook.ucfirst($rad).'e';
                if (method_exists($class, $method)) {
                    $eventMethod = $rad.$event;
                    self::$eventMethod(function($model) use ($method){
                        return $model->$method($model);
                    });
                }
            }
        }


        //动态分表(查询)
        if(static::$tables == true){
            static::addGlobalScope(function (\Illuminate\Database\Eloquent\Builder $builder) {
                array_map(function($where)use($builder){
                    if($where['column'] == static::$tables['column']){
                        $where['operator'] == '=' && $builder->getQuery()->from(static::$tables['prefix'].$where['value']);
                    }
                }, $builder->getQuery()->wheres);
            });
        }
    }

    /**
     * 动态分表(插入)
     * @param array $attributes
     * @param bool $exists
     */
    public function newInstance($attributes = [], $exists = false)
    {
        $model = parent::newInstance($attributes, $exists);

        if(static::$tables == true){
            isset($attributes[static::$tables['column']]) && $model->setTable(static::$tables['prefix']. $attributes[static::$tables['column']]);
        }
        return $model;
    }

    /**
     * 新旧数据对比
     * Write Log Compare Before and After Data
     * @param Model $model
     * @param string $event
     */
    protected function compare(self $model, $event = 'saved')
    {
        $dirty = $model->getDirty();
        $compare = array();
        foreach ($dirty as $field => $new) {
            $old = $model->getOriginal($field);
            if ($old != $new) $compare[$field] = array($old=>$new);
        }
        if($compare) \Log::dir('db/compare/'.$event)->info('['.$model->getTable().']'.json_encode($compare));
    }

    /**
     *
     * @return bool
     */
    protected function isCompositeKey()
    {
        return is_array($this->primaryKey);
    }

    /**
     * 修复Eloquent联合主键 [Warning: Illegal offset type in isset or empty] BUG问题
     * Set the keys for a save update query.
     * This is a fix for tables with composite keys
     * TODO: Investigate this later on
     * @see https://github.com/laravel/framework/issues/5517#issuecomment-113655441
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query) {
        if ($this->isCompositeKey()) {
            foreach ((array) $this->primaryKey as $pk) {
                $query->where($pk, '=', $this->original[$pk]);
            }
            return $query;
        }else{
            return parent::setKeysForSaveQuery($query);
        }
    }

    /**
     * 修复Eloquent联合主键 [Warning: array_key_exists(): The first argument should be either a string or an integer] BUG问题
     * @see https://github.com/maksimru/composite-primary-keys/blob/master/src/Http/Traits/HasCompositePrimaryKey.php#L231
     * @param  string  $column
     * @param  int  $amount
     * @param  array  $extra
     * @param  string  $method
     * @return int
     */
    protected function incrementOrDecrement($column, $amount, $extra, $method)
    {
        $query = $this->newQueryWithoutRelationships();

        if (! $this->exists) {
            return $query->{$method}($column, $amount, $extra);
        }

        $this->incrementOrDecrementAttributeValue($column, $amount, $extra, $method);

        if($this->isCompositeKey()){
            foreach ((array) $this->primaryKey as $key) {
                $query->where($key, $this->getAttribute($key));
            }
            return $query->{$method}($column, $amount, $extra);
        }else{
            return $query->where(
                $this->getKeyName(), $this->getKey()
            )->{$method}($column, $amount, $extra);
        }
    }
}