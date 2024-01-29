<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Data;

/**
 * Contains methods for validating the user password recovery code.
 *
 * Содержит методы для валидации кода восстановления пользовательского пароля.
 */
class NewPasswordHash extends EmailRecoveryHash
{
    /** @inheritDoc */
    #[\Override]
    public static function generate(
        #[\SensitiveParameter] int $userId,
        #[\SensitiveParameter] string $email
    ): string
    {
        return parent::generate($userId, $email);
    }

    /** @inheritDoc */
    #[\Override]
    public static function check(
        #[\SensitiveParameter] int $userId,
        #[\SensitiveParameter] string $email,
        #[\SensitiveParameter] ?string $hash,
        #[\SensitiveParameter] ?string $originHash
    ): bool
    {
        return parent::check($userId, $email, $hash, $originHash);
    }
}