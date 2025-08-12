<?php

/**
* 数据库表结构定义类
* @see \Illuminate\Database\Schema\Blueprint
* 
* === 列定义方法 ===
* @method \Illuminate\Database\Schema\ColumnDefinition bigIncrements(string $column)   创建一个自增的 UNSIGNED BIGINT 类型主键列
* @method \Illuminate\Database\Schema\ColumnDefinition bigInteger(string $column)   创建一个 BIGINT 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition binary(string $column)    创建一个 BLOB 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition boolean(string $column)  创建一个 BOOLEAN 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition char(string $column, int $length = 255)   创建一个指定长度的 CHAR 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition date(string $column)    创建一个 DATE 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition dateTime(string $column, int $precision = 0)    创建一个 DATETIME 类型的列，可指定精度
* @method \Illuminate\Database\Schema\ColumnDefinition dateTimeTz(string $column, int $precision = 0)  创建一个带时区的 DATETIME 类型的列，可指定精度
* @method \Illuminate\Database\Schema\ColumnDefinition decimal(string $column, int $total = 8, int $places = 2)   创建一个 DECIMAL 类型的列，指定总位数和小数位数
* @method \Illuminate\Database\Schema\ColumnDefinition double(string $column, int $total = null, int $places = null)   创建一个 DOUBLE 类型的列，可指定总位数和小数位数
* @method \Illuminate\Database\Schema\ColumnDefinition enum(string $column, array $allowed)   创建一个 ENUM 类型的列，指定允许的值
* @method \Illuminate\Database\Schema\ColumnDefinition float(string $column, int $total = null, int $places = null)   创建一个 FLOAT 类型的列，可指定总位数和小数位数
* @method \Illuminate\Database\Schema\ColumnDefinition foreignId(string $column)    创建一个无符号的 BIGINT 类型列，通常用于外键
* @method \Illuminate\Database\Schema\ColumnDefinition foreignIdFor($model, string $column = null)    创建一个外键列，名称基于模型
* @method \Illuminate\Database\Schema\ColumnDefinition geometry(string $column)    创建一个 GEOMETRY 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition geometryCollection(string $column)    创建一个 GEOMETRYCOLLECTION 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition id(string $column = 'id')  创建一个自增的 UNSIGNED BIGINT 类型主键列，默认名称为 id
* @method \Illuminate\Database\Schema\ColumnDefinition increments(string $column)  创建一个自增的 UNSIGNED INTEGER 类型主键列
* @method \Illuminate\Database\Schema\ColumnDefinition integer(string $column)  创建一个 INTEGER 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition ipAddress(string $column = 'ip_address')  创建一个用于存储 IP 地址的列
* @method \Illuminate\Database\Schema\ColumnDefinition json(string $column)   创建一个 JSON 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition jsonb(string $column)  创建一个 JSONB 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition lineString(string $column)    创建一个 LINESTRING 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition longText(string $column)   创建一个 LONGTEXT 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition macAddress(string $column = 'mac_address')  创建一个用于存储 MAC 地址的列
* @method \Illuminate\Database\Schema\ColumnDefinition mediumIncrements(string $column)    创建一个自增的 UNSIGNED MEDIUMINT 类型主键列
* @method \Illuminate\Database\Schema\ColumnDefinition mediumInteger(string $column)  创建一个 MEDIUMINT 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition mediumText(string $column) 创建一个 MEDIUMTEXT 类型的列
* @method void morphs(string $name, string $indexName = null)    创建多态关联所需的 id 和 type 列
* @method \Illuminate\Database\Schema\ColumnDefinition multiLineString(string $column)    创建一个 MULTILINESTRING 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition multiPoint(string $column)    创建一个 MULTIPOINT 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition multiPolygon(string $column)    创建一个 MULTIPOLYGON 类型的列
* @method void nullableMorphs(string $name, string $indexName = null)    创建可为空的多态关联所需的 id 和 type 列
* @method void nullableTimestamps(int $precision = 0)  创建可为空的 created_at 和 updated_at 列
* @method \Illuminate\Database\Schema\ColumnDefinition point(string $column)    创建一个 POINT 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition polygon(string $column)    创建一个 POLYGON 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition rememberToken()   创建一个用于"记住我"功能的 VARCHAR(100) 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition set(string $column, array $allowed)    创建一个 SET 类型的列，指定允许的值
* @method \Illuminate\Database\Schema\ColumnDefinition smallIncrements(string $column) 创建一个自增的 UNSIGNED SMALLINT 类型主键列
* @method \Illuminate\Database\Schema\ColumnDefinition smallInteger(string $column) 创建一个 SMALLINT 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition softDeletes(string $column = 'deleted_at', int $precision = 0) 创建一个用于软删除的 TIMESTAMP 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition softDeletesTz(string $column = 'deleted_at', int $precision = 0) 创建一个带时区的用于软删除的 TIMESTAMP 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition string(string $column, int $length = 255)   创建一个 VARCHAR 类型的列，默认长度为 255
* @method \Illuminate\Database\Schema\ColumnDefinition text(string $column)   创建一个 TEXT 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition time(string $column, int $precision = 0)   创建一个 TIME 类型的列，可指定精度
* @method \Illuminate\Database\Schema\ColumnDefinition timeTz(string $column, int $precision = 0) 创建一个带时区的 TIME 类型的列，可指定精度
* @method \Illuminate\Database\Schema\ColumnDefinition tinyIncrements(string $column)    创建一个自增的 UNSIGNED TINYINT 类型主键列
* @method \Illuminate\Database\Schema\ColumnDefinition tinyInteger(string $column)    创建一个 TINYINT 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition timestamp(string $column, int $precision = 0) 创建一个 TIMESTAMP 类型的列，可指定精度
* @method \Illuminate\Database\Schema\ColumnDefinition timestampTz(string $column, int $precision = 0)   创建一个带时区的 TIMESTAMP 类型的列，可指定精度
* @method void timestamps(int $precision = 0)  添加 created_at 和 updated_at 列
* @method void timestampsTz(int $precision = 0)    添加带时区的 created_at 和 updated_at 列
* @method \Illuminate\Database\Schema\ColumnDefinition unsignedBigInteger(string $column)   创建一个无符号的 BIGINT 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition unsignedDecimal(string $column, int $total = 8, int $places = 2)   创建一个无符号的 DECIMAL 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition unsignedInteger(string $column)  创建一个无符号的 INTEGER 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition unsignedMediumInteger(string $column)    创建一个无符号的 MEDIUMINT 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition unsignedSmallInteger(string $column) 创建一个无符号的 SMALLINT 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition unsignedTinyInteger(string $column)  创建一个无符号的 TINYINT 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition uuid(string $column = 'uuid')    创建一个 UUID 类型的列
* @method \Illuminate\Database\Schema\ColumnDefinition year(string $column)    创建一个 YEAR 类型的列
*
* === 索引和约束方法 ===
* @method \Illuminate\Support\Fluent primary(string|array $columns, string $name = null, string $algorithm = null)    定义主键
* @method \Illuminate\Support\Fluent unique(string|array $columns, string $name = null, string $algorithm = null)    定义唯一索引
* @method \Illuminate\Support\Fluent index(string|array $columns, string $name = null, string $algorithm = null)    定义普通索引
* @method \Illuminate\Support\Fluent fullText(string|array $columns, string $name = null, string $algorithm = null)    定义全文索引
* @method \Illuminate\Support\Fluent spatialIndex(string|array $columns, string $name = null)    定义空间索引
* @method \Illuminate\Database\Schema\ForeignKeyDefinition foreign(string|array $columns, string $name = null)    定义外键约束
*
* === 表操作方法 ===
* @method \Illuminate\Support\Fluent create()    指示表需要被创建
* @method void engine(string $engine)    指定表的存储引擎
* @method void charset(string $charset)    指定表的字符集
* @method void collation(string $collation)    指定表的排序规则
* @method \Illuminate\Support\Fluent temporary()    指示表需要是临时的
* @method void comment(string $comment)    为表添加注释
* @method \Illuminate\Support\Fluent rename(string $to)    指示表需要被重命名
* @method \Illuminate\Support\Fluent drop()    指示表需要被删除
* @method \Illuminate\Support\Fluent dropIfExists()    指示表需要被删除（如果存在）
*
* === 删除操作方法 ===
* @method \Illuminate\Support\Fluent dropColumn(string|array $columns)    删除列
* @method \Illuminate\Support\Fluent dropPrimary(string|array $index = null)    删除主键
* @method \Illuminate\Support\Fluent dropUnique(string|array $index)    删除唯一索引
* @method \Illuminate\Support\Fluent dropIndex(string|array $index)    删除普通索引
* @method \Illuminate\Support\Fluent dropFullText(string|array $index)    删除全文索引
* @method \Illuminate\Support\Fluent dropSpatialIndex(string|array $index)    删除空间索引
* @method \Illuminate\Support\Fluent dropForeign(string|array $index)    删除外键约束
* @method \Illuminate\Support\Fluent dropTimestamps()    删除 created_at 和 updated_at 列
* @method \Illuminate\Support\Fluent dropTimestampsTz()    删除带时区的 created_at 和 updated_at 列
* @method \Illuminate\Support\Fluent dropSoftDeletes(string $column = 'deleted_at')    删除软删除列
* @method \Illuminate\Support\Fluent dropSoftDeletesTz(string $column = 'deleted_at')    删除带时区的软删除列
* @method \Illuminate\Support\Fluent dropRememberToken()    删除 remember_token 列
* @method \Illuminate\Support\Fluent dropMorphs(string $name, string $indexName = null)    删除多态关联列
*
* === 重命名操作方法 ===
* @method \Illuminate\Support\Fluent renameColumn(string $from, string $to)    重命名列
* @method \Illuminate\Support\Fluent renameIndex(string $from, string $to)    重命名索引
*/
class Blueprint extends \Illuminate\Database\Schema\Blueprint
{

}
