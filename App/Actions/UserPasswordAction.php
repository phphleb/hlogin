<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

use App\Bootstrap\Auth\Handlers\AdditionalPassword;
use Hleb\Helpers\HostHelper;
use Hleb\Static\Log;
use Hleb\Static\Request;
use Hleb\Static\Router;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\CurrentUser;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Data\NewPasswordHash;
use Phphleb\Hlogin\App\Exceptions\HloginMailException;
use Phphleb\Hlogin\App\Helpers\EmailHelper;
use Phphleb\Hlogin\App\Mail\PasswordRecoveryMail;
use Phphleb\Hlogin\App\Models\UserModel;
use Phphleb\Hlogin\App\RegData;
use Phphleb\Hlogin\App\RegType;

/**
 * Processes the request from the password recovery panel and sends an email with a link.
 *
 * Обрабатывает запрос из панели восстановления пароля и высылает письмо со ссылкой.
 *
 * @internal
 */
final class UserPasswordAction extends AbstractBaseAction
{
    /** @inheritDoc */
    #[\Override]
   public function execute(#[\SensitiveParameter] array $params): array
   {
       $email = $params['value']['email'] ?? null;
       $captcha = $params['value']['captcha'] ?? null;
       $detector = $params['value']['detector'] ?? null;

       $requiredError = AuthLang::trans($this->lang, 'error_empty_data');
       $formatError = AuthLang::trans($this->lang, 'error_pattern_data');
       $captchaError = AuthLang::trans($this->lang, 'captcha_error');

       $errorCells = [];

       // Check the E-mail format (always required).
       // Проверка формата E-mail (всегда обязательный).
       if (empty($email)) {
           $errorCells['email'] = $requiredError;
       } else {
           $config = ConfigStorage::getConfig();
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

       // There is no request rate check here so that access can be restored in case of brute force.
       // Проверки частоты запросов здесь нет, чтобы можно было восстановить доступ в случае брутфорса.

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

       if ($errorCells) {
           return $this->getErrorResponse(['form' => $errorCells, 'captcha' => $isCaptcha], 'Form validation error');
       }

       $currentUser = CurrentUser::get();
       if ($currentUser) {
           return $this->getErrorResponse([], 'The current user is already authorized.');
       }

       $insertHandler = \class_exists(AdditionalPassword::class);
       if ($insertHandler) {
           $handler = (new AdditionalPassword());
           if (!\is_subclass_of($handler, BaseAdditional::class)) {
               throw new \RuntimeException('The action class must inherit from ' . BaseAdditional::class);
           }
           if ($handler->insert($params) === false) {
               return $this->getErrorResponse(['data' => null, 'captcha' => $isCaptcha, 'system_message' => $handler->getErrorMessage()], 'These forms have not been verified');
           }
       }

       // Check if the user exists.
       // Проверка существования пользователя.
       $user = UserModel::getCells('email', EmailHelper::convert($email), ['id', 'email', 'sessionkey', 'regtype']);
       if (!$user) {
           $loginMatch = AuthLang::trans($this->lang, 'undefined_email');
           return $this->getErrorResponse(['data' => null, 'captcha' => $isCaptcha, 'system_message' => $loginMatch], 'User not found');
       }
       if ($user['regtype'] < RegType::PRIMARY_USER) {
           return $this->getErrorResponse([
               'data' => null,
               'system_message' => AuthLang::trans($this->lang, 'deleted_user'),
               'action' => ['type' => 'UserPassword']
           ], 'The user with this E-mail has been deleted or blocked');
       }


       if ($insertHandler) {
           $handler->afterAction($user['id']);
       }

       $code = NewPasswordHash::generate($user['id'], $email);

       UserModel::setCells('id', $user['id'], ['code' => $code]);

       $senderName = Request::getHost();
       if (!empty($config['mail']['sender-name'])) {
           $senderName = $config['mail']['sender-name'];
       }
       $header = AuthLang::trans($this->lang, 'password_recovery_header');
       $title = $senderName . ': ' . $header;
       $secureLink = Router::address(
               routeName: 'hlogin.action.page',
               replacements: ['lang' => $this->lang, 'action' => 'recovery'],
               endPart: false,
           ) . '?code=' . $code;

       $mailFrom = EmailHelper::default(Request::getHost());
       if (!empty($config['mail']['from'])) {
           $mailFrom = $config['mail']['from'];
       }

       try {
           (new PasswordRecoveryMail(
               $senderName,
               $mailFrom,
               EmailHelper::convert($email),
               $this->lang,
               onlyToFile: HostHelper::isLocalhost(Request::getHost()),
           ))->send($title, $header, $secureLink);
       } catch (HloginMailException $e) {
           Log::error($e->getMessage(), ['email' => $email, 'title' => $title, 'name' => $senderName]);

           return $this->getErrorResponse(['data' => null, 'system_message' => $e->getMessage()], 'Error sending email');
       }

       return $this->getSuccessResponse(
           [
               'data' => [
                   'id' => AuthLang::trans($this->lang, 'password_send_title'),
                   'value' => AuthLang::trans($this->lang, 'password_recovery_text'),
               ],
               'action' => ['type' => 'CustomMessage'],
               'captcha' => true,
           ],
           'Successful send E-mail',
       );
   }
}