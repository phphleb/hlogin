<?php

namespace Phphleb\Hlogin\App\System;

final class HCreator
{
    const HLOGIN_DIR = __DIR__ . "/../../";

    private function __construct(){}

    public static function include($path) {
        ob_start();
        require realpath($path);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}

