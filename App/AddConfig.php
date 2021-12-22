<?php
/**
 * Copying/updating configuration files for the registration service.
 *
 * Копирование/обновление конфигурационных файлов для сервиса регистрации.
 */

namespace Phphleb\Hlogin\App;

use Phphleb\Spreader\ConfigTransfer;
use Phphleb\Updater\Classes\Data;

final class AddConfig
{
    public function hloginCopy(): void
    {
        $design = Data::getDesign();
        $configFile = $this->getStorageDirPath() . "/register/config.json";
        $configStandardFile = realpath(__DIR__ . "/../standard_config.json");
        $standardData = $this->getOrigin($configStandardFile);

        $transfer = new ConfigTransfer($configFile, 'hlogin');
        $data = $transfer->get();
        if (!$data) {
            $data = $standardData;
        }
        $data['hlogin']['version'] = $standardData['hlogin']['version'];
        $data['hlogin']['reg_data']['design'] = $design;

        $transfer->save($data);
    }

    public function mullerCopy(): void
    {
        $configFile = $this->getStorageDirPath() . "/lib/muller/config.json";
        $configStandardFile = realpath(__DIR__ . "/../config/muller_config.json");
        $standardData = $this->getOrigin($configStandardFile);

        $transfer = new ConfigTransfer($configFile, 'muller');
        $data = $transfer->get();
        if (!$data) {
            $data = $standardData;
        }
        $data['muller']['version'] = $standardData['muller']['version'];

        $transfer->save($data);
    }

    public function copyHloginInfo(): void
    {
        $infoFile = $this->getStorageDirPath() . "/register/INFO.md";
        $infoStandardFile = realpath(__DIR__ . "/../config/info_main_config.md");
        if (!file_exists($infoFile)) {
            $this->createDir($infoFile);
            copy($infoStandardFile, $infoFile);
            chmod($infoFile, 0775);
        }
    }

    public function ucaptchaCopy(): void
    {
        $configFile = $this->getStorageDirPath() . "/lib/ucaptcha/config.json";
        $configStandardFile = realpath(__DIR__ . "/../config/ucaptcha_config.json");
        $standardData = $this->getOrigin($configStandardFile);

        $transfer = new ConfigTransfer($configFile, 'ucaptcha');
        $data = $transfer->get();
        if (!$data) {
            $data = $standardData;
        }
        $data['ucaptcha']['version'] = $standardData['ucaptcha']['version'];

        $transfer->save($data);
    }

    public function contactCopy(): void
    {
        $configFile = $this->getStorageDirPath() . "/register/contact_config.json";
        $configStandardFile = realpath(__DIR__ . "/../config/contact_config.json");
        $standardData = $this->getOrigin($configStandardFile);

        $transfer = new ConfigTransfer($configFile, 'contact');
        $data = $transfer->get();
        if (!$data) {
            $data = $standardData;
        }
        $data['contact']['version'] = $standardData['contact']['version'];

        $transfer->save($data);
    }

    private function createDir($path): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }

    private function getStorageDirPath(): string
    {
        return defined('HLEB_GLOBAL_DIRECTORY') ?
            (defined('HLEB_STORAGE_DIRECTORY') ? rtrim(HLEB_STORAGE_DIRECTORY, '\\/ ') : HLEB_GLOBAL_DIRECTORY . DIRECTORY_SEPARATOR . 'storage') :
            dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'storage';
    }

    private function getOrigin(string $file): ?array
    {
        return json_decode(file_get_contents($file), true) ?: null;
    }

}

