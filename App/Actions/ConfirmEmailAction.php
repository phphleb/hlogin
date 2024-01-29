<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

use App\Bootstrap\Auth\Handlers\AdditionalConfirmEmail;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Data\EmailRecoveryHash;
use Phphleb\Hlogin\App\Models\UserModel;
use Phphleb\Hlogin\App\RegType;

/**
 * @internal
 */
final class ConfirmEmailAction extends AbstractBaseAction
{
    /** @inheritDoc */
    #[\Override]
    public function execute(#[\SensitiveParameter] array $params): array
    {
        $code = $params['value']['code'] ?? null;

        if (!$code) {
            return $this->getErrorResponse(['system_message' => AuthLang::trans($this->lang, 'link expired')]);
        }

        $config = ConfigStorage::getConfig();

        // Checking the connection to the database.
        // Проверка подключения к базе данных.
        if (!UserModel::checkTableUsers()) {
            return $this->getErrorResponse(['system_message' => 'Error! Failed to connect to the table with users to confirm email.']);
        }

        $insertHandler = \class_exists(AdditionalConfirmEmail::class);
        if ($insertHandler) {
            $handler = (new AdditionalConfirmEmail());
            if (!\is_subclass_of($handler, BaseAdditional::class)) {
                throw new \RuntimeException('The action class must inherit from ' . BaseAdditional::class);
            }
            if ($handler->insert($params) === false) {
                return $this->getErrorResponse(['data' => null, 'captcha' => true, 'system_message' => $handler->getErrorMessage()], 'These forms have not been verified');
            }
        }

        $user = UserModel::getCells('hash', $code);

        // Depending on the type of confirmation, a new E-mail or the one specified during registration is used for verification.
        // В зависимости от типа подтверждения используется для проверки новый E-mail или указанный при регистрации.
        if (empty($user) || !EmailRecoveryHash::check($user['id'], $user['newemail'] ?? $user['email'], $code, $user['hash'])) {
            return $this->getErrorResponse(['system_message' => AuthLang::trans($this->lang, 'link expired')]);
        }

        // Checks access level.
        // Проверяется уровень доступа.
        if ($user['regtype'] < RegType::UNDEFINED_USER) {
            return $this->getErrorResponse([
                'data' => null,
                'system_message' => AuthLang::trans($this->lang, 'deleted_user'),
                'action' => ['type' => 'UserEnter']
            ], 'The user with this E-mail has been deleted or blocked');
        }

        if ($user['newemail']) {
            // Apply the new E-mail and log out on all devices.
            // Применение нового E-mail и выход на всех устройствах.
            UserModel::setCells('id', $user['id'], ['email' => $user['newemail'], 'newemail' => null, 'hash' => null, 'sessionkey' => null]);
        } else {
             // Basic email confirmation upon registration.
            // Базовое подтверждение Email при регистрации.
            $registerType = $user['regtype'];
            if ($user['regtype'] === RegType::PRIMARY_USER) {
                $registerType = RegType::REGISTERED_USER;
                $regtype = (int)($config['registration']['regtype-after-confirm'] ?? 0);
                if ($regtype && $regtype > RegType::PRIMARY_USER && $regtype < RegType::REGISTERED_COMMANDANT) {
                    $registerType = $regtype;
                }
            }
            UserModel::setCells('id', $user['id'], ['confirm' => 1, 'hash' => null, 'regtype' => $registerType]);
        }

        if ($insertHandler) {
            $handler->afterAction($user['id']);
        }

        return $this->getSuccessResponse(
            [
                'data' => [
                    'id' => AuthLang::trans($this->lang, 'email_confirm_header'),
                    'value' => AuthLang::trans($this->lang, 'message_log_success'),
                ],
                'action' => ['type' => 'CustomMessage'],
                'captcha' => false,
            ],
            'Successful confirm',
        );
    }
}