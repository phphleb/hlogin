<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Wrappers;

use Phphleb\Hlogin\App\Data\PasswordData;
use Phphleb\Hlogin\App\Helpers\EmailHelper;
use Phphleb\Hlogin\App\Helpers\ReadlineHelper;
use Phphleb\Hlogin\App\Models\UserLogModel;
use Phphleb\Hlogin\App\Models\UserModel;
use Phphleb\Hlogin\App\RegData;
use Phphleb\Hlogin\App\RegType;

class CreateUserTask extends BaseCreate
{
    public function run(int $level): bool
    {
        if (!$this->init()) {
            return false;
        }
        $email = $this->getEmail();
        $email = EmailHelper::convert($email);
        $name = match ($level) {
            RegType::REGISTERED_ADMIN => 'Admin',
            RegType::REGISTERED_COMMANDANT => 'SuperAdmin',
            default => 'User',
        };
        $passwordHash = PasswordData::generateHash(self::getPassword());
        $result = UserModel::createEmptyUser($email, $name, $passwordHash);

        if ($result && $user = UserModel::checkEmailAddressAndGetData($email)) {
            UserLogModel::copyDataToLog($user['id'], UserLogModel::CREATE_ACTION, 'Creation via console command');
            echo 'New user (ID ' . $user['id'] . ') successfully created!' . PHP_EOL;
            return true;
        }

        echo 'Failed to create new user :(' . PHP_EOL;
        return false;
    }

    private function getEmail(): string
    {
        $email = ReadlineHelper::action(
            'Enter an existing E-mail',
            'Error: Wrong format [E-mail]',
            RegData::EMAIL_PATTERN,
        );
        if (UserModel::checkEmailAddressAndGetData($email)) {
            echo 'Not Allowed: The user with this E-mail is already registered.' . PHP_EOL;
            return $this->getEmail();
        }
        return $email;
    }

    private function getPassword(): string
    {
        $password = ReadlineHelper::action(
            'Enter password',
            'Error: Wrong format [password]',
            RegData::PASSWORD_PATTERN,
        );

        return $password;
    }
}