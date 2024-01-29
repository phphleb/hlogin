<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Helpers;

use Hleb\Helpers\HostHelper;

class EmailHelper
{
    /**
     * For a full comparison and registration, all E-mails must be unified.
     *
     * Для полноценного сравнения и регистрации все E-mail должны быть унифицированы.
     */
    public static function convert(string $email): string
    {
        return \strtolower($email);
    }

    /**
     * Returns the standardized default E-mail address.
     *
     * Возвращает стандартизированный адрес E-mail по умолчанию.
     */
    public static function default(string $host): string
    {
        if (HostHelper::isLocalhost($host)) {
            $host = 'example.localhost';
        }
        return 'no-reply@' . $host;
    }
}