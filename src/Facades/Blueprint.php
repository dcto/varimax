<?php

/**
* 数据类型
* @see \Illuminate\Database\Schema\Blueprint
* @method bigIncrements(int $id)   自增ID，类型为bigint
* @method bigInteger(int $id)   等同于数据库中的BIGINT类型
* @method binary(array $data)    等同于数据库中的BLOB类型
* @method boolean(boolean $confirmed)  等同于数据库中的BOOLEAN类型
* @method char(string $name, int $len);   等同于数据库中的CHAR类型
* @method date(string $name)    等同于数据库中的DATE类型
* @method dateTime(string $name)    等同于数据库中的DATETIME类型
* @method dateTimeTz(string $name)  等同于数据库中的DATETIME类型（带时区）
* @method decimal(string $amount, int $len, int $decimal)   等同于数据库中的DECIMAL类型，带一个精度和范围
* @method double(string $column, int $len, int $decimal)   等同于数据库中的DOUBLE类型，带精度, 总共15位数字，小数点后8位.
* @method enum(string $name, array $enum);   等同于数据库中的 ENUM类型
* @method float(string $name)   等同于数据库中的 FLOAT 类型
* @method increments(string $id)  数据库主键自增ID
* @method integer(string $name)  等同于数据库中的 INTEGER 类型
* @method ipAddress(string $ip)  等同于数据库中的 IP 地址
* @method json(string $name)   等同于数据库中的 JSON 类型
* @method jsonb(string $name)  等同于数据库中的 JSONB 类型
* @method longText(string $content)   等同于数据库中的 LONGTEXT 类型
* @method macAddress(string $mac)  等同于数据库中的 MAC 地址
* @method mediumIncrements(string $id)    自增ID，类型为无符号的mediumint
* @method mediumInteger(string $numbers)  等同于数据库中的 MEDIUMINT类型
* @method mediumText(string $description) 等同于数据库中的 MEDIUMTEXT类型
* @method morphs(string $taggable)    添加一个 INTEGER类型的 taggable_id 列和一个 STRING类型的 taggable_type列
* @method nullableTimestamps();  和 timestamps()一样但允许 NULL值.
* @method rememberToken();   添加一个 remember_token 列： VARCHAR(100) NULL.
* @method smallIncrements(string $name) 自增ID，类型为无符号的smallint
* @method smallInteger(string $name) 等同于数据库中的 SMALLINT 类型
* @method softDeletes(); 新增一个 deleted_at 列 用于软删除.
* @method string(string $name)   等同于数据库中的 VARCHAR 列  .
* @method string(string $name, int $len)  等同于数据库中的 VARCHAR，带一个长度
* @method text(string $content)   等同于数据库中的 TEXT 类型
* @method time(string $name)   等同于数据库中的 TIME类型
* @method timeTz(string $name) 等同于数据库中的 TIME 类型（带时区）
* @method tinyInteger(string $name)    等同于数据库中的 TINYINT 类型
* @method timestamp(string $name) 等同于数据库中的 TIMESTAMP 类型
* @method timestampTz(string $name)   等同于数据库中的 TIMESTAMP 类型（带时区）
* @method timestamps();  添加 created_at 和 updated_at列
* @method timestampsTz();    添加 created_at 和 updated_at列（带时区）
* @method unsignedBigInteger(string $name)   等同于数据库中无符号的 BIGINT 类型
* @method unsignedInteger(string $name)  等同于数据库中无符号的 INT 类型
* @method unsignedMediumInteger(string $name)    等同于数据库中无符号的 MEDIUMINT 类型
* @method unsignedSmallInteger(string $name) 等同于数据库中无符号的 SMALLINT 类型
* @method unsignedTinyInteger(string $name)  等同于数据库中无符号的 TINYINT 类型
* @method uuid(string $id)    等同于数据库的UUID
*/
class Blueprint extends \Illuminate\Database\Schema\Blueprint
{

}
