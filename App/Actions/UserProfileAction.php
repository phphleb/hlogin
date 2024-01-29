<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

use App\Bootstrap\Auth\Handlers\AdditionalProfile;
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
use Phphleb\Hlogin\App\RegType;

/**
 * @internal
 */
final class UserProfileAction extends AbstractBaseAction
{
    /** @inheritDoc */
    #[\Override]
   public function execute(array $params): array
   {
       $errorCells = [];
       $email = $params['value']['email'] ?? null;
       $password = $params['value']['password'] ?? null;
       $password1 = $params['value']['password1'] ?? null;
       $password2 = $params['value']['password2'] ?? null;
       $login = $params['value']['login'] ?? null;

       \is_string($login) and $login = \trim($login);

       // Composite address if there are allowed domains.
       // Составной адрес при наличии разрешенных доменов.
       if ($email && !empty($params['value']['domain'])) {
           $email .= '@' . $params['value']['domain'];
       }

       $requiredError = AuthLang::trans($this->lang, 'error_empty_data');
       $formatError = AuthLang::trans($this->lang, 'error_pattern_data');
       // $checkboxError = AuthLang::trans($this->lang, 'error_empty_checkbox');
       $passwordMismatchError = AuthLang::trans($this->lang, 'error_password_mismatch');

       $data = [];

       $config = ConfigStorage::getConfig();

       // Check the E-mail format (always required).
       // Проверка формата E-mail (всегда обязательный).
       if (empty($email)) {
           $errorCells['email'] = $requiredError;
       } else {
           $emailPattern = $config['registration']['email-pattern'] ?? RegData::EMAIL_PATTERN;
           if (!preg_match($emailPattern, (string)$email)) {
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

       $cells = (new ConfigCellNormalizer())->update($config['registration']['cells']);
       $tableCells = $config['registration']['table-cell-length'];
       foreach ($tableCells as $k => $length) {
           $value = $params['value'][$k] ?? null;
           \is_string($value) and $params['value'][$k] = \trim($value);
           if ($cells[$k]['prof']) {
               if ($cells[$k]['req'] && $value === '' || $value === null) {
                   $errorCells[$k] = $requiredError;
               }
               if (\mb_strlen((string)$value) && \mb_strlen((string)$value) > $length) {
                   $errorCells[$k] = $formatError;
               }
           }
       }

       if (empty($password)) {
           $errorCells['password'] = $requiredError;
       } else {
           if (!PasswordData::validatePasswordString((string)$password)) {
               $errorCells['password'] = $formatError;
           }
       }
       $newPassword = null;
       // Change password.
       // Изменение пароля.
       if (!empty($password1)) {
           if (!PasswordData::validatePasswordString((string)$password1)) {
               $errorCells['password1'] = $formatError;
           }
           if ($password1 !== $password2) {
               $errorCells['password2'] = $passwordMismatchError;
           }
           if (empty($password2)) {
               $errorCells['password2'] = $requiredError;
           }
           $newPassword = PasswordData::generateHash($password1);
       }

       $currentUser = CurrentUser::get();
       if (!$currentUser) {
           return $this->getErrorResponse([], AuthLang::trans($this->lang, 'user_not_reg'));
       }
       if ($currentUser['regtype'] < RegType::UNDEFINED_USER) {
           return $this->getErrorResponse([
               'data' => null,
               'system_message' => AuthLang::trans($this->lang, 'deleted_user'),
               'action' => ['type' => 'UserEnter']
           ], 'The user with this E-mail has been deleted or blocked');
       }
       if ($currentUser['regtype'] < RegType::REGISTERED_USER  || !$currentUser['confirm']) {
           return $this->getSuccessResponse(
               [
                   'data' => [
                       'id' => AuthLang::trans($this->lang, 'profile_page'),
                       'value' => AuthLang::trans($this->lang, 'email_not_confirm'),
                   ],
                   'action' => ['type' => 'CustomEmailMessage'],
                   'captcha' => true,
               ],
               'To receive data, you must confirm your email.',
           );
       }


       $hash = $currentUser['hash'];
       if ($email !== $currentUser['email']) {
           // Check if the user exists.
           // Проверка существования пользователя.
           $user = UserModel::getCells('email', EmailHelper::convert($email), ['id']);
           if ($user) {
               $error = AuthLang::trans($this->lang, 'registered_email');
               return $this->getErrorResponse(['data' => null, 'system_message' => $error], 'There has already been an attempt to register with this E-mail');
           }
           $hash = EmailRecoveryHash::generate($currentUser['id'], $email);

           // If domains for mail are limited to a list, you need to send this list in the response.
           // Если домены для почты ограничены списком, нужно прислать этот список в ответе.
           if ($config['mail']['email-sources'] && !empty($email) && \str_contains($email, '@')) {
               $emailDomain = \explode('@', $email)[1];
               if (!\in_array($emailDomain, $config['mail']['email-sources'])) {
                   $errorCells['email'] = $formatError;
                   $data = $config['mail']['email-sources'];
               }
           }
       }

       if ($errorCells) {
           return $this->getErrorResponse(['form' => $errorCells, 'data' => $data], 'Form validation error');
       }

       if (!empty($login) && $login !== $currentUser['login']) {
           $user = UserModel::getCells('login', $login, ['id']);
           if ($user) {
               $error = AuthLang::trans($this->lang, 'exists');
               return $this->getErrorResponse(['data' => null, 'system_message' => $error], 'An attempt has already been made to register with this login');
           }
       }

       $insertHandler = \class_exists(AdditionalProfile::class);
       if ($insertHandler) {
           $handler = (new AdditionalProfile());
           if (!\is_subclass_of($handler, BaseAdditional::class)) {
               throw new \RuntimeException('The action class must inherit from ' . BaseAdditional::class);
           }
           if ($handler->insert($params) === false) {
               return $this->getErrorResponse(['data' => null, 'system_message' => $handler->getErrorMessage()], 'These forms have not been verified');
           }
       }

       UserModel::updateUser(
           $currentUser['email'],
           $email,
           $newPassword ?? $currentUser['password'],
           \time(),
           (bool)$currentUser['confirm'],
           $currentUser['regtype'],
           login: $params['value']['login'] ?? null,
           name: $params['value']['name'] ?? null,
           surname: $params['value']['surname'] ?? null,
           phone: $params['value']['phone'] ?? null,
           address: $params['value']['address'] ?? null,
           promocode: $params['value']['promocode'] ?? null,
           ip: Request::getUri()->getIp(),
           subscription: $currentUser['subscription'],
           hash: $hash,
       );

       if ($insertHandler) {
           $handler->afterAction($currentUser['id']);
       }

       if ($email === $currentUser['email']) {
           return $this->getSuccessResponse(
               [
                   'data' => $email,
                   'system_message' => AuthLang::trans($this->lang, 'message_log_success'),
               ],
               'Successful update',
           );
       }

       $senderName = Request::getHost();
       if (!empty($config['mail']['sender-name'])) {
           $senderName = $config['mail']['sender-name'];
       }
       $header = AuthLang::trans($this->lang, 'email_confirm_header');
       $title = $senderName . ': ' . $header;
       $secureLink = Router::address(
               routeName: 'hlogin.action.page',
               replacements: ['lang' => $this->lang, 'action' => 'confirm'],
               endPart: false,
           ) . '?code=' . $hash;

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
           ))->send($title, $header, $secureLink, $password);
       } catch (HloginMailException $e) {
           Log::error($e->getMessage(), ['email' => $email, 'title' => $title, 'name' => $senderName]);

           return $this->getErrorResponse(['data' => null, 'system_message' => $e->getMessage()], 'Error sending email');
       }

       return $this->getSuccessResponse(
           [
               'data' => [
                   'id' => AuthLang::trans($this->lang, 'profile_page'),
                   'value' => AuthLang::trans($this->lang, 'user_new_email_text'),
               ],
               'action' => ['type' => 'CustomEmailMessage'],
               'captcha' => true,
           ],
           'Successful update',
       );
   }
}
