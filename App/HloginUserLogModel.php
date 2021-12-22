<?php


namespace Phphleb\Hlogin\App;

use Hleb\Main\DB;

final class HloginUserLogModel extends \Hleb\Scheme\App\Models\MainModel
{
    const CELL_ID = 'id'; // int(11) NOT NULL AUTO_INCREMENT

    const CELL_REGTYPE = 'regtype'; // int(2) NOT NULL

    const CELL_ACTION = 'action'; // varchar(25) DEFAULT NULL

    const CELL_PARENT = 'parent'; // int(11) NOT NULL

    const CELL_EMAIL = 'email'; // varchar(100) NOT NULL

    const CELL_IP = 'ip'; // varchar(50) DEFAULT NULL

    const CELL_NAME = 'name'; // varchar(100) DEFAULT NULL

    const CELL_SURNAME = 'surname'; // varchar(100) DEFAULT NULL

    const CELL_PHONE = 'phone'; // varchar(30) DEFAULT NULL

    const CELL_ADDRESS= 'address'; // varchar(255) DEFAULT NULL

    const CELL_LOGDATE = 'logdate'; // timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP

    const REG_ACTION = 'registration';

    const MODIFICATION_ACTION = 'modification';

    /**
     * Установленное в конфигурационном файле название таблицы с архивом пользователей
     * @return string
     */
    public static function getTableName(): string
    {
        return 'userlogs';
    }

    /**
     * Проверка существования таблицы
     * @return bool
     */
    public static function checkTableUsers(): bool
    {
        try {
            $result = DB::run("SELECT 1 FROM " . self::getTableName() . " LIMIT 1")
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
            $stmt = DB::run("SELECT * FROM " . self::getTableName() . " WHERE email=?", [$email]);
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
        return DB::run(
                "INSERT INTO " . self::getTableName() . " (`parent`, `regtype`, `action`, `email`, `ip`, `name`, `surname`, `phone`, `address`, `description`, `moderatorid`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
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
            $stmt = DB::run("SELECT * FROM " . self::getTableName() . " WHERE parent=?", [$id]);
            return !empty($stmt) && !empty($stmt->rowCount()) ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return false;
        }
    }

    public static function createRegisterLogTable() {
        return DB::run("
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
    )");
    }

}