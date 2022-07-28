<?php


namespace Phphleb\Hlogin\App;

use Hleb\Main\DB;

class BaseModel extends \Hleb\Scheme\App\Models\MainModel
{
    protected static ?\PDO $pdo = null;

    public static function getConnectionData()
    {
        $connection = [
            'driver' => self::connection()->getAttribute(\PDO::ATTR_DRIVER_NAME),
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

    protected static function run($sql, $args = []): \PDOStatement
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($args);

        return $stmt;
    }

    protected static function connection(): \PDO
    {
        if (empty(self::$pdo)) {
            self::$pdo = DB::getNewPdoInstance();
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
        return self::$pdo;
    }

}

