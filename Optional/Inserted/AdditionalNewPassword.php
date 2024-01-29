<?php

declare(strict_types=1);

namespace App\Bootstrap\Auth\Handlers;

use Phphleb\Hlogin\App\Actions\BaseAdditional;

/**
 * When adding this class to the `\app\Bootstrap\Auth\Handlers` folder of the project
 * it adds its own data processing to the link's password recovery process.
 * Allows you to expand the basic capabilities of user authorization.
 *
 * При добавлении этого класса в папку `\app\Bootstrap\Auth\Handlers` проекта
 * он добавляет собственную обработку данных к процессу восстановления пароля по ссылке.
 * Позволяет расширять базовые возможности авторизации пользователя.
 */
final class AdditionalNewPassword extends BaseAdditional
{
    protected string $errorMessage = 'Self-made error when parameters do not match.';

    /**
     * The method is executed after the standard fields are checked.
     *
     * Метод выполняется после проверки стандартных полей.
     *
     * @param array $params - parameters from the form.
     *                      - параметры из формы.
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
     * Executed after processing of user data is complete.
     *
     * Выполняется после завершения обработки пользовательских данных.
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