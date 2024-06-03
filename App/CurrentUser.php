<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App;

use Hleb\Static\Cookies;
use Hleb\Static\Session;
use Hleb\Static\Settings;
use Hleb\Static\System;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Data\CookieData;
use Phphleb\Hlogin\App\Data\SessionStorage;
use Phphleb\Hlogin\App\Models\UserLogModel;
use Phphleb\Hlogin\App\Models\UserModel;

/**
 * Class for manipulating the data of the current user.
 * Receiving and updating data.
 * Not suitable for use in console commands.
 *
 * Класс для манипуляций с данными текущего пользователя.
 * Получение и обновление данных.
 * Не подходит для использования в консольных командах.
 */
final class CurrentUser
{
    private const SESSION_FULL_KEY = 'HLOGIN_USER_SES_KEY';

    private const COOKIE_NAME = 'hlogin_v10n';

    private static bool|null|array $user = false;

    private static ?string $requestId = null;

    /**
     * Returns the data of the current user.
     * The data is cached and can be changed in the update() method.
     *
     * Возвращает данные текущего пользователя.
     * Данные кешируются и могут быть изменены в методе update().
     */
    public static function get(bool $cached = true): array|null
    {
        self::init();
        // Return user data from memory.
        // Возвращаются данные пользователя из памяти.
        if ($cached && self::$user !== false) {
            return self::$user;
        }
        // Checking the session for the presence of an authorization code.
        // Проверка сессии на наличие кода авторизации.
        $parts = self::sessionSplitKey((string)Session::get(self::SESSION_FULL_KEY));
        $key = $parts['id'];
        $timeOut = (int)$parts['timeout'];
        if ($key && $timeOut) {
            if (SessionStorage::searchValidTtl($timeOut)) {
                $user = SessionStorage::getUserByKey($key, (int)$timeOut);
                // Verifies that the user exists and that their session key has not changed.
                // Проверка существования пользователя и что его сессионный ключ не менялся.
                if ($user && $user['sessionkey'] === Session::get(self::SESSION_FULL_KEY)) {
                    return self::$user = $user;
                }
            }
            // If the code from the session did not fit, then null is returned.
            // Если код из сессии не подошел, то возвращается null.
            return self::$user = null;
        }
        // Checking cookies for the saved authorization code.
        // Проверка cookies на наличие сохраненного кода авторизации.
        $key = Cookies::get(self::COOKIE_NAME)->value();
        if ($key) {
            if (CookieData::searchValidKey($key)) {
                $user = CookieData::getUserByKey($key);
                if ($user) {
                    // Saving to the user's session to take from it next time.
                    // Сохранение в сессию пользователя, чтобы в следующий раз взять из неё.
                    Session::set(self::SESSION_FULL_KEY, $key);
                    return self::$user = $user;
                }
            }
        }
        return self::$user = null;
    }

    /**
     * Update the data of an existing current user.
     * The array of updated data must contain the names
     * and values of the changed fields.
     *
     * Обновление данных существующего текущего пользователя.
     * Массив обновляемых данных должен содержать названия
     * и значения изменяемых полей.
     */
    public static function update(int $userId, array $data): void
    {
        self::init();
        $user = self::get();

        if (!$user || ($user['id'] !== $userId) || (isset($data['id']) && $data['id'] !== $userId)) {
            throw new \RuntimeException('The ID of the user being changed does not match.');
        }
        $isChanged = false;
        foreach($user as $name => $cell) {
            if (\array_key_exists($name, $data) && $cell !== $data[$name]) {
                $isChanged = true;
            }
        }
        // If the data is identical, then nothing is stored.
        // Если данные идентичны, то ничего не сохраняется.
        if (!$isChanged) {
            return;
        }
        $previous = UserModel::getUserViaId($userId);
        if (!$previous) {
            throw new \RuntimeException('The update method cannot be applied when user data is missing.');
        }
        unset($previous['id']);
        UserModel::setCells('id', $userId, $previous);
        // If the session key is changed, it must be reassigned.
        // Если ключ сессии изменён, его нужно переназначить.
        if (\array_key_exists('sessionkey', $previous)) {
            Session::set(self::SESSION_FULL_KEY, $previous['sessionkey']);
            if (Cookies::get(self::COOKIE_NAME)->value()) {
                Cookies::set(self::COOKIE_NAME, $previous['sessionkey']);
            }
        }
        UserLogModel::copyDataToLog($userId, UserLogModel::UPDATE_ACTION, 'Update the current user\'s data');
        // Since the data has been changed, the cache is reset.
        // Так как данные изменены, то кеш обнуляется.
        self::$user = false;
    }


    /**
     * Data update only in session.
     *
     * Обновление данных только в сессии.
     */
    public static function updateSessionKey(string $key): void
    {
        Session::set(self::SESSION_FULL_KEY, $key);
    }

    /**
     * Data update only in session.
     *
     * Обновление данных только в Cookies.
     */
    public static function updateCookiesKey(string $key): void
    {
        $config = ConfigStorage::getConfig();
        Cookies::set(self::COOKIE_NAME, $key, ['expires' => time() + $config['registration']['session-duration']]);
    }

    /**
     * Logging out.
     *
     * Выход из авторизации.
     */
    public static function exit(bool $onAllDevices = false): void
    {
        if ($onAllDevices) {
            $user = self::get();
            UserModel::setCells('id', $user['id'], ['sessionkey' => null]);
        }
        Cookies::clear();
        Session::clear();
        if (!Settings::isAsync()) {
            session_destroy();
        }
        self::$requestId = null;
        self::$user = false;
    }

    /**
     * Parsing the session key into its component parts.
     * In case the key is equal to the empty string, each part returned is null.
     *
     * Разбор сессионного ключа по составным частям.
     * В случае, если ключ равен пустой строке, каждая возвращаемая часть равна null.
     */
    public static function sessionSplitKey(string $key): array
    {
        $p = \explode('$', $key);

        return ['id' => $p[1] ?? null, 'hash' => $p[2] ?? null, 'timeout' => $p[3] ?? null];
    }

    /**
     * For an asynchronous request, the cache needs to be refreshed.
     *
     * Для асинхронного запроса необходимо обновить кеш.
     */
    private static function init(): void
    {
        $requestId = System::getRequestId();
        if (self::$requestId !== $requestId) {
            self::$requestId = $requestId;
            self::$user = false;
        }
    }
}
