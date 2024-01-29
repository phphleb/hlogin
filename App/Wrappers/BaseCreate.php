<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Wrappers;

use Phphleb\Hlogin\App\Models\ActionLogModel;
use Phphleb\Hlogin\App\Models\UserLogModel;
use Phphleb\Hlogin\App\Models\UserModel;

/**
 * Base class for manipulating tables (creating entities).
 *
 * Базовый класс для манипуляций с таблицами (создание сущностей).
 */
abstract class BaseCreate
{
    protected function init(): bool
    {
        echo 'Database driver:  ' . UserModel::getDriverName() . PHP_EOL;
        echo 'Connection:' . PHP_EOL;
        $data = $this->formatDataRows(UserModel::getConnectionData());
        echo '  '. \implode(PHP_EOL . '  ', $data) . PHP_EOL . PHP_EOL;
        $tableInfo = UserModel::checkTableUsers() ? '' : ' (create)';
        if (!UserModel::createRegisterTableIfNotExists()) {
            echo 'Error: Could not create or access table `' . UserModel::getTableName() . '`.' . PHP_EOL;
            return false;
        }
        echo 'Users table name: ' . UserModel::getTableName() . $tableInfo . PHP_EOL;

        $tableInfo = UserLogModel::checkLogTable() ? '' : ' (create)';
        if (!UserLogModel::createRegisterLogTableIfNotExists()) {
            echo 'Error: Could not create or access table `' . UserLogModel::getTableName() . '`.' . PHP_EOL;
            return false;
        }
        echo 'Registration logs table name: ' . UserLogModel::getTableName() . $tableInfo . PHP_EOL;

        $tableInfo = UserLogModel::checkLogTable() ? '' : ' (create)';
        if (!ActionLogModel::createActionLogTableIfNotExists()) {
            echo 'Error: Could not create or access table `' . ActionLogModel::getTableName() . '`.' . PHP_EOL;
            return false;
        }
        echo 'Admin action logs table name: ' . ActionLogModel::getTableName() . $tableInfo . PHP_EOL . PHP_EOL;

        return true;
    }

    private function formatDataRows(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (\is_int($key)) {
                $result[] = $value;
            }
        }
        return $result;
    }
}