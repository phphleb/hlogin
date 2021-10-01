<?php

namespace Phphleb\Hlogin\App\Redirection;

use Hleb\Constructor\Handlers\Request;
use Phphleb\Hlogin\App\Main;
use Phphleb\Hlogin\App\System\UserRegistration;
use Phphleb\Hlogin\App\Translate;

// Routes for profile pages

class Profile
{
    const REGISTER_PAGE_PATH = 'hlogin/authorization/register_page';

    public function get() {
        if (UserRegistration::checkDeleted()) {
            return "User has been deleted or blocked.";
        }
        $lang = Request::get('lang');
        Translate::setLang($lang);
        $title = Translate::get('profile_page');

        $data = Main::getConfigRegData('cell_reg_page_head');
        $this->code = html_entity_decode(html_entity_decode($data)) ?? '';

        if (UserRegistration::checkRegisterAndHigher()) {
            return hleb_v5ds34hop4nm1d_page_view(self::REGISTER_PAGE_PATH, ['type' => 'UserProfile', 'title' => $title, 'insertedCode' => $this->code]);
        }
        if (UserRegistration::checkPrimaryOnly()) {
            return hleb_v5ds34hop4nm1d_page_view(self::REGISTER_PAGE_PATH, ['type' => 'MessageFromConfirmEmail', 'title' => $title, 'insertedCode' => $this->code]);
        }
        return hleb_v5ds34hop4nm1d_page_view(self::REGISTER_PAGE_PATH, ['type' => 'UserEnter', 'title' => Translate::get('enter_page'), 'insertedCode' => $this->code]);
    }

}

