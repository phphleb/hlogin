<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Mail;

use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Exceptions\HloginMailException;
use Phphleb\Muller\StandardMail;

final class StandardServer implements MailInterface
{
    private StandardMail $sender;

    /**
     * @inheritDoc
     * @internal
     */
    public function __construct(
        #[\SensitiveParameter] string $name,
        #[\SensitiveParameter] string $to,
        #[\SensitiveParameter] string $from,
        private readonly string       $design,
        string                        $lang,
        bool                          $sendToLog,
        bool                          $sendToEmail
    )
    {
        $this->sender = new StandardMail(false);
        $this->sender->setNameFrom($name);
        $this->sender->setTo($to);
        $this->sender->setAddressFrom($from);
        $this->sender->setParameters('-f' . $from);
        $this->sender->setDebugPath(Settings::getPath('@storage/logs'));
        $this->sender->setDebug(true);
        if ($sendToLog) {
            $this->sender->saveFileIntoDirectory(Settings::getPath('@storage/logs'));
            if (!$sendToEmail) {
                $this->sender->saveOnlyToFile(true);
            }
        }
    }

    /**
     * @inheritDoc
     * @internal
     * @throws HloginMailException
     */
    public function sendConfirmRegisterEmail(
        #[\SensitiveParameter] string  $title,
        #[\SensitiveParameter] string  $header,
        #[\SensitiveParameter] string  $messageHtml,
        #[\SensitiveParameter] string  $key,
        #[\SensitiveParameter] ?string $password = null,
    ): void
    {
        $this->send($title, $header, $messageHtml);
    }

    /**
     * @inheritDoc
     * @internal
     * @throws HloginMailException
     */
    public function sendConfirmNewEmail(
        #[\SensitiveParameter] string $title,
        #[\SensitiveParameter] string $header,
        #[\SensitiveParameter] string $messageHtml,
        #[\SensitiveParameter] string $key,
    ): void
    {
        $this->sendConfirmRegisterEmail($title, $header, $messageHtml, $key);
    }

    /**
     * @inheritDoc
     * @internal
     * @throws HloginMailException
     */
    public function sendPasswordRecoveryMail(
        #[\SensitiveParameter] string $title,
        #[\SensitiveParameter] string $header,
        #[\SensitiveParameter] string $messageHtml,
        #[\SensitiveParameter] string $key,
    ): void
    {
        $this->send($title, $header, $messageHtml);
    }

    /**
     * @inheritDoc
     * @internal
     * @throws HloginMailException
     */
    public function sendContactMessageMail(
        #[\SensitiveParameter] string $title,
        #[\SensitiveParameter] string $header,
        #[\SensitiveParameter] string $message,
        #[\SensitiveParameter] string $messageHtml,
    ): void
    {
        $this->send($title, $header, $messageHtml);
    }

    /**
     * @throws HloginMailException
     */
    private function send($title, $header, $messageHtml): void
    {
        $this->sender->setTitle($title);
        $this->sender->setMessage($this->design, $messageHtml);
        $this->sender->setTemplateHeader($header);
        $this->sender->send();

        if ($this->sender->getErrors()) {
            throw new HloginMailException((string)\current(($this->sender->getErrors())));
        }
    }
}