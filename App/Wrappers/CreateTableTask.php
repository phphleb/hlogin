<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Wrappers;

class CreateTableTask extends BaseCreate
{
    public function run(): bool
    {
        if (!$this->init()) {
            echo 'Error: Could not create or access table.' . PHP_EOL;
            return false;
        }
        echo 'Table created successfully!' . PHP_EOL;

        return true;
    }
}