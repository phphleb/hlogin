<?php

    /**
     * Removing the `hlogin` registration from the project.
     */

    require __DIR__ . "/loader.php";
    require __DIR__ . "/../updater/FileRemover.php";

    $remover = new \Phphleb\Updater\FileRemover(__DIR__ . DIRECTORY_SEPARATOR);

    $remover->setSpecialNames('hlogin', 'Hlogin');

    $remover->run();

    print PHP_EOL . "For complete removal, you need to clear the cache: run `php console --clear-cache`, `php console --clear-routes-cache` and `composer dump-autoload`" . PHP_EOL;

