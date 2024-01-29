<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin\Handlers;

use Hleb\Static\Request;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\Helpers\EmailHelper;
use Phphleb\Hlogin\App\Models\UserModel;
use Phphleb\Hlogin\App\RegData;
use Phphleb\Hlogin\App\RegType;

/**
 * @internal
 */
final readonly class RightDataHandler extends BaseHandler
{
    #[\Override]
    public function index(): array
    {
        $data = Request::post('json_data')->asArray();

        if (empty($data['id'])) {
            return $this->errorResponse(AuthLang::trans($this->lang, 'user_not_found'));
        }
        $userId = (int)$data['id'];
        $searchUser = UserModel::getCells('id', $userId);
        if (!$searchUser) {
            return $this->errorResponse(AuthLang::trans($this->lang, 'user_not_found'));
        }
        if ($searchUser['regtype'] >= RegType::REGISTERED_ADMIN) {
            return $this->errorResponse(AuthLang::trans($this->lang, 'not_enough_rights'));
        }
        $email = $searchUser['email'];
        if (isset($data['email'])) {
            $email = EmailHelper::convert((string)$data['email']);
            if (!preg_match(RegData::EMAIL_PATTERN, $email)) {
                return $this->errorResponse(AuthLang::trans($this->lang, 'data_validation_failed'));
            }
            if ($email !== $searchUser['email']) {
                $user = UserModel::getCells('email', $email, ['id']);
                if ($user) {
                    return $this->errorResponse(AuthLang::trans($this->lang, 'exists'));
                }
            }
        }
        $regtype = $searchUser['regtype'];
        if (isset($data['regtype'])) {
            $regtype = (int)$data['regtype'];
            if ($regtype >= RegType::REGISTERED_ADMIN) {
                return $this->errorResponse(AuthLang::trans($this->lang, 'not_enough_rights'));
            }
        }
        $confirm = $searchUser['confirm'];
        if (isset($data['confirm'])) {
            $confirm = (int)$data['confirm'];
            if (!in_array($confirm, [0, 1])) {
                return $this->errorResponse(AuthLang::trans($this->lang, 'data_validation_failed'));
            }
        }
        $subscription = $searchUser['subscription'];
        if (isset($data['subscription'])) {
            $subscription = (int)$data['subscription'];
            if (!in_array($subscription, [0, 1])) {
                return $this->errorResponse(AuthLang::trans($this->lang, 'data_validation_failed'));
            }
        }

        $login = $searchUser['login'];
        if (isset($data['login'])) {
            $login = (string)$data['login'];
            if (!empty($login)) {
                $user = UserModel::getCells('login', $login, ['id']);
                if ($user) {
                    return $this->errorResponse(AuthLang::trans($this->lang, 'exists'));
                }
            }
        }

        $name = $this->updateStrCell($data, 'name', $searchUser['name']);
        $surname = $this->updateStrCell($data, 'surname', $searchUser['surname']);
        $phone = $this->updateStrCell($data, 'phone', $searchUser['phone']);
        $address = $this->updateStrCell($data, 'address', $searchUser['address']);

        $originUserData = $this->getSpecialUserData($userId);

        UserModel::setCells('id', $userId, [
            'email' => $email,
            'regtype' => $regtype,
            'subscription' => $subscription,
            'confirm' => $confirm,
            'login' => $login,
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'surname' => $surname,
        ]);

        $newUserData = $this->getSpecialUserData($userId);

        $this->afterUserUpdate($originUserData, $newUserData);

        return $this->successResponse(['lang' => $this->lang]);
    }

    private function updateStrCell(array $array, string $name, mixed $origin)
    {
        $result = $origin;
        if (isset($array[$name])) {
            $result = $array[$name];
            if (empty($array[$name]) && $array[$name] !== '0') {
                $result = null;
            }
        }
        return $result;
    }
}