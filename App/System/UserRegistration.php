<?php

namespace Phphleb\Hlogin\App\System;

use Hleb\Constructor\Handlers\Key;
use Hleb\Constructor\Handlers\Request as Request;
use Phphleb\Hlogin\App\HloginUserModel;


final class UserRegistration
{
    // Banned
    // Забаненный
    const BANNED_USER = -2;

    // Deleted
    // Удалённый
    const DELETED_USER = -1;

    // Undefined user
    // Не определённый
    const UNDEFINED_USER = 0;

    // Logged in but not confirmed Email
    // Не подтвердивший E-mail
    const PRIMARY_USER = 1;

    // Registered
    // Зарегистрированный
    const REGISTERED_USER = 2;

    // Arbitrary first-level registered user
    // Произвольный зарегистрированный пользователь первого уровня
    const VARIABLE_FIRST_LEVEL_REG_USER = 3;

    // Arbitrary second-level registered user
    // Произвольный зарегистрированный пользователь второго уровня
    const VARIABLE_SECOND_LEVEL_REG_USER = 4;

    // Arbitrary third-level registered user
    // Произвольный зарегистрированный пользователь третьего уровня
    const VARIABLE_THIRD_LEVEL_REG_USER = 5;

    // Administrator
    // Администратор
    const REGISTERED_ADMIN = 10;

    // Superadmin
    // Суперадмин
    const REGISTERED_COMANDANTE = 11;

    const CONTENT_TYPE = 'registration';

    static protected $type = null;

    static protected $email = null;

    static protected $id = null;

    static protected $sessionkey = null;

    private function __construct(){}

    /**
     * Checking for a user who is marked as deleted or banned.
     * Проверка на пользователя, который помечен удалённым или забанен.
     * @return bool
     */
    static public function checkDeleted() {
        return self::getNumericType() < self::UNDEFINED_USER;
    }

    /**
     * Check for any unblocked user.
     * Проверка на любого незаблокированного пользователя.
     * @return bool
     */
    static public function checkActiveUser() {
        return self::getNumericType() >= self::UNDEFINED_USER;
    }

    /**
     * Checking for general registration, without a confirmed E-mail and higher.
     * Проверка на любую регистрацию, без подтвержденного E-mail и выше.
     * @return bool
     */
    static public function checkPrimaryAndHigher() {
        return self::getNumericType() >= self::PRIMARY_USER;
    }

    /**
     * Checking for registered and higher.
     * Проверка на зарегистрированного и выше.
     * @return bool
     */
    static public function checkRegisterAndHigher() {
        return self::getNumericType() >= self::REGISTERED_USER;
    }

    /**
     * Checking for a registered user of the first level and higher.
     * Проверка на зарегистрированного пользователя первого уровня и выше.
     * @return bool
     */
    static public function checkFirstLevelRegisterAndHigher() {
        return self::getNumericType() >= self::VARIABLE_FIRST_LEVEL_REG_USER;
    }

    /**
     * Checking for a registered user of the second level and higher.
     * Проверка на зарегистрированного пользователя второго уровня и выше.
     * @return bool
     */
    static public function checkSecondLevelRegisterAndHigher() {
        return self::getNumericType() >= self::VARIABLE_SECOND_LEVEL_REG_USER;
    }

    /**
     * Checking for a registered user of the third level and higher.
     * Проверка на зарегистрированного пользователя третьего уровня и выше.
     * @return bool
     */
    static public function checkThirdLevelRegisterAndHigher() {
        return self::getNumericType() >= self::VARIABLE_THIRD_LEVEL_REG_USER;
    }

    /**
     * Checking for administrator and higher.
     * Проверка на администратора и выше.
     * @return bool
     */
    static public function checkAdminAndHigher() {
        return self::getNumericType() >= self::REGISTERED_ADMIN;
    }

    /**
     * Checking for registration, without a confirmed E-mail.
     * Проверка регистрацию, без подтвержденного E-mail.
     * @return bool
     */
    static public function checkPrimaryOnly() {
        return self::getNumericType() === self::PRIMARY_USER;
    }

    /**
     * Checking for registered.
     * Проверка на зарегистрированного.
     * @return bool
     */
    static public function checkRegisterOnly() {
        return self::getNumericType() === self::REGISTERED_USER ;
    }

    /**
     * Checking for a registered user of the first level.
     * Проверка на зарегистрированного пользователя первого уровня.
     * @return bool
     */
    static public function checkFirstLevelRegisterOnly() {
        return self::getNumericType() === self::VARIABLE_FIRST_LEVEL_REG_USER;
    }

    /**
     * Checking for a registered user of the second level.
     * Проверка на зарегистрированного пользователя второго уровня.
     * @return bool
     */
    static public function checkSecondLevelRegisterOnly() {
        return self::getNumericType() === self::VARIABLE_SECOND_LEVEL_REG_USER;
    }

    /**
     * Checking for a registered user of the third level.
     * Проверка на зарегистрированного пользователя третьего уровня.
     * @return bool
     */
    static public function checkThirdLevelRegisterOnly() {
        return self::getNumericType() === self::VARIABLE_THIRD_LEVEL_REG_USER;
    }

    /**
     * Checking for administrator.
     * Проверка на администратора.
     * @return bool
     */
    static public function checkAdminOnly() {
        return self::getNumericType() === self::REGISTERED_ADMIN;
    }

    /**
     * Checking for super administrator.
     * Проверка на суперадминистратора.
     * @return bool
     */
    static public function checkComandante() {
        return self::getNumericType() === self::REGISTERED_COMANDANTE;
    }

    /**
     * Checking for registration of the current user, returns a numeric type.
     * Проверка на регистрацию текущего пользователя, возвращает числовой тип.
     * @return integer
     */
    static public function getNumericType() {
        return self::createType();
    }


    /**
     * Проверка на существование E-mail
     * @return bool|string
     */
    static public function checkEmailAddress() {
        if(isset($_SESSION['HLOGIN_REGISTRATION_ID']) && is_array(self::updateData('sessionkey', $_SESSION['HLOGIN_REGISTRATION_ID']))) {
            return self::$email;
        }
        $regActive = Request::getCookie('HLOGIN_VERIFICATION_INDEX');
        if (!empty($regActive) && self::updateData('sessionkey', $regActive) && is_string(self::$email)) {
            return self::$email;
        }
        return false;
    }

    /**
     * Возвращает ID, если пользователь зарегистрирован
     * @return null|int
     */
    static public function getUserId() {
        if (self::$id) {
            return self::$id;
        }
        $userData = HloginUserModel::getCells('sessionkey', $_SESSION['HLOGIN_REGISTRATION_ID'] ?? null, ['id']);
        self::$id = $userData['id'] ?? null;
        return self::$id;
    }

    /**
     * Checking for registration by integer user type and comparison sign.
     * Проверка на регистрацию по числовому типу пользователя и знаку сравнения.
     * @param integer|array $type
     * getRegType(UserRegistration::REGISTERED_COMANDANTE, '='),
     * getRegType(UserRegistration::REGISTERED_USER)
     * or getRegType([UserRegistration::BANNED_USER, UserRegistration::DELETED_USER])
     *
     * @param string|null $cp
     * @return bool
     */
    static public function getRegType($type, $cp = '>=') {
        $t = self::getNumericType();
        if ((is_integer($type) && (
                    ($cp == '=' && $t == $type) ||
                    ($cp == '>=' && $t >= $type) ||
                    ($cp == '<=' && $t >= $type) ||
                    ($cp == '<>' && $t != $type) ||
                    ($cp == '!=' && $t != $type) ||
                    ($cp == '>' && $t > $type) ||
                    ($cp == '<' && $t < $type)
                )) || (is_array($type) && in_array($t, $type))
        ) {
            return true;
        }
        return false;
    }

    // Устанавливает E-mail без записи в базу данных
    static public function setEmailAddress(string $value) {
        self::$email = $value;
    }

    // Устанавливает тип без записи в базу данных
    static public function setType($value = self::UNDEFINED_USER) {
        self::$type = $value;
    }

    // Возвращает E-mail
    static public function getCurrentEmail() {
        return UserRegistration::checkEmailAddress();
    }

    // Реализует выход
    static public function exit() {
        if(!isset($_SESSION)) session_start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    static private function createType() {
        if (is_null(self::$type)) {
            self::$type = self::checkType();
            self::$type = !empty(self::$type) ? intval(self::$type) : 0;
        }
        return self::$type;
    }

    static private function checkType() {
        if (!empty($_SESSION['HLOGIN_REGISTRATION_ID']) && is_array(self::updateData('sessionkey', $_SESSION['HLOGIN_REGISTRATION_ID']))) {
            return self::$type;
        }
        $regActive = Request::getCookie('HLOGIN_VERIFICATION_INDEX');
        if (empty($_SESSION['HLOGIN_REGISTRATION_ID']) && !empty($regActive) && is_array(self::updateData('sessionkey', $regActive))) {
            $_SESSION['HLOGIN_REGISTRATION_ID'] = self::$sessionkey;
            return self::$type;
        }
        return self::UNDEFINED_USER;
    }

    static private function updateData(string $name, string $value) {
        $data = HloginUserModel::getCells($name, $value, ['id', 'email', 'regtype', 'sessionkey']);
        if (!empty($data['email'])) {
            self::$type = $data['regtype'];
            self::$email = $data['email'];
            self::$id = $data['id'];
            self::$sessionkey = $data['sessionkey'];
            return $data;
        }
        return false;
    }


}

