<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Mail;

use Phphleb\Hlogin\App\Content\AuthLang;

/** @internal */
class ConfirmRegisterEmail extends BaseMail
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
        $passwordText = $password ?  "\n" . '<br>' . AuthLang::trans($this->lang,'password') . ': <b>' . $password . '</b><br>' . "\n" : '';

        $message = $this->getByDesignRow(AuthLang::trans($this->lang, 'email_confirm_message'), $this->design);

        if ($this->duplicateOnEnglish && $this->lang !== 'en') {
            $message .= '\n' . $this->getByDesignRow(AuthLang::trans('en', 'email_confirm_message'), $this->design);
        }
        $message = $this->updateMessage($message, $key, $passwordText, AuthLang::trans($this->lang, 'warning_below'));

        $this->sender->sendConfirmRegisterEmail($title, $header, $message, $key, $password);
    }

    protected function updateMessage(
        #[\SensitiveParameter] string $template,
        #[\SensitiveParameter] string $confirmLink,
        #[\SensitiveParameter] ?string $userPassword,
        $warning
    ): string
    {
        foreach (['confirm_link' => $confirmLink, 'user_password' => $userPassword, 'warning' => $warning]
                 as $key => $value) {
            $template = \str_replace('{{' . $key . '}}', $value, $template);
        }
        return (string)$template;
    }
}