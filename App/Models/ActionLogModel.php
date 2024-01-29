<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Models;

use Hleb\Static\Log;
use Phphleb\Hlogin\App\Data\ConfigStorage;

final class ActionLogModel extends BaseModel
{
    /**
     * The name of the table with the history of administrator actions.
     *
     * Название таблицы с историей действий администраторов.
     */
    public static function getTableName(): string
    {
        return ConfigStorage::getRegConfig()['admin-log-table'];
    }

    /**
     * Getting archived data by parameter name.
     *
     * Получение архивных данных по названию параметра.
     */
    public static function getUserLogsDataByEmail(#[\SensitiveParameter] string $param): bool|array
    {
        try {
            $time = \microtime(true);
            $sql = "SELECT * FROM " . self::getTableName() . " WHERE change=?";
            $stmt = self::run($sql, [$param]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC) !== false;
            self::log($time, $sql);
            return $result;
        } catch (\Throwable $exception) {
            Log::error($exception);
            return false;
        }
    }

    /**
     * Archiving data for activities.
     *
     * Архивирование данных для действий.
     */
    public static function createRowFromData(
        #[\SensitiveParameter] $change,
        #[\SensitiveParameter] $previous,
        #[\SensitiveParameter] $gettype,
        #[\SensitiveParameter] $description = null,
        #[\SensitiveParameter] $moderatorid = null
    ): bool
    {
        return self::exec(
                "INSERT INTO " . self::getTableName() . " (change, previous, gettype, description, moderatorid) VALUES (?, ?, ?, ?, ?)",
                [$change, $previous, $gettype, $description, $moderatorid]
            );
    }

    /**
     * Obtaining archived data for a period.
     *
     * Получение архивных данных за период.
     */
    public static function getUserLogsDataByPeriod(string $startDate, string $endDate): bool
    {
        try {
            $time = \microtime(true);
            $sql = "SELECT * FROM " . self::getTableName() . " WHERE logdate <= ? AND logdate >= ?";
            $stmt = self::run($sql, [$endDate, $startDate]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC) !== false;
            self::log($time, $sql);
            return $result;
        } catch (\Throwable $exception) {
            Log::error($exception);
            return false;
        }
    }

    /**
     * Creating a table for the archive of actions in the administrative panel.
     *
     * Создание таблицы для архива действий в административной панели.
     */
    public static function createActionLogTableIfNotExists(): bool
    {
        if (self::getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $result = self::getConnection()->exec("
     CREATE TABLE IF NOT EXISTS " . self::getTableName() . " (
        id BIGSERIAL PRIMARY KEY,
        changedata varchar(255) DEFAULT NULL,
        previousdata varchar(255) DEFAULT NULL,
        fromtype varchar(10) DEFAULT NULL,
        description varchar(255) DEFAULT NULL,
        moderatorid int(11) DEFAULT NULL,
        logdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    )");
            return $result !== false;
        }

        $result = self::getConnection()->exec("
     CREATE TABLE IF NOT EXISTS " . self::getTableName() . " (
        id int(11) NOT NULL AUTO_INCREMENT,
        changedata varchar(255) DEFAULT NULL,
        previousdata varchar(255) DEFAULT NULL,
        fromtype varchar(10) DEFAULT NULL,
        description varchar(255) DEFAULT NULL,
        moderatorid int(11) DEFAULT NULL,
        logdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY AUTO_INCREMENT (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        return $result !== false;
    }

}

