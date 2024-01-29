<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Helpers;

use Hleb\HttpMethods\Specifier\DataType;
use Phphleb\Hlogin\App\Models\UserModel;

/**
 * @internal
 */
final readonly class RecoveryPasswordCodeHelper
{
    /**
     * @internal
     */
    public function __construct(private DataType $code)
    {
    }

    /**
     * Checking the password recovery code.
     *
     * Проверка кода для восстановления пароля.
     *
     * @internal
     */
    public function check(): bool
    {
        return (bool)UserModel::getCells('code', $this->code->asString(), ['id']);
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