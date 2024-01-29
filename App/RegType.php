<?php

namespace Phphleb\Hlogin\App;

final class RegType
{
    // Banned
    // Забаненный
    public const BANNED_USER = -2;

    // Deleted
    // Удалённый
    public const DELETED_USER = -1;

    // Undefined user
    // Не определённый
    public const UNDEFINED_USER = 0;

    // Logged in but not confirmed Email
    // Не подтвердивший E-mail
    public const PRIMARY_USER = 1;

    // Registered
    // Зарегистрированный
    public const REGISTERED_USER = 2;

    // Arbitrary first-level registered user
    // Произвольный зарегистрированный пользователь первого уровня
    public const FIRST_LEVEL_REG_USER = 3;

    // Arbitrary second-level registered user
    // Произвольный зарегистрированный пользователь второго уровня
    public const SECOND_LEVEL_REG_USER = 4;

    // Arbitrary third-level registered user
    // Произвольный зарегистрированный пользователь третьего уровня
    public const THIRD_LEVEL_REG_USER = 5;

    // Administrator
    // Администратор
    public const REGISTERED_ADMIN = 10;

    // Super admin
    // Супер-админ
    public const REGISTERED_COMMANDANT = 11;

    public const RULES = ['=', '>=', '<=', '<>', '!=', '>', '<'];

    /**
     * Checking for registration by integer user type and comparison sign.
     *
     * Проверка на регистрацию по числовому типу пользователя и знаку сравнения.
     *
     * getRegType(UserRegistration::REGISTERED_COMMANDANT, '='),
     * getRegType(UserRegistration::REGISTERED_USER)
     * or getRegType([UserRegistration::BANNED_USER, UserRegistration::DELETED_USER])
     *
     * @param integer|array $type
     * @param string|null $cp
     * @return bool
     */
    public static function check(int|array $type, string|null $cp = '>='): bool
    {
        $t = AuthUser::getNumericType();
        return ((\is_integer($type) && (
                    ($cp == '=' && $t === $type) ||
                    ($cp == '>=' && $t >= $type) ||
                    ($cp == '<=' && $t >= $type) ||
                    ($cp == '<>' && $t != $type) ||
                    ($cp == '!=' && $t != $type) ||
                    ($cp == '>' && $t > $type) ||
                    ($cp == '<' && $t < $type)
                )) || (\is_array($type) && \in_array($t, $type, true))
        );
    }
}