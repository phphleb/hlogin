<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Mail;

use Phphleb\Hlogin\App\Content\AuthLang;

/** @internal */
class ConfirmNewRegisterEmail extends ConfirmRegisterEmail
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
        $message = $this->getByDesignRow(AuthLang::trans($this->lang, 'email_confirm_message'), $this->design);

        if ($this->duplicateOnEnglish && $this->lang !== 'en') {
            $message .= '\n' . $this->getByDesignRow(AuthLang::trans('en', 'email_confirm_message'), $this->design);
        }
        $message = $this->updateMessage($message, $key, '', AuthLang::trans($this->lang, 'warning_below'));

        $this->sender->sendConfirmNewEmail($title, $header, $message, $key);
    }
}