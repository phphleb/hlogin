<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Mail;

use Phphleb\Hlogin\App\Content\AuthLang;

/** @internal */
final class ContactMessageMail extends BaseMail
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
        $message = $this->getByDesignRow(AuthLang::trans($this->lang, 'feeedback_message'), $this->design);

        $message = (string)\str_replace("{{message}}", $text, $message);

        $this->sender->sendContactMessageMail($title, $header, $text, $message);
    }
}