<?php


namespace Phphleb\Hlogin\App;

use Phphleb\Spreader\ConfigTransfer;

final class Converter
{
    protected static bool $origin = true;

    protected static ?array $data = null;

    private function __construct(){}

    public static function getData(string $path, string $target): ?array
    {
        if (!self::$origin || is_null(self::$data)) {
            self::$data = (new ConfigTransfer($path, $target))->get();
        }
        return self::$data;
    }

    public static function saveData(array $data, string $path, string $target): void
    {
        self::$data = $data;
        self::$origin = false;
        (new ConfigTransfer($path, $target))->save($data);
    }

    public static function testJson(string $path): bool
    {
        try {
            $data = (new ConfigTransfer($path, 'hlogin'))->get();
        } catch (\Throwable $exception) {
            self::setOriginJson($path);
            return false;
        }
        if (!isset($data, $data['hlogin'], $data['hlogin']['reg_data']['design'], $data['hlogin']['version'])) {
            self::setOriginJson($path);
            return false;
        }
        return true;
    }

    public static function setOriginJson(string $path): void
    {
        self::setJson($path, HLEB_VENDOR_DIRECTORY . "/phphleb/hlogin/standard_config.json", "hlogin");
    }

    public static function testCaptchaJson(string $path): bool
    {
        try {
            $data = (new ConfigTransfer($path, 'ucaptcha'))->get();
        } catch (\Exception $exception) {
            self::setOriginCaptchaJson($path);
            return false;
        }
        if (!isset($data, $data['ucaptcha'], $data['ucaptcha']['version'])) {
            self::setOriginCaptchaJson($path);
            return false;
        }
        return true;
    }

    public static function setOriginCaptchaJson(string $path): void
    {
        self::setJson($path, HLEB_VENDOR_DIRECTORY . "/phphleb/hlogin/config/ucaptcha_config.json", "ucaptcha");
    }

    public static function testMullerJson(string $path): bool
    {
        try {
            $data = (new ConfigTransfer($path, 'muller'))->get();
        } catch (\Exception $exception) {
            self::setOriginMullerJson($path);
            return false;
        }
        if (!isset($data, $data['muller'], $data['muller']['version'])) {
            self::setOriginMullerJson($path);
            return false;
        }
        return true;
    }

    public static function setOriginMullerJson(string $path): void
    {
        self::setJson($path, HLEB_VENDOR_DIRECTORY . "/phphleb/hlogin/config/muller_config.json", "muller");
    }

    public static function testContactJson(string $path): bool
    {
        try {
            $data = (new ConfigTransfer($path, 'contact'))->get();
        } catch (\Exception $exception) {
            self::setOriginContactJson($path);
            return false;
        }
        if (!isset($data, $data['contact'], $data['contact']['version'])) {
            self::setOriginContactJson($path);
            return false;
        }
        return true;
    }

    public static function setOriginContactJson(string $path): void
    {
        self::setJson($path, HLEB_VENDOR_DIRECTORY . "/phphleb/hlogin/config/contact_config.json", "contact");
    }

    private static function setJson(string $path, string $originPath, string $target): void
    {
        $originData = json_decode(file_get_contents($originPath), true);
        (new ConfigTransfer($path, $target))->save($originData);
    }

}

