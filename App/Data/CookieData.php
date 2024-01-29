<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Data;

use Hleb\Helpers\Abracadabra;
use Phphleb\Hlogin\App\Models\UserModel;

/**
 * A set of methods for processing the user's session key.
 *
 * Комплекс методов по обработке сессионного ключа пользователя.
 */
class CookieData
{
    /**
     * Key generation according to the unique user ID.
     *
     * Генерация ключа согласно уникальному ID пользователя.
     *
     * @param int $ttl - number of seconds of key activity from the present moment.
     *                 - количество секунд активности ключа от настоящего момента.
     */
   public static function generateKey(#[\SensitiveParameter] int $userId, #[\SensitiveParameter] int $ttl): string
   {
      return 'SES$' . $userId . '$N' . Abracadabra::generate(200) . '$' . ($ttl + \time()) . '==';
   }

    /**
     * Checking if a key exists.
     *
     * Проверка ключа на существование.
     *
     * @return false|int - returns false if key or user data does not match
     *                   - возвращает false при несовпадении ключа или данные пользователя.
     */
   public static function getUserByKey(#[\SensitiveParameter] string $key): false|array
   {
      $userId = self::searchUserId($key);
      if (!$userId) {
          return false;
      }
      $user = UserModel::getCells('id', $userId);
      if (!$user) {
          return false;
      }
      if ($key === $user['sessionkey']) {
          return $user;
      }
      return false;
   }

    /**
     * Finding the user ID in the key.
     * Receiving a positive response does not mean that the key itself is valid.
     *
     * Нахождение в ключе ID пользователя.
     * Получение положительного ответа не означает, что сам ключ валидный.
     */
   public static function searchUserId(#[\SensitiveParameter] string $key): false|int
   {
      if (!\str_starts_with($key, 'SES$')) {
          return false;
      }
      $parts = \explode('$', \rtrim($key, '='));
      if (empty($parts[1]) || !\is_numeric($parts[1])) {
          return false;
      }
      return (int)$parts[1];
   }

    /**
     * Checking the activity of the key by the time of its existence.
     * Receiving a positive response does not mean that the key itself is valid.
     *
     * Проверка активности ключа по времени его существования.
     * Получение положительного ответа не означает, что сам ключ валидный.
     */
   public static function searchValidKey(#[\SensitiveParameter] string $key): bool
   {
       return (int)self::getExpireTimeFromKey($key) >= \time();
   }

    /**
     *  Getting the expiration time from the session key.
     * Receiving a positive response does not mean that the key itself is valid.
     *
     * Получение времени истечения действия из сессионного ключа.
     * Получение положительного ответа не означает, что сам ключ валидный.
     */
   public static function getExpireTimeFromKey(#[\SensitiveParameter] string $key): int|false
   {
       if (!\str_starts_with($key, 'SES$')) {
           return false;
       }
       $parts = \explode('$', \rtrim($key, '='));
       $time = \end($parts);
       if (empty($time) || !\is_numeric($time)) {
           return false;
       }
       return (int)$time;
   }
}