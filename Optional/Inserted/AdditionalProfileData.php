<?php

declare(strict_types=1);

namespace App\Bootstrap\Auth\Handlers;

use Phphleb\Hlogin\App\Actions\BaseAdditional;

/**
 * When adding this class to the `\app\Bootstrap\Auth\Handlers` folder of the project
 * it adds data processing to the retrieval of user profile data.
 * Allows you to expand the basic capabilities of user authorization.
 *
 * При добавлении этого класса в папку `\app\Bootstrap\Auth\Handlers` проекта
 * он добавляет обработку данных к получению данных профиля пользователя.
 * Позволяет расширять базовые возможности авторизации пользователя.
 */
final class AdditionalProfileData extends BaseAdditional
{
    protected string $errorMessage = 'Self-made error when parameters do not match.';

    /**
     * The method is executed after checking for user authorization.
     *
     * Метод выполняется после проверки на авторизацию пользователя.
     *
     * @param array $params - current user data.
     *                      - данные текущего пользователя.
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
     * Returns custom user data for display in the profile.
     *
     * Возвращает кастомные данные пользователя для вывода в профиле.
     *
     * @param int $userId - user ID.
     *                    - идентификатор пользователя.     *
     * @return array - a named array with additional form values,
     *                 in which the keys are the names of the fields.     *
     *               - именованный массив с дополнительными значениями формы,
     *                 в котором ключи - это названия полей.
     */
    #[\Override]
    public function afterAction(int $userId): array
    {
        // ... //

        return [];
    }
}