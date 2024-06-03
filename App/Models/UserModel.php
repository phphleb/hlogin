<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Models;

use Hleb\Helpers\Abracadabra;
use Hleb\Static\Log;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Data\CookieData;
use Phphleb\Hlogin\App\Data\PasswordData;
use Phphleb\Hlogin\App\Helpers\UserSearchHelper;
use Phphleb\Hlogin\App\RegType;

final class UserModel extends BaseModel
{
    public static array $fullUserComposition = [];

    /**
     * Returns the name of the table with users set in the configuration file.
     *
     * Возвращает установленное в конфигурационном файле название таблицы с пользователями.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return ConfigStorage::getRegConfig()['user-table'];
    }

    /**
     * Getting a user by E-mail (or null if such a user is not registered).
     *
     * Получение пользователя по E-mail (или null, если такого пользователя не зарегистрировано).
     */
    public static function checkEmailAddressAndGetData(#[\SensitiveParameter] string $email): mixed
    {
        try {
            $time = \microtime(true);
            $sql = "SELECT * FROM " . self::getTableName() . " WHERE email=?";
            $stmt = self::run($sql, [$email]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            self::log($time, $sql);
            return $result;
        } catch (\Throwable $exception) {
            Log::error($exception);
            return false;
        }
    }

    /**
     * Deleting user data.
     *
     * Удаление данных пользователя.
     */
    public static function deleteUser(#[\SensitiveParameter] string $email): bool
    {
        self::clearUser();

        try {
            return self::exec("DELETE FROM " . self::getTableName() . " WHERE email=?", [$email]);
        } catch (\Throwable $exception) {
            Log::error($exception);
            return false;
        }
    }

    /**
     * Creating a new user.
     *
     * Создание нового пользователя.
     */
    public static function createNewUser(
        #[\SensitiveParameter] string $email,
        #[\SensitiveParameter] string $password,
        #[\SensitiveParameter] int    $period,
        #[\SensitiveParameter] int    $regtype = RegType::PRIMARY_USER,
        #[\SensitiveParameter] string $login = null,
        #[\SensitiveParameter] string $name = null,
        #[\SensitiveParameter] string $surname = null,
        #[\SensitiveParameter] string $phone = null,
        #[\SensitiveParameter] string $address = null,
        #[\SensitiveParameter] string $promocode = null,
        #[\SensitiveParameter] string $ip = null,
        #[\SensitiveParameter] int    $subscription = 0,
        #[\SensitiveParameter] string $hash = null,
        #[\SensitiveParameter] string $code = null,
    ): bool
    {
        self::clearUser();

        try {
            $password = PasswordData::generateHash($password);
            if (self::exec("INSERT INTO " . self::getTableName() . " (email, password, period, regtype, login, name, surname, phone, address, promocode, ip, subscription, hash, code) VALUES
        (:email, :password, :period, :regtype, :login, :name, :surname, :phone, :address, :promocode, :ip, :subscription, :hash, :code)",
                ['email' => $email, 'password' => $password, 'period' => $period, 'regtype' => $regtype, 'login' => $login, 'name' => $name, 'surname' => $surname, 'phone' => $phone, 'address' => $address, 'promocode' => $promocode, 'ip' => $ip, 'subscription' => $subscription, 'hash' => $hash, 'code' => $code])) {
                return self::setSessionKeyByEmail($email);
            }
            return false;
        } catch (\Throwable $exception) {
            Log::error($exception);
            return false;
        }
    }

    public static function updateUser(
        #[\SensitiveParameter] string $oldEmail,
        #[\SensitiveParameter] string $newemail,
        #[\SensitiveParameter] string $password,
        #[\SensitiveParameter] int    $period,
        #[\SensitiveParameter] bool   $confirm,
        #[\SensitiveParameter] int    $regtype = RegType::PRIMARY_USER,
        #[\SensitiveParameter] string $login = null,
        #[\SensitiveParameter] string $name = null,
        #[\SensitiveParameter] string $surname = null,
        #[\SensitiveParameter] string $phone = null,
        #[\SensitiveParameter] string $address = null,
        #[\SensitiveParameter] string $promocode = null,
        #[\SensitiveParameter] string $ip = null,
        #[\SensitiveParameter] int    $subscription = 0,
        #[\SensitiveParameter] string $hash = null,
        #[\SensitiveParameter] string $code = null,
    ): bool
    {
        self::clearUser();

        try {
            if (self::exec("UPDATE " . self::getTableName() . " SET newemail=:newemail , password=:password, period=:period, confirm=:confirm, regtype=:regtype, login=:login, name=:name, surname=:surname, phone=:phone, address=:address, promocode=:promocode, ip=:ip, subscription=:subscription, hash=:hash, code=:code
            WHERE email=:oldEmail",
                ['oldEmail' => $oldEmail, 'newemail' => $newemail, 'password' => $password, 'period' => $period, 'confirm' => (int)$confirm, 'regtype' => $regtype, 'login' => $login, 'name' => $name, 'surname' => $surname, 'phone' => $phone, 'address' => $address, 'promocode' => $promocode, 'ip' => $ip, 'subscription' => $subscription, 'hash' => $hash, 'code' => $code])) {
                return true;
            }
            return false;
        } catch (\Throwable $exception) {
            Log::error($exception);
            return false;
        }
    }

    public static function setPeriodTime(#[\SensitiveParameter] string $email): bool
    {
        self::clearUser();

        try {
            return self::exec("UPDATE " . self::getTableName() . " SET period=? WHERE email=?", [\time(), $email]);
        } catch (\Throwable $exception) {
            Log::error($exception);
            return false;
        }
    }

    /**
     * Returns the result of creating a new table.
     * Returns true even if such a table already exists.
     *
     * Возвращает результат создания новой таблицы.
     * Возвращает true даже если такая таблица уже была ранее.
     */
    public static function createRegisterTableIfNotExists(): bool
    {
        if (self::getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $result = self::getConnection()->exec("
    CREATE TABLE IF NOT EXISTS  " . self::getTableName() . " (
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
        newemail varchar(100) default NULL,
        hash varchar(100) DEFAULT NULL,
        code varchar(100) DEFAULT NULL,
        sessionkey varchar(255) DEFAULT NULL,
        UNIQUE (email)
    )");
            return $result !== false;
        }

        if (self::getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $result = self::getConnection()->exec("
    CREATE TABLE IF NOT EXISTS  " . self::getTableName() . " (
         id INTEGER PRIMARY KEY AUTOINCREMENT,
         regtype INTEGER NOT NULL DEFAULT 0,
         confirm INTEGER NOT NULL DEFAULT 0,
         email TEXT NOT NULL,
         login TEXT,
         password TEXT NOT NULL,
         name TEXT,
         surname TEXT,
         phone TEXT,
         address TEXT,
         promocode TEXT,
         ip TEXT,
         subscription TEXT NOT NULL DEFAULT '0',
         period INTEGER NOT NULL DEFAULT 0,
         regdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
         newemail TEXT,
         hash TEXT,
         code TEXT,
         sessionkey TEXT,
         UNIQUE(email)
    )");
            return $result !== false;
        }

        $result = self::getConnection()->exec("
    CREATE TABLE IF NOT EXISTS  " . self::getTableName() . " (
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
        newemail varchar(100) default NULL,
        hash varchar(100) DEFAULT NULL,
        code varchar(100) DEFAULT NULL,
        sessionkey varchar(255) DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        return $result !== false;
    }

    /**
     * Returns the result of creating a new user with a minimal set of data.
     *
     * Возвращает результат создания нового пользователя с минимальным набором данных.
     */
    public static function createEmptyUser(
        #[\SensitiveParameter] string $email,
        #[\SensitiveParameter] string $name,
        #[\SensitiveParameter] string $passwordHash,
        #[\SensitiveParameter] int    $type = RegType::REGISTERED_ADMIN,
    ): bool
    {
        self::clearUser();

        if (self::exec("INSERT INTO " . self::getTableName() . " (regtype, confirm, name, email, password) VALUES (?, ?, ?, ?, ?)", [$type, 1, $name, $email, $passwordHash])) {
            return self::setSessionKeyByEmail($email);
        }
        return false;
    }

    /**
     * Returns the number of rows in the table with users.
     *
     * Возвращает кол-во строк в таблице с пользователями.
     *
     * @param bool $active - only for active users.
     *                     - только для активных пользователей.
     */
    public static function getCountAllUsers(bool $active): int
    {
        if ($active) {
            $sql = "SELECT COUNT(*) AS count FROM " . self::getTableName() . " WHERE regtype > " . RegType::UNDEFINED_USER;
        } else {
            $sql = "SELECT COUNT(*) AS count FROM " . self::getTableName();
        }
        $result = self::run($sql)->fetch();

        return $result['count'] ?? 0;
    }

    /**
     * Checking the existence of a table with users.
     *
     * Проверка существования таблицы c пользователями.
     */
    public static function checkTableUsers(): bool
    {
        try {
            $time = \microtime(true);
            $sql = "SELECT 1 FROM " . self::getTableName() . " LIMIT 1";
            $result = self::getConnection()->query($sql);
            self::log($time, $sql);
        } catch (\Throwable $exception) {
            Log::error($exception);
            return false;
        }
        return $result !== false;
    }

    /**
     * Specialized retrieval of current user data by his ID.
     *
     * Специализированное получение данных текущего пользователя по его ID.
     */
    public static function getUserViaId(int $id): mixed
    {
         if (self::$fullUserComposition) {
             if (!array_key_exists($id, self::$fullUserComposition)) {
                 self::clearUser();
             } else {
                 return self::$fullUserComposition[$id];
             }
         }
        return self::$fullUserComposition[$id] = self::getCells('id', $id);
    }

    /**
     * Selecting specific query fields.
     *
     * Выборка определенных полей запроса.
     *
     * @param string $searchName - unique name of the search field.
     *                           - уникальное название поля для поиска.
     * @param $searchValue - matching with the name to select a unique string.
     *                     - сопоставление с названием для выборки уникальной строки.
     *
     * @param array|string $returnCells - search columns to be returned.
     *                                  - искомые столбцы, которые будут возвращены.
     */
    public static function getCells(
        #[\SensitiveParameter] string       $searchName,
        #[\SensitiveParameter]              $searchValue,
        #[\SensitiveParameter] array|string $returnCells = []
    ): mixed
    {
        $returnCells = \is_array($returnCells) ? $returnCells : [$returnCells];
        try {
            $time = \microtime(true);
            if (empty($returnCells)) {
                $sql = "SELECT * FROM " . self::getTableName() . " WHERE {$searchName}=? LIMIT 1";
            } else {
                $sql = "SELECT " . \implode(", ", $returnCells) . " FROM " . self::getTableName() . " WHERE {$searchName}=? LIMIT 1";
            }
            $result = self::run($sql, [$searchValue])->fetch();
            self::log($time, $sql);
            return $result;
        } catch (\Throwable $exception) {
            Log::error($exception);
            return false;
        }
    }

    /**
     * Saving field values by string with name=>value match.
     *
     * Сохранение значений полей по строке с совпадением имени=>значения.
     */
    public static function setCells(
        #[\SensitiveParameter] string $name,
        #[\SensitiveParameter]        $perm,
        #[\SensitiveParameter] array  $cells
    ): bool
    {
        self::clearUser();

        $searchName = 'prefixFromDuplicateName' . $name;
        $list = \array_merge([$searchName => $perm], $cells);
        $param = [];
        foreach ($cells as $key => $value) {
            $param[] = $key . "=:" . $key;
        }
        try {
            return self::exec("UPDATE " . self::getTableName() . " SET " . \implode(", ", $param) . " WHERE {$name}=:{$searchName}", $list);
        } catch (\Throwable $exception) {
            Log::error($exception);
            return false;
        }
    }

    private static function setSessionKeyByEmail(#[\SensitiveParameter] string $email): bool
    {
        self::clearUser();

        $user = self::getCells('email', $email, 'id');
        if ($user) {
            $ttl = ConfigStorage::getRegConfig()['session-duration'] ?? null;
            if (empty($ttl) || !is_int($ttl)) {
                throw new \RuntimeException('The `registration.session-duration` parameter was incorrectly specified in the library configuration.');
            }
            $ttl < 3600 and $ttl = 3600;
            return self::setCells('id', $user['id'], ['sessionkey' => CookieData::generateKey($user['id'], $ttl)]);
        }
        return false;
    }

    /**
     * Returns an array with user data by conditions.
     *
     * Возвращает массив с данными пользователей по условиям.
     *
     * @param array $listSort - an array with conditions for limiting output and sorting.
     *                        - массив с условиями для ограничения выдачи и сортировкой.
     *
     * @param bool $active - displaying only active users (without deleted or banned users).
     *                     - отображение только активных пользователей (без удаленных и забаненных).
     *
     * @param array $filters - an array with selection by conditions.
     *                       - массив с выборкой по условиям.
     *
     * @internal
     */
    public static function getUsers(array $listSort, array $filters = [], bool $active = true, bool $adminOnly = false): false|array
    {
        $pageNumber = intval($listSort['page'] ?? 1);
        $pageLimit = intval($listSort['limit'] ?? 50);

        $sort = [];
        [$where, $list] = UserSearchHelper::getFilterData($filters);
        if ($adminOnly) {
            $list['regtype'] = 10;
            $where .= ' regtype >= :regtype AND';
        } else if ($active) {
            $list['regtype'] = 0;
            $where .= ' regtype >= :regtype AND';
        }
        foreach ($listSort as $key => $param) {
            if (in_array($key, ['id', 'confirm', 'subscription'])) {
                $cell = explode('_', $key)[0];
                $sort[] = $param === 1 ? "$cell ASC" : "$cell DESC";
            }
        }
        $sort = count($sort) ? 'ORDER BY ' . implode(', ', $sort) : '';
        $firstLimit = ($pageNumber == 0 || $pageNumber == 1) ? 0 : ($pageNumber - 1) * $pageLimit;
        if ($pageLimit > 1000) {
            $pageLimit = 1000;
        }
        if ($where) {
            $where = ' WHERE ' . $where . ' 1=1 ';
        }
        if (self::getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            return self::run("SELECT id, email, login, name, surname, phone, address, promocode, regtype, ip, subscription, regdate, confirm FROM " . self::getTableName() . " {$where} {$sort} OFFSET {$firstLimit} LIMIT {$pageLimit}", $list)
                ->fetchAll();
        } else {
            return self::run("SELECT id, email, login, name, surname, phone, address, promocode, regtype, ip, subscription, regdate, confirm FROM " . self::getTableName() . " {$where} {$sort} LIMIT {$firstLimit}, {$pageLimit}", $list)
                ->fetchAll();
        }
    }

    /**
     * Returns the number of rows in the table with users according to filters.
     *
     * Возвращает кол-во строк в таблице с пользователями согласно фильтрам.
     *
     * @param bool $active - only for active users.
     *                     - только для активных пользователей.
     *
     * @param array $filters - an array of filters from the form.
     *                       - массив фильтров из формы.
     *
     * @internal
     */
    public static function getCount(array $filters = [], bool $active = true): int
    {
        [$where, $list] = UserSearchHelper::getFilterData($filters);
        if ($active) {
            $list['regtype'] = 0;
            $where .= ' regtype >= :regtype AND';
        }
        if ($where) {
            $where = ' WHERE ' . $where . ' 1=1 ';
        }
        return (int)self::run('SELECT COUNT(1) as count FROM ' . self::getTableName() . $where, $list)->fetchColumn();
    }

    /**
     * Rollback for an asynchronous request.
     *
     * Откат для асинхронного запроса.
     */
    public static function rollback(): void
    {
       self::clearUser();
    }

    /**
     * Clearing user data.
     *
     * Очистка данных пользователя.
     */
    private static function clearUser(): void
    {
        self::$fullUserComposition = [];
    }
}

