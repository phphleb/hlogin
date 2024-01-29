<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Mail;

use Phphleb\Hlogin\App\Content\AuthLang;

/** @internal */
final class PasswordRecoveryMail extends BaseMail
{
    /**
     * @inheritDoc
     * @internal
     */
    #[\Override]
    public function send(
        #[\SensitiveParameter] string $title,
        #[\SensitiveParameter] string $header,
        #[\SensitiveParameter] ?string $key = null,
        #[\SensitiveParameter] ?string $password = null,
        #[\SensitiveParameter] ?string $text = null,
    ): void
    {
        $message = $this->getByDesignRow(AuthLang::trans($this->lang, 'password_recovery_message'), $this->design);

        if ($this->duplicateOnEnglish && $this->lang !== 'en') {
            $message .= '\n' . $this->getByDesignRow(AuthLang::trans('en', 'password_recovery_message'), $this->design);
        }
        $message = $this->updateMessage($message, $key, AuthLang::trans($this->lang, 'warning_below'));

        $this->sender->sendPasswordRecoveryMail($title, $header, $message, $key);
    }

    protected function updateMessage(
        #[\SensitiveParameter] string $template,
        #[\SensitiveParameter] string $confirmLink,
        $warning
    ): string
    {
        foreach (['recovery_link' => $confirmLink, 'warning' => $warning]
                 as $key => $value) {
            $template = \str_replace('{{' . $key . '}}', $value, $template);
        }
        return (string)$template;
    }
}