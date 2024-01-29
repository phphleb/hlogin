<?php
declare(strict_types=1);

namespace App\Middlewares\Hlogin;

use Hleb\Base\Middleware;
use Hleb\Http403ForbiddenException;
use Hleb\Static\Redirect;
use Hleb\Static\Request;
use Hleb\Static\Router;
use Hleb\Static\Session;
use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Content\ScriptLoader;
use Phphleb\Hlogin\App\RegData;
use Phphleb\Hlogin\App\RegType;

/**
 * A class for specifying the type of registration in routes or route groups.
 * The presence of this class in the route automatically places the necessary registration
 * scripts at the end of the page, unless the NO_PANEL mode is specified.
 * Thus, in order to use a replacement for this class, you need
 * to implement its inheritance from this class.
 *
 * Класс для указания типа регистрации в маршрутах или группах маршрутов.
 * Присутствие этого класса в маршруте автоматически приводит к размещению необходимых
 * скриптов регистрации в конце страницы, если не указан режим NO_PANEL.
 * Таким образом, чтобы использовать замену этому классу нужно реализовать
 * её наследование от этого класса.
 */
class Registrar extends Middleware
{
    /**
     * Sets the default display of panels, panels are closed,
     * standard buttons are visible.
     * The standard registration buttons
     * can also be universally disabled in the settings.
     *
     * Устанавливает дефолтное отображение панелей,
     * панели закрыты, стандартные кнопки видимы.
     * Стандартные кнопки регистрации также могут
     * быть повсеместно отключены из настроек.
     */
    public const DEFAULT_PANEL = 0;

    /**
     * Prohibits the display of standard buttons on the page,
     * while the panels can be opened independently,
     * it may be necessary with your own implementation
     * of methods for opening panels.
     *
     * Запрещает отображение стандартных кнопок на странице,
     * при этом панели можно открыть самостоятельно,
     * может быть необходимо при собственной реализации
     * методов открытия панелей.
     */
    public const NO_BUTTON = 1;

    /**
     * Disables the display of panels and standard buttons,
     * leaving only the registration check.
     * May be required for APIs or other pages where registration
     * panels are not needed, but authorization is required.
     * If there is authorization on each page,
     * there should be a check to prevent blocked users
     * from accessing the site.
     *
     * Отключает вывод панелей и стандартных кнопок, оставляя
     * только проверку регистрации.
     * Может быть необходимо для API или иных страниц,
     * где панели регистрации не нужны, но авторизация необходима.
     * При наличии авторизации на каждой странице должна быть проверка,
     * чтобы запретить доступ заблокированным пользователям к сайту.
     */
    public const NO_PANEL = 2;

    /**
     * Alias for NO_PANEL.
     *
     * Псевдоним для NO_PANEL.
     */
    public const API = 2;

    /**
     * Force the display of panels and standard buttons,
     * even if the buttons are prohibited in the configuration.
     * Used for technical debugging.
     *
     * Принудительное отображение панелей и стандартных кнопок,
     * даже если кнопки запрещены в конфигурации.
     * Используется для технической отладки.
     */
    public const SHOW_BUTTON = 3;

    /**
     * Checks for compliance with the current version of RegData::API_VERSION.
     * This way it is determined when the library has been updated
     * but not installed into the project.
     *
     * Проверяется на соответствие с актуальной версией RegData::API_VERSION.
     * Таким образом определяется, когда библиотека была обновлена,
     * но не установлена в проект.
     */
    public const CURRENT_VERSION = '2';

    public const ALLOW_TYPES = [self::NO_PANEL, self::DEFAULT_PANEL, self::NO_BUTTON, self::SHOW_BUTTON, self::API];

    private const ERROR = 'The %s parameter in the array data for controller %s is not set correctly.';

    protected static bool $isUsed = false;

    protected int $registerType;

    protected ?string $registerCompare;

    protected int $registerLayout;

    /**
     * Allows you to pass registration settings to controller methods.
     * For example:
     *
     * Позволяет передавать в методы контроллера настройки регистрации.
     * Например:
     *
     * Route::toGroup()->middleware(Registrar::class, data: [RegType::PRIMARY_USER, '>=', Registrar::DEFAULT_PANEL]);
     *
     * @throws \ErrorException
     */
    final public function __construct(array $config = [])
    {
        parent::__construct($config);

        self::$isUsed = true;

        [$this->registerType, $this->registerCompare, $this->registerLayout] = self::validate();
    }

    /**
     * Checking registration according to specified conditions.
     *
     * Проверка регистрации по заданным условиям.
     */
    public function index(): false|null
    {
        if (!RegType::check($this->registerType, $this->registerCompare)) {
            if ($this->registerType < RegType::UNDEFINED_USER) {
                throw new Http403ForbiddenException();
            }
            Session::set(RegData::PREVIOUS_PAGE_SESSION_NAME, Request::getUri()->getPath());
            Redirect::to('/' . Settings::getAutodetectLang() . '/login/action/enter/');
            return false;
        }
        ScriptLoader::setMode($this->registerLayout);
        return null;
    }

    /**
     * With an asynchronous request, you can check whether the class was initiated in the route.
     *
     * При асинхронном запросе можно проверить был ли класс инициирован в маршруте.
     */
    public static function isUsed(): bool
    {
        return self::$isUsed;
    }

    /**
     * Rollback the initialization mark for execution after an asynchronous request.
     *
     * Откат метки инициализации для выполнения после асинхронного запроса.
     */
    public static function rollback(): void
    {
        self::$isUsed = false;
    }

    /**
     * @throws \ErrorException
     */
    protected function validate(): array
    {
        /**
         * Raises an error if the installation or update of the library was not completed.
         *
         * Вызывает ошибку если установка или обновление библиотеки не было доведено до конца.
         */
        if (self::CURRENT_VERSION !== RegData::API_VERSION) {
            throw new \ErrorException('HLOGIN library update not completed');
        }

        $middlewareData = Router::data();

        $registerType = $middlewareData[0] ?? RegType::REGISTERED_COMMANDANT;
        if ($registerType < RegType::BANNED_USER || $registerType > RegType::REGISTERED_COMMANDANT) {
            throw new \ErrorException(sprintf(self::ERROR, 'first', self::class));
        }
        $registerCompare = $middlewareData[1] ?? '>=';
        if (!\in_array($registerCompare, RegType::RULES)) {
            throw new \ErrorException(sprintf(self::ERROR, 'second', self::class));
        }
        $registerLayout = $middlewareData[2] ?? self::DEFAULT_PANEL;
        if (!in_array($registerLayout, self::ALLOW_TYPES)) {
            throw new \ErrorException(sprintf(self::ERROR, 'third', self::class));
        }

        return [$registerType, $registerCompare, $registerLayout];
    }
}

