<?php


namespace Phphleb\Hlogin\App;

use Phphleb\Nicejson\JsonConverter;

final class Converter
{
    protected static $origin = true;

    protected static $data = null;

    private function __construct(){}

    public static function getData(string $path) {
        if (!self::$origin || is_null(self::$data)) {
            self::$data = json_decode(file_get_contents($path), true);
        }
        return self::$data;
    }

    public static function saveData(array $data, string $path) {
        self::$data = $data;
        self::$origin = false;
        $jsonConverted = (new JsonConverter(json_encode($data)))->get();
        file_put_contents($path, $jsonConverted);
    }

    public static function testJson(string $path) {
        try {
            $json = json_decode(file_get_contents($path), true);
        } catch (\Exception $exception) {
            self::setOriginJson($path);
            return false;
        }
        if (!isset($json, $json['hlogin'], $json['hlogin']['reg_data']['design'], $json['hlogin']['version'])) {
            self::setOriginJson($path);
            return false;
        }
        return true;
    }

    public static function setOriginJson(string $path) {
        self::setJson($path, HLEB_VENDOR_DIRECTORY . "/phphleb/hlogin/standard_config.json");
    }

    public static function testCaptchaJson(string $path) {
        if (!file_exists($path)) {
            self::setOriginCaptchaJson($path);
            return false;
        }
        try {
            $json = json_decode(file_get_contents($path), true);
        } catch (\Exception $exception) {
            self::setOriginCaptchaJson($path);
            return false;
        }
        if (!isset($json, $json['ucaptcha'], $json['ucaptcha']['version'])) {
            self::setOriginCaptchaJson($path);
            return false;
        }
        return true;
    }

    public static function setOriginCaptchaJson(string $path) {
        self::setJson($path, HLEB_VENDOR_DIRECTORY . "/phphleb/hlogin/config/ucaptcha_config.json");
    }

    public static function testMullerJson(string $path) {
        if (!file_exists($path)) {
            self::setOriginMullerJson($path);
            return false;
        }
        try {
            $json = json_decode(file_get_contents($path), true);
        } catch (\Exception $exception) {
            self::setOriginMullerJson($path);
            return false;
        }
        if (!isset($json, $json['muller'], $json['muller']['version'])) {
            self::setOriginMullerJson($path);
            return false;
        }
        return true;
    }

    public static function setOriginMullerJson(string $path) {
        self::setJson($path, HLEB_VENDOR_DIRECTORY . "/phphleb/hlogin/config/muller_config.json");
    }

    public static function testContactJson(string $path) {
        if (!file_exists($path)) {
            self::setOriginContactJson($path);
            return false;
        }
        try {
            $json = json_decode(file_get_contents($path), true);
        } catch (\Exception $exception) {
            self::setOriginContactJson($path);
            return false;
        }
        if (!isset($json, $json['contact'], $json['contact']['version'])) {
            self::setOriginContactJson($path);
            return false;
        }
        return true;
    }

    public static function setOriginContactJson(string $path) {
        self::setJson($path, HLEB_VENDOR_DIRECTORY . "/phphleb/hlogin/config/contact_config.json");
    }

    private static function setJson( string $path, string $originPath) {
        $directory = dirname($path);
        if (!file_exists($directory)) {
            mkdir($directory, 0775, true);
        }
        if (!file_exists($path)) {
            $fp = fopen( $path, "w");
            fwrite($fp, file_get_contents($originPath));
            fclose($fp);
        } else {
            file_put_contents($path, file_get_contents($originPath));
        }
    }

}