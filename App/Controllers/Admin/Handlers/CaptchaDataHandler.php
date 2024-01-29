<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin\Handlers;

use Hleb\Static\Request;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Ucaptcha\Captcha;

/**
 * @internal
 */
final readonly class CaptchaDataHandler  extends BaseHandler
{
    #[\Override]
    public function index(): array
    {
        $data = Request::post('json_data')->asArray();

        $config = ConfigStorage::getCaptchaConfig();

        if (isset($data['active'])){
            $config['active'] = (bool)$data['active'];
        }

        if (isset($data['design'])){
            $config['design'] = (string)$data['design'];
            $list = Captcha::TYPES;
            $list[] = 'auto';
            if (!\in_array($config['design'], $list)) {
                $config['design'] = Captcha::TYPE_BASE;
            }
        }

        ConfigStorage::saveCaptchaConfig($config);

        $this->afterSettingUpdate('captcha');

        return $this->successResponse(['lang' => $this->lang]);
    }
}