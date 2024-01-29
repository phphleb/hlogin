<?php

declare(strict_types=1);

namespace App\Bootstrap\Auth\Handlers;

/**
 * When you add this class to your project's `\app\Bootstrap\Auth\Handlers` folder,
 * it adds its own handlers when saving data for the admin panel.
 * Allows you to expand the basic capabilities of the administrative panel,
 * for example, save all changes to logs.
 *
 * При добавлении этого класса в папку `\app\Bootstrap\Auth\Handlers` проекта он добавляет
 * собственные обработчики при сохранении данных для административной панели.
 * Позволяет расширять базовые возможности административной панели, например,
 * сохранять в логи все изменения.
 */
final class AdditionalAdminPanel
{
    /**
     * Additional handler for settings pages.
     *
     * Дополнительный обработчик для страниц с настройками.
     *
     * @param string $pageType - name of the settings page, for example 'settings' or 'email'.
     *                         - название страницы настроек, например 'settings' или 'email'.
     *
     * @param string $lang - page language, for example 'en' or 'ru'.
     *                     - язык страницы, например 'en' или 'ru'.
     *
     * @param array|null $originConfig - an array with settings before changes.
     *                                 - массив с настройками до изменения.
     *
     * @param array|null $newConfig - an array with settings after changes.
     *                              - массив с настройками после изменения.
     *
     * @param int|null $moderatorId - ID of the administrator who changed the data.
     *                               - ID администратора, изменившего данные.
     */
    public function afterChangingSettings(
        string                            $pageType,
        string                            $lang,
        #[\SensitiveParameter] array|null $originConfig,
        #[\SensitiveParameter] array|null $newConfig,
        int|null                          $moderatorId,
    ): void
    {
        // ... //
    }

    /**
     * Additional handler for the page with user data changes.
     *
     * Дополнительный обработчик для страницы с изменением данных пользователя.
     *
     * @param string $lang - page language, for example 'en' or 'ru'.
     *                      - язык страницы, например 'en' или 'ru'.
     *
     * @param array|null $originUserData - an array with user data before changes.
     *                                   - массив с данными пользователя до изменения.
     *
     * @param array|null $newUserData - an array with user data after the change.
     *                                - массив с данными пользователя после изменения.
     *
     * @param int|null $moderatorId - ID of the administrator who changed the data.
     *                              - ID администратора, изменившего данные.
     */
    public function afterChangingUserData(
        string                            $lang,
        #[\SensitiveParameter] array|null $originUserData,
        #[\SensitiveParameter] array|null $newUserData,
        int|null                          $moderatorId,
    ): void
    {
        // ... //
    }
}