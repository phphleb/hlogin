<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

use App\Bootstrap\Auth\Handlers\AdditionalRegister;
use Hleb\Helpers\Abracadabra;
use Hleb\Helpers\HostHelper;
use Hleb\Static\Log;
use Hleb\Static\Request;
use Hleb\Static\Router;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\Content\ConfigCellNormalizer;
use Phphleb\Hlogin\App\CurrentUser;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Data\EmailRecoveryHash;
use Phphleb\Hlogin\App\Data\PasswordData;
use Phphleb\Hlogin\App\Exceptions\HloginMailException;
use Phphleb\Hlogin\App\Helpers\EmailHelper;
use Phphleb\Hlogin\App\Mail\ConfirmRegisterEmail;
use Phphleb\Hlogin\App\Models\UserModel;
use Phphleb\Hlogin\App\RegData;

/**
 * @internal
 */
final class UserRegisterAction extends AbstractBaseAction
{
    /** @inheritDoc */
    #[\Override]
   public function execute(array $params): array
   {
       $errorCells = [];
       $email = $params['value']['email'] ?? null;
       $password = $params['value']['password'] ?? null;
       $password1 = $params['value']['password1'] ?? null;
       $captcha = $params['value']['captcha'] ?? null;
       $detector = $params['value']['detector'] ?? null;
       $terms = $params['value']['terms'] ?? null;
       $login = $params['value']['login'] ?? null;

       \is_string($login) and $login = \trim($login);

       // Composite address if there are allowed domains.
       // Составной адрес при наличии разрешенных доменов.
       if ($email && !empty($params['value']['domain'])) {
           $email .= '@' . $params['value']['domain'];
       }

       $requiredError = AuthLang::trans($this->lang, 'error_empty_data');
       $formatError = AuthLang::trans($this->lang, 'error_pattern_data');
       $captchaError = AuthLang::trans($this->lang, 'captcha_error');
       $checkboxError = AuthLang::trans($this->lang, 'error_empty_checkbox');
       $passwordMismatchError = AuthLang::trans($this->lang, 'error_password_mismatch');

       $data = [];

       $config = ConfigStorage::getConfig();
       if ($config['registration']['enter-only']) {
           return $this->getErrorResponse(['system_message' => AuthLang::trans($this->lang, 'registration_disabled')], 'Registration is disabled');
       }

       // Check the E-mail format (always required).
       // Проверка формата E-mail (всегда обязательный).
       if (empty($email)) {
           $errorCells['email'] = $requiredError;
       } else {
           $emailPattern = $config['registration']['email-pattern'] ?? RegData::EMAIL_PATTERN;
           if (!preg_match($emailPattern, (string)$email) || $detector !== '') {
               $errorCells['email'] = $formatError;
           }
       }

       // Checking the connection to the database.
       // Проверка подключения к базе данных.
       if (!UserModel::checkTableUsers()) {
           return $this->getErrorResponse([
               'system_message' => 'No data was received from the database'],
               'Problem getting data from a table with users'
           );
       }

       // Checking the frequency of requests to one E-mail.
       // Проверка частоты запросов к одному E-mail.
       if (!$this->requestRateCheck((string)$email)) {
           return $this->getErrorResponse(['system_message' => 'Try again later'], 'Time ban');
       }

       // Status of successful completion of captcha.
       // Статус успешного прохождения captcha.
       $isCaptcha = false;
       // Checking the existence of captcha and the correctness of the code for it.
       // Проверка существования captcha и правильности кода для неё.
       if (!$this->captchaCheck((string)($captcha))) {
           $errorCells['captcha'] = $captchaError;
       } else if ($this->captchaIsActive()) {
           $isCaptcha = true;
       }

       $cells = (new ConfigCellNormalizer())->update($config['registration']['cells']);
       $tableCells = $config['registration']['table-cell-length'];
       foreach ($tableCells as $k => $length) {
           $value = $params['value'][$k] ?? null;
           \is_string($value) and $params['value'][$k] = \trim($value);
           if ($cells[$k]['on']) {
               if ($cells[$k]['req'] && $value === '' || $value === null) {
                   $errorCells[$k] = $requiredError;
               }
               if (\mb_strlen((string)$value) && \mb_strlen((string)$value) > $length) {
                   $errorCells[$k] = $formatError;
               }
           }
       }
       if (($cells['terms-of-use']['on'] || $cells['privacy-policy']['on']) && empty($terms)) {
           $errorCells['terms'] = $checkboxError;
       }

       if ($cells['password']['on']) {
           if (empty($password)) {
               $errorCells['password'] = $requiredError;
           } else {
               if (!PasswordData::validatePasswordString((string)$password)) {
                   $errorCells['password'] = $formatError;
               }
           }
           if (empty($password1)) {
               $errorCells['password1'] = $requiredError;
           } else {
               if (!PasswordData::validatePasswordString((string)$password1)) {
                   $errorCells['password1'] = $formatError;
               }
               if ($password1 !== $password) {
                   $errorCells['password1'] = $passwordMismatchError;
               }
           }
       } else {
           $password = Abracadabra::generate(8);
       }

       // If domains for mail are limited to a list, you need to send this list in the response.
       // Если домены для почты ограничены списком, нужно прислать этот список в ответе.
       if ($config['mail']['email-sources'] && !empty($email) && \str_contains($email, '@')) {
           $emailDomain = \explode('@', $email)[1];
           if (!\in_array($emailDomain, $config['mail']['email-sources'])) {
               $errorCells['email'] = $formatError;
               $data = $config['mail']['email-sources'];
           }
       }


       if ($errorCells) {
           return $this->getErrorResponse(['form' => $errorCells, 'data' => $data, 'captcha' => $isCaptcha], 'Form validation error');
       }

       $currentUser = CurrentUser::get();
       if ($currentUser) {
           return $this->getErrorResponse([], 'The current user is already authorized.');
       }

       // Check if the user exists.
       // Проверка существования пользователя.
       $user = UserModel::getCells('email', EmailHelper::convert($email), ['id']);
       if ($user) {
           $error = AuthLang::trans($this->lang, 'registered_email');
           return $this->getErrorResponse(['data' => null, 'captcha' => $isCaptcha, 'system_message' => $error], 'There has already been an attempt to register with this E-mail');
       }

       if (!empty($login)) {
           $user = UserModel::getCells('login', $login, ['id']);
           if ($user) {
               $error = AuthLang::trans($this->lang, 'exists');
               return $this->getErrorResponse(['data' => null, 'captcha' => $isCaptcha, 'system_message' => $error], 'An attempt has already been made to register with this login');
           }
       }

       $insertHandler = \class_exists(AdditionalRegister::class);
       if ($insertHandler) {
           $handler = (new AdditionalRegister());
           if (!\is_subclass_of($handler, BaseAdditional::class)) {
               throw new \RuntimeException('The action class must inherit from ' . BaseAdditional::class);
           }
           if ($handler->insert($params) === false) {
               return $this->getErrorResponse(['data' => null, 'captcha' => $isCaptcha, 'system_message' => $handler->getErrorMessage()], 'These forms have not been verified');
           }
       }

       UserModel::createNewUser(
           $email,
           $password,
           \time(),
           login: $params['value']['login'] ?? null,
           name: $params['value']['name'] ?? null,
           surname: $params['value']['surname'] ?? null,
           phone: $params['value']['phone'] ?? null,
           address: $params['value']['address'] ?? null,
           promocode: $params['value']['promocode'] ?? null,
           ip: Request::getUri()->getIp(),
           subscription: (int)($params['value']['subscription'] ?? 0),
           hash: null,
       );
       $user = PasswordData::getUserByPassword(EmailHelper::convert($email), $password, ['id', 'email', 'sessionkey', 'hash']);
       if (!$user) {
           return $this->getErrorResponse(['data' => null, 'captcha' => $isCaptcha, 'system_message' => 'u_error'], 'Failed to create user');
       }

       $hash = EmailRecoveryHash::generate($user['id'], $user['email']);

       UserModel::setCells('id', $user['id'], ['hash' => $hash]);

       if ($insertHandler) {
           $handler->afterAction($user['id']);
       }

       /**
        * The user needs to be re-verified as changes may have been made from outside.
        *
        * Необходимо заново проверить пользователя, так как могли быть внесены изменения извне.
        */
       $updUser = PasswordData::getUserByPassword(EmailHelper::convert($email), $password, ['id', 'email', 'sessionkey', 'hash']);

       if ($user['id'] !== $updUser['id'] || $user['sessionkey'] !== $updUser['sessionkey'] && $updUser['hash'] !== $hash) {
           throw new \RuntimeException("The new user's details do not match.");
       }

       $senderName = Request::getHost();
       if (!empty($config['mail']['sender-name'])) {
           $senderName = $config['mail']['sender-name'];
       }
       $title = $senderName . ': ' . AuthLang::trans($this->lang, 'email_confirm_title');
       $header = AuthLang::trans($this->lang, 'email_confirm_header');

       // The ban on displaying a password in E-mail does not apply to registration without a password.
       // Запрет на отображение пароля в E-mail не распространяется на случай регистрации без пароля.
       if (!$config['registration']['password-in-mail'] && $cells['password']['on']) {
           $password = null;
       }

       $secureLink = Router::address(
               routeName: 'hlogin.action.page',
               replacements: ['lang' => $this->lang, 'action' => 'confirm'],
               endPart: false,
           ) . '?code=' . $updUser['hash'];

       $mailFrom = EmailHelper::default(Request::getHost());
       if (!empty($config['mail']['from'])) {
           $mailFrom = $config['mail']['from'];
       }
       try {
           (new ConfirmRegisterEmail(
               $senderName,
               $mailFrom,
               EmailHelper::convert($email),
               $this->lang,
               onlyToFile: HostHelper::isLocalhost(Request::getHost()),
           ))->send($title, $header, $secureLink, \strip_tags($password));
       } catch (HloginMailException $e) {
           Log::error($e->getMessage(), ['email' => $email, 'title' => $title, 'name' => $senderName]);

           return $this->getErrorResponse(['data' => null, 'captcha' => $isCaptcha, 'system_message' => $e->getMessage()], $e->getMessage());
       }

       return $this->getSuccessResponse(
           [
               'data' => $user['email'],
               'action' => ['type' => 'ReloadPage'],
               'captcha' => $isCaptcha
           ],
           'Successful registration',
       );
   }
}
