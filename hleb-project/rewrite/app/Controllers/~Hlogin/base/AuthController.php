<?php
declare(strict_types=1);

namespace App\Controllers\Hlogin;

use Hleb\Base\Controller;
use Hleb\Constructor\Data\View;
use Hleb\HttpMethods\Specifier\DataType;
use Hleb\Static\Request;
use Hleb\Static\Response;
use Phphleb\Hlogin\App\Controllers\AuthPageController;
use Phphleb\Hlogin\App\Controllers\AuthServiceController;
use Phphleb\Hlogin\App\Controllers\DataPageController;

/**
 * Pages for displaying authorization and registration data.
 *
 * Страницы для вывода данных авторизации и регистрации.
 */
class AuthController extends Controller
{
    /**
     * Service pages for registration (logout and logout on all devices).
     *
     * Служебные страницы для регистрации (выход и выход на всех устройствах).
     *
     * @throws \AsyncExitException
     */
    public function exit(): View
    {
        return (new AuthServiceController($this->config))->exit();
    }

    /**
     * Separate pages for forms (feedback, registration, login).
     *
     * Отдельные страницы для форм (обратная связь, регистрация, вход).
     *
     * @throws \AsyncExitException
     */
    public function action(): View
    {
        return (new AuthPageController($this->config))->index(Request::param('action'));
    }

    /**
     * Separate page for user profile.
     *
     * Отдельная страница для профиля пользователя.
     *
     * @throws \AsyncExitException
     */
    public function profile(): View
    {
        return (new AuthPageController($this->config))->index(new DataType('profile'));
    }

    /**
     * Handling Ajax requests for registration forms.
     *
     * Обработка Ajax-запросов для форм регистрации.
     *
     * @throws \AsyncExitException
     */
    public function data(): array
    {
        // Technical errors, they are not visible during normal operation.
        // Технические ошибки, при обычной работе они не видны.
        $data = Request::post('json_data')->asString();
        if (!$data) {
            Response::setStatus(400);
            return [
                'result' => 'error',
                'message' => 'A required query parameter is missing.',
                'content' => null,
            ];
        }
        try {
            $params = \json_decode($data, true, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Response::setStatus(500);
            return [
                'result' => 'error',
                'message' => $e->getMessage(),
                'content' => null,
            ];
        }
        if (empty($params['method'])) {
            Response::setStatus(400);
            return [
                'result' => 'error',
                'message' => 'You must specify the method for which the data is passed.',
                'content' => null,
            ];
        }
        return (new DataPageController($this->config))->index();
    }
}
