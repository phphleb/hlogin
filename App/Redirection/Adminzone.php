<?php

namespace Phphleb\Hlogin\App\Redirection;

use Hleb\Constructor\Handlers\Request;
use Phphleb\Adminpan\Add\AdminPanData;
use Phphleb\Hlogin\App\Main as Main;
use Phphleb\Hlogin\App\OriginData;
use Phphleb\Hlogin\App\System\HCreator;
use Phphleb\Hlogin\App\Translate;

class  Adminzone
{
    public static function supportedLanguages() {
        return OriginData::LANGUAGES;
    }

    function get($type) {
        $this->params();
        return $this->$type();
    }

    public static function i18n(string $name, $lang = null) {
        return Translate::get($name, $lang);
    }

    protected function users() {
        return $this->run("/views/panels/user_list.php");
    }

    protected function settings() {
        return $this->run("/views/registration/settings.php");
    }

    protected function captcha() {
        return $this->run("/views/registration/captcha.php");
    }

    protected function email() {
        return $this->run("/views/registration/mail.php");
    }

    protected function feedback() {
        return $this->run("/views/registration/contact.php");
    }

    protected function management() {
        return $this->run("/views/panels/user_management.php");
    }

    protected function profile() {
        redirect('/' . AdminPanData::getLang() . '/login/profile/');
    }

    protected function run($path) {
        return HCreator::include(HCreator::HLOGIN_DIR . $path);
    }

    protected function params() {
        $version = Main::getVersion();
        $lang = trim(Request::get("lang")) ?? "en";
        AdminPanData::setColor("#434c61");
        AdminPanData::setLang($lang);
        Translate::setLang($lang);
        AdminPanData::setLogo('/en/login/resource/version/all/svg/svg/hlogin-logo/');
        AdminPanData::setLink("/", "main_page");
        AdminPanData::setI18nList(Translate::load($lang));
        AdminPanData::setDataFromHeader('<link rel="stylesheet" href="/en/login/resource/' . $version . '/all/css/css/hlogin-adminzone/">');
        AdminPanData::setDataFromHeader('<script src="/en/login/resource/' . $version . '/all/js/js/hlogin-adminzone/" async=""></script>');
        //AdminPanData::setInstruction("");
    }

}

