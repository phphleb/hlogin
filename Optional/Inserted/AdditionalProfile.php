<?php

declare(strict_types=1);

namespace App\Bootstrap\Auth\Handlers;

use Phphleb\Hlogin\App\Actions\BaseAdditional;

/**
 * When adding this class to the `\app\Bootstrap\Auth\Handlers` folder of the project
 * it adds its own data processing to the process of saving profile data.
 * Allows you to expand basic registration capabilities.
 *
 * При добавлении этого класса в папку `\app\Bootstrap\Auth\Handlers` проекта
 * он добавляет собственную обработку данных к процессу сохранения данных профиля.
 * Позволяет расширять базовые возможности регистрации.
 */
final class AdditionalProfile extends BaseAdditional
{
    protected string $errorMessage = 'Self-made error when parameters do not match.';

    /**
     * The method is executed after the standard fields are checked, but before the new user is saved to the table.
     *
     * Метод выполняется после проверки стандартных полей, но до сохранения нового пользователя в таблицу.
     *
     * @param array $params - parameters from the profile form.
     *                      - параметры из формы профиля.
     * @return bool - if false, returns an error to the form with the message from $this->errorMessage.
     *              - при значении false возвращает в форму ошибку с сообщением из $this->errorMessage.
     */
    #[\Override]
    public function insert(array $params): bool
    {
        // ... //

        return true;
    }

    /**
     * Executed immediately after user data has been changed in the database.
     *
     * Выполняется сразу после изменения данных пользователя в базе данных.
     *
     * @param int $userId - user ID.
     *                    - идентификатор пользователя.
     * @return void
     */
    #[\Override]
    public function afterAction(int $userId)
    {
        // ... //
    }
}