<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App;

use Hleb\Static\System;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use RuntimeException;

class PanelData
{
    private static ?string $requestId = null;

    private static string|null $design = null;

    private static string|null $lang = null;

    /**
     * @internal
     */
    public static function getDesign(): ?string
    {
        self::init();
        return self::$design;
    }

    /**
     * @internal
     */
    public static function getLang(): ?string
    {
        self::init();
        return self::$lang;
    }

    /**
     * Setting the design on the page.
     *
     * Установка дизайна на странице.
     */
    public static function setLocalDesign(string $design): void
    {
        self::init();
        $config = ConfigStorage::getConfig();
        $all = $config['registration']['design-options'];
        if (!in_array($design, $all)) {
            throw new RuntimeException("Incorrect design `$design` specified");
        }
        self::$design = $design;
    }

    /**
     * Setting the language type on the page.
     *
     * Установка типа языка на странице.
     */
    public static function setLocalLang(string $lang): void
    {
        self::init();
        if (!in_array($lang, AuthLang::getAll())) {
            throw new RuntimeException("Incorrect language `$lang` specified");
        }
        self::$lang = $lang;
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
            self::$lang = null;
            self::$design = null;
        }
    }
}