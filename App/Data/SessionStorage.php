<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Data;

use Phphleb\Hlogin\App\Models\UserModel;

/**
 * A set of methods for processing user authorization data.
 *
 * Комплекс методов по обработке авторизационных данных пользователя.
 */
class SessionStorage
{
    /**
     * Getting a user by ID and session activity time (UNIX time).
     *
     * Получение пользователя по ID и времени активности сессии (UNIX time).
     */
   public static function getUserByKey(#[\SensitiveParameter] string $userId, int $time): false|array
   {
      $active = self::searchValidTtl($time);
      if (!$active) {
          return false;
      }
      $user = UserModel::getUserViaId((int)$userId);
      if (!$user) {
          return false;
      }

      return $user;
   }

    /**
     * Saving the key to the database.
     *
     * Сохранение ключа в базу данных.
     */
   public static function saveKey(#[\SensitiveParameter] int $userId, #[\SensitiveParameter] string $key): bool
   {
       return UserModel::setCells('id', $userId, ['sessionkey' => $key]);
   }

    /**
     * Checking the activity of the session by the time of its existence.
     *
     * Проверка активности сессии по времени eё существования.
     */
    public static function searchValidTtl(int $time): bool
    {
        return $time >= \time();
    }
}
