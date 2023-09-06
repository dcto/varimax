<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Schema
 * @see \Illuminate\Database\Schema\Builder
 *
 * @method static bool hasTable(string $table)
 * @method static bool hasColumn(string $table, string $column)
 * @method static bool hasColumns(string $table, array $columns)
 * @method static string getColumnType(string $table, string $column)
 * @method static array getColumnListing(string $table)
 * @method static \Illuminate\Database\Schema\Builder table(string $table, Closure $callback)
 * @method static \Illuminate\Database\Schema\Builder create(string $table, Closure $callback)
 * @method static \Illuminate\Database\Schema\Builder drop(string $table)
 * @method static \Illuminate\Database\Schema\Builder dropIfExists(string $table)
 * @method static \Illuminate\Database\Schema\Builder rename(string $from, string $to)
 * @method static bool enableForeignKeyConstraints()
 * @method static bool disableForeignKeyConstraints()
 * @method static \Illuminate\Database\Connection getConnection()
 * @method static $this setConnection(\Illuminate\Database\Connection $connection)
 * @method static void blueprintResolver(\Closure $resolver)
 * @method
 *
 * $table->bigIncrements('id');   自增ID，类型为bigint
 * $table->bigInteger('votes');   等同于数据库中的BIGINT类型
 * $table->binary('data');    等同于数据库中的BLOB类型
 * $table->boolean('confirmed');  等同于数据库中的BOOLEAN类型
 * $table->char('name', 4);   等同于数据库中的CHAR类型
 * $table->date('created_at');    等同于数据库中的DATE类型
 * $table->dateTime('created_at');    等同于数据库中的DATETIME类型
 * $table->dateTimeTz('created_at');  等同于数据库中的DATETIME类型（带时区）
 * $table->decimal('amount', 5, 2);   等同于数据库中的DECIMAL类型，带一个精度和范围
 * $table->double('column', 15, 8);   等同于数据库中的DOUBLE类型，带精度, 总共15位数字，小数点后8位.
 * $table->enum('choices', ['foo', 'bar']);   等同于数据库中的 ENUM类型
 * $table->float('amount');   等同于数据库中的 FLOAT 类型
 * $table->increments('id');  数据库主键自增ID
 * $table->integer('votes');  等同于数据库中的 INTEGER 类型
 * $table->ipAddress('visitor');  等同于数据库中的 IP 地址
 * $table->json('options');   等同于数据库中的 JSON 类型
 * $table->jsonb('options');  等同于数据库中的 JSONB 类型
 * $table->longText('description');   等同于数据库中的 LONGTEXT 类型
 * $table->macAddress('device');  等同于数据库中的 MAC 地址
 * $table->mediumIncrements('id');    自增ID，类型为无符号的mediumint
 * $table->mediumInteger('numbers');  等同于数据库中的 MEDIUMINT类型
 * $table->mediumText('description'); 等同于数据库中的 MEDIUMTEXT类型
 * $table->morphs('taggable');    添加一个 INTEGER类型的 taggable_id 列和一个 STRING类型的 taggable_type列
 * $table->nullableTimestamps();  和 timestamps()一样但允许 NULL值.
 * $table->rememberToken();   添加一个 remember_token 列： VARCHAR(100) NULL.
 * $table->smallIncrements('id'); 自增ID，类型为无符号的smallint
 * $table->smallInteger('votes'); 等同于数据库中的 SMALLINT 类型
 * $table->softDeletes(); 新增一个 deleted_at 列 用于软删除.
 * $table->string('email');   等同于数据库中的 VARCHAR 列  .
 * $table->string('name', 100);   等同于数据库中的 VARCHAR，带一个长度
 * $table->text('description');   等同于数据库中的 TEXT 类型
 * $table->time('sunrise');   等同于数据库中的 TIME类型
 * $table->timeTz('sunrise'); 等同于数据库中的 TIME 类型（带时区）
 * $table->tinyInteger('numbers');    等同于数据库中的 TINYINT 类型
 * $table->timestamp('added_on'); 等同于数据库中的 TIMESTAMP 类型
 * $table->timestampTz('added_on');   等同于数据库中的 TIMESTAMP 类型（带时区）
 * $table->timestamps();  添加 created_at 和 updated_at列
 * $table->timestampsTz();    添加 created_at 和 updated_at列（带时区）
 * $table->unsignedBigInteger('votes');   等同于数据库中无符号的 BIGINT 类型
 * $table->unsignedInteger('votes');  等同于数据库中无符号的 INT 类型
 * $table->unsignedMediumInteger('votes');    等同于数据库中无符号的 MEDIUMINT 类型
 * $table->unsignedSmallInteger('votes'); 等同于数据库中无符号的 SMALLINT 类型
 * $table->unsignedTinyInteger('votes');  等同于数据库中无符号的 TINYINT 类型
 * $table->uuid('id');    等同于数据库的UUID
 */
class Schema extends Facade
{
    /**
     * Get a schema builder instance for a connection.
     *
     * @param  string  $name
     * @return \Illuminate\Database\Schema\Builder
     */
    public static function connection($name)
    {
        return static::$app['db']->connection($name)->getSchemaBuilder();
    }

    /**
     * Get a schema builder instance for the default connection.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected static function getFacadeAccessor()
    {
        return static::$app['db']->connection()->getSchemaBuilder();
    }
}
