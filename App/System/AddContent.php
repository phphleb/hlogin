<?php

namespace Phphleb\Hlogin\App\System;

use Hleb\Main\Errors\ErrorOutput;
use Phphleb\Hlogin\App\Main as Main;
use Phphleb\Hlogin\App\OriginData;
use Phphleb\Nicejson\JsonConverter;
use Phphleb\Ucaptcha\Captcha;

final class AddContent
{
    const SCRIPT_NAME = 'hlogin_init_script';

    static protected $create = false;

    private function __construct(){}

    static function getScriptCode($type = null, $forced = false) {
        if (self::$create === false || $forced) {
            self::$create = true;
            return self::data($type);
        }
        return '';
    }

    static protected function getRegistration() {
        return UserRegistration::getNumericType();
    }

    static protected function data($type = null) {
        $name = self::SCRIPT_NAME;

        // Registration
        $version = Main::getVersion();
        $config = (new JsonConverter(json_encode(self::clearConfig(Main::getConfigRegData(), $type)), 0, 0, "\\" . "\n" . str_repeat(" ", 13)))->get();
        $csrf = hleb_c3dccfa0da1a3e_csrf_token();
        $action = 'return';
        $reg = self::getRegistration();
        $endingUrl = HLEB_PROJECT_ENDING_URL ? 1 : 0;
        $jsFunctions = "/en/login/resource/$version/all/js/js/hlogin-functions" . ($endingUrl ? "/" : "");
        $design = OriginData::getDesign();

        // Captcha
        $captcha = Main::getConfigUCaptchaData();
        $captchaActive = $captcha["active"] === "on" && (new Captcha)->isPassed() === false ? 1 : 0;

        // Send message
        $contact = Main::getConfigContact();
        $sendMessageBlock = $contact['data']['active'] == "on" ? 1 : 0;
        $lang = OriginData::getLocalLang();

        if(empty($captcha) || empty($contact)) {
            $message = 'HLOGIN-LIB-001-LOAD_CONFIG_ERROR:' .
                ' The configuration files of the `HLOGIN` library in the `storage` folder are not included. If there are multiple entry points, copy these files from the original folder or execute `php console phphleb/hlogin --add` in the current root directory.' . '~' .
                ' Не подключены конфигурационные файлы библиотеки `HLOGIN` в папке `storage`. Если точек входа несколько, скопируйте эти файлы из оригинальной папки или выполните `php console phphleb/hlogin --add` в текущей корневой директории.';
            ErrorOutput::get($message);
        }


        return <<<SCRIPT
<script class="$name">
      if (typeof $name !== 'function'){ 
          function $name() { 
              var $name = {};
              $name.csrfToken = '{$csrf}';
              $name.userRegister = {$reg};
              $name.version = '{$version}';
              $name.config = '{$config}';
              $name.endingUrl = {$endingUrl};
              $name.captchaActive = {$captchaActive};
              $name.isContact = {$sendMessageBlock};
              $name.lang = '{$lang}';
              $name.design = '{$design}';
              $action $name;
        }
      }
var {$name}_interval = setInterval(function(){
        var {$name}_body = document.getElementsByTagName('body');
        if({$name}_body != null && {$name}_body.length){
            clearInterval({$name}_interval); 
            var script = document.createElement('script');
            script.src = '{$jsFunctions}';
            script.type  ='text/javascript';
            script.async = true;
            {$name}_body[0].appendChild(script);
        }
    }, 20);  
</script>
SCRIPT;
    }

    static protected function clearConfig($data, $type) {
        if (isset($data['reg_table_name'])) {
            unset($data['reg_table_name']);
        }
        if (isset($data['cell_reg_page_head'])) {
            unset($data['cell_reg_page_head']);
        }
        if((isset($data['block_orient']) && !OriginData::isRegPanel()) || $type === OriginData::HIDE_BUTTONS) {
            $data['block_orient'] = 'none';
        }
        return $data;
    }

}

