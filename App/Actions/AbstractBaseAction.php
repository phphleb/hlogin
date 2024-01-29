<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Models\UserModel;
use Phphleb\Hlogin\App\RegData;
use Phphleb\Ucaptcha\Captcha;

abstract class AbstractBaseAction
{
    public function __construct(
        protected string $method,
        protected string $lang,
    )
    {
    }

    /**
     * Actions taken must return an 'ok' or 'error' status.
     * They can also return 'content' as an array.
     * content => action contains a call to frontend commands.
     * For example, opening a window and redirecting to the home page:
     *
     * Выполняемые действия должны возвращать статус 'ok' или 'error'.
     * Также они могут возвращать 'content' в виде массива.
     * content => action содержит вызов на frontend команды.
     * Например, открытие окна и редирект на главную:
     *
     * [
     *   'status' => 'ok',
     *   'content' => [
     *       'action' => [
     *           'type' => 'UserEnter',
     *           'value' => null,
     *           'id' => 'example@mail.ru',
     *      ]
     *    ]
     * ]
     *
     * [
     *   'status' => 'ok',
     *   'content' => [
     *       'action' => [
     *           'type' => 'RedirectToPage',
     *           'value' => '/',
     *           'id' => null,
     *      ]
     *    ]
     * ]
     *
     * [
     *    'status' => 'error',
     *    'content' => [
     *        'form' => [
     *            'email' => 'required',
     *            'password' => 'incorrect',
     *       ],
     *       'captcha' => true,
     *     ],
     *    'message' => 'Validation error',
     *  ]
     */
    abstract public function execute(array $params): array;

    final protected function getSuccessResponse(array $content, ?string $message = null): array
    {
        return [
            'status' => 'ok',
            'content' => $content,
            'message' => $message,
            'method' => $this->method,
            'lang' => $this->lang,
        ];
    }

    final protected function getErrorResponse(array $content, ?string $message = null): array
    {
        return [
            'status' => 'error',
            'content' => $content,
            'message' => $message,
            'method' => $this->method,
            'lang' => $this->lang,
        ];
    }

    /**
     * Returns the captcha activity status in the library settings.
     *
     * Возвращает статус активности captcha в настройках библиотеки.
     */
    final protected function captchaIsActive(): bool
    {
        $config = ConfigStorage::getConfig();

        return (bool)$config['captcha']['active'];
    }

    /**
     * Returns the result of completing the captcha.
     * If captcha is disabled, but for some reason the code is there,
     * it will be checked for validity.
     *
     * Возвращает результат прохождения captcha.
     * Если captcha отключена, но по какой-то причине код есть,
     * он будет проверен на валидность.
     */
    final protected function captchaCheck(string $code): bool
    {
        if (!empty($code) && !preg_match(RegData::CAPTCHA_PATTERN, $code)) {
            return false;
        }
        $captcha = new Captcha();
        if ($captcha->isPassed()) {
            return true;
        }
        if ($this->captchaIsActive() && !$captcha->check($code)) {
            return false;
        }
        return true;
    }

    /**
     * This check prevents frequent (less than 2 seconds) requests to a specific email.
     * There should be a mechanism on the frontend to prevent sending at a shorter interval.
     *
     * Эта проверка предотвращает частые (менее 2 сек) запросы к конкретному email.
     * На frontend должен быть механизм, предотвращающий отправку с меньшим интервалом.
     */
    final protected function requestRateCheck (string $email): bool
    {
        $userData = UserModel::getCells('email', $email, ['id', 'period']);

        return ($userData && $userData['period'] && $userData['period'] >= \time() - 2) === false;
    }
}