<?php

namespace Phphleb\Hlogin\App\Redirection;

use Hleb\Constructor\Handlers\Key;
use Hleb\Constructor\Handlers\Request;
use Phphleb\Hlogin\App\Helper;
use Phphleb\Hlogin\App\HloginUserModel;
use Phphleb\Hlogin\App\HloginUserLogModel;
use Phphleb\Hlogin\App\Main;
use Phphleb\Hlogin\App\OriginData;
use Phphleb\Hlogin\App\System\SendEmail;
use Phphleb\Hlogin\App\System\UserRegistration;
use Phphleb\Hlogin\App\Translate;
use Phphleb\Ucaptcha\Captcha;

// Getting data through ajax

class Data extends OriginData
{
    // Поля для которых можно выставить обязательное значение
    const REQUIRED_CELLS = ['email', 'phone', 'surname', 'address', 'promocode', 'password'];
    // Поля всегда обязательные, если выбраны
    const MANDATORY_CELLS = ['email', 'terms', 'privacy_policy'];
    // Время на запоминание входа пользователя
    const REGISTER_SESSION_PERIOD = 60 * 60 * 24 * 7;

    const OTHER = '_other';

    const LINE = '____________________________';

    // Определенный язык
    protected $lang;
    // Параметр `action` из url
    protected $action;
    // Параметр `value` из url
    protected $value;
    // GET-параметр `code`
    protected $code;
    // POST - параметры
    protected $data;
    // Содержит data-type названия параметров, которые не прошли проверку
    protected $errorCells = [];
    // Содержит data-type значения параметров прошедших проверку
    protected $valueCells = [];

    public function get() {
        $this->lang = Request::get('lang');
        if(!in_array($this->lang, OriginData::getLanguages())) {
            http_response_code (404);
            return null;
        }
        Translate::setLang($this->lang);
        $this->action = Request::get('action');
        $this->value = Request::get('value');
        if (empty($this->lang) || empty($this->action) || empty($this->value)) return null;
        if ($this->action == 'ajax') {
            return $this->ajax();
        }
        return null;
    }

    private function ajax() {
        if ($this->value !== 'getuserregisterdata' && empty(Request::getPost())) {
            return $this->ajaxFormat("", null, "Invalid query POST-data", [self::OTHER]);
        }
        $this->reformatDataByPost();
        switch ($this->value) {
            case 'userregister':
                return $this->ajaxUserRegister();
                break;
            case 'userpassword':
                return $this->ajaxUserPassword();
                break;
            case 'userenter':
                return $this->ajaxUserEnter();
                break;
            case 'newpassword':
                return $this->ajaxNewPassword();
                break;
            case 'userprofile':
                return $this->ajaxUserProfile();
                break;
            case 'getuserregisterdata':
                return $this->ajaxUserGetProfileData();
                break;
            case 'messagefromconfirmemail':
                return $this->ajaxSendMessageFromConfirmEmail();
                break;
            case 'contactmessage':
                return $this->ajaxContactMessage();
                break;
            default:
        }
        // 404
        return false;
    }

    /**
     * Обработка запроса на регистрацию
     * @return false|string
     */
    private function ajaxUserRegister() {
        if(!$this->checkCaptcha()) {
            return $this->ajaxFormat(self::USER_REGISTER_TYPE, null, Translate::get('captcha_error'), ['captcha']);
        }
        if (!$this->applyMainExplorerCheck(self::USER_REGISTER_TYPE)) {
            return $this->ajaxFormat(self::USER_REGISTER_TYPE, null, Translate::get('data_validation_failed'), [self::OTHER]);
        }
        $email = Helper::convertEmail($this->checkedCell('email', self::EMAIL_PATTERN));

        $this->valueCells['email'] = $email;
        if (!$email) {
            $this->errorCells[] = 'email';
        }
        $this->checkValue('phone');
        $this->checkValue('name');
        $this->checkValue('surname');
        $this->checkValue('address');
        $this->checkValue('promocode');
        $this->checkValue('password', 'password1', self::PASSWORD_PATTERN);
        $this->checkValue('password', 'password2', self::PASSWORD_PATTERN);
        $this->checkValue('subscription');
        if ($this->selectConfirmCells()) {
            if(!$this->checkConfirmCell()) {
                $this->errorCells[] = 'confirm';
            }
        } else {
            $this->checkValue('terms');
            $this->checkValue('privacy_policy');
        }

        $hasPasswordOnRegister = $this->isSetPassword();

        if ($hasPasswordOnRegister && (!isset($this->data['password1']['value'], $this->data['password2']['value']) ||
                $this->data['password1']['value'] !== $this->data['password2']['value'])
        ) {
            $this->errorCells[] = 'password1';
            $this->errorCells[] = 'password2';
        }

        if (empty($this->errorCells)) {
            if (!HloginUserModel::checkTableUsers() || !HloginUserLogModel::checkTableUsers()) {
                return $this->ajaxFormat(self::USER_REGISTER_TYPE, null, Translate::get('empty_database'), [self::OTHER]);
            }
            $row = HloginUserModel::checkEmailAddressAndGetData($email);
            if ($row !== false) {
                HloginUserModel::setPeriodTime($email);
                return $this->ajaxFormat(self::USER_REGISTER_TYPE, null, Translate::get('registered_email'), ['email']);
            }

            // Хэш для куки
            $hashForSession = md5($email) . "-" . Helper::getProtectedHashGenerator();
            // Часть хеша для восстановления E-mail
            $hashForMail = md5($email . microtime()) . "-" . Helper::getProtectedHashGenerator();
            $password = $this->valueCells['password1'] ?? $this->getPasswordGenerator();
            HloginUserModel::createNewUser(
                $email,
                password_hash($password . Key::get(), PASSWORD_DEFAULT),
                time(),
                UserRegistration::PRIMARY_USER,
                $this->valueCells['login'] ?? null,
                $this->valueCells['name'] ?? null,
                $this->valueCells['surname'] ?? null,
                $this->valueCells['phone'] ?? null,
                $this->valueCells['address'] ?? null,
                $this->valueCells['promocode'] ?? null,
                Request::getRemoteAddress(),
                isset($this->valueCells['subscription']) && $this->valueCells['subscription'] == 'on' ? 1 : 0,
                $hashForMail,
                $hashForSession,
            );

            $newRow = HloginUserModel::checkEmailAddressAndGetData($email);

            // Сохранение логов регистрации
            if(!$this->createUserLog($newRow)) {
                return $this->actionsAfterUnsuccessfulSending($email, '[backend_error_to_create_user_log]');
            }

            // Отправка подтверждения Email
            if (!empty($newRow['id'])) {
                // Отправка письма
                try {
                    $senderResult = $this->sendMailConfirmEmail($email, Translate::get('email_confirm_title') . ' ' . Request::getHost(), $newRow['hash'], $hasPasswordOnRegister ? null : $password);
                } catch (\Exception $e){
                    error_log($e->getMessage());
                    return $this->actionsAfterUnsuccessfulSending($email, '[backend_error_to_send_email]');
                }
                if (!$senderResult) {
                    return $this->actionsAfterUnsuccessfulSending($email, '[failed_to_send_email]');
                }

                // Обработка промокода для разработчика
                $promocode = $this->getConfig('cell_promocode') ? (isset($this->data['promocode']) ? $this->data['promocode'] : false) : false;
                $this->applyMainExplorerCheckPromocode($promocode, $newRow['id']);

                // Только по E-mail
                if (!$hasPasswordOnRegister) {
                    return $this->ajaxFormat('UserMessage', ['title' => Translate::get('user_register_title'), 'message' => Translate::get('user_register_message') . ' <b>' . $email . '</b>'], null, [self::OTHER], $email);
                } else {
                    // Вход на сайт
                    $this->setEnterDataRegister($newRow['sessionkey'], $email, UserRegistration::PRIMARY_USER);
                    // Редирект на страницу указанную после регистрации
                    return $this->ajaxFormat('RedirectToPage',  $this->urlAfterRegistration(), null);
                }
            }
        }
        return $this->ajaxFormat(self::USER_REGISTER_TYPE, null, Translate::get('total_error'), $this->errorCells);
    }

    /**
     * Обработка запроса на отправку восстановления учетных данных
     * @return false|string
     */
    private function ajaxUserPassword() {
        if(!$this->checkCaptcha()) {
            return $this->ajaxFormat(self::USER_PASSWORD_TYPE, null, Translate::get('captcha_error'), ['captcha']);
        }
        if (!$this->applyMainExplorerCheck(self::USER_PASSWORD_TYPE)) {
            return $this->ajaxFormat(self::USER_PASSWORD_TYPE, null, Translate::get('data_validation_failed'), [self::OTHER]);
        }
        $email = Helper::convertEmail($this->checkedCell('email', self::EMAIL_PATTERN));
        if ($email) {
            if (!HloginUserModel::checkTableUsers() || !HloginUserLogModel::checkTableUsers()) {
                return $this->ajaxFormat(self::USER_PASSWORD_TYPE, null, Translate::get('empty_database'), [self::OTHER]);
            }
            $row = HloginUserModel::checkEmailAddressAndGetData($email);
            if ($row === false) {
                return $this->ajaxFormat(self::USER_PASSWORD_TYPE, null, Translate::get('undefined_email'), ['email']);
            }
            if ($row['regtype'] < UserRegistration::UNDEFINED_USER) {
                return $this->ajaxFormat(self::USER_PASSWORD_TYPE, null, Translate::get('deleted_user'), [self::OTHER]);
            }
            if (!HloginUserModel::setPeriodTime($email)) {
                return $this->ajaxFormat(self::USER_PASSWORD_TYPE, null, Translate::get('undefined_email'), ['email']);
            }
            $cookieName = "HLOGIN_RE-SENDING_EMAIL_TO_RESTORE_ACCESS";
            if (Request::getCookie($cookieName)) {
                return $this->ajaxFormat(self::USER_PASSWORD_TYPE, null, Translate::get('again_message'), [self::OTHER]);
            }
            setcookie($cookieName, '1', time() + 50, '/');
            // Отправка письма
            $senderResult = $this->sendMailPasswordRecovery($email, Translate::get('recovery_from') . ' ' . Request::getHost(), $row['hash'], $row['id'], $row['email']);
            if (!$senderResult) {
                return $this->ajaxFormat(self::USER_PASSWORD_TYPE, null, Translate::get('total_error'), [self::OTHER]);
            }
            return $this->ajaxFormat('UserMessage', ['title' => Translate::get('password_send_title'), 'message' => Translate::get('password_send_message') . ' <b>' . $email . '</b>'], null, [self::OTHER], $email);
        }
        return $this->ajaxFormat(self::USER_PASSWORD_TYPE, null, 'Error_message', ['email']);
    }

    /**
     * Обработка запроса на вход от пользователя
     * @return false|string
     */
    private function ajaxUserEnter() {
        if(!$this->checkCaptcha()) {
            return $this->ajaxFormat(self::USER_ENTER_TYPE, null, Translate::get('captcha_error'), ['captcha']);
        }
        if (!$this->applyMainExplorerCheck(self::USER_ENTER_TYPE)) {
            return $this->ajaxFormat(self::USER_ENTER_TYPE, null, Translate::get('data_validation_failed'), [self::OTHER]);
        }
        $email = Helper::convertEmail($this->checkedCell('email', self::EMAIL_PATTERN));
        if ($email) {
            if (!HloginUserModel::checkTableUsers() || !HloginUserLogModel::checkTableUsers()) {
                return $this->ajaxFormat(self::USER_ENTER_TYPE, null, Translate::get('empty_database'), [self::OTHER]);
            }
            $row = HloginUserModel::checkEmailAddressAndGetData($email);
            if ($row === false) {
                return $this->ajaxFormat(self::USER_ENTER_TYPE, null, Translate::get('pair_mismatch'), ['email', 'password1']);
            }
            if ($row['regtype'] < UserRegistration::UNDEFINED_USER) {
                return $this->ajaxFormat(self::USER_ENTER_TYPE, null, Translate::get('deleted_user'), [self::OTHER]);
            }
            if (!HloginUserModel::setPeriodTime($email) || $this->getSearchBruteForce($row['period'])) {
                // Обнуление данных сессии
                UserRegistration::exit();
                return $this->ajaxFormat(self::USER_ENTER_TYPE, null, Translate::get('reload_page_or_other_action'), [self::OTHER]);

            }
            $this->checkValue('password', 'password1', self::PASSWORD_PATTERN);
            if (count($this->errorCells) > 0) {
                return $this->ajaxFormat(self::USER_ENTER_TYPE, null, Translate::get('pair_mismatch'), ['email', 'password1']);
            }

            $regData = HloginUserModel::getCells('email', $email, ['id', 'password', 'regtype', 'sessionkey']);

            if ($regData && !empty($regData['password']) && isset($this->valueCells['password1']) && password_verify($this->valueCells['password1'] . Key::get(), $regData['password'])) {
                // ... Вход на сайт
                $this->setEnterDataRegister($regData['sessionkey'], $email, $regData['regtype']);

                // Запись в куки, если отмечен флаг 'запомнить вход'
                if ($this->getCheckboxValue('remember')) {
                    setcookie('HLOGIN_VERIFICATION_INDEX', $regData['sessionkey'], time() + self::REGISTER_SESSION_PERIOD, '/');
                }
                // Редирект на соответствующую страницу
                $urlRedirect = $this->getRedirectToUrlAfterEnter();
                if($urlRedirect) {
                    return $this->ajaxFormat('RedirectToPage', $urlRedirect, null);
                }
                return $this->ajaxFormat('ReloadPage', 'reload', null);
            }
            return $this->ajaxFormat(self::USER_ENTER_TYPE, $this->valueCells, Translate::get('pair_mismatch'), ['email', 'password1']);
        }
        return $this->ajaxFormat(self::USER_ENTER_TYPE, null, 'Error_message', ['email']);
    }

    /**
     * Обработка запроса на задание нового пароля от пользователя
     * @return false|string
     */
    private function ajaxNewPassword() {
        if(!$this->checkCaptcha()) {
            return $this->ajaxFormat(self::NEW_PASSWORD_TYPE, null, Translate::get('captcha_error'), ['captcha']);
        }
        if (!$this->applyMainExplorerCheck(self::NEW_PASSWORD_TYPE)) {
            return $this->ajaxFormat(self::NEW_PASSWORD_TYPE, null, Translate::get('data_validation_failed'), [self::OTHER]);
        }
        $firstPassword = $this->checkedCell('password1', self::PASSWORD_PATTERN);
        $secondPassword = $this->checkedCell('password2', self::PASSWORD_PATTERN);

        if(empty($firstPassword) || $firstPassword !== $secondPassword) {
            return $this->ajaxFormat(self::NEW_PASSWORD_TYPE, null, Translate::get('data_validation_failed'), ['password1', 'password2']);
        }

        $codeParts = explode('-', strval($this->code));
        if (empty($this->code) || count($codeParts) !== 3) {
            return $this->ajaxFormat(self::NEW_PASSWORD_TYPE, null, Translate::get('total_error'), [self::OTHER]);
        }
        $code = array_shift($codeParts);
        $hash = implode('-', $codeParts);

        $regData = HloginUserModel::getCells('hash', $hash, ['id', 'email', 'password', 'regtype', 'confirm', 'hash']);
        if (!$regData) {
            return $this->ajaxFormat(self::NEW_PASSWORD_TYPE, null, Translate::get('link expired'), [self::OTHER]);
        }
        if ($regData['regtype'] < UserRegistration::UNDEFINED_USER) {
            return $this->ajaxFormat(self::NEW_PASSWORD_TYPE, null, Translate::get('deleted_user'), [self::OTHER]);
        }
        if(!Helper::checkDynamicHash($regData['id'], $regData['email'], $code)) {
            return $this->ajaxFormat(self::NEW_PASSWORD_TYPE, null, Translate::get('link expired'), [self::OTHER]);
        }
        if (password_verify($firstPassword . Key::get(), $regData['password'])) {
            return $this->ajaxFormat(self::NEW_PASSWORD_TYPE, null, Translate::get('duplicate_pass'), ['password1', 'password2']);
        }
        // Сохранение нового пароля по коду
        // Перерасчет хеша для входа и сессии
        $hash = md5($regData['email']) . "-" . Helper::getProtectedHashGenerator();
        // Если запущена смена E-mail или E-mail не подтверждён
        if(empty($regData['confirm'])) {
            $hash = $regData['hash'];
        }
        $cells = [
            'hash' => $hash,
            'sessionkey' => md5($regData['email']) . "-" . Helper::getProtectedHashGenerator(),
            'password' => password_hash($firstPassword . Key::get(), PASSWORD_DEFAULT)
        ];
        $result = HloginUserModel::setCells('email', $regData['email'], $cells);

        // Сброс куки с запомненной сессией входа
        setcookie('HLOGIN_VERIFICATION_INDEX', '', 0, '/');

        if (!$result) {
            return $this->ajaxFormat(self::NEW_PASSWORD_TYPE, null, Translate::get('total_error'), [self::OTHER]);
        }

        // Редирект на вход
        return $this->ajaxFormat('RedirectToPage', '/' . $this->lang . '/login/profile/', null);
    }

    /**
     * Обработка запроса на сохранение данных профиля пользователя
     * @return false|string
     */
    private function ajaxUserProfile() {
        if (!$this->applyMainExplorerCheck(self::USER_PROFILE_TYPE)) {
            return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('data_validation_failed'), [self::OTHER]);
        }
        $checkActualPassword = $this->checkedCell('password1', self::PASSWORD_PATTERN);
        $newPassword = $this->checkedCell('password', self::PASSWORD_PATTERN);
        $email = Helper::convertEmail($this->checkedCell('email', self::EMAIL_PATTERN));
        $this->valueCells['email'] = $email;

        $mainRegisterType = $this->getMainRegisterType();

        if (is_null($mainRegisterType) || $mainRegisterType < UserRegistration::REGISTERED_USER) {
            if ($mainRegisterType == UserRegistration::PRIMARY_USER) {
                return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('email_not_verified'), [self::OTHER], $email);
            } else {
                return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('user_not_reg'), [self::OTHER], $email);
            }
        }

        $mainRegisterEmail = $this->getMainRegisterEmail();
        if (empty($mainRegisterEmail)) {
            return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('user_not_reg'), ['email'], $email);
        }

        $regData = HloginUserModel::checkEmailAddressAndGetData($mainRegisterEmail);

        if (empty($regData['confirm'])) {
            return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('email_not_verified'), [self::OTHER], $email);
        }

        if (empty($regData['email'])) {
            return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('user_not_reg'), ['email'], $email);
        }

        // Проверяется пароль
        if (!password_verify($checkActualPassword . Key::get(), $regData['password'])) {
            return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('wrong_password'), ['password1'], $email);
        }

        $data = [];
        // Изменяется пароль, старые доступы деактивируются
        if (!is_null($newPassword)) {
            if ($checkActualPassword !== $newPassword) {
                $data = [
                    'hash' => md5($email) . "-" . Helper::getProtectedHashGenerator(),
                    'sessionkey' => md5($email) . "-" . Helper::getProtectedHashGenerator(),
                    'password' => password_hash($newPassword . Key::get(), PASSWORD_DEFAULT)
                ];
                // Пересоздание куки
                if (is_null($this->getMainRegisterType())) {
                    setcookie('HLOGIN_VERIFICATION_INDEX', $data['sessionkey'], time() + self::REGISTER_SESSION_PERIOD, '/');
                }
            }
        }

        if (!$email) {
            $this->errorCells[] = 'email';
        }

        $this->checkValue('phone');
        $this->checkValue('name');
        $this->checkValue('surname');
        $this->checkValue('address');

        if (empty($this->errorCells)) {
            // Перерегистрация, если менялся
            if ($regData['email'] !== $this->valueCells['email']) {
                if(HloginUserModel::checkEmailAddressAndGetData($this->valueCells['email'])) {
                    return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('registered_email'), [self::OTHER]);
                }
                // Высылка письма на новый E-mail
                $senderResult = $this->sendMailConfirmEmail($email, Translate::get('change_email_on') . ' ' . Request::getHost(), $regData['hash']);
                if (!$senderResult) {
                    return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('total_error'), [self::OTHER]);
                }
                $this->setEnterDataRegister($regData['sessionkey'], $this->valueCells['email'], $regData['regtype']);
                $data['confirm'] = 0;
            }

            // Сохранение логов
            if(!$this->createUserLog($regData, HloginUserLogModel::MODIFICATION_ACTION)) {
                return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('total_error'), [self::OTHER]);
            }

            $data['email'] = $this->valueCells['email'];
            $data['phone'] = !empty($this->valueCells['phone']) ? $this->valueCells['phone'] : null;
            $data['name'] = !empty($this->valueCells['name']) ? $this->valueCells['name'] : null;
            $data['surname'] = !empty($this->valueCells['surname']) ? $this->valueCells['surname'] : null;
            $data['address'] = !empty($this->valueCells['address']) ? $this->valueCells['address'] : null;

            $result = HloginUserModel::setCells('email', $regData['email'], $data);

            if ($result) {
                if ($regData['email'] !== $this->valueCells['email']) {
                    return $this->ajaxFormat('UserMessage', ['title' => Translate::get('user_new_email'), 'message' => Translate::get('user_new_email_text') . ' <b>' . $email . '</b>'], null, [self::OTHER], $email);
                }
                return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, null, [], $this->valueCells['email']);
            }
        }
        return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('nothing_changed'), [self::OTHER]);
    }

    /** Запрос данных пользователя */
    private function ajaxUserGetProfileData() {
        $mainRegisterType = $this->getMainRegisterType();
        if (is_null($mainRegisterType) || $mainRegisterType < UserRegistration::REGISTERED_USER) {
            if($mainRegisterType === UserRegistration::PRIMARY_USER) {
                return $this->ajaxFormat('MessageFromConfirmEmail', json_encode(['success']), null, []);
            }
            return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('user_not_reg'), [self::OTHER]);
        }
        $mainRegisterEmail = $this->getMainRegisterEmail();
        if (empty($mainRegisterEmail)) {
            return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('user_not_reg'), [self::OTHER]);
        }

        $regData = HloginUserModel::checkEmailAddressAndGetData($mainRegisterEmail);
        if (empty($regData['email'])) {
            return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('user_not_reg'), [self::OTHER]);
        }

        $result = [
            'email' => $regData['email'],
            'phone' => $regData['phone'],
            'name' => $regData['name'],
            'surname' => $regData['surname'],
            'address' => $regData['address'],
        ];
        if (empty($regData['confirm'])) {
            return $this->ajaxFormat(self::USER_PROFILE_TYPE, null, Translate::get('email_not_verified') . '. <br>(' . $regData['email'] . ')', [self::OTHER], $regData['email']);
        }
        return $this->ajaxFormat('UserRegisterData', json_encode($result), null, $regData['email']);
    }

    /**
     * Повторная отправка письма с подтверждением E-mail
     */
    private function ajaxSendMessageFromConfirmEmail() {
        $email = $this->getMainRegisterEmail();
        if(empty($email)) {
            return $this->ajaxFormat('MessageFromConfirmEmail', '{}', 'undefined_user', []);
        }
        $newRow = HloginUserModel::checkEmailAddressAndGetData($email);
        // Отправка подтверждения Email
        if (!empty($newRow['id'])) {
            // Отправка письма
            try {
                $senderResult = $this->sendMailConfirmEmail($email, Translate::get('register_on') . ' ' . Request::getHost(), $newRow['hash']);
            } catch (\Throwable $e) {
                error_log($e->getMessage());
                return $this->ajaxFormat('MessageFromConfirmEmail', '{}', 'backend_error_to_send_email', [self::OTHER]);
            }
            if (!$senderResult) {
                return $this->ajaxFormat('MessageFromConfirmEmail', '{}', 'failed_to_send_email', [self::OTHER]);
            }
        }

        return $this->ajaxFormat('MessageFromConfirmEmail', json_encode(['success']), null, []);
    }

    /**
     * Отправка письма из обратной связи
     */
    private function ajaxContactMessage() {
        if(!$this->checkCaptcha()) {
            return $this->ajaxFormat(self::CONTACT_MESSAGE, null, Translate::get('captcha_error'), ['captcha']);
        }
        if (!$this->applyMainExplorerCheck(self::CONTACT_MESSAGE)) {
            return $this->ajaxFormat(self::CONTACT_MESSAGE, null, Translate::get('data_validation_failed'), [self::OTHER]);
        }
        $email = Helper::convertEmail($this->checkedCell('email', self::EMAIL_PATTERN));
        if (!$email) {
            $this->errorCells[] = 'email';
        }
        $message = $this->checkedCell('contact_text', self::MESSAGE_PATTERN);
        // Проверка сообщения и антибот от простой подстановки набора символов в поля.
        if(!$message || strripos(trim($message), ' ') === false) {
            $this->errorCells[] = 'contact_text';
        }
        $name = $this->checkedCell('sender_name', self::NAME_PATTERN);
        if(!isset($this->data['sender_name']['value']) || trim($this->data['sender_name']['value']) === '') {
            $name = 'User';
        }
        if(is_null($name)) {
            $this->errorCells[] = 'sender_name';
        }
        if(count($this->errorCells)) {
            return $this->ajaxFormat(self::CONTACT_MESSAGE, null, Translate::get('data_validation_failed'), $this->errorCells);
        }
        $senderResult = $this->sendMailContactMessage($name, $email, $message);
        if (!$senderResult) {
            return $this->ajaxFormat(self::CONTACT_MESSAGE, null, Translate::get('total_error'), [self::OTHER]);
        }
        return $this->ajaxFormat('MessageAfterContact', json_encode(['success']), null, [], $email);
    }

    /**
     * Установка прав входа на сайт.
     * @param string $sessionkey
     * @param int $regtype
     */
    private function setEnterDataRegister(string $sessionkey, $email, $regtype = UserRegistration::UNDEFINED_USER) {
        if (!empty($email)) {
            UserRegistration::setType($regtype);
            UserRegistration::setEmailAddress($email);
            $_SESSION['HLOGIN_REGISTRATION_ID'] = $sessionkey;
        }
    }

    /**
     * Возвращает E-mail текущего зарегистрированного пользователя
     * @return string|null
     */
    private function getMainRegisterEmail() {
        $email = UserRegistration::checkEmailAddress();
        return is_string($email)? $email : null;
    }

    /**
     * Возвращает тип регистрации текущего зарегистрированного пользователя
     * @return int|null
     */
    private function getMainRegisterType() {
        $type = UserRegistration::getNumericType();
        return is_int($type) ? $type : null;
    }

    /**
     * При запросе одного и того же пользователя проверяет частоту запросов
     * @param int $reqtime - значение предыдущего запроса из бд
     * @return bool
     */
    private function getSearchBruteForce(int $reqtime) {
        return $reqtime + 3 > time();
    }

    /**
     * Генерация произвольного валидного пароля пользователя
     * @return string
     */
    private function getPasswordGenerator() {
        $symbols = str_shuffle('abcdefghkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ123456789');
        $length = rand(6, 10);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $symbols[rand(0, strlen($symbols) - 1)];
        }
        return $password;
    }

    /**
     * Сдандартизированный возврат для ajax API
     * @param string $type - тип выполняемого действия, например 'UserEnter' - вход
     * @param array|string|null $result - результирующее успешное значение
     * @param string|null $error - текст сообщения или null как отсутствие ошибки
     * @param array $error_cells - перечень data-type невалидных полей
     * @param string|null $email - проверенный E-mail для заполнения в дальнейшем
     * @return false|string
     */
    private function ajaxFormat(string $type, $result = null, $error = null, $error_cells = [], $email = null) {
        return json_encode(['result' => $result, 'type' => $type, 'error' => $error, 'error_cells' => $error_cells, 'email' => $email]);
    }

    /**  Конвертация пришедших из POST значений в более удобный формат  */
    private function reformatDataByPost() {
        $post = Request::getPost();
        $jsonData = json_decode(rawurldecode($post['json_data'] ?? '{}'), true);

        if ($jsonData && isset($jsonData['data']) && isset($jsonData['name'])) {
            $this->data['name'] = $jsonData['name'];
        }
        if ($jsonData && isset($jsonData['code'])) {
            $this->code = $jsonData['code'];
        }
        $data = !empty($jsonData['data']) ? $jsonData['data'] : [];
        foreach ($data as $str) {
            if (isset($str['data-type'])) {
                $this->data[$str['data-type']] = $str;
            }
        }
    }

    /**
     * Присваевает общим переменным для значения и ошибок соответствующие значения после проверки
     * @param string $name - название первичного типа параметра
     * @param string|null $secondName - название параметра из data-type, если отличается от его первичного типа
     * @param string|null $pattern
     */
    private function checkValue(string $name, string $secondName = null, string $pattern = null) {
        if ($this->getConfig('cell_' . $name) || $this->getConfig('profile_' . $name) || $name == 'password') {
            $variableName = is_null($secondName) ? $name : $secondName;
            $value = $this->checkedCell($variableName, $pattern);
            if ((
                    // Если параметр в списке могущих быть обязательными и выбран обязательным или всегда обязательные, выполняется проверка значения
                    in_array($name, self::REQUIRED_CELLS) && $this->getRequired('required_' . $name)) || in_array($name, self::MANDATORY_CELLS)) {
                // Значение проверяется
                if (empty($value)) {
                    $this->errorCells[] = $variableName;
                    return;
                }
            }
            $this->valueCells[$variableName] = $value;
        }
    }

    /**
     * Возвращает обязательность наличия параметра
     * @param string $name - cell_ и название параметра
     * @return bool
     */
    private function getConfig(string $name) {
        $hlogin = Main::getConfigHlogin();
        return !empty($hlogin['reg_data'][$name]) && $hlogin['reg_data'][$name] == 'on';
    }

    /**
     * Возвращает обязательность значения поля
     * @param string $name - required_ и название параметра
     * @return bool
     */
    private function getRequired(string $name) {
        $hlogin = Main::getConfigHlogin();
        return !empty($hlogin['reg_data'][$name]) && $hlogin['reg_data'][$name] == 'on';
    }

    /**
     * Поверяет на соответствие регулярному выражению и в случае успеха возвращает значение
     * @param string $name - название параметра из data-type
     * @param string|null $pattern - регулярное выражение
     * @return string|null
     */
    private function checkedCell(string $name, $pattern = null) {
        $value = trim($this->data[$name]['value'] ?? '');
        return (!empty($value) && (is_null($pattern) || preg_match($pattern, $value))) ? $value : null;
    }

    /**
     * Поверяет выбран ли чекбокс
     * @param string $name - название параметра из data-type
     * @return bool
     */
    private function getCheckboxValue(string $name) {
        return $this->data[$name]['checked'] === "checked";
    }

    /**
     * Отправляет письмо для подтверждения E-mail
     * @param string $to - E-mail отправителя
     * @param string $title - заголовок письма
     * @param string $hashFromEmail - хеш для подтверждения
     * @param null|string $password = дополнительная отправка пароля (если это необязательное поле при регистрации)
     * @return bool
     */
    private function sendMailConfirmEmail(string $to, string $title, string $hashFromEmail, $password = null) {
        $design = $this->getEmailDesign();
        $confirmUrl = Request::getFullHost() . '/' . $this->lang . '/login/action/confirm/?code=' . $hashFromEmail;
        $userPassword = !empty($password) ? "\n" . '<br>' . Translate::get('password') . ': <b>' . $password . '</b><br>' . "\n" : '';
        $textStandard = Translate::getMailData($design, 'email_confirm_message', ['confirm_link' => $confirmUrl, 'warning' => Translate::get('warning_below'), 'user_password' => $userPassword]);
        if($this->isDuplicateOnEnglish() && $this->lang !== 'en') {
            $textStandard .= "\n" . self::LINE . "\n" . "<br><br>" .  "\n" . Translate::getMailData($design, 'email_confirm_message', ['confirm_link' => $confirmUrl, 'warning' => Translate::get('warning_below', 'en'), 'user_password' => $userPassword], 'en');
        }
        $params = [
            'to' => $to,
            'from' => $this->getEmailFrom(),
            'name' => Request::getDomain(),
            'title' =>  $title,
            'header' => Translate::get('email_confirm_header'),
            'design' => $design,
            'save_log' => $this->getEmailSaveToLog(),
            'message' => $textStandard . "\n" . $this->getTextUnderMessage(),
        ];
        $post = new SendEmail($params, $this->getNotSendByEmail());
        if (count($post->getErrors())) {
            return false;
        }
        $post->send();
        return empty($post->getErrors());
    }

    /**
     * Отправка письма для изменения пароля
     * @param string $to
     * @param string $title
     * @param string $hash
     * @param int $senderId
     * @param string $senderMail
     * @return bool
     */
    private function sendMailPasswordRecovery(string $to, string $title, string $hash, int $senderId, string $senderMail) {
        $design = $this->getEmailDesign();
        $code = Helper::createDynamicHash($senderId, $senderMail) . '-' . $hash;
        $recoveryUrl = Request::getFullHost() . '/' . $this->lang . '/login/action/recovery/?code=' . $code;
        $textStandard = Translate::getMailData($design, 'password_recovery_message', ['recovery_link' => $recoveryUrl, 'warning' => Translate::get('warning_below')]);
        if($this->isDuplicateOnEnglish() && $this->lang !== 'en') {
            $textStandard .= "\n" . self::LINE . "\n" . "<br><br>" .  "\n" . Translate::getMailData($design, 'password_recovery_message', ['recovery_link' => $recoveryUrl, 'warning' => Translate::get('warning_below', 'en')], 'en');
        }
        $params = [
            'to' => $to,
            'from' => $this->getEmailFrom(),
            'name' => Request::getDomain(),
            'title' =>  $title,
            'header' => Translate::get('password_recovery_header'),
            'design' => $design,
            'save_log' => $this->getEmailSaveToLog(),
            'message' => $textStandard . "\n" . $this->getTextUnderMessage(),
        ];
        $post = new SendEmail($params, $this->getNotSendByEmail());
        if (count($post->getErrors())) {
            return false;
        }
        $post->send();
        return empty($post->getErrors());
    }

    /**
     * Отправка письма от пользователя
     * @param string $senderName
     * @param string $senderMail
     * @param string $message
     * @return bool
     */
    private function sendMailContactMessage(string $senderName, string $senderMail, string $message) {
        $design = $this->getEmailDesign();
        $register = UserRegistration::checkPrimaryAndHigher();
        $params = [
            'to' => $this->getEmailTo(),
            'from' => $this->getEmailFrom(),
            'name' => $senderName,
            'title' =>  ($register ? 'Message from register user (ID ' . UserRegistration::getUserId() . '):' : 'Message from:' ) . $senderName . ". " . Request::getDomain(),
            'header' => 'Feedback',
            'design' => $design,
            'save_log' => $this->getEmailSaveToLog(),
            'message' => "Message from: " . $senderName . " [" . $senderMail . "]<br>" . "\n" .  Translate::getMailData($design, 'feeedback_message', ['message' => $message]),
        ];
        $post = new SendEmail($params, $this->getNotSendByEmail());
        if (count($post->getErrors())) {
            return false;
        }
        $post->send();
        return empty($post->getErrors());
    }

    /**
     * Возвращает результат основной проверки по пользовательским правилам
     * класса 'App\Optional\MainHloginExplorer', если он подключен.
     * Класс должен иметь метод 'check'.
     * @param string $queryType - тип запроса, согласно константам в классе 'Phphleb\Hlogin\App\OriginData'.
     * @return bool
     */
    private function applyMainExplorerCheck(string $queryType) {
        if (class_exists('App\Optional\MainHloginExplorer')) {
            return (bool)(new \App\Optional\MainHloginExplorer())->check($queryType);
        };
        return true;
    }

    /**
     * Устанавливает обработку промокода для
     * класса 'App\Optional\MainHloginExplorer', если он подключен.
     * Класс должен иметь метод 'setPromocode'.
     * @param string|false $value - значение промокода.
     * @param int $userId - идентификатор пользователя, для которого пришло значение.
     */
    private function applyMainExplorerCheckPromocode($value, $userId) {
        if (class_exists('App\Optional\MainHloginExplorer') && $value !== false) {
            (new \App\Optional\MainHloginExplorer())->setPromocode($value, $userId);
        }
    }

    /**
     * Если до входа запрашивался url на защищенную страницу, то возвращается этот url
     * @return string|false
     */
    private function getRedirectToUrlAfterEnter() {
        if(isset($_SESSION['HLEB_REGISTER_REDIRECT_URL'])) {
            $url = $_SESSION['HLEB_REGISTER_REDIRECT_URL'];
            unset($_SESSION['HLEB_REGISTER_REDIRECT_URL']);
            return $url ?? '/';
        }
        return false;
    }

    /**
     * Действия отката при неудачной отправке подтверждающего письма регистрации.
     */
    private function actionsAfterUnsuccessfulSending(string $email, string $error) {
        $deleteRow = HloginUserModel::deleteUser($email);
        return $this->ajaxFormat(self::USER_REGISTER_TYPE, null, Translate::get('failed_to_create_user') . ' ' . $error, [self::OTHER]);
    }

    /**
     * Сохраняет в таблицу `userlogs` историю пользователя
     * @param array $parent - данные пользователя
     * @param string $action - тип логирования
     * @return mixed
     */
    private function createUserLog(array $parent, string $action = HloginUserLogModel::REG_ACTION){
        try {
            $result = HloginUserLogModel::createRowFromData(
                $parent['id'],
                $parent['regtype'],
                $action,
                $parent['email'],
                $this->standardization($parent['ip'] ?? null),
                $this->standardization($parent['name'] ?? null),
                $this->standardization($parent['surname'] ?? null),
                $this->standardization($parent['phone'] ?? null),
                $this->standardization($parent['address'] ?? null)
            );
        } catch (\Throwable $e) {
            return false;
        }
        return $result;
    }

    private function getEmailFrom() {
        $config = Main::getConfigMuller();
        if (!empty($config['data']['mail_from'])) {
            return $config['data']['mail_from'];
        }
        return 'no-reply@' . Request::getDomain();
    }

    private function getEmailTo() {
        $config = Main::getConfigContact();
        if (!empty($config['data']['mail_to'])) {
            return $config['data']['mail_to'];
        }
        return 'admin@' . Request::getDomain();
    }

    private function getTextUnderMessage() {
        $config = Main::getConfigMuller();
        return !empty($config['data']['regards_block']) ? html_entity_decode($config['data']['regards_block']) : '';
    }

    private function getEmailDesign() {
        $config = Main::getConfigMuller();
        if (!empty($config['data']['design']) && $config['data']['design'] !== 'auto') {
            return $config['data']['design'];
        }
        $hlogin = Main::getConfigHlogin();
        return $hlogin['reg_data']['design'];
    }

    private function isDuplicateOnEnglish() {
        $config = Main::getConfigMuller();
        return isset($config['data']['duplicate-en-text']) && $config['data']['duplicate-en-text'] === 'on';
    }

    private function getEmailSaveToLog() {
        $config = Main::getConfigMuller();
        return isset($config['data']['save_log']) && $config['data']['save_log'] === 'on';
    }

    private function getNotSendByEmail() {
        $config = Main::getConfigMuller();
        return isset($config['data']['not_send_by_email']) && $config['data']['not_send_by_email'] === 'on';
    }

    /**
     * Проверка кода капчи
     * @return bool
     */
    private function checkCaptcha() {
        $config = Main::getConfigUCaptchaData();
        if(isset($config['active']) && $config['active'] !== 'on'){
            return true;
        }
        if((new Captcha)->isPassed()) {
            return true;
        }
        return (new Captcha)->check($this->data['captcha']['value']);
    }

    /**
     * Возвращает URL из конфига
     * @return string
     */
    private function urlAfterRegistration() {
        $url = '/' . $this->lang . '/login/profile/';
        $config = Main::getConfigHlogin();
        if (!empty($config['reg_data']['get_url_after_reg'])) {
            $url = str_replace('$LANG', $this->lang, $config['reg_data']['get_url_after_reg']);
        }
        return $url;
    }

    /**
     * Проверка на необязательность пароля (регистрация только по E-mail)
     * @return bool
     */
    private function isSetPassword() {
        $config = Main::getConfigHlogin();
        return !empty($config['reg_data']['cell_password']) && $config['reg_data']['cell_password'] == 'on';
    }

    /**
     * @return bool
     */
    private function selectConfirmCells() {
        $config = Main::getConfigHlogin()['reg_data'];
        return (isset($config['cell_privacy_policy'], $config['cell_terms']) && $config['cell_privacy_policy'] === 'on' && $config['cell_terms'] === 'on');
    }

    /**
     * @return bool
     */
    private function checkConfirmCell() {
        return !empty($this->data['confirm']['value']) && $this->data['confirm']['value'] === 'on';
    }

    /**
     * @param $value
     * @return mixed|null
     */
    private function standardization($value) {
        return is_null($value) || $value === '' ? null : $value;
    }


}

