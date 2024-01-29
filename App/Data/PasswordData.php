<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Data;

use Hleb\Constructor\Data\Key;
use Hleb\Helpers\Abracadabra;
use Phphleb\Hlogin\App\Models\UserModel;
use Phphleb\Hlogin\App\RegData;

/**
 * A set of methods for processing a user's password.
 *
 * Комплекс методов по обработке пароля пользователя.
 */
class PasswordData
{
    /**
     * Returns the generated hash for the password.
     *
     * Возвращает сгенерированный хеш для пароля.
     */
    public static function generateHash(#[\SensitiveParameter] string $password): string
    {
        return \password_hash($password . Key::get(), PASSWORD_DEFAULT);
    }

    /**
     * Generates a random password, the length must match the pattern:
     *
     * Генерирует рандомный пароль, длина должна соответствовать паттерну:
     *
     * @see RegData::PASSWORD_PATTERN
     */
    public static function generateRandomPassword(int $length): string
    {
        return Abracadabra::generate($length);
    }

    /**
     * Returns user data by E-mail and password or false.
     *
     * Возвращает данные пользователя по E-mail и паролю или false.
     */
    public static function getUserByPassword(
        #[\SensitiveParameter] string $email,
        #[\SensitiveParameter] string $password,
        array $returnCells = []
    ): false|array
    {
        empty($returnCells) and $returnCells = RegData::USER_TABLE_CELLS;
        \in_array('password', $returnCells) or $returnCells = \array_merge($returnCells, ['password']);
        $user = UserModel::getCells('email', $email, $returnCells);
        if (!$user || !\password_verify($password . Key::get(), $user['password'])) {
            return false;
        }

        return $user;
    }

    /**
     * Checking the password format, a positive result
     * does not mean that the password is correct.
     *
     * Проверка формата пароля, положительный результат
     * не означает, что пароль правильный.
     */
    public static function validatePasswordString(#[\SensitiveParameter] string $password): bool
    {
        return \preg_match(RegData::PASSWORD_PATTERN, $password) !== false;
    }
}