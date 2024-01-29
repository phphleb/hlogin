<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App;

final class AuthUser
{
     /**
     * Checking for registration of the current user, returns a numeric type.
     *
     * Проверка на регистрацию текущего пользователя, возвращает числовой тип.
     */
    public static function getNumericType(): int
    {
        $user = CurrentUser::get(cached: false);

        return $user ? (int)$user['regtype'] : 0;
    }

    /**
     * Checking for a deleted or banned user.
     *
     * Проверка на удаленного или забаненного пользователя.
     */
    public static function checkDeleted(): bool
    {
        $user = CurrentUser::get(cached: false);

        return (int)$user['regtype'] === RegType::DELETED_USER;
    }

    /**
     * Check for any unblocked user.
     *
     * Проверка на любого незаблокированного пользователя.
     */
    public static function checkActiveUser(): bool
    {
        $user = CurrentUser::get(cached: false);

        return (int)$user['regtype'] >= RegType::UNDEFINED_USER;
    }

    /**
     * Checking for any registered user, including those who have
     * not confirmed their email.
     *
     * Проверка на любого зарегистрированного пользователя,
     * в том числе не подтвердившего E-mail.
     */
    public static function checkPrimaryAndHigher(): bool
    {
        $user = CurrentUser::get(cached: false);

        return (int)$user['regtype'] >= RegType::PRIMARY_USER;
    }

    /**
     * Проверка на любого зарегистрированного пользователя, подтвердившего E-mail.
     *
     * Check for any registered user who has confirmed their email.
     */
    public static function checkRegisterAndHigher(): bool
    {
        $user = CurrentUser::get(cached: false);

        return (int)$user['regtype'] >= RegType::REGISTERED_USER;
    }

    /**
     * Returns the current user's data from the database if he is registered.
     *
     * Возвращает из БД данные текущего пользователя, если он зарегистрирован.
     */
    public static function current(): ?User
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
    public static function logout(bool $onAllDevices = false): void
    {
        CurrentUser::exit($onAllDevices);
    }
}