<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin\Handlers;

use Hleb\Static\Request;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\RegData;

/**
 * @internal
 */
final readonly class ContactDataHandler extends BaseHandler
{
    #[\Override]
    public function index(): array
    {
        $data = Request::post('json_data')->asArray();

        $config = ConfigStorage::getContactConfig();

        if (isset($data['active'])){
            $config['active'] = (bool)$data['active'];
        }

        if (isset($data['email'])){
            if ($data['email'] !== '' && !preg_match(RegData::EMAIL_PATTERN, (string)$data['email'])) {
                return $this->errorResponse(AuthLang::trans($this->lang,'data_validation_failed'));
            }
            $config['for-email'] = $data['email'];
        }

        ConfigStorage::saveContactConfig($config);

        $this->afterSettingUpdate('contact');

        return $this->successResponse(['lang' => $this->lang]);
    }
}