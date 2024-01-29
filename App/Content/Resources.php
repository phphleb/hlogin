<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Content;

use Hleb\Helpers\ResourceViewHelper;
use Hleb\Http403ForbiddenException;
use Hleb\HttpMethods\External\SystemRequest;
use Hleb\HttpMethods\Intelligence\AsyncConsolidator;
use Hleb\Static\Request;
use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Ucaptcha\Captcha;

final class Resources
{
    private const  ALLOWED_EXT = ['css', 'js', 'svg', 'png', 'gif'];

    public function __construct(private readonly SystemRequest $request)
    {
    }

    /**
     * Returns the result of a system query to the library.
     *
     * Возвращает результат вызова системного запроса к библиотеке.
     */
    public function get(): bool
    {
        $path = $this->request->getUri()->getPath();

        $address = \explode('/', \trim($path, '/'));

        $part = \array_pop($address);
        $ext = \array_pop($address);

        if (!\in_array($ext, self::ALLOWED_EXT)) {
            return false;
        }

        if (empty($part) || !\preg_match("#^[aA-zZ0-9\-]+$#", $part)) {
            return false;
        }
        if ($ext === 'png' && $part === 'ucaptcha') {
            // The session is forcibly initiated.
            // Принудительно инициируется сессия.
            AsyncConsolidator::initAllCookies();

            $config = ConfigStorage::getConfig();
            if (!$config['captcha']['active']) {
                throw new Http403ForbiddenException('Captcha is disabled in the library settings.');
            }
            $design = $config['captcha']['design'];
            if ($design === 'auto') {
                $design = match(Request::get('design')->value()) {
                    'dark' => Captcha::TYPE_DARK,
                    'game' => Captcha::TYPE_3D,
                    default => Captcha::TYPE_BASE,
                };
              }
            (new Captcha())->createImage($design);
            return true;
        }
        $file = null;
        if (\str_starts_with($part, 'hloginstyle') || \str_starts_with($part, 'hloginlang')  || \str_starts_with($part, 'hloginexit')) {
            $file = Settings::getRealPath('@app/Bootstrap/Auth/Resources/' . $ext . '/' . $part . '.' . $ext);
        }

        if (!$file) {
            $file = Settings::getRealPath('@library/hlogin/web/' . $ext . '/' . $part . '.' . $ext);
            if (!$file) {
                if ($ext === 'svg') {
                    $path = '@library/hlogin/web/svg';
                    if (\str_starts_with($part, 'contact')) {
                        $file = Settings::getRealPath($path . '/contactbase.svg');
                    } else if (\str_starts_with($part, 'user')) {
                        $file = Settings::getRealPath($path . '/userbase.svg');
                    }  else if (\str_starts_with($part, 'profile')) {
                        $file = Settings::getRealPath($path . '/profilebase.svg');
                    }else if (\str_starts_with($part, 'checkboxnone')) {
                        $file = Settings::getRealPath($path . '/checkboxnonebase.svg');
                    } else if (\str_starts_with($part, 'checkboxon')) {
                        $file = Settings::getRealPath($path . '/checkboxonbase.svg');
                    }
                }
                if (!$file) {
                    return false;
                }
            }
        }

        return (new ResourceViewHelper())->add($file);
    }
}