<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers;

use Hleb\Base\Controller;
use Hleb\Constructor\Data\View;
use Hleb\Http403ForbiddenException;
use Hleb\HttpMethods\Specifier\DataType;
use Hleb\Static\Request;
use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\CurrentUser;

/**
 * @internal
 */
class AuthPageController extends Controller
{
    /**
     * Separate pages for forms (feedback, registration, login).
     *
     * Отдельные страницы для форм (обратная связь, регистрация, вход).
     */
    public function index(DataType $actionData): View
    {
        // Password confirmation.
        // Подтверждение пароля.
        $action = $actionData->asString();
        if (in_array($action, ['password', 'confirm'])) {
            $code = Request::get('code')->asString(null);
            if (!$code) {
                throw new Http403ForbiddenException();
            }
        }
        $title = match ($action) {
            'registration' => 'reg_settings',
            'feedback' => 'feedback',
            'enter' => 'enter_page',
            'confirm' => 'email_confirm_header',
            'password' => 'new_password_page',
            'profile' => 'profile_page',
            default => 'authorization',
        };
        $user = CurrentUser::get();
        // Exit from authorization to confirm access is made when requesting from the page.
        // Выход из авторизации для подтверждения доступа сделан при запросе со страницы.
        if ($user !== null && !in_array($action, ['password', 'confirm'])) {
            $action = 'profile';
        } else if ($action === 'profile') {
            $action = 'enter';
        }
        $command = match ($action) {
            'registration' => 'UserRegister',
            'feedback' => 'ContactMessage',
            'enter' => 'UserEnter',
            'confirm' => 'ConfirmEmail',
            'recovery' => 'NewPassword',
            'profile' => 'UserProfile',
            default => null,
        };
        $title = AuthLang::trans(Settings::getAutodetectLang(), $title);

        return \view('hlogin/authorization/register_page', ['command' => $command, 'title' => $title]);
    }
}