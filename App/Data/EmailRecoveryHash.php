<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Data;

use Hleb\Constructor\Data\Key;

/**
 * Contains methods for validating the user's E-mail recovery code.
 *
 * Содержит методы для валидации кода восстановления пользовательского E-mail.
 */
class EmailRecoveryHash
{
    /**
     * Generates a hash for checking E-mail (96 hexadecimal digits + two hyphens).
     *
     * Генерирует хеш для проверки E-mail (96 шестнадцатеричных цифр + два дефиса).
     *
     * @param int $userId - User ID (constant value, but the checked E-mail can be changed).
     *                    - ID пользователя (постоянное значение, но проверяемый E-mail может быть изменён).
     *
     * @param string $email - User's E-mail (can be changed).
     *                      - E-mail пользователя (может быть изменён).
     */
    public static function generate(#[\SensitiveParameter] int $userId, #[\SensitiveParameter] string $email): string
    {
        return \md5($userId . '-' . $email . '-' . Key::get()) . '-' . \md5(\date('Y-m-d')) . '-' . self::getHash();
    }

    /**
     * Compare the hash stored in the database with the user's hash.
     * Due to the fact that the hash has a storage period
     * of one day (unofficially up to two days),
     * it is not enough to simply compare the values.
     *
     * Сравнение хранящегося в базе данных хеша с пользовательским хешем.
     * Ввиду того, что хеш имеет длительность хранения
     * одни сутки (неофициально до двух суток),
     * недостаточно просто сравнить значения.
     *
     * @param int $userId - User ID (constant value, but the checked E-mail can be changed).
     *                    - ID пользователя (постоянное значение, но проверяемый E-mail может быть изменён).
     *
     * @param string $email - User's E-mail (can be changed).
     *                      - E-mail пользователя (может быть изменён).
     *
     * @param string $hash - verified hash.
     *                     - проверяемый хеш.
     *
     * @param null|string $originHash - original hash from storage.
     *                                - оригинальный хеш из хранилища.
     *
     * @return bool
     */
    public static function check(
        #[\SensitiveParameter] int $userId,
        #[\SensitiveParameter] string $email,
        #[\SensitiveParameter] ?string $hash,
        #[\SensitiveParameter] ?string $originHash
    ): bool
    {
        // If the hash in the storage is not set or the hash is empty. Also if the hashes are not equal.
        // Если не задан хеш в хранилище или пришёл пустой. Также если хеши не равны.
        if (empty($hash) || empty($originHash || $hash !== $originHash)) {
            return false;
        }
        $parts = \explode('-', $hash);
        // If the hash format doesn't match.
        // Если формат хеша не подходит.
        if (\count($parts) !== 3) {
            return false;
        }
        $originParts = \explode('-', $originHash);
        // Checking for a match between the generated key and the user's.
        // Проверка на совпадение сгенерированного ключа и пользовательского.
        if (\end($parts) !== \end($originParts)) {
            return false;
        }
        // Checking for a match of the user data being checked.
        // Проверка на совпадение проверяемых данных пользователя.
        if ($parts[0] !== \md5($userId . '-' . $email . '-' . Key::get())) {
            return false;
        }
        $dateList = [
            \md5(\date('Y-m-d')),
            \md5(\date('Y-m-d', \strtotime('+1 day'))),
            \md5(\date('Y-m-d', \strtotime('-1 day'))),
        ];
        // Check key expiration date.
        // Проверка даты истечения ключа.
        if (!\in_array($originParts[1], $dateList)) {
            return false;
        }

        return true;
    }

    /**
     * Part of the hash randomly generated.
     *
     * Часть хеша сгенерированная рандомно.
     */
    private static function getHash(): string
    {
        try {
            $key = \bin2hex(\random_bytes(100));
        } catch (\Exception) {
            $key = \str_shuffle(\mt_rand() . '-' . \microtime());
        }
        return \md5($key);
    }

}