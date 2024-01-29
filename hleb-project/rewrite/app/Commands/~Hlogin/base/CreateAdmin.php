<?php
declare(strict_types=1);

namespace App\Commands\Hlogin;

use Hleb\Base\Task;
use Phphleb\Hlogin\App\RegType;
use Phphleb\Hlogin\App\Wrappers\CreateUserTask;

class CreateAdmin extends Task
{
    /**
     * Create a new administrator.
     *
     * Создание нового администратора.
     */
    protected function run(): int
    {
        if ((new CreateUserTask())->run(RegType::REGISTERED_COMMANDANT)) {
            return self::SUCCESS_CODE;
        }
        return self::ERROR_CODE;
    }
}
