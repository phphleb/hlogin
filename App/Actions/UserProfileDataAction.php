<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

use App\Bootstrap\Auth\Handlers\AdditionalProfileData;
use Hleb\Helpers\ArrayHelper;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\CurrentUser;
use Phphleb\Hlogin\App\Models\UserModel;
use Phphleb\Hlogin\App\RegType;

/**
 * @internal
 */
class UserProfileDataAction extends AbstractBaseAction
{
    public function execute(array $params): array
    {
        // Checking the connection to the database.
        // Проверка подключения к базе данных.
        if (!UserModel::checkTableUsers()) {
            return $this->getErrorResponse([
                'system_message' => 'No data was received from the database'],
                'Problem getting data from a table with users'
            );
        }

        // Get the current user's data or null if not authorized.
        // Получение данных текущего пользователя или null, если не был авторизован.
        $user = CurrentUser::get();
        if (!$user) {
            return $this->getErrorResponse([
                'system_message' => 'Failed to retrieve data'],
                'The user is not registered'
            );
        }
        unset($user['hash'], $user['password'], $user['seesionkey']);

        // Checks access level.
        // Проверяется уровень доступа.
        if ($user['regtype'] < RegType::UNDEFINED_USER) {
            return $this->getErrorResponse([
                'data' => null,
                'system_message' => AuthLang::trans($this->lang, 'deleted_user'),
                'action' => ['type' => 'UserEnter']
            ], 'The user with this E-mail has been deleted or blocked');
        }
        if ($user['regtype'] < RegType::PRIMARY_USER) {
            return $this->getErrorResponse([
                'data' => null,
                'system_message' => AuthLang::trans($this->lang, 'data_validation_failed'),
                'action' => ['type' => 'UserEnter']
            ], 'The user is not registered');
        }
        if ($user['regtype'] < RegType::REGISTERED_USER || !$user['confirm']) {
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

        $insertHandler = \class_exists(AdditionalProfileData::class);
        if ($insertHandler) {
            $handler = (new AdditionalProfileData());
            if (!\is_subclass_of($handler, BaseAdditional::class)) {
                throw new \RuntimeException('The action class must inherit from ' . BaseAdditional::class);
            }
            if ($handler->insert($params) === false) {
                return $this->getErrorResponse(['data' => null, 'system_message' => $handler->getErrorMessage()], 'Additional data has not been verified');
            }
        }

        $data = ArrayHelper::only($user, ['id', 'email', 'regtype', 'confirm', 'login', 'name', 'surname', 'address', 'phone']);

        if ($insertHandler) {
            $data = \array_merge($handler->afterAction($user['id']) ?? [], $data);
        }

        return $this->getSuccessResponse(['data' => $data],'User data was successfully received');
    }
}