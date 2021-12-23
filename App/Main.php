<?php


namespace Phphleb\Hlogin\App;


use Phphleb\Spreader\ConfigTransfer;

final class Main
{
    protected static array $configData = [];

    protected static ?ConfigTransfer $transfer = null;

    private function __construct() {}

    public static function getVersion() {
        $config = self::getConfig();
        return $config['hlogin']['version'];
    }

    public static function getDefaultLang() {
        $config = self::getConfig();
        return $config['hlogin']['reg_data']['lang'];
    }

    public static function getTableName() {
        $config = self::getConfig()['hlogin'];
        if (!empty($config['reg_data']['reg_table_name'])) {
            return $config['reg_data']['reg_table_name'];
        }
        return 'users';
    }

    public static function getConfig(string $storageFile = "register/config.json", string $target = 'hlogin'): ?array
    {
        if (empty(self::$configData[$storageFile])) {

            if(is_null(self::$transfer)) {
                self::$transfer = new ConfigTransfer();
            }
            $path = defined('HLEB_GLOBAL_DIRECTORY') ?
                (defined('HLEB_STORAGE_DIRECTORY') ? rtrim(HLEB_STORAGE_DIRECTORY, '\\/ ') : HLEB_GLOBAL_DIRECTORY . DIRECTORY_SEPARATOR . 'storage') :
                dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'storage';

            self::$configData[$storageFile] = self::$transfer->setTarget($path . '/' . $storageFile, $target)->get(true);
        }
        return self::$configData[$storageFile];
    }

    public static function getConfigHlogin() {
        $config = self::getConfig();
        if(!empty($config['hlogin'])) {
            return $config['hlogin'];
        }
        return [];
    }

    public static function getConfigRegData($name = null) {
        $config = self::getConfig();
        if(!empty($config['hlogin']['reg_data'])) {
            if(is_null($name)) {
                return $config['hlogin']['reg_data'];
            } else {
                return isset($config['hlogin']['reg_data'][$name]) ? $config['hlogin']['reg_data'][$name] : null;
            }
        }
        return null;
    }

    public static function getConfigUCaptchaData() {
        $config = self::getConfig("lib/ucaptcha/config.json", "ucaptcha");
        if (isset($config['ucaptcha'], $config['ucaptcha']["data"])) {
            $design = $config['ucaptcha']["data"]['design'];
            if($design == 'auto') {
                $originDesign = self::getConfig()['hlogin']['reg_data']['design'] ?? 'base';
                $config['ucaptcha']["data"]['design'] = $originDesign === 'dark' ? 'dark' : ($originDesign === 'game' ? '3d' : 'base');
            }
            return $config['ucaptcha']["data"];
        }
        return [];
    }

    public static function getConfigMuller() {
        $config = self::getConfig("lib/muller/config.json", "muller");
        if (!empty($config['muller'])) {
            return $config['muller'];
        }
        return [];
    }

    public static function getConfigContact() {
        $config = self::getConfig("register/contact_config.json", "contact");
        if (!empty($config['contact'])) {
            return $config['contact'];
        }
        return [];
    }
}
