<?php


namespace Phphleb\Hlogin\App;


use Hleb\Constructor\Handlers\Key;
use Phphleb\Nicejson\JsonConverter;
use Phphleb\Updater\Classes\Data;

/**
 * Копирование/обновление конфигурационных файлов для сервиса регистрации
 */

final class AddConfig
{
    public function hloginCopy() {
        $design = Data::getDesign();
        $configFile = $this->getStorageDirPath() . "/register/config.json";
        $configStandardFile = realpath(__DIR__ . "/../standard_config.json");
        if (!file_exists($configFile)) {
            $this->createDir($configFile);
            copy($configStandardFile, $configFile);
            chmod($configFile, 0775);
            $config = json_decode(file_get_contents($configFile));
            $config->hlogin->reg_data->design = $design;
            $jsonConverted = (new JsonConverter(json_encode($config)))->get();
            file_put_contents($configFile, $jsonConverted);
        } else {
            $config = json_decode(file_get_contents($configFile));
            $standardConfig = json_decode(file_get_contents($configStandardFile));
            $config->hlogin->reg_data->design = $design;
            $config->hlogin->version = $standardConfig->hlogin->version;
            $jsonConverted = (new JsonConverter(json_encode($config)))->get();
            file_put_contents($configFile, $jsonConverted);
        }
    }

    public function mullerCopy() {
        $configFile = $this->getStorageDirPath() . "/lib/muller/config.json";
        $configStandardFile = realpath(__DIR__ . "/../config/muller_config.json");
        if (!file_exists($configFile)) {
            $this->createDir($configFile);
            copy($configStandardFile, $configFile);
            chmod($configFile, 0775);
        } else {
            $config = json_decode(file_get_contents($configFile));
            $standardConfig = json_decode(file_get_contents($configStandardFile));
            $config->muller->version = $standardConfig->muller->version;
            $jsonConverted = (new JsonConverter(json_encode($config)))->get();
            file_put_contents($configFile, $jsonConverted);
        }
    }

    public function copyHloginInfo() {
        $infoFile = $this->getStorageDirPath() . "/register/INFO.md";
        $infoStandardFile = realpath(__DIR__ . "/../config/info_main_config.md");
        if (!file_exists($infoFile)) {
            $this->createDir($infoFile);
            copy($infoStandardFile, $infoFile);
            chmod($infoFile, 0775);
        }
    }

    public function ucaptchaCopy() {
        $configFile = $this->getStorageDirPath() . "/lib/ucaptcha/config.json";
        $configStandardFile = realpath(__DIR__ . "/../config/ucaptcha_config.json");
        if (!file_exists($configFile)) {
            $this->createDir($configFile);
            copy($configStandardFile, $configFile);
            chmod($configFile, 0775);
        } else {
            $config = json_decode(file_get_contents($configFile));
            $standardConfig = json_decode(file_get_contents($configStandardFile));
            $config->ucaptcha->version = $standardConfig->ucaptcha->version;
            $jsonConverted = (new JsonConverter(json_encode($config)))->get();
            file_put_contents($configFile, $jsonConverted);
        }
    }

    public function contactCopy() {
        $configFile = $this->getStorageDirPath() . "/register/contact_config.json";
        $configStandardFile = realpath(__DIR__ . "/../config/contact_config.json");
        if (!file_exists($configFile)) {
            $this->createDir($configFile);
            copy($configStandardFile, $configFile);
            chmod($configFile, 0775);
        } else {
            $config = json_decode(file_get_contents($configFile));
            $standardConfig = json_decode(file_get_contents($configStandardFile));
            $config->contact->version = $standardConfig->contact->version;
            $jsonConverted = (new JsonConverter(json_encode($config)))->get();
            file_put_contents($configFile, $jsonConverted);
        }
    }

    private function createDir($path) {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }
    
    private function getStorageDirPath() {
        return defined('HLEB_GLOBAL_DIRECTORY') ?
            (defined('HLEB_STORAGE_DIRECTORY') ? rtrim(HLEB_STORAGE_DIRECTORY , '\\/ ') : HLEB_GLOBAL_DIRECTORY . DIRECTORY_SEPARATOR . 'storage') :
            dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'storage';
    }

}