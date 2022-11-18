<?php

namespace Phphleb\Hlogin\App\Redirection;

use Hleb\Constructor\Handlers\Request;
use Hleb\Main\Insert\PageFinisher;
use Hleb\Main\MainTemplate;
use Phphleb\Hlogin\App\HloginUserModel;
use Phphleb\Hlogin\App\Main;
use Phphleb\Hlogin\App\OriginData;
use Phphleb\Hlogin\App\System\UserRegistration;
use Phphleb\Hlogin\App\Translate;

// Various actions

class Action
{
    const EXIT_PAGE_PATH = 'hlogin/authorization/exit_page';

    const EXIT_PAGE_FORCED_PATH = 'hlogin/authorization/exit_page_forced';

    const REGISTER_PAGE_PATH = 'hlogin/authorization/register_page';

    private $code = '';

    function get() {
        $action = Request::get('action');
        $lang = Request::get('lang');

        if(!in_array($lang, OriginData::getLanguages())) {
            return hleb_view('404');
        }
        if ($action === 'exit') {
            return hleb_view(self::EXIT_PAGE_PATH);
        }
        if ($action === 'exitforce') {
            return hleb_view(self::EXIT_PAGE_FORCED_PATH);
        }
        if (UserRegistration::checkDeleted()) {
            return "User has been deleted or blocked.";
        }
        if (in_array($action, ['registration', 'enter', 'recovery']) && UserRegistration::checkPrimaryAndHigher()) {
            hleb_redirect('/' . $lang . '/login/profile/');
        }

        Translate::setLang($lang);

        $data = Main::getConfigRegData('cell_reg_page_head');
        $this->code = html_entity_decode(html_entity_decode($data)) ?? '';

        switch ($action) {
            case 'registration':
                return $this->page('UserRegister', Translate::get('register_page'));
                break;
            case 'enter':
                return $this->page('UserEnter', Translate::get('enter_page'));
                break;
            case 'recovery':
                return $this->page('NewPassword', Translate::get('new_password_page'));
                break;
            case 'confirm':
                return $this->confirmEmail();
                break;
            case 'contact':
                return $this->page('ContactMessage', Translate::get('contact_message'));
                break;
            default:
                return hleb_view('404');
        }
    }

    private function page(string $type, string $title = null) {
        return hleb_view(self::REGISTER_PAGE_PATH, ['type' => $type, 'title' => $title, 'insertedCode' => $this->code]);
    }

    private function confirmEmail() {
        $code = Request::getGetString('code');
        if (empty($code)) {
            if (UserRegistration::checkRegisterAndHigher()) {
                return hleb_view(self::REGISTER_PAGE_PATH, ['type' => 'EmailConfirmSuccess', 'title' => 'Е-mail', 'insertedCode' => $this->code]);
            }
            hleb_redirect('/' . Request::get('lang') . '/login/action/enter/');
        }
        $regData = HloginUserModel::getCells('hash', $code, ['id', 'email', 'hash', 'regtype', 'confirm', 'sessionkey']);
        if (!$regData || $regData['regtype'] < UserRegistration::PRIMARY_USER || $regData['hash'] !== $code) {
            return hleb_view(self::REGISTER_PAGE_PATH, ['type' => 'ErrorConfirmEmail', 'title' => 'Error', 'insertedCode' => $this->code]);
        }
        $confirmResult = true;
        if ($regData['confirm'] !== 1) {
            $confirmResult = HloginUserModel::setCells('email', $regData['email'], ['confirm' => 1]);
        }
        $regtypeResult = true;
        $type = UserRegistration::REGISTERED_USER;
        if($regData['regtype'] == UserRegistration::PRIMARY_USER) {
            $regtypeResult = HloginUserModel::setCells('email', $regData['email'], ['regtype' => UserRegistration::REGISTERED_USER]);
        }  else {
            $type = $regData['regtype'];
        }
        if($regtypeResult && $confirmResult) {
            // Вход
            $this->login($regData['email'], $regData['sessionkey'], $type);
            hleb_redirect('/' . Request::get('lang') . '/login/action/confirm/');
        }

        return hleb_view(self::REGISTER_PAGE_PATH, ['type' => 'ErrorConfirmEmail', 'title' => 'Error', 'insertedCode' => $this->code]);
    }

    /*
     * Проверка регистрации при входе на внутренние страницы по условиям доступа
     */
    public function checkRegisterData($type = 0, $compare = '>=', $panels = null) {
        // Проверка регистрации
        $check = UserRegistration::getRegType($type, $compare);
        if (!$check && ($type < 0 || $panels === OriginData::FROM_API)) {
            http_response_code(403);
            exit();
        }
        if (!$check) {
            // Редирект на регистрацию и сохранение текущей страницы в сессию для возврата
            $urlLang = \Phphleb\Hlogin\App\Main::getDefaultLang() ?? 'en';
            $urlParts = explode('/', Request::getMainClearUrl());
            if (count($urlParts)) {
                $urlLangSearch = in_array(strtolower($urlParts[0]), \Phphleb\Hlogin\App\OriginData::getLanguages());
                if ($urlLangSearch) {
                    $urlLang = $urlParts[0];
                }
                $fullUrl = Request::getMainConvertUrl();
                $_SESSION['HLEB_REGISTER_REDIRECT_URL'] = $fullUrl;
            } else {
                unset($_SESSION['HLEB_REGISTER_REDIRECT_URL']);
            }
            hleb_redirect('/' . $urlLang . '/login/action/enter/');
        }
        if (is_null($panels)) {
            $panels = OriginData::SHOW_PANELS;
        }
        // Показ панелей
        OriginData::setRegPanelOn($panels === OriginData::SHOW_PANELS);
        if (($panels === OriginData::SHOW_PANELS || $panels === OriginData::HIDE_BUTTONS) && Request::getMethod() === 'GET') {
            $this->createContent($panels);
        } else {
            PageFinisher::setVisibleType(UserRegistration::CONTENT_TYPE, false);
        }
    }

    /* При входе на защищенную страницу формирует данные для загрузки и показа контента регистрации. */
    protected function createContent($type = OriginData::SHOW_PANELS) {
        ob_start();
        new MainTemplate('/hlogin/templates/add', ['type' => $type, 'forced' => true]);
        $data = ob_get_contents();
        ob_end_clean();
        if($data) {
            PageFinisher::setDynamicContent(UserRegistration::CONTENT_TYPE, $data);
        }
    }

    private function login(string $email, string $sessionkey, $regtype = UserRegistration::UNDEFINED_USER) {
        if (!empty($email) && is_numeric($regtype)) {
            UserRegistration::setType($regtype);
            UserRegistration::setEmailAddress($email);
            $_SESSION['HLOGIN_REGISTRATION_ID'] = $sessionkey;
        }
    }

}

