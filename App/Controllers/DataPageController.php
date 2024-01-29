<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers;

use Hleb\Base\Controller;
use Hleb\Static\Request;
use Phphleb\Hlogin\App\Actions\AdminzoneEnterAction;
use Phphleb\Hlogin\App\Actions\ChangeDesignAction;
use Phphleb\Hlogin\App\Actions\ConfirmEmailAction;
use Phphleb\Hlogin\App\Actions\ContactMessageAction;
use Phphleb\Hlogin\App\Actions\NewPasswordAction;
use Phphleb\Hlogin\App\Actions\RegisterEmailAction;
use Phphleb\Hlogin\App\Actions\UserEnterAction;
use Phphleb\Hlogin\App\Actions\UserPasswordAction;
use Phphleb\Hlogin\App\Actions\UserProfileAction;
use Phphleb\Hlogin\App\Actions\UserProfileDataAction;
use Phphleb\Hlogin\App\Actions\UserRegisterAction;

/**
 * @internal
 */
class DataPageController extends Controller
{
    /**
     * Handling Ajax requests for registration forms.
     *
     * Обработка Ajax-запросов для форм регистрации.
     */
    public function index(): array
    {
        $data = Request::post('json_data')->asString();

        // This is an additional conversion in case the previous conversion
        // to a string disabled the check.
        // Это дополнительное преобразование на тот случай, если в предыдущей
        // конвертации в строку проверка будет отключена.
        $params = (array)\json_decode($data, true);
        \array_walk_recursive($params, function (&$item, $key) {
            if (\is_string($item)) {
                $item = \htmlspecialchars($item, ENT_NOQUOTES);
            }
        });

        return match ($params['method'] ?? null) {
            'UserEnter' => (new UserEnterAction($params['method'], $params['lang']))->execute($params),
            'UserProfileData' => (new UserProfileDataAction($params['method'], $params['lang']))->execute($params),
            'UserProfile' => (new UserProfileAction($params['method'], $params['lang']))->execute($params),
            'UserRegister' => (new UserRegisterAction($params['method'], $params['lang']))->execute($params),
            'ChangeDesign' => (new ChangeDesignAction($params['method'], $params['lang']))->execute($params),
            'ContactMessage' => (new ContactMessageAction($params['method'], $params['lang']))->execute($params),
            'AdminzoneEnter' => (new AdminzoneEnterAction($params['method'], $params['lang']))->execute($params),
            'UserPassword' => (new UserPasswordAction($params['method'], $params['lang']))->execute($params),
            'ConfirmEmail' => (new ConfirmEmailAction($params['method'], $params['lang']))->execute($params),
            'NewPassword' => (new NewPasswordAction($params['method'], $params['lang']))->execute($params),
            'RegisterEmail' => (new RegisterEmailAction($params['method'], $params['lang']))->execute($params),
            default => [
                'status' => 'error',
                'message' => 'Could not find a suitable event to execute.',
                'content' => null,
            ]
        };
    }
}