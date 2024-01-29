<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App;

use DateTimeImmutable;

final class User
{
    readonly public int $id;

    readonly public int $regtype;

    readonly public bool $confirm;

    readonly public ?string $email;

    readonly public ?string $login;

    readonly public ?string $name;

    readonly public ?string $surname;

    readonly public ?string $phone;

    readonly public ?string $address;

    readonly public bool $subscription;

    readonly public ?string $regdate;


    private array $array;


    public function __construct(array $user)
    {
        $data = [
            'id' => $user['id'],
            'regtype' => $user['regtype'],
            'confirm' => (bool)$user['confirm'],
            'email' => $user['email'],
            'login' => $user['login'],
            'name' => $user['name'],
            'surname' => $user['surname'],
            'phone' => $user['phone'],
            'address' => $user['address'],
            'subscription' => (bool)$user['subscription'],
            'regdate' => $user['regdate'],
        ];

        $this->id = $data['id'];
        $this->regtype = $data['regtype'];
        $this->confirm = $data['confirm'];
        $this->email = $data['email'];
        $this->login = $data['login'];
        $this->name = $data['name'];
        $this->surname = $data['surname'];
        $this->phone = $data['phone'];
        $this->address = $data['address'];
        $this->subscription = $data['subscription'];
        $this->regdate = $data['regdate'];

        $this->array = $data;
    }

    /**
     * Returns the administrator attribute.
     *
     * Возвращает признак администратора.
     */
    public function isAdmin(): bool
    {
        return $this->regtype >= RegType::REGISTERED_ADMIN;
    }

    /**
     * Returns the attribute of the super-administrator.
     *
     * Возвращает признак супер-администратора.
     */
    public function isSuperAdmin(): bool
    {
        return $this->regtype >= RegType::REGISTERED_COMMANDANT;
    }

    /**
     * Returns the attribute of a verified user.
     *
     * Возвращает признак подтверждённого пользователя.
     */
    public function isConfirm(): bool
    {
        return $this->confirm;
    }

    /**
     * The user has completed initial registration,
     * but has not confirmed his email.
     *
     * Пользователь прошёл первичную регистрацию,
     * но не подтвердил E-mail.
     */
    public function isPreRegistered(): bool
    {
        return $this->regtype === RegType::PRIMARY_USER;
    }

    /**
     * Any authorized user.
     *
     * Любой авторизованный пользователь.
     */
    public function isAuth(): bool
    {
        return $this->regtype >= RegType::PRIMARY_USER;
    }

    /**
     * Returns the numeric type of the user registration value.
     *
     * Возвращает числовой тип значения регистрации пользователя.
     */
    public function getRegType(): int
    {
        return $this->regtype;
    }

    /**
     * Returns the user's email.
     *
     * Возвращает E-mail пользователя.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Returns the user ID.
     *
     * Возвращает ID пользователя.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the user's login.
     *
     * Возвращает логин пользователя.
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * Returns the username.
     *
     * Возвращает имя пользователя.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Returns the user's last name.
     *
     * Возвращает фамилию пользователя.
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Returns the user's address.
     *
     * Возвращает адрес пользователя.
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * Returns a sign of subscription to news.
     *
     * Возвращает признак подписки на новости.
     */
    public function isSubscription(): bool
    {
        return $this->subscription;
    }

    /**
     * Returns the registration date as an object.
     *
     * Возвращает дату регистрации в виде объекта.
     */
    public function getRegDate(): DateTimeImmutable|false
    {
        return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->regdate);
    }

    /**
     * Returns user data as a named array.
     *
     * Возвращает данные пользователя в виде именованного массива.
     */
    public function asArray(): array
    {
      return $this->array;
    }
}