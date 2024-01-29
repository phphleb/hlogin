<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Models;

use Hleb\Static\Log;
use Phphleb\Hlogin\App\Data\ConfigStorage;

final class UserLogModel extends BaseModel
{
    public const CREATE_ACTION = 'Create new user';

    public const UPDATE_ACTION = 'Update user';

    public const DELETE_ACTION = 'Delete user';

    /**
     * The name of the user history table.
     *
     * Название таблицы с историей пользователей.
     */
    public static function getTableName(): string
    {
        return ConfigStorage::getRegConfig()['user-log-table'];
    }

    /**
     * Checking for the existence of a table.
     *
     * Проверка существования таблицы.
     */
    public static function checkLogTable(): bool
    {
        try {
            $time = \microtime(true);
            $sql = "SELECT 1 FROM " . self::getTableName() . " LIMIT 1";
            $result = self::getConnection()->query($sql);
            self::log($time, $sql);
        } catch (\Throwable) {
            return false;
        }
        return $result !== false;
    }

    /**
     * Receiving archived data by E-mail.
     *
     * Получение архивных данных по E-mail.
     */
    public static function getUserLogsDataByEmail(#[\SensitiveParameter] string $email): bool|array
    {
        try {
            $time = \microtime(true);
            $sql = "SELECT * FROM " . self::getTableName() . " WHERE email=?";
            $stmt = self::run($sql, [$email]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC) !== false;
            self::log($time, $sql);
            return $result;
        } catch (\Throwable $exception) {
            Log::error($exception);
            return false;
        }
    }

    /**
     * Archiving data from the user table.
     *
     * Архивирование данных из таблицы пользователей.
     */
    public static function createRowFromData(
        #[\SensitiveParameter] int $parent,
        #[\SensitiveParameter] int $regtype,
        #[\SensitiveParameter] string $action,
        #[\SensitiveParameter] string $email,
        #[\SensitiveParameter] $ip,
        #[\SensitiveParameter] $name,
        #[\SensitiveParameter] $surname,
        #[\SensitiveParameter] $phone,
        #[\SensitiveParameter] $address,
        #[\SensitiveParameter] $description = null,
        #[\SensitiveParameter]$moderatorid = null,
    ): bool
    {
        return self::exec(
                "INSERT INTO " . self::getTableName() . " (parent, regtype, action, email, ip, name, surname, phone, address, description, moderatorid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$parent, $regtype, $action, $email, $ip, $name, $surname, $phone, $address, $description, $moderatorid]
            );
    }

    /**
     * Getting archived data by user id.
     *
     * Получение архивных данных по user id.
     */
    public static function getUserLogsDataById(#[\SensitiveParameter] string $id): bool
    {
        try {
            $stmt = self::run("SELECT * FROM " . self::getTableName() . " WHERE parent=?", [$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) !== false;
        } catch (\Throwable $exception) {
            Log::error($exception);
            return false;
        }
    }

    /**
     * Creating a table with users.
     *
     * Создание таблицы с пользователями.
     */
    public static function createRegisterLogTableIfNotExists(): bool
    {
        if (self::getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $result = self::getConnection()->exec("
     CREATE TABLE IF NOT EXISTS " . self::getTableName() . " (
        id BIGSERIAL PRIMARY KEY,
        parent integer NOT NULL,
        regtype integer NOT NULL,
        action varchar(25) DEFAULT NULL,
        email varchar(100) NOT NULL,
        ip varchar(50) DEFAULT NULL,
        name varchar(100) DEFAULT NULL,
        surname varchar(100) DEFAULT NULL,
        phone varchar(30) DEFAULT NULL,
        address varchar(255) DEFAULT NULL,
        logdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        description varchar(255) DEFAULT NULL,
        moderatorid integer DEFAULT NULL
    )");
            return $result !== false;
        }

        $result =  self::getConnection()->exec("
     CREATE TABLE IF NOT EXISTS " . self::getTableName() . " (
        id int(11) NOT NULL AUTO_INCREMENT,
        parent int(11) NOT NULL,
        regtype int(2) NOT NULL,
        action varchar(25) DEFAULT NULL,
        email varchar(100) NOT NULL,
        ip varchar(50) DEFAULT NULL,
        name varchar(100) DEFAULT NULL,
        surname varchar(100) DEFAULT NULL,
        phone varchar(30) DEFAULT NULL,
        address varchar(255) DEFAULT NULL,
        logdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        description varchar(255) DEFAULT NULL,
        moderatorid int(11) DEFAULT NULL,
        PRIMARY KEY AUTO_INCREMENT (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        return $result !== false;
    }

    /**
     * When this method is called, the current user data is copied to the log.
     *
     * При вызове этого метода текущие данные пользователя копируются в лог.
     */
    public static function copyDataToLog(
        #[\SensitiveParameter] int         $userId, string|null $action = null,
        #[\SensitiveParameter] string|null $description = null,
        #[\SensitiveParameter] int|null    $moderatorId = null,
    ): bool
    {
        $userData = UserModel::getCells('id', $userId);
        if (!$userData) {
            return false;
        }
        self::createRowFromData(
            parent:$userId,
            regtype: $userData['regtype'],
            action: 'Create new user',
            email: $userData['email'],
            ip: $userData['ip'],
            name: $userData['name'],
            surname: $userData['surname'],
            phone: $userData['phone'],
            address: $userData['address'],
            description: $description,
            moderatorid: $moderatorId,
        );

        return true;
    }

}

