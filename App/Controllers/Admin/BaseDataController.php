<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin;

use Hleb\Base\Controller;
use Hleb\Constructor\Data\View;
use Hleb\Static\Request;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\Controllers\Admin\Handlers\AdditionalDataHandler;
use Phphleb\Hlogin\App\Controllers\Admin\Handlers\CaptchaDataHandler;
use Phphleb\Hlogin\App\Controllers\Admin\Handlers\EmailDataHandler;
use Phphleb\Hlogin\App\Controllers\Admin\Handlers\RightDataHandler;
use Phphleb\Hlogin\App\Controllers\Admin\Handlers\SettingDataHandler;
use Phphleb\Hlogin\App\Controllers\Admin\Handlers\UserDataHandler;
use Phphleb\Hlogin\App\CurrentUser;
use Phphleb\Hlogin\App\Models\UserModel;
use Phphleb\Hlogin\App\RegType;
use Phphleb\Hlogin\App\Controllers\Admin\Handlers\ContactDataHandler;

/**
 * @internal
 */
final class BaseDataController extends Controller
{
    /**
     * Distribution of Ajax requests from the administrative panel.
     *
     * Распределение Ajax запросов из административной панели.
     */
    public function index(): array|View
    {
        $lang = Request::data()['lang']->asString();
        $type = Request::data()['action']->asString();

        if (!UserModel::checkTableUsers()) {
            return [
                'status' => 'error',
                'message' => 'Problem getting data from a table with users'
            ];
        }

        $user = CurrentUser::get();
        if (!$user) {
            return [
                'status' => 'error',
                'message' => AuthLang::trans($lang, 'user_not_reg')
            ];
        }
        if ($user['regtype'] < RegType::UNDEFINED_USER) {
            return [
                'status' => 'error',
                'message' => AuthLang::trans($lang, 'deleted_user')
            ];
        }
        if ($user['regtype'] < RegType::REGISTERED_COMMANDANT) {
            return [
                'status' => 'error',
                'message' => AuthLang::trans($lang, 'not_enough_rights')
            ];
        }

        return match ($type) {
            'setting' => (new SettingDataHandler($lang))->index(),
            'additional' => (new AdditionalDataHandler($lang))->index(),
            'captcha' => (new CaptchaDataHandler($lang))->index(),
            'email' => (new EmailDataHandler($lang))->index(),
            'contact' => (new ContactDataHandler($lang))->index(),
            'users' => (new UserDataHandler($lang))->index(),
            'rights' => (new RightDataHandler($lang))->index(),
            default => view('404'),
        };
    }
}