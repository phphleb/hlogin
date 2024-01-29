<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin\Handlers;

use Hleb\Static\Request;
use Phphleb\Hlogin\App\Content\AuthDesign;
use Phphleb\Hlogin\App\Content\ConfigCellNormalizer;
use Phphleb\Hlogin\App\Data\ConfigStorage;

/**
 * @internal
 */
final readonly class SettingDataHandler extends BaseHandler
{
    #[\Override]
    public function index(): array
    {
        $data = Request::post('json_data')->asArray();

        $design = ConfigStorage::getDesign();

        $config = ConfigStorage::getRegConfig();

        // Updating the panel design.
        // Обновление дизайна панелей.
        if (isset($data['design']) && $data['design'] !== $design) {
            if (!in_array($data['design'], $config['design-options'])) {
                $data['design'] = $config['design'];
            }
            ConfigStorage::saveDesign($data['design']);
            AuthDesign::set($data['design']);
        }

        // Update registration data.
        // Обновление регистрационных данных.

        if (isset($data['block_active'])){
            $config['button']['active'] = (bool)$data['block_active'];
        }
        if (isset($data['enter_only'])){
            $config['enter-only'] = (bool)$data['enter_only'];
        }
        if (isset($data['password_in_mail'])){
            $config['password-in-mail'] = (int)(bool)$data['password_in_mail'];
        }

        if (isset($data['on_password'])){
            $config['cells']['password']['on'] = (int)(bool)$data['on_password'];
        }
        if (isset($data['on_phone'])){
            $config['cells']['phone']['on'] = (int)(bool)$data['on_phone'];
        }
        if (isset($data['on_name'])){
            $config['cells']['name']['on'] = (int)(bool)$data['on_name'];
        }
        if (isset($data['on_surname'])){
            $config['cells']['surname']['on'] = (int)(bool)$data['on_surname'];
        }
        if (isset($data['on_address'])){
            $config['cells']['address']['on'] = (int)(bool)$data['on_address'];
        }
        if (isset($data['on_promo_code'])){
            $config['cells']['promocode']['on'] = (int)(bool)$data['on_promo_code'];
        }
        if (isset($data['on_subscription'])){
            $config['cells']['subscription']['on'] = (int)(bool)$data['on_subscription'];
        }
        if (isset($data['on_terms_of_use'])){
            $config['cells']['terms-of-use']['on'] = (int)(bool)$data['on_terms_of_use'];
        }
        if (isset($data['on_privacy_policy'])){
            $config['cells']['privacy-policy']['on'] = (int)(bool)$data['on_privacy_policy'];
        }

        if (isset($data['req_password'])){
            $config['cells']['password']['req'] = (int)(bool)$data['req_password'];
        }
        if (isset($data['on_phone'])){
            $config['cells']['phone']['req'] = (int)(bool)$data['req_phone'];
        }
        if (isset($data['req_name'])){
            $config['cells']['name']['req'] = (int)(bool)$data['req_name'];
        }
        if (isset($data['req_surname'])){
            $config['cells']['surname']['req'] = (int)(bool)$data['req_surname'];
        }
        if (isset($data['req_address'])){
            $config['cells']['address']['req'] = (int)(bool)$data['req_address'];
        }

        if (isset($data['prof_phone'])){
            $config['cells']['phone']['prof'] = (int)(bool)$data['prof_phone'];
        }
        if (isset($data['prof_name'])){
            $config['cells']['name']['prof'] = (int)(bool)$data['prof_name'];
        }
        if (isset($data['prof_surname'])){
            $config['cells']['surname']['prof'] = (int)(bool)$data['prof_surname'];
        }
        if (isset($data['prof_address'])){
            $config['cells']['address']['prof'] = (int)(bool)$data['prof_address'];
        }

        if (isset($data['link_to_user_agreement'])){
            $config['src']['terms-of-use'] = (string)$data['link_to_user_agreement'];
        }
        if (isset($data['link_to_privacy_policy'])){
            $config['src']['privacy-policy'] = (string)$data['link_to_privacy_policy'];
        }
        if (isset($data['get_url_after_reg'])){
            $config['src']['url-after-reg'] = (string)$data['get_url_after_reg'];
        }


        $config['cells'] = (new ConfigCellNormalizer())->update($config['cells']);

        ConfigStorage::saveRegConfig($config);

        $this->afterSettingUpdate('settings');

        return $this->successResponse(['lang' => $this->lang]);
    }
}