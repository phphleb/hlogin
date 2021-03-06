<?php


namespace Phphleb\Hlogin\App;

use Hleb\Main\DB;
use Phphleb\Hlogin\App\System\UserRegistration;

final class HloginUserModel extends \Hleb\Scheme\App\Models\MainModel
{
    public const CELL_ID = 'id'; // int(11) NOT NULL AUTO_INCREMENT

    public const CELL_REGTYPE = 'regtype'; // int(2) NOT NULL DEFAULT 0

    public const CELL_CONFIRM = 'confirm'; // int(1) NOT NULL DEFAULT 0

    public const CELL_EMAIL = 'email'; // varchar(100) NOT NULL

    public const CELL_LOGIN = 'login'; // varchar(100) DEFAULT NULL

    public  const CELL_PASSWORD = 'password'; // varchar(255) NOT NULL

    public const CELL_NAME = 'name'; // varchar(100) DEFAULT NULL

    public const CELL_SURNAME = 'surname'; // varchar(100) DEFAULT NULL

    public const CELL_PHONE = 'phone'; // varchar(30) DEFAULT NULL

    public const CELL_ADDRESS= 'address'; // varchar(255) DEFAULT NULL

    public const CELL_PROOCODE = 'promocode'; // varchar(100) DEFAULT NULL

    public const CELL_IP = 'ip'; // varchar(50) DEFAULT NULL

    public const CELL_SUBSCRIPTION = 'subscription'; // varchar(1) DEFAULT NULL

    public const CELL_PERIOD = 'period'; // int(11) NOT NULL DEFAULT 0

    public const CELL_REGDATE = 'regdate'; // timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP

    public const CELL_HASH = 'hash'; // varchar(100) DEFAULT NULL

    public const CELL_SESSIONKEY = 'sessionkey'; // varchar(100) DEFAULT NULL

    protected static $name = 'users';

    protected static ?\PDO $pdo = null;

    /**
     * Установленное в конфигурационном файле название таблицы с пользователями
     * @return string
     */
    public static function getTableName() {
        return Main::getTableName();
    }

    // Получение пользователя по E-mail
    public static function checkEmailAddressAndGetData(string $email) {
        try {
            $stmt = self::run("SELECT * FROM " . Main::getTableName() . " WHERE email=?", [$email]);
            return !empty($stmt) && !empty($stmt->rowCount()) ? $stmt->fetch() : false;
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return false;
        }
    }

    // Возвращает массив с данными соединения
    public static function getConnectionData()
    {
        $connection = [
            'driver' => self::getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME),
            'connection' => defined('HLEB_TYPE_DB') ? HLEB_TYPE_DB : 'undefined',
        ];

        if (defined('HLEB_TYPE_DB')) {
            foreach (HLEB_PARAMETERS_FOR_DB[HLEB_TYPE_DB] as $key => $param) {
                if (is_numeric($key)) {
                    $row = explode('=', $param);
                    if (count($row) === 2) {
                        $connection[$row[0]] = $row[1];
                    }
                }
            }
        }
        return $connection;
    }

    // Удаление данных пользователя
    public static function deleteUser(string $email) {
        try {
            $stmt = self::run("DELETE FROM " . Main::getTableName() . " WHERE email=?", [$email]);
            return $stmt->fetch();
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return false;
        }
    }

    // Создание нового пользователя
    public static function createNewUser(
        string $email,
        string $password,
        int $period,
        int $regtype = UserRegistration::PRIMARY_USER,
        string $login = null,
        string $name = null,
        string $surname = null,
        string $phone = null,
        string $address = null,
        string $promocode = null,
        string $ip = null,
        string $subscription = null,
        string $hash = null,
        string $sessionkey = null
    ) {
        try {
            $stmt = self::run("INSERT INTO " . Main::getTableName() . " (email, password, period, regtype, login, name, surname, phone, address, promocode, ip, subscription, hash, sessionkey) VALUES
        (:email, :password, :period, :regtype, :login, :name, :surname, :phone, :address, :promocode, :ip, :subscription, :hash, :sessionkey)",
                ['email' => $email, 'password' => $password, 'period' => $period, 'regtype' => $regtype, 'login' => $login, 'name' => $name, 'surname' => $surname, 'phone' => $phone, 'address' => $address, 'promocode' => $promocode, 'ip' => $ip, 'subscription' => $subscription, 'hash' => $hash, 'sessionkey' => $sessionkey]);
            return !empty($stmt->rowCount());
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return false;
        }
    }

    public static function setPeriodTime($email) {
        try {
            $stmt = self::run("UPDATE " . Main::getTableName() . " SET period=? WHERE email=?", [time(), $email]);
            return $stmt->rowCount() === 1;
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return false;
        }
    }

    public static function createRegisterTable() {
        if (self::getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            return self::run("
    CREATE TABLE IF NOT EXISTS  " . Main::getTableName() . " (
        id SERIAL PRIMARY KEY,
        regtype integer NOT NULL DEFAULT '0',
        login varchar(100) DEFAULT NULL,
        confirm integer NOT NULL DEFAULT '0',
        email varchar(100) NOT NULL,
        password varchar(255) NOT NULL,
        name varchar(100) DEFAULT NULL,
        surname varchar(100) DEFAULT NULL,
        phone varchar(30) DEFAULT NULL,
        address varchar(255) DEFAULT NULL,
        promocode varchar(100) DEFAULT NULL,
        ip varchar(50) DEFAULT NULL,
        subscription integer NOT NULL DEFAULT '0',
        period integer NOT NULL DEFAULT '0',
        regdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        hash varchar(100) DEFAULT NULL,
        sessionkey varchar(100) DEFAULT NULL,
        UNIQUE (email)
    )");
        }
        return self::run("
    CREATE TABLE IF NOT EXISTS  " . Main::getTableName() . " (
        id int(11) NOT NULL AUTO_INCREMENT,
        regtype int(2) NOT NULL DEFAULT '0',
        login varchar(100) DEFAULT NULL,
        confirm int(1) NOT NULL DEFAULT '0',
        email varchar(100) NOT NULL,
        password varchar(255) NOT NULL,
        name varchar(100) DEFAULT NULL,
        surname varchar(100) DEFAULT NULL,
        phone varchar(30) DEFAULT NULL,
        address varchar(255) DEFAULT NULL,
        promocode varchar(100) DEFAULT NULL,
        ip varchar(50) DEFAULT NULL,        
        subscription int(1) NOT NULL DEFAULT '0',
        period int(11) NOT NULL DEFAULT '0',    
        regdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        hash varchar(100) DEFAULT NULL,
        sessionkey varchar(100) DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }

    public static function createAdmin(string $email, string $passwordHash, string $hash, string $sessionKey) {
        $adminType = UserRegistration::REGISTERED_COMANDANTE;
        return self::run(
                "INSERT INTO " . Main::getTableName() . " (id, regtype, confirm, name, email, password, hash, sessionkey) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [1, $adminType, 1, 'Admin', $email, $passwordHash, $hash, $sessionKey])
                ->rowCount() === 1;
    }

    /**
     * Проверка существования таблицы
     * @return bool
     */
    public static function checkTableUsers() {
        try {
            $result = self::run("SELECT 1 FROM " . self::getTableName() . " LIMIT 1", [])
                ->rowCount();
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return false;
        }
        return true;
    }

    public static function getCells(string $searchName, $searchValue, $returnCells = []) {
        $returnCells = is_array($returnCells) ? $returnCells : [$returnCells];
        try {
            if (empty($returnCells)) {
                return self::run("SELECT * FROM " . self::getTableName() . " WHERE {$searchName}=? LIMIT 1", [$searchValue])
                    ->fetch();
            }
            return self::run("SELECT " . implode(", ", $returnCells) . " FROM " . self::getTableName() . " WHERE {$searchName}=? LIMIT 1", [$searchValue])
                ->fetch();
        } catch (\Exception $exception) {
            error_log(__CLASS__ . ":" . $exception->getLine() . "" . $exception->getMessage());
            return false;
        }

    }

    public static function setCells(string $name, $perem, array $cells) {
        $searchName = 'prefixFromDuplicateName' . $name;
        $list = array_merge([$searchName => $perem], $cells);
        $param = [];
        foreach ($cells as $key => $value) {
            $param[] = $key . "=:" . $key;
        }
        try {
            return self::run("UPDATE " . self::getTableName() . " SET " . implode(", ", $param) . " WHERE {$name}=:{$searchName}", $list)
                    ->rowCount() == 1;
        } catch (\Exception $exception) {
            error_log(__CLASS__ . ":" . $exception->getLine() . " " . $exception->getMessage());
            return false;
        }
    }

    public static function getUsers(array $listSort, array $filters = []) {
        $pageNumber = intval($listSort['page'] ?? 1);
        $pageLimit = intval($listSort['limit'] ?? 50);
        $sort = [];
        [$where, $list] = self::getFilter($filters);
        $list['regtype'] = 0;
        $where .= ' regtype >= :regtype ';
        foreach ($listSort as $key => $param) {
            if (in_array($key, ['id_sort', 'confirm_sort', 'regtype_sort', 'subscription_sort'])) {
                $cell = explode('_', $key)[0];
                $sort[] = (int) $param ? "$cell ASC" : "$cell DESC";
            }
        }
        $sort = count($sort) ? 'ORDER BY ' . implode(', ', $sort) : '';
        $firstLimit = ($pageNumber == 0 || $pageNumber == 1) ? 0 : ($pageNumber - 1) * $pageLimit;
        if ($pageLimit > 1000) {
            $pageLimit = 1000;
        }
        if (self::getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            return self::run("SELECT id, email, login, name, surname, phone, address, promocode, regtype, ip, subscription, regdate, confirm FROM " . self::getTableName() . " WHERE {$where} {$sort} OFFSET {$firstLimit} LIMIT {$pageLimit}", $list)
                ->fetchAll();
        } else {
            return self::run("SELECT id, email, login, name, surname, phone, address, promocode, regtype, ip, subscription, regdate, confirm FROM " . self::getTableName() . " WHERE {$where} {$sort} LIMIT {$firstLimit}, {$pageLimit}", $list)
                ->fetchAll();
        }
    }

    public static function getCount(array $filters = []) {
        [$where, $list] = self::getFilter($filters);
        $list['regtype'] = 0;
        $where .= ' regtype >= :regtype ';
        return (int) self::run('SELECT COUNT(1) as count FROM ' . self::getTableName() . ' WHERE ' . $where, $list)->fetchColumn();
    }

    private static function getFilter(array $filters = []) {
        $where = '';
        $list = [];
        $listSelector = ['1' => '=', '2' => '>', '3' => '<', '4' => '>=', '5' => '<=', '6' => '!=', '7' => 'LIKE'];
        $listName = ['id', 'regtype', 'email', 'regdate', 'phone', 'confirm'];
        foreach($filters as $key => $filter) {
            if(!in_array($filter['name'] ?? '', $listName) || !preg_match('/^[0-9a-zA-Z\_\ \.\@\-\:\;\&]{1,50}$/', $filter['value'] ?? '')) break;
            $selector = $listSelector[$filter['selector']];
            $where .= " {$filter['name']} " . $selector . " :{$filter['name']}{$key} AND";
            $list[$filter['name'] . $key ] = $filter['selector'] === '7' ? '%' . $filter['value'] . '%' : $filter['value'];
        }
        return [$where, $list];
    }

    protected static function run($sql, $args = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($args);

        return $stmt;
    }

    protected static function getConnection(): \PDO
    {
        if (empty(self::$pdo)) {
            self::$pdo = DB::getNewPdoInstance();
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
        return self::$pdo;
    }

}

