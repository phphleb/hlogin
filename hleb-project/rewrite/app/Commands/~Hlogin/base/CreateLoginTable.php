<?php
declare(strict_types=1);

namespace App\Commands\Hlogin;

use Hleb\Base\Task;
use Phphleb\Hlogin\App\RegType;
use Phphleb\Hlogin\App\Wrappers\CreateTableTask;
use Phphleb\Hlogin\App\Wrappers\CreateUserTask;

class CreateLoginTable  extends Task
{
    /**
     * Create tables for login.
     *
     * Создание таблиц для регистрации.
     */
    protected function run(): int
    {
        if ((new CreateTableTask())->run()) {
            return self::SUCCESS_CODE;
        }
        if ((new CreateUserTask())->run(RegType::REGISTERED_COMMANDANT)) {
            return self::SUCCESS_CODE;
        }
        return self::ERROR_CODE;
    }
}
