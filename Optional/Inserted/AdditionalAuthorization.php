<?php

declare(strict_types=1);

namespace App\Bootstrap\Auth\Handlers;

use Phphleb\Hlogin\App\Actions\BaseAdditional;

/**
 * When adding this class to the `\app\Bootstrap\Auth\Handlers` folder of the project
 * it adds data processing to the authorization process.
 * Allows you to expand the basic capabilities of user authorization.
 *
 * При добавлении этого класса в папку `\app\Bootstrap\Auth\Handlers` проекта
 * он добавляет обработку данных к процессу авторизации.
 * Позволяет расширять базовые возможности авторизации пользователя.
 */
final class AdditionalAuthorization extends BaseAdditional
{
    protected string $errorMessage = 'Self-made error when parameters do not match.';

    /**
     * The method is executed after the standard fields are checked.
     *
     * Метод выполняется после проверки стандартных полей.
     *
     * @param array $params - parameters from the login form.
     *                      - параметры из формы входа.
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
     * Executed immediately after user authorization.
     *
     * Выполняется сразу после авторизации пользователя.
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