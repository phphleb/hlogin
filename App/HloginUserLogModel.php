<?php


namespace Phphleb\Hlogin\App;

use Hleb\Main\DB;

final class HloginUserLogModel extends BaseModel
{
    public const CELL_ID = 'id'; // int(11) NOT NULL AUTO_INCREMENT

    public const CELL_REGTYPE = 'regtype'; // int(2) NOT NULL

    public const CELL_ACTION = 'action'; // varchar(25) DEFAULT NULL

    public const CELL_PARENT = 'parent'; // int(11) NOT NULL

    public const CELL_EMAIL = 'email'; // varchar(100) NOT NULL

    public  const CELL_IP = 'ip'; // varchar(50) DEFAULT NULL

    public const CELL_NAME = 'name'; // varchar(100) DEFAULT NULL

    public const CELL_SURNAME = 'surname'; // varchar(100) DEFAULT NULL

    public const CELL_PHONE = 'phone'; // varchar(30) DEFAULT NULL

    public const CELL_ADDRESS= 'address'; // varchar(255) DEFAULT NULL

    public const CELL_LOGDATE = 'logdate'; // timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP

    public const CELL_DESCRIPTION = 'description'; // varchar(255) DEFAULT NULL

    public const CELL_MODERATOR_ID = 'moderatorid'; // int(11) DEFAULT NULL

    const REG_ACTION = 'registration';

    const MODIFICATION_ACTION = 'modification';

    /**
     * Проверка существования таблицы
     * @return bool
     */
    public static function checkTableUsers(): bool
    {
        try {
            $result = self::run("SELECT 1 FROM " . self::getTableName() . " LIMIT 1")
                ->rowCount();
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Получение архивных данных по Email
     * @param string $email
     * @return array|bool
     */
    public static function getUserLogsDataByEmail(string $email) {
        try {
            $stmt = self::run("SELECT * FROM " . self::getTableName() . " WHERE email=?", [$email]);
            return !empty($stmt) && !empty($stmt->rowCount()) ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return false;
        }
    }

    /**
     * Архивирование данных из таблицы пользователей
     */
    public static function createRowFromData(
        int $parent,
        int $regtype,
        string $action,
        string $email,
        $ip,
        $name,
        $surname,
        $phone,
        $address,
        $description = null,
        $moderatorid = null
    ) {
        return self::run(
                "INSERT INTO " . self::getTableName() . " (parent, regtype, action, email, ip, name, surname, phone, address, description, moderatorid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$parent, $regtype, $action, $email, $ip, $name, $surname, $phone, $address, $description, $moderatorid]
            )->rowCount() === 1;
    }

    /**
     * Получение архивных данных по user id
     * @param string $id - идентификатор из таблицы с пользователями
     * @return array|bool
     */
    public static function getUserLogsDataById(string $id) {
        try {
            $stmt = self::run("SELECT * FROM " . self::getTableName() . " WHERE parent=?", [$id]);
            return !empty($stmt) && !empty($stmt->rowCount()) ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return false;
        }
    }

    public static function createRegisterLogTable() {
        if (self::connection()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            return self::run("
    CREATE TABLE IF NOT EXISTS userlogs (
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
        }

        return self::run("
     CREATE TABLE userlogs (
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

    }

}

