<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Content;

use App\Middlewares\Hlogin\Registrar;
use Hleb\Static\Request;
use Hleb\Static\Response;
use Hleb\Static\Settings;
use Hleb\Static\System;
use Phphleb\Hlogin\App\AuthUser;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Helpers\ConfirmEmailCodeHelper;
use Phphleb\Hlogin\App\Helpers\RecoveryPasswordCodeHelper;
use Phphleb\Hlogin\App\PanelData;
use Phphleb\Nicejson\JsonConverter;
use Phphleb\Ucaptcha\Captcha;
use RuntimeException;

final class ScriptLoader
{
    private const SCRIPT_NAME = 'hlogin_init_script';

    static private bool $isUsed = false;

    static private ?string $requestId = null;

    static private ?int $loadMode = null;

    private function __construct()
    {
    }

    private function __clone(): void
    {
    }

    /** @throws RuntimeException */
    final public function __wakeup(): void
    {
        throw new RuntimeException("Cannot serialize singleton");
    }

    final public function __serialize(): array
    {
        self::__wakeup();
    }

    final public function __unserialize(array $data)
    {
        self::__wakeup();
    }

    /**
     * Setting the output type of panels and standard buttons.
     * If it was not performed, then the registration middleware
     * controller was not added to the current route.
     *
     * Установка типа вывода панелей и стандартных кнопок.
     * Если не была произведена, то значит middleware-контроллер
     * регистрации не был добавлен к текущему маршруту.
     */
    public static function setMode(
        #[ExpectedValues(Registrar::ALLOW_TYPES)]
        int $loadMode
    ): void
    {
        self::init();
        self::$loadMode = $loadMode;
    }

    /**
     * Returns the HTML code of the registration script
     * to be placed on the page.
     * It can be used only once (on the first request).
     *
     * Возвращает HTML-код скрипта для размещения на странице.
     * Может быть использован только один раз (по первому запросу).
     *
     * @param bool $forced - forced return of content.
     *                     - принудительная отдача контента.
     *
     * @param string|null $openPanel - displaying the panel in the entire browser window without a close button.
     *                               - вывод панели во всё окно браузера без кнопки закрытия.
     *
     *
     * @param null|int $loadMode - setting the display mode of panels and buttons when manually adding a registration script.
     *                             Secondary to the set parameter in middleware and will not be used in this case.
     *
     *                           - установка режима вывода панелей и кнопок при ручном добавлении скрипта регистрации.
     *                             Вторично для установленного параметра в middleware и не будет использовано в таком случае.
     */
    public static function get(bool $forced = false, string|null $openPanel  = null, ?int $loadMode = null): null|string
    {
        self::init();

        if ($loadMode !== null && self::$loadMode === null) {
            if (!in_array($loadMode, Registrar::ALLOW_TYPES)) {
                throw new RuntimeException(sprintf('The `loadMode` parameter is not specified.'));
            }
            self::$loadMode = $loadMode;
        }
        if (self::$loadMode === null) {
            throw new RuntimeException('The registration middleware was not added to the current route.');
        }

        // Allows the script to be output only once unless otherwise specified.
        // Позволяет выводить скрипт только единожды, если не указано иное.
        if (self::$isUsed && !$forced) {
            return null;
        }

        self::$isUsed = true;

        if (self::$loadMode === Registrar::NO_PANEL && !$forced) {
            return null;
        }

        $config = ConfigStorage::getConfig();

        $code = match ($openPanel) {
            'ConfirmEmail' => (new ConfirmEmailCodeHelper(Request::get('code')))->getVerified(),
            'NewPassword' => (new RecoveryPasswordCodeHelper(Request::get('code')))->getVerified(),
            default => null,
        };

        // Language definition.
        // Определение языка.
        $defaultLang = Settings::getDefaultLang();
        $lang = Settings::getAutodetectLang();
        $trans = AuthLang::getAll();
        if (!in_array($lang, $trans)) {
            if (!in_array($defaultLang, $trans)) {
                throw new RuntimeException('The default language from settings cannot be applied to current translation files.');
            }
            $lang = $defaultLang;
        }

        $cells = (new ConfigCellNormalizer())->update($config['registration']['cells']);

        $data = [
            'csrfToken' => \csrf_token(),
            'lang' => PanelData::getLang() ?? $lang,
            'languages' => $trans,
            'endingUrl' => Settings::isEndingUrl(),
            'config' => [
                'design' => PanelData::getDesign() ?? AuthDesign::getActual(),
                'version' => $config['version'],
                'registration' => [
                    'type' => AuthUser::getNumericType(),
                    'button' => [
                        'active' => $config['registration']['button']['active'],
                    ],
                    'design-options' => $config['registration']['design-options'],
                    'enter-only' => $config['registration']['enter-only'],
                    'homepage-redirect' => $config['registration']['homepage-redirect'],
                    'cells' => $cells,
                    'src' => $config['registration']['src'],
                ],
                'captcha' => [
                    'active' => $config['captcha']['active'] && !(new Captcha())->isPassed(),
                    'design' => $config['captcha']['design'],
                ],
                'contact' => [
                    'active' => $config['contact']['active'],
                ],
                'mail' => [],
                'code' => $code,
                'startCommand' => $openPanel,
            ],
        ];

        if (self::$loadMode === Registrar::NO_BUTTON) {
            $data['config']['registration']['button']['active'] = false;
        }
        if (self::$loadMode === Registrar::SHOW_BUTTON) {
            $data['config']['registration']['button']['active'] = true;
        }

        $scriptUri = '/hlresource/hlogin/v' . $config['version'] . '/js';

        return self::addScript($scriptUri, $data, $openPanel);
    }


    /**
     * Output of the registration script only if there is a registration middleware
     * controller and the script has not been output previously.
     * The registration controller should have set the loadMode type.
     *
     * Вывод скрипта для регистрации только если есть middleware-контроллер
     * регистрации и скрипт не был выведен ранее.
     * Контроллер регистрации должен был установить тип loadMode.
     */
    public static function set(): void
    {
        if (self::$loadMode !== null) {
            $script = self::get();
            if ($script) {
                Response::addToBody($script);
            }
        }
    }

    /**
     * For an asynchronous request, you need to update the data.
     *
     * Для асинхронного запроса необходимо обновить данные.
     */
    private static function init(): void
    {
        $requestId = System::getRequestId();
        if (self::$requestId !== $requestId) {
            self::$requestId = $requestId;
            self::$isUsed = false;
            self::$loadMode = null;
        }
    }

    /**
     * Returns the bootstrap script to place on the page.
     *
     * Возвращает загрузочный скрипт для размещения на странице.
     *
     * @param string $src - path to the script file.
     *                    - путь к файлу скрипта.
     *
     * @param array $data - registration settings.
     *                     - настройки регистрации.
     * @return string
     */
    private static function addScript(string $src, array $data, string|null $openPanel): string
    {
        $name = self::SCRIPT_NAME;
        $config = (new JsonConverter())->get($data);
        $slash = $data['endingUrl'] ? '/' : '';
        $buttonScript = PHP_EOL;
        if (!$openPanel && $data['config']['registration']['button']['active']) {
            $buttonScript .= "<script async src='$src/hloginbutton{$slash}'></script>" . PHP_EOL;
        }
        // To prevent a conflict when adding to the page,
        // then a reverse replacement will be performed.
        // Для предотвращения конфликта при добавлении на страницу,
        // потом будет произведена обратная замена.
        $config = \str_replace("'", "&apos;", $config);

        return "
<script id='{$name}' async src='{$src}/hloginscript{$slash}'
  data-config='{$config}'
></script>" . $buttonScript;
    }
}