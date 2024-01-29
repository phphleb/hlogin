<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Helpers;

use Hleb\DynamicStateException;
use Hleb\Main\Console\WebConsole;

class ReadlineHelper
{
    /**
     * Universal question on the command line with checking the answer.
     *
     * Универсальный вопрос в командной строке с проверкой ответа.
     */
    public static function action(string $text, string $error, string $pattern): false|string
    {
        if (WebConsole::isUsed()) {
            $error =  'User input is not supported in Web Console mode.';
            print 'ERROR: ' . $error;
            throw new DynamicStateException($error);
        }
        $str = \trim(\readline($text . '>'));
        if (\preg_match($pattern, $str)) return $str;
        print $error  .  PHP_EOL;

        return self::action($text, $error, $pattern);
    }
}