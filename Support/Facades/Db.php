<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class DB
 *
 * @method static \Illuminate\Database\Connection connection(string $name = null)
 * @method static \Illuminate\Database\Connection reconnect(string $name = null)
 * @method static void disconnect(string $name = null)
 * @method static void purge(string $name = null)
 * @method static string getDefaultConnection()
 * @method static void setDefaultConnection($name)
 * @method static void extend(string $name, callable $resolver)
 * @method static \Illuminate\Database\Connection getConnection(string $name = null)
 * @method static array getConnections()
 * @method static \Illuminate\Database\Schema\MySqlBuilder getSchemaBuilder()
 * @method static void useDefaultQueryGrammar()
 * @method static void useDefaultSchemaGrammar()
 * @method static void useDefaultPostProcessor()
 * @method static \Illuminate\Database\Query\Builder table(string $table)
 * @method static \Illuminate\Database\Query\Builder query()
 * @method static \Illuminate\Database\Query\Expression raw(mixed $value)
 * @method static \Illuminate\Database\Query\Expression selectOne(string $query, array $bindings = array())
 * @method static mixed selectFromWriteConnection(string $query, array $bindings = array())
 * @method static array select(string $query, array $bindings = array(), bool $useReadPdo = true)
 * @method static array insert(string $query, array $bindings = array())
 * @method static bool update(string $query, array $bindings = array())
 * @method static int delete(string $query, array $bindings = array())
 * @method static int statement(string $query, array $bindings = array())
 * @method static bool affectingStatement(string $query, array $bindings = array())
 * @method static bool unprepared(string $query, array $bindings = array())
 * @method static array prepareBindings(string $query, array $bindings = array())
 * @method static mixed transaction(Closure $callback)
 * @method static mixed beginTransaction()
 * @method static void commit()
 * @method static void rollBack()
 * @method static void transactionLevel()
 * @method static array pretend(Closure $callback)
 * @method static void logQuery(string $query, array $bindings, float|null $time = null)
 * @method static void listen(Closure $callback)
 * @method static bool isDoctrineAvailable()
 * @method static \Doctrine\DBAL\Schema\Column getDoctrineColumn(string $table, string $column)
 * @method static \Doctrine\DBAL\Schema\AbstractSchemaManager getDoctrineSchemaManager()
 * @method static \Doctrine\DBAL\Connection\Connection getDoctrineConnection()
 * @method static \PDO getPdo()
 * @method static \PDO getReadPdo()
 * @method static $this setPdo(PDO|null $pdo)
 * @method static $this setReadPdo(PDO|null $pdo)
 * @method static $this setReconnector(callable $reconnector)
 * @method static string|null getName()
 * @method static mixed getConfig(string $option)
 * @method static string getDriverName()
 * @method static \Illuminate\Database\Query\Grammars\Grammar getQueryGrammar()
 * @method static void setQueryGrammar(\Illuminate\Database\Query\Grammars\Grammar $grammar)
 * @method static \Illuminate\Database\Query\Grammars\Grammar getSchemaGrammar()
 * @method static void setSchemaGrammar(\Illuminate\Database\Query\Grammars\Grammar $grammar)
 * @method static \Illuminate\Database\Query\Processors\Processor getPostProcessor()
 * @method static void setPostProcessor(\Illuminate\Database\Query\Processors\Processor $processor)
 * @method static \Illuminate\Contracts\Events\Dispatcher getEventDispatcher()
 * @method static void setEventDispatcher(\Illuminate\Contracts\Events\Dispatcher $events)
 * @method static bool pretending()
 * @method static int getFetchMode()
 * @method static int setFetchMode(int $fetchMode)
 * @method static array getQueryLog()
 * @method static void flushQueryLog()
 * @method static void enableQueryLog()
 * @method static void disableQueryLog()
 * @method static bool logging()
 * @method static string getDatabaseName()
 * @method static string setDatabaseName(string $database)
 * @method static string getTablePrefix()
 * @method static void setTablePrefix(string $prefix)
 * @method static \Illuminate\Database\Grammar withTablePrefix(\Illuminate\Database\Grammar $grammar)
 */
class DB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'db';
    }
}
