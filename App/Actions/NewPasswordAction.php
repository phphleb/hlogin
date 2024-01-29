<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

use App\Bootstrap\Auth\Handlers\AdditionalNewPassword;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\Data\NewPasswordHash;
use Phphleb\Hlogin\App\Data\PasswordData;
use Phphleb\Hlogin\App\Models\UserModel;

/**
 * Processes a request from the password recovery panel via a link.
 *
 * Обрабатывает запрос из панели восстановления пароля по ссылке.
 *
 * @internal
 */
final class NewPasswordAction extends AbstractBaseAction
{
    /** @inheritDoc */
    #[\Override]
    public function execute(#[\SensitiveParameter] array $params): array
    {
        $captcha = $params['value']['captcha'] ?? null;
        $detector = $params['value']['detector'] ?? null;
        $password = $params['value']['password'] ?? null;
        $password1 = $params['value']['password1'] ?? null;
        $code = $params['value']['code'] ?? null;

        if ($detector || !$code) {
            return $this->getErrorResponse(['system_message' => AuthLang::trans($this->lang, 'link expired')]);
        }

        $requiredError = AuthLang::trans($this->lang, 'error_empty_data');
        $formatError = AuthLang::trans($this->lang, 'error_pattern_data');
        $captchaError = AuthLang::trans($this->lang, 'captcha_error');
        $passwordMismatchError = AuthLang::trans($this->lang, 'error_password_mismatch');
        $duplicatePasswordError = AuthLang::trans($this->lang, 'duplicate_pass');


        // Checking the connection to the database.
        // Проверка подключения к базе данных.
        if (!UserModel::checkTableUsers()) {
            return $this->getErrorResponse([
                'system_message' => 'No data was received from the database'],
                'Problem saving new password to database'
            );
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
        if (!empty($password)) {
            if (!PasswordData::validatePasswordString((string)$password)) {
                $errorCells['password'] = $formatError;
            }
            if ($password !== $password1) {
                $errorCells['password1'] = $passwordMismatchError;
            }
            if (empty($password1)) {
                $errorCells['password1'] = $requiredError;
            }
            $newPassword = PasswordData::generateHash($password);

            $user = UserModel::getCells('code', $code, ['password']);
            if (!$user) {
                $errorCells['password'] = $formatError;
            }
            if ($newPassword === $user['password']) {
                $errorCells['password'] = $duplicatePasswordError;
            }
        }

        if ($errorCells) {
            return $this->getErrorResponse(['form' => $errorCells, 'captcha' => $isCaptcha], 'Form validation error');
        }

        $insertHandler = \class_exists(AdditionalNewPassword::class);
        if ($insertHandler) {
            $handler = (new AdditionalNewPassword());
            if (!\is_subclass_of($handler, BaseAdditional::class)) {
                throw new \RuntimeException('The action class must inherit from ' . BaseAdditional::class);
            }
            if ($handler->insert($params) === false) {
                return $this->getErrorResponse(['data' => null, 'captcha' => $isCaptcha, 'system_message' => $handler->getErrorMessage()], 'These forms have not been verified');
            }
        }

        $user = UserModel::getCells('code', $code, ['id']);

        if (!$user || !NewPasswordHash::check($user['id'], $user['email'], $code, $user['code'])) {
            return $this->getErrorResponse(['system_message' => AuthLang::trans($this->lang, 'link expired')]);
        }

        UserModel::setCells('id', $user['id'], ['password' => $newPassword, 'code' => null]);

        if ($insertHandler) {
            $handler->afterAction($user['id']);
        }

        return $this->getSuccessResponse(
            [
                'data' => [
                    'id' => AuthLang::trans($this->lang, 'send_password'),
                    'value' => AuthLang::trans($this->lang, 'password_changed'),
                ],
                'action' => ['type' => 'CustomMessage'],
                'captcha' => true,
            ],
            'Password successfully updated',
        );

    }
}