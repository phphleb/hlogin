<?php

namespace Phphleb\Hlogin\App\Controllers;

use Hleb\Base\Controller;
use Hleb\Constructor\Data\View;
use Hleb\Http403ForbiddenException;
use Hleb\Static\Csrf;
use Hleb\Static\Request;
use Hleb\Static\Settings;

/**
 * @internal
 */
class AuthServiceController extends Controller
{
    /**
     * Service pages for registration (logout and logout on all devices).
     *
     * Служебные страницы для регистрации (выход и выход на всех устройствах).
     */
    public function exit(): View
    {
        $type = Request::param('value')->value();
        $key = Request::get('_token')->asString();
        if (!$key || $key !== Csrf::token()) {
            throw new Http403ForbiddenException();
        }
        $page = 'exit_page';
        if ($type === 'forced') {
            $page = 'exit_page_forced';
        }

        return \view(
            "hlogin/authorization/$page",
            [
                'lang' => Settings::getAutodetectLang(),
                'type' => Request::param('value'),
            ]
        );
    }
}