<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

use Hleb\Helpers\HostHelper;
use Hleb\Static\Log;
use Hleb\Static\Request;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\CurrentUser;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Exceptions\HloginMailException;
use Phphleb\Hlogin\App\Helpers\EmailHelper;
use Phphleb\Hlogin\App\Mail\ContactMessageMail;
use Phphleb\Hlogin\App\RegData;

/**
 * @internal
 */
final class ContactMessageAction extends AbstractBaseAction
{
    /** @inheritDoc */
    #[\Override]
   public function execute(array $params): array
   {
       $errorCells = [];
       $email = $params['value']['email'] ?? null;
       $message = (string)($params['value']['message'] ?? '');
       $captcha = $params['value']['captcha'] ?? null;
       $detector = $params['value']['detector'] ?? null;

       $requiredError = AuthLang::trans($this->lang, 'error_empty_data');
       $formatError = AuthLang::trans($this->lang, 'error_pattern_data');
       $captchaError = AuthLang::trans($this->lang, 'captcha_error');

       // Check that sending messages is allowed.
       // Проверка, что отправка сообщений разрешена.
       $config = ConfigStorage::getConfig();
       if (!$config['contact']['active']) {
           return $this->getErrorResponse(['system_message' => AuthLang::trans($this->lang, 'again_message')], 'Feedback is disabled');
       }

       if (empty($email)) {
           $errorCells['email'] = $requiredError;
       } else {
           if (!preg_match(RegData::EMAIL_PATTERN, (string)$email) || $detector !== '') {
               $errorCells['email'] = $formatError;
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

       if (empty($message)) {
           $errorCells['message'] = $requiredError;
       } else if (!str_contains($message, ' ')) {
           $errorCells['message'] = $formatError;
       }


       if ($errorCells) {
           return $this->getErrorResponse(['form' => $errorCells, 'captcha' => $isCaptcha], 'Form validation error');
       }

       $email = EmailHelper::convert($email);
       $name = $email;

       $user = CurrentUser::get();
       if ($user) {
           $userEmail = $user['email'] !== $email ? $email . ' (' . $user['email'] . ')' : $email;
           $title = str_replace(
               ['{sender}', '{domain}', '{id}'],
               [$userEmail, Request::getHost(), $user['id']],
               (string)$config['contact']['title']['register']);
           $name = $user['name'] . ' ' . $user['surname'];
       } else {
           $title = str_replace(
               ['{sender}', '{domain}'],
               [$email, Request::getHost()],
               (string)$config['contact']['title']['guest']);
       }
       empty(trim($name, ' ')) and  $name = $email;

       $mailTo = EmailHelper::default(Request::getHost());
       if (!empty($config['mail']['from'])) {
           $mailTo = $config['contact']['for-email'];
       }
       if (!empty($config['contact']['for-email'])) {
           $mailTo = $config['contact']['for-email'];
       }

       try {
           (new ContactMessageMail(
               $name,
               $email,
               $mailTo,
               $this->lang,
               onlyToFile: HostHelper::isLocalhost(Request::getHost()),
           ))->send(
               $title,
               AuthLang::trans($this->lang, 'feedback'),
               text: $message,
           );
       }  catch (HloginMailException $e) {
           Log::error($e->getMessage(), ['email' => $email, 'title' => $title, 'message' => $message]);

           return $this->getErrorResponse(['data' => null, 'system_message' => $e->getMessage(), 'captcha' => $isCaptcha], $e->getMessage());
       }


       return $this->getSuccessResponse(
           [
               'data' => [
                   'id' => AuthLang::trans($this->lang, 'contact_send_message'),
                   'value' => AuthLang::trans($this->lang, 'sender_mail'),
               ],
               'action' => ['type' => 'CustomMessage'],
               'captcha' => $isCaptcha
           ],
           'Message sent successfully',
       );
   }
}