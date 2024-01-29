<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;
use Hleb\Static\Settings;
use Hleb\Static\System;
use Phphleb\Hlogin\App\Data\ConfigStorage;

abstract class BaseModel extends Model
{
    protected static ?\PDO $pdo = null;

    private static string|null $requestId = null;

    private static string|null $driver = null;

    abstract public static function getTableName(): string;

    /**
     * Returns the name of the used database driver.
     *
     * Возвращает название используемого драйвера БД.
     */
    public static function getDriverName(): string
    {
        return self::$driver ?? self::$driver = self::getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Returns the connection data for the connection being used.
     *
     * Возвращает данные подключения для используемого соединения.
     */
    public static function getConnectionData(): array
    {
        return DB::getConfig(self::getConnectionNameFromConfig());
    }

    /**
     * Getting a connection for console and WEB requests (including asynchronous ones).
     *
     * Получение подключения для консольных и WEB-запросов (в том числе асинхронных).
     */
    protected static function getConnection(): \PDO
    {
        $requestId = System::getRequestId();
        if (self::$requestId !== $requestId || self::$pdo === null) {
            self::$requestId = $requestId;
            self::$pdo = DB::getNewInstance(self::getConnectionNameFromConfig());
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
        return self::$pdo;
    }

    /**
     * Returns the connection name from the library configuration.
     *
     * Возвращает название подключения из конфигурации библиотеки.
     */
    protected static function getConnectionNameFromConfig(): string
    {
        if ($type = ConfigStorage::getDatabaseType()) {
            return $type;
        }
        return Settings::getParam('database', 'base.db.type');
    }

    /**
     * Execution of an arbitrary SQL query with the return of the query object.
     *
     * Выполнение произвольного SQL-запроса c возвращением объекта запроса.
     */
    protected static function run(#[\SensitiveParameter] $sql, #[\SensitiveParameter] $args = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($args);

        return $stmt;
    }

    /**
     * Execute an arbitrary SQL query and return a result.
     *
     * Выполнение произвольного SQL-запроса с возвращением результата.
     */
    protected static function exec(#[\SensitiveParameter] $sql, #[\SensitiveParameter] $args = []): bool
    {
        $time = \microtime(true);
        $stmt = self::getConnection()->prepare($sql);
        $result = $stmt->execute($args);

        self::log($time, $sql);

        return $result;
    }

    /**
     * @internal
     */
    protected static function log(float $startTime, #[\SensitiveParameter] string $sql): void
    {
        System::createSqlQueryLog(
            $startTime,
            $sql,
            self::getConnectionNameFromConfig(),
            tag: 'hlogin',
            driver: self::getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME)
        );
    }
}
