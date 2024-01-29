<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Helpers;

use Hleb\HttpMethods\Specifier\DataType;
use Phphleb\Hlogin\App\Models\UserModel;

/**
 * @internal
 */
final readonly class ConfirmEmailCodeHelper
{
    /**
     * @internal
     */
    public function __construct(private DataType $code)
    {
    }

    /**
     * Checking the code to confirm your email.
     *
     * Проверка кода для подтверждения E-mail.
     *
     * @internal
     */
    public function check(): bool
    {
        return (bool)UserModel::getCells('hash', $this->code->asString(), ['id']);
    }

    /**
     * Returns the code or null if the code does not pass the test.
     *
     * Возвращает код или null если код не прошел проверку.
     *
     * @internal
     */
    public function getVerified(): string|null
    {
        if (!$this->check()) {
            return null;
        }
        return $this->code->asString();
    }

}