<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

use App\Bootstrap\Auth\Handlers\AdditionalAuthorization;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\CurrentUser;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Data\CookieData;
use Phphleb\Hlogin\App\Data\PasswordData;
use Phphleb\Hlogin\App\Helpers\EmailHelper;
use Phphleb\Hlogin\App\Models\UserModel;
use Phphleb\Hlogin\App\RegData;
use Phphleb\Hlogin\App\RegType;

/**
 * @internal
 */
final class UserEnterAction extends AbstractBaseAction
{
    /** @inheritDoc */
    #[\Override]
   public function execute(array $params): array
   {
       $errorCells = [];
       $email = $params['value']['email'] ?? null;
       $password = $params['value']['password'] ?? null;
       $captcha = $params['value']['captcha'] ?? null;
       $remember = $params['value']['remember'] ?? null;
       $detector = $params['value']['detector'] ?? null;

       $requiredError = AuthLang::trans($this->lang, 'error_empty_data');
       $formatError = AuthLang::trans($this->lang, 'error_pattern_data');
       $captchaError = AuthLang::trans($this->lang, 'captcha_error');

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

       // Checking the frequency of requests to one E-mail.
       // Проверка частоты запросов к одному E-mail.
       if (!$this->requestRateCheck((string)$email)) {
           return $this->getErrorResponse(['system_message' => 'Try again later'], 'Time ban');
       }

       // Check the password format (always required).
       // Проверка формата пароля (всегда обязателен).
       if (empty($password)) {
           $errorCells['password'] = $requiredError;
       } else {
           if (!PasswordData::validatePasswordString((string)$password)) {
               $errorCells['password'] = $formatError;
           }
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

       if ($errorCells) {
           return $this->getErrorResponse(['form' => $errorCells, 'captcha' => $isCaptcha], 'Form validation error');
       }

       $currentUser = CurrentUser::get();
       if ($currentUser) {
           return $this->getErrorResponse([], 'The current user is already authorized.');
       }

       $insertHandler = \class_exists(AdditionalAuthorization::class);
       if ($insertHandler) {
           $handler = (new AdditionalAuthorization());
           if (!\is_subclass_of($handler, BaseAdditional::class)) {
               throw new \RuntimeException('The action class must inherit from ' . BaseAdditional::class);
           }
           if ($handler->insert($params) === false) {
               return $this->getErrorResponse(['data' => null, 'captcha' => $isCaptcha, 'system_message' => $handler->getErrorMessage()], 'These forms have not been verified');
           }
       }

       // Check if the user exists.
       // Проверка существования пользователя.
       $user = PasswordData::getUserByPassword(EmailHelper::convert($email), $password, ['id', 'email', 'sessionkey', 'regtype']);
       if (!$user) {
           $loginMatch = AuthLang::trans($this->lang, 'pair_mismatch');
           return $this->getErrorResponse(['data' => null, 'captcha' => $isCaptcha, 'system_message' => $loginMatch], 'E-mail and password pair mismatch');
       }
       if ($user['regtype'] < RegType::PRIMARY_USER) {
           return $this->getErrorResponse([
               'data' => null,
               'system_message' => AuthLang::trans($this->lang, 'deleted_user'),
               'action' => ['type' => 'UserEnter']
           ], 'The user with this E-mail has been deleted or blocked');
       }


       $key = (string)$user['sessionkey'];
       if (!$key || !CookieData::searchValidKey($key)) {
           $ttl = ConfigStorage::getRegConfig()['session-duration'] ?? null;
           if (empty($ttl) || !\is_int($ttl)) {
               throw new \RuntimeException('The `registration.session-duration` parameter was incorrectly specified in the library configuration');
           }
           $ttl < 3600 and $ttl = 3600;
           // Update the session key over time.
           // Обновление сессионного ключа по времени.
           $key = CookieData::generateKey($user['id'], $ttl);
       }

       // To prevent logging out on another PC or browser, the current key can be saved with an expiration mark.
       // The disadvantage may be that the user logs in just before the expiration date, then he will have to log in twice.
       //
       // Для предотвращения выхода на другом ПК или браузере может сохраниться текущий ключ с меткой истечения.
       // Минусом может быть вход пользователя перед самым сроком истечения, тогда ему надо будет войти дважды.
       UserModel::setCells('id', $user['id'], ['sessionkey' => $key, 'period' => time()]);

       // User authorization.
       // Авторизация пользователя.
       CurrentUser::updateSessionKey($key);
       CurrentUser::updateCookiesKey($remember ? $key : '');

       if ($insertHandler) {
           $handler->afterAction($user['id']);
       }

       return $this->getSuccessResponse(
           [
               'data' => $user['email'],
               'action' => ['type' => 'ReloadPage'],
               'captcha' => $isCaptcha,
           ],
           'Successful authorization'
       );
   }
}