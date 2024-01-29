<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Mail;

use App\Bootstrap\Auth\MailServer;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Exceptions\HloginMailException;

abstract class BaseMail
{
    protected array $errors = [];

    protected object $sender;

    protected bool $sendToEmail;

    protected bool $sendToFile;

    protected bool $letterCaption;

    protected bool $duplicateOnEnglish;

    protected string $design;

    protected string $type;

    protected bool $standardSender = true;

    /**
     * @internal
     * @throws HloginMailException
     */
    public function __construct(
        #[\SensitiveParameter] protected string $name,
        #[\SensitiveParameter] protected string $mailFrom,
        #[\SensitiveParameter] protected string $mailTo,
        protected string                        $lang,
        protected bool                          $onlyToFile,
    )
    {
        if (empty($mailTo)) {
            throw new HloginMailException("Empty parameter `mailTo`.");
        }
        if (empty($name)) {
            throw new HloginMailException("Empty parameter `name`.");
        }
        if (empty($mailFrom)) {
            throw new HloginMailException("Empty parameter `mailFrom`.");
        }
        $config = ConfigStorage::getConfig();

        $this->sendToFile = (bool)$config['mail']['save-to-file'];
        $this->sendToEmail = (bool)$config['mail']['send-to-email'];
        if ($onlyToFile) {
            $this->sendToEmail = false;
        }
        $this->letterCaption = (bool)$config['mail']['letter-caption'];
        $this->design = (string)$config['mail']['design'];
        $this->duplicateOnEnglish = (bool)$config['mail']['duplicate'];

        $class = \class_exists('App\Bootstrap\Auth\MailServer') ? MailServer::class : StandardServer::class;
        $interface = MailInterface::class;
        $interfaces = \class_implements($class);
        if (!isset($interfaces[$interface])) {
            throw new \RuntimeException("The `$class` class must have the `$interface` interface.");
        }
        /** @var MailInterface $sender */
        $this->sender = new $class(
            $this->name,
            $this->mailTo,
            $this->mailFrom,
            $this->design,
            $this->lang,
            $this->sendToFile,
            $this->sendToEmail,
        );
    }

    protected function getByDesignRow(array $rows, string $design): string
    {
        if (isset($rows[$design])) {
            return $rows[$design];
        }
        return $rows['universal'];
    }

    /**
     * Universal sending of letters.
     *
     * Универсальная отправка письма.
     *
     * @param string $title - text for the letter header in the desired language.
     *                      - текст для заголовка письма на нужном языке.
     *
     * @param string $header - text for the message title in the letter.
     *                       - текст для заголовка сообщения в письме.
     *
     * @param string|null $key - key, if present for the letter type.
     *                          - ключ, если присутствует для типа письма.
     *
     * @param string|null $password - password, if present for the letter type.
     *                              - пароль, если присутствует для типа письма.
     *
     * @param string|null $text - text, if present for the letter type.
     *                              - текст, если присутствует для типа письма.
     * @internal
     */
    abstract public function send(
        #[\SensitiveParameter] string $title,
        #[\SensitiveParameter] string $header,
        #[\SensitiveParameter] ?string $key = null,
        #[\SensitiveParameter] ?string $password = null,
        #[\SensitiveParameter] ?string $text = null,
    );
}