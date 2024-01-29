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
final readonly class EmailDataHandler  extends BaseHandler
{
    #[\Override]
    public function index(): array
    {
        $data = Request::post('json_data')->asArray();

        $config = ConfigStorage::getMailConfig();

        if (isset($data['email'])){
            if ($data['email'] !== '' && !preg_match(RegData::EMAIL_PATTERN, (string)$data['email'])) {
              return $this->errorResponse(AuthLang::trans($this->lang,'data_validation_failed'));
            }
            $config['from'] = $data['email'];
        }

        if (isset($data['not_send_by_email'])){
            $config['send-to-email'] = (int)!$data['not_send_by_email'];
        }
        if (isset($data['save_post_to_log'])){
            $config['save-to-file'] = (int)$data['save_post_to_log'];
        }
        if (isset($data['duplicate_english'])){
            $config['duplicate'] = (int)$data['duplicate_english'];
        }
        if (isset($data['design'])){
            $config['design'] = $data['design'];
        }

        ConfigStorage::saveMailConfig($config);

        $this->afterSettingUpdate('email');

        return $this->successResponse(['lang' => $this->lang]);
    }
}