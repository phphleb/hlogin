<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\Container;

use Phphleb\Hlogin\App\CurrentUser;
use Phphleb\Hlogin\App\RegType;
use Phphleb\Hlogin\App\User;

/**
 * Base library class for obtaining data of the currently registered user.
 * Can be used as a container for a framework.
 *
 * Базовый класс библиотеки для получения данных текущего зарегистрированного пользователя.
 * Может быть использован в качестве контейнера для фреймворка.
 */
class Auth
{
    /**
     * Returns the current user's data from the database if he is registered.
     *
     * Возвращает из БД данные текущего пользователя, если он зарегистрирован.
     */
    public function current(): ?User
    {
        $user = CurrentUser::get(cached: false);

        return $user && $user['regtype'] >= RegType::REGISTERED_USER ? new User($user) : null;
    }

    /**
     * Exit authorization for the current user.
     * After exiting, you must be redirected to a page without authorization.
     *
     * Выход из авторизации для текущего пользователя.
     * После выхода необходимо направить на страницу без авторизации.
     */
    public function logout(bool $onAllDevices = false): void
    {
        CurrentUser::exit($onAllDevices);
    }
}