<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Data;

use Phphleb\Spreader\Src\TransferInterface;
use Phphleb\Spreader\Transfer;

class ConfigStorage
{
    private const ERROR = 'Parameter `%s` not specified in library `phphleb/hlogin` configuration.';

    private static ?TransferInterface $transfer = null;

    /**
     * Getting all data from the library configuration.
     *
     * Получение всех данных из конфигурации библиотеки.
     */
    public static function getConfig(): ?array
    {
        return self::transfer()->get('phphleb/hlogin', 'config');
    }

    /**
     * Saving all data for the library configuration.
     *
     * Сохранение всех данных для конфигурации библиотеки.
     */
    public static function saveConfig(array $config): void
    {
        $transfer = self::transfer();
        $transfer->disableCache();

        $transfer->save('phphleb/hlogin', 'config', $config);
    }

    /**
     * Returns the API version from the library configuration.
     *
     * Возвращает версию API из конфигурации библиотеки.
     */
    public static function getApiVersion(): int
    {
        $config = self::getConfig();
        if (empty($config['version'])) {
            throw new \RuntimeException(self::getErrorMessage('version'));
        }
        return $config['version'];
    }

    /**
     * Saving the API version for the library.
     * If multiple parameters are saved, then it is better
     * to use saveConfig() in this case.
     *
     * Сохранение версии API для библиотеки.
     * Если сохраняется несколько параметров,
     * то в этом случае лучше использовать saveConfig().
     */
    public static function saveApiVersion(int $version): void
    {
        $config = self::getConfig();
        $config['version'] = $version;
        self::saveConfig($config);
    }

    /**
     * Returns the registration panel design type from the library configuration.
     *
     * Возвращает тип дизайна панели регистрации из конфигурации библиотеки.
     */
    public static function getDesign(): string
    {
        $config = self::getConfig();
        if (empty($config['design'])) {
            throw new \RuntimeException(self::getErrorMessage('design'));
        }
        return $config['design'];
    }

    /**
     * Saving a design type for a library.
     * If multiple parameters are saved, then it is better
     * to use saveConfig() in this case.
     *
     * Сохранение типа дизайна для библиотеки.
     * Если сохраняется несколько параметров,
     * то в этом случае лучше использовать saveConfig().
     */
    public static function saveDesign(string $design): void
    {
        $config = self::getConfig();
        $config['design'] = $design;
        self::saveConfig($config);
    }

    /**
     * Getting the configuration for the mail client.
     * If several configurations are needed,
     * then for performance reasons it is better
     * to get them via getConfig().
     *
     * Получение конфигурации для почтового клиента.
     * Если необходимо несколько конфигураций,
     * то из соображений быстродействия
     * их лучше получать через getConfig().
     */
    public static function getMailConfig(): array
    {
        $config = self::getConfig();
        if (empty($config['mail'])) {
            throw new \RuntimeException(self::getErrorMessage('mail'));
        }

        return $config['mail'];
    }

    /**
     * Saving the configuration for the mail client.
     * If multiple parameters are saved, then it is better
     * to use saveConfig() in this case.
     *
     * Сохранение конфигурации для почтового клиента.
     * Если сохраняется несколько параметров,
     * то в этом случае лучше использовать saveConfig().
     */
    public static function saveMailConfig(array $data): void
    {
        $config = self::getConfig();
        $config['mail'] = $data;
        self::saveConfig($config);
    }

    /**
     * Getting configuration for captcha.
     * If several configurations are needed,
     * then for performance reasons it is better
     * to get them via getConfig().
     *
     * Получение конфигурации для captcha.
     * Если необходимо несколько конфигураций,
     * то из соображений быстродействия
     * их лучше получать через getConfig().
     */
    public static function getCaptchaConfig(): array
    {
        $config = self::getConfig();
        if (empty($config['captcha'])) {
            throw new \RuntimeException(self::getErrorMessage('captcha'));
        }

        return $config['captcha'];
    }

    /**
     * Saving configuration for captcha.
     * If multiple parameters are saved, then it is better
     * to use saveConfig() in this case.
     *
     * Сохранение конфигурации для captcha.
     * Если сохраняется несколько параметров,
     * то в этом случае лучше использовать saveConfig().
     */
    public static function saveCaptchaConfig(array $data): void
    {
        $config = self::getConfig();
        $config['captcha'] = $data;
        self::saveConfig($config);
    }

    /**
     * Getting the configuration for the feedback section.
     * If several configurations are needed,
     * then for performance reasons it is better
     * to get them via getConfig().
     *
     * Получение конфигурации для раздела обратной связи.
     * Если необходимо несколько конфигураций,
     * то из соображений быстродействия
     * их лучше получать через getConfig().
     */
    public static function getContactConfig(): array
    {
        $config = self::getConfig();
        if (empty($config['contact'])) {
            throw new \RuntimeException(self::getErrorMessage('contact'));
        }

        return $config['contact'];
    }

    /**
     * Saving the configuration for the feedback form.
     * If multiple parameters are saved, then it is better
     * to use saveConfig() in this case.
     *
     * Сохранение конфигурации для формы обратной связи.
     * Если сохраняется несколько параметров,
     * то в этом случае лучше использовать saveConfig().
     */
    public static function saveContactConfig(array $data): void
    {
        $config = self::getConfig();
        $config['contact'] = $data;
        self::saveConfig($config);
    }

    /**
     * Get configuration for general registration settings.
     * If several configurations are needed,
     * then for performance reasons it is better
     * to get them via getConfig().
     *
     * Получение конфигурации для общих настроек регистрации.
     * Если необходимо несколько конфигураций,
     * то из соображений быстродействия
     * их лучше получать через getConfig().
     */
    public static function getRegConfig(): array
    {
        $config = self::getConfig();
        if (empty($config['registration'])) {
            throw new \RuntimeException(self::getErrorMessage('registration'));
        }

        return $config['registration'];
    }

    /**
     * Saving configuration for general registration settings.
     * If multiple parameters are saved, then it is better
     * to use saveConfig() in this case.
     *
     * Сохранение конфигурации для общих настроек регистрации.
     * Если сохраняется несколько параметров,
     * то в этом случае лучше использовать saveConfig().
     */
    public static function saveRegConfig(array $data): void
    {
        $config = self::getConfig();
        $config['registration'] = $data;
        self::saveConfig($config);
    }

    /**
     * Getting the parameter is moved to a separate method,
     * since it is optional.
     * Defines the connection type from the framework configuration.
     *
     * Получение параметра вынесено в отдельный метод,
     * так как он необязательный.
     * Определяет тип подключения из конфигурации фреймворка.
     */
    public static function getDatabaseType(): false|string
    {
        return self::getRegConfig()['database-type'] ?? false;
    }

    private static function transfer(): Transfer
    {
        self::$transfer or self::$transfer = new Transfer();

        return self::$transfer;
    }

    private static function getErrorMessage(string $paramName): string
    {
        return \sprintf(self::ERROR, $paramName);
    }
}