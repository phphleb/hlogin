<?php


namespace Phphleb\Hlogin\App;

use Hleb\Main\DB;
use Phphleb\Hlogin\App\System\UserRegistration;

final class HloginUserModel extends \Hleb\Scheme\App\Models\MainModel
{
    const CELL_ID = 'id'; // int(11) NOT NULL AUTO_INCREMENT

    const CELL_REGTYPE = 'regtype'; // int(2) NOT NULL DEFAULT 0

    const CELL_CONFIRM = 'confirm'; // int(1) NOT NULL DEFAULT 0

    const CELL_EMAIL = 'email'; // varchar(100) NOT NULL

    const CELL_LOGIN = 'login'; // varchar(100) DEFAULT NULL

    const CELL_PASSWORD = 'password'; // varchar(255) NOT NULL

    const CELL_NAME = 'name'; // varchar(100) DEFAULT NULL

    const CELL_SURNAME = 'surname'; // varchar(100) DEFAULT NULL

    const CELL_PHONE = 'phone'; // varchar(30) DEFAULT NULL

    const CELL_ADDRESS= 'address'; // varchar(255) DEFAULT NULL

    const CELL_PROOCODE = 'promocode'; // varchar(100) DEFAULT NULL

    const CELL_IP = 'ip'; // varchar(50) DEFAULT NULL

    const CELL_SUBSCRIPTION = 'subscription'; // varchar(1) DEFAULT NULL

    const CELL_PERIOD = 'period'; // int(11) NOT NULL DEFAULT 0

    const CELL_REGDATE = 'regdate'; // timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP

    const CELL_HASH = 'hash'; // varchar(100) DEFAULT NULL

    const CELL_SESSIONKEY = 'sessionkey'; // varchar(100) DEFAULT NULL

    protected static $name = 'users';

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
            $stmt = DB::run("SELECT * FROM " . Main::getTableName() . " WHERE email=?", [$email]);
            return !empty($stmt) && !empty($stmt->rowCount()) ? $stmt->fetch() : false;
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return false;
        }
    }

    // Удаление данных пользователя
    public static function deleteUser(string $email) {
        try {
            $stmt = DB::run("DELETE FROM " . Main::getTableName() . " WHERE email=?", [$email]);
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
            $stmt = DB::run("INSERT INTO " . Main::getTableName() . " (email, password, period, regtype, login, name, surname, phone, address, promocode, ip, subscription, hash, sessionkey) VALUES
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
            $stmt = DB::run("UPDATE " . Main::getTableName() . " SET period=? WHERE email=?", [time(), $email]);
            return $stmt->rowCount() === 1;
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return false;
        }
    }

    public static function createRegisterTable() {
        return DB::run("
    CREATE TABLE " . Main::getTableName() . " (
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
        UNIQUE KEY email_address (email)
    )");
    }

    public static function createAdmin(string $email, string $passwordHash, string $hash, string $sessionKey) {
        $adminType = UserRegistration::REGISTERED_COMANDANTE;
        return DB::run(
                "INSERT INTO " . Main::getTableName() . " (id, regtype, confirm, name, email, password, hash, sessionkey) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [1, $adminType, 1, 'Admin', $email, $passwordHash, $hash, $sessionKey])
                ->rowCount() === 1;
    }

    /**
     * Проверка существования таблицы
     * @return bool
     */
    public static function checkTableUsers() {
        try {
            $result = DB::run("SELECT 1 FROM " . self::getTableName() . " LIMIT 1", [])
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
                return DB::run("SELECT * FROM " . self::getTableName() . " WHERE {$searchName}=? LIMIT 1", [$searchValue])
                    ->fetch();
            }
            return DB::run("SELECT " . implode(", ", $returnCells) . " FROM " . self::getTableName() . " WHERE {$searchName}=? LIMIT 1", [$searchValue])
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
            return DB::run("UPDATE " . self::getTableName() . " SET " . implode(", ", $param) . " WHERE {$name}=:{$searchName}", $list)
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
        $where .= ' `regtype` >= :regtype ';
        foreach ($listSort as $key => $param) {
            if (in_array($key, ['id_sort', 'confirm_sort', 'regtype_sort', 'subscription_sort'])) {
                $cell = explode('_', $key)[0];
                $sort[] = (int) $param ? "`$cell` ASC" : "`$cell` DESC";
            }
        }
        $sort = count($sort) ? 'ORDER BY ' . implode(', ', $sort) : '';
        $firstLimit = ($pageNumber == 0 || $pageNumber == 1) ? 0 : ($pageNumber - 1) * $pageLimit;
        if ($pageLimit > 1000) {
            $pageLimit = 1000;
        }
        return DB::run("SELECT `id`, `email`, `login`, `name`, `surname`, `phone`, `address`, `promocode`, `regtype`, `ip`, `subscription`, `regdate`, `confirm` FROM " . self::getTableName() . " WHERE {$where} {$sort} LIMIT {$firstLimit}, {$pageLimit}", $list)
            ->fetchAll();
    }

    public static function getCount(array $filters = []) {
        [$where, $list] = self::getFilter($filters);
        $list['regtype'] = 0;
        $where .= ' `regtype` >= :regtype ';
        return (int) DB::run('SELECT COUNT(1) as count FROM ' . self::getTableName() . ' WHERE ' . $where, $list)->fetchColumn();
    }

    private static function getFilter(array $filters = []) {
        $where = '';
        $list = [];
        $listSelector = ['1' => '=', '2' => '>', '3' => '<', '4' => '>=', '5' => '<=', '6' => '!=', '7' => 'LIKE'];
        $listName = ['id', 'regtype', 'email', 'regdate', 'phone', 'confirm'];
        foreach($filters as $key => $filter) {
            if(!in_array($filter['name'] ?? '', $listName) || !preg_match('/^[0-9a-zA-Z\_\ \.\@\-\:\;\&]{1,50}$/', $filter['value'] ?? '')) break;
            $selector = $listSelector[$filter['selector']];
            $where .= " `{$filter['name']}` " . $selector . " :{$filter['name']}{$key} AND";
            $list[$filter['name'] . $key ] = $filter['selector'] === '7' ? '%' . $filter['value'] . '%' : $filter['value'];
        }
        return [$where, $list];
    }

}

