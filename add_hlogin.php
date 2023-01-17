<?php

    /**
     * @author  Foma Tuturov <fomiash@yandex.ru>
     *
     * Adding `hlogin` functionality.
     */

    include_once __DIR__ . "/loader.php";
    include_once __DIR__ . "/../updater/FileUploader.php";

    $designPatterns = \Phphleb\Hlogin\App\OriginData::GLOBAL_PATTERNS; // The first value will be the main

    $uploader = new \Phphleb\Updater\FileUploader(__DIR__ . DIRECTORY_SEPARATOR . "hleb-project-relationship");

    $uploader->setDesign($designPatterns);

    $uploader->setPluginNamespace(__DIR__, 'Hlogin');

    $uploader->setSpecialNames('hlogin', 'Hlogin');

    if ($uploader->run()) {

        $config = new \Phphleb\Hlogin\App\AddConfig($uploader);
        $config->hloginCopy();
        $config->contactCopy();
        $config->copyHloginInfo();
        $config->mullerCopy();
        $config->ucaptchaCopy();

        print PHP_EOL . "After installing the new version, run `php console --clear-cache` and `php console --update-routes-cache`" . PHP_EOL;
    }

