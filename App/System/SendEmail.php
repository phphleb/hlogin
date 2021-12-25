<?php


namespace Phphleb\Hlogin\App\System;


use Phphleb\Muller\StandardMail;
use Phphleb\Muller\Src\DefaultMail;

class SendEmail
{
    private DefaultMail $sender;
    private array $errors = [];

    /**
     * Инициализация конструктора.
     * @param array $params - параметры отправки
     * @param bool $onlyToFile - сохранение только в файл
     */
    public function __construct(array $params, bool $onlyToFile = false) {
        try {
            if (empty($params['to']) || empty($params['name']) || empty($params['from'])) {
                $this->errors[] = "Empty parameter.";
                return;
            }
            $this->sender = class_exists('App\Optional\HloginMailServer') ? new App\Optional\HloginMailServer() : new StandardMail(false);
            $this->sender->setNameFrom($params['name']);
            $this->sender->setTo($params['to']);
            $this->sender->setAddressFrom($params['from']);
            $this->sender->setTitle($params['title']);
            $this->sender->setTemplateHeader($params['header'] ?? '');
            $this->sender->setParameters('-f' . $params['from']);
            $this->sender->setMessage($params['design'], $params['message']);
            $this->sender->setDebugPath(HLEB_GLOBAL_DIRECTORY . DIRECTORY_SEPARATOR . 'storage/logs');
            $this->sender->setDebug(true);
            if($onlyToFile || $params['save_log']) {
                $this->sender->saveFileIntoDirectory(HLEB_GLOBAL_DIRECTORY . DIRECTORY_SEPARATOR . 'storage/logs');
            }
            if($onlyToFile) {
                $this->sender->saveOnlyToFile(true);
            }
        } catch (\Exception $exception) {
            $errors[] = $exception->getMessage();
        }
    }

    public function send() {
        if (empty($this->errors)) {
            $result = $this->sender->send();
            if (!$result) {
                $this->errors[] = "Not sended.";
            }
        }
    }

    public function getErrors() {
        return $this->errors;
    }

}