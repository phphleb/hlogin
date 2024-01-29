<?php

namespace Phphleb\Hlogin\App\Controllers;

use ErrorException;
use Hleb\Base\PageController;
use Hleb\Static\Settings;
use Phphleb\Adminpan\Src\ConfigDirector;
use Phphleb\Hlogin\App\Controllers\Admin\AdditionalPageController;
use Phphleb\Hlogin\App\Controllers\Admin\BaseDataController;
use Phphleb\Hlogin\App\Controllers\Admin\CaptchaPageController;
use Phphleb\Hlogin\App\Controllers\Admin\ContactPageController;
use Phphleb\Hlogin\App\Controllers\Admin\EmailPageController;
use Phphleb\Hlogin\App\Controllers\Admin\RightPageController;
use Phphleb\Hlogin\App\Controllers\Admin\SettingPageController;
use Phphleb\Hlogin\App\Controllers\Admin\UserPageController;

/**
 * @internal
 */
class BaseAdminZoneController  extends PageController
{
    protected int $apiVersion;

    /**
     * @throws ErrorException
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->apiVersion = ConfigDirector::API_VERSION;
        $uri = ((array)(new ConfigDirector('hlogin'))->getConfig())['logoUri'] ?? null;
        $this->logoUri = $uri ?: "/hlresource/hlogin/v{$this->apiVersion}/svg/hlogo";
    }

    /**
     * @return string
     */
    public function userPage(): string
    {
        $trans = [
            'en' => 'Admin panel | List of users',
            'ru' => 'Административная панель | Список пользователей',
            'de' => 'Admin-Panel | eine Liste von Benutzern',
            'es' => 'Panel de administración | una lista de usuarios',
            'zh' => '行政面板 | 用戶列表'
        ];
        $this->title = $trans[$this->language ?? Settings::getAutodetectLang()];

        return (new UserPageController($this->config))->index();
    }

    /**
     * @return string
     */
    public function rightPage(): string
    {
        $trans = [
            'en' => 'Admin panel | Editing user data',
            'ru' => 'Административная панель | Редактирование данных пользователя',
            'de' => 'Admin-Panel | Benutzerdaten bearbeiten',
            'es' => 'Panel de administración | Editar datos de usuario',
            'zh' => '行政面板 | 編輯用戶數據'
        ];
        $this->title = $trans[$this->language ?? Settings::getAutodetectLang()];

        return (new RightPageController($this->config))->index();
    }

    /**
     * @return string
     */
    public function settingPage(): string
    {
        $trans = [
            'en' => 'Administrative panel | Registration settings',
            'ru' => 'Административная панель | Настройки регистрации',
            'de' => 'Admin-Panel | Registrierungseinstellungen',
            'es' => 'Panel de administración | Configuración de registro',
            'zh' => '行政面板 | 註冊設置'
        ];
        $this->title = $trans[$this->language ?? Settings::getAutodetectLang()];

        return (new SettingPageController($this->config))->index();
    }

    /**
     * @return string
     */
    public function captchaPage(): string
    {
        $trans = [
            'en' => 'Administrative panel | CAPTCHA settings',
            'ru' => 'Административная панель | Настройки captcha',
            'de' => 'Admin-Panel | CAPTCHA-Einstellungen',
            'es' => 'Panel de administración | Configuración de CAPTCHA',
            'zh' => '行政面板 | 驗證碼設置'
        ];
        $this->title = $trans[$this->language ?? Settings::getAutodetectLang()];

        return (new CaptchaPageController($this->config))->index();
    }

    /**
     * @return string
     */
    public function emailPage(): string
    {
        $trans = [
            'en' => 'Administrative panel | Email settings',
            'ru' => 'Административная панель | Настройки E-mail',
            'de' => 'Admin-Panel | Email Einstellungen',
            'es' => 'Panel de administración | Ajustes del correo electrónico',
            'zh' => '行政面板 | 電子郵件設置'
        ];
        $this->title = $trans[$this->language ?? Settings::getAutodetectLang()];

        return (new EmailPageController($this->config))->index();
    }

    /**
     * @return string
     */
    public function additionalPage(): string
    {
        $trans = [
            'en' => 'Administrative panel | Additional settings',
            'ru' => 'Административная панель | Дополнительные настройки',
            'de' => 'Admin-Panel | Zusätzliche Einstellungen',
            'es' => 'Panel de administración | Ajustes adicionales',
            'zh' => '行政面板 | 其他設置'
        ];
        $this->title = $trans[$this->language ?? Settings::getAutodetectLang()];

        return (new AdditionalPageController($this->config))->index();
    }

    /**
     * @return string
     */
    public function contactPage(): string
    {
        $trans = [
            'en' => 'Administrative panel | Feedback setting',
            'ru' => 'Административная панель | Настройка обратной связи',
            'de' => 'Admin-Panel | Feedback-Einstellung',
            'es' => 'Panel de administración | Configuración de comentarios',
            'zh' => '行政面板 | 反饋設置'
        ];
        $this->title = $trans[$this->language ?? Settings::getAutodetectLang()];

        return (new ContactPageController($this->config))->index();
    }

    /**
     * Internal redirect to user profile page.
     *
     * Внутренний редирект на страницу профиля пользователя.
     */
    public function profilePage(): string
    {
        $lang = Settings::getAutodetectLang();
        \hl_redirect("/$lang/login/profile/");
        return '';
    }

    /**
     * Handling Ajax requests for the admin panel.
     *
     * Обработка Ajax-запросов для административной панели.
     */
    public function data(): array
    {
        return (new BaseDataController($this->config))->index();
    }
}