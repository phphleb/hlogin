<?php

declare(strict_types=1);

namespace App\Bootstrap\Auth\Handlers;

use Phphleb\Hlogin\App\Actions\BaseAdditional;

/**
 * When adding this class to the `\app\Bootstrap\Auth\Handlers` folder of the project
 *  it adds its own data processing to the authorization process.
 * Allows you to expand basic registration capabilities.
 *
 * При добавлении этого класса в папку `\app\Bootstrap\Auth\Handlers` проекта
 * он добавляет собственную обработку данных к процессу авторизации.
 * Позволяет расширять базовые возможности регистрации.
 */
final class AdditionalRegister extends BaseAdditional
{
    protected string $errorMessage = 'Self-made error when parameters do not match.';

    /**
     * The method is executed after the standard fields are checked, but before the new user is saved to the table.
     *
     * Метод выполняется после проверки стандартных полей, но до сохранения нового пользователя в таблицу.
     *
     * @param array $params - parameters from the registration form.
     *                      - параметры из формы регистрации.
     * @return bool - if false, returns an error to the form with the message from $this->errorMessage.
     *              - при значении false возвращает в форму ошибку с сообщением из $this->errorMessage.
     */
    #[\Override]
    public function insert(array $params): bool
    {
        if (isset($params['value']['promocode'])) {
            // Actions with promotional code.
            // Действия с промокодом.
        }

        return true;
    }

    /**
     * Executed immediately after saving a new user to the database.
     *
     * Выполняется сразу после сохранения нового пользователя в базу данных.
     *
     * @param int $userId - identifier of the new created user.
     *                    - идентификатор нового созданного пользователя.
     * @return void
     */
    #[\Override]
    public function afterAction(int $userId)
    {
        // ... //
    }
}