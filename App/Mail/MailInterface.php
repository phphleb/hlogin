<?php

namespace Phphleb\Hlogin\App\Mail;

use Phphleb\Hlogin\App\Exceptions\HloginMailException;

/**
 * Allows you to replace the original mail sending class with the required mail server.
 *
 * Позволяет заменить исходный класс отправки писем на необходимый почтовый сервер.
 */
interface MailInterface
{
    /**
     * Initializing basic parameters for sending a letter.
     *
     * Инициализация базовых параметров для отправки письма.
     *
     * @param string $name - sender's name, set in the settings or the current domain will be substituted.
     *                     - имя отправителя, задаётся в настройках или будет подставлен текущий домен.
     *
     * @param string $to - E-mail address of the recipient of the letter.
     *                   - E-mail адрес получателя письма.
     *
     * @param string $from - E-mail address of the sender of the letter.
     *                     - E-mail адрес отправителя письма.
     *
     * @param string $design - label of the design used for the letter, for example, 'base'.
     *                       - метка используемого дизайна для письма, например, 'base'.
     *
     * @param string $lang - a language tag on which the letter should be, for example 'en'.
     *                     - языковая метка, на котором должно быть письмо, например 'en'.
     *
     * @param bool $sendToLog - a parameter from the settings that determines sending the letter to a log, for example to a file.
     *                        - параметр из настроек, определяющий отправку письма в лог, например в файл.
     *
     * @param bool $sendToEmail - a parameter from the settings that determines whether to send a letter to E-mail.     *
     *                       - параметр из настроек, определяющий отправлять ли письмо на E-mail.
     *
     * @throws HloginMailException
     */
    public function __construct(
        string $name,
        string $to,
        string $from,
        string $design,
        string $lang,
        bool $sendToLog,
        bool $sendToEmail,
    );

    /**
     * Sending a letter either with a pre-prepared text or the ability to compose a letter
     * based on the sending type and substituted values.
     * Called during initial registration to notify the user.
     *
     * Отправка письма как с уже подготовленным текстом, так и возможностью
     * составить письмо по типу отправки и подставляемым значениям.
     * Вызывается при первичной регистрации с уведомлением пользователя.
     *
     * @param string $title - the headline of the message.
     *                      - заголовок сообщения.
     *
     * @param string $header - the title of the text in the letter.
     *                       - заголовок текста в письме.
     *
     * @param string $messageHtml - message text (HTML).
     *                        - текст сообщения (HTML).
     *
     * @param string $key - if a wildcard key or hash is used, it will be passed here.
     *                    - если используется подстановочный ключ или хеш, то он будет передан здесь.
     *
     * @param string|null $password - if a password is used in the letter, it will be transmitted here.
     *                              - если используется пароль в письме, то он будет передан здесь.
     *
     * @throws HloginMailException
     */
    public function sendConfirmRegisterEmail(
        string  $title,
        string  $header,
        string  $messageHtml,
        string  $key,
        ?string $password = null,
    );

    /**
     * Called when the user changes their email address and confirms
     * the new email address, respectively.
     *
     * Вызывается при смене E-mail пользователем и подтверждении
     * нового почтового адреса соответственно.
     *
     * @see self::sendConfirmRegisterEmail()
     *
     * @throws HloginMailException
     */
    public function sendConfirmNewEmail(
        string $title,
        string $header,
        string $messageHtml,
        string $key,
    );

    /**
     * Called when a password recovery email is sent.
     *
     * Вызывается при отправке письма на восстановление пароля.
     *
     * @see self::sendConfirmRegisterEmail()
     *
     * @throws HloginMailException
     */
    public function sendPasswordRecoveryMail(
        string $title,
        string $header,
        string $messageHtml,
        string $key,
    );

    /**
     * Called when a password recovery email is sent.
     *
     * Вызывается при отправке письма на восстановление пароля.
     *
     * @see self::sendConfirmRegisterEmail()
     *
     * @throws HloginMailException
     */
    public function sendContactMessageMail(
        string $title,
        string $header,
        string $message,
        string $messageHtml,
    );
}