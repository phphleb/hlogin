<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin;

use Hleb\Base\Controller;
use Hleb\Constructor\Data\View;
use Hleb\Helpers\ArrayHelper;
use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\Data\ConfigStorage;

/**
 * @internal
 */
final class SettingPageController extends Controller
{
    use SettingDataTrait;

    public function index(): View
    {
        $config = ConfigStorage::getConfig();
        $lang = Settings::getAutodetectLang();
        $designOptions = ArrayHelper::moveFirstByValue($config['registration']['design-options'], $config['design']);
        $baseDesign = $config['design'];
        $languageOptions = AuthLang::getAll();
        $buttonActive = (bool)$config['registration']['button']['active'];
        $onlyEnter = (bool)$config['registration']['enter-only'];
        $cells = $config['registration']['cells'];
        $linkToUserAgreement = $config['registration']['src']['terms-of-use'];
        $linkToPrivacyPolicy = $config['registration']['src']['privacy-policy'];
        $getUrlAfterReg = $config['registration']['src']['url-after-reg'];
        $sendPasswordInMail = $config['registration']['password-in-mail'];

        return view(
            'hlogin/adminzone/setting',
            [
                'config' => $config,
                'lang' => $lang,
                'trans' => $this->getTrans($lang),
                'designOptions' => $designOptions,
                'baseDesign' => $baseDesign,
                'languageOptions' => $languageOptions,
                'buttonActive' => $buttonActive,
                'onlyEnter' => $onlyEnter,
                'cells' => $cells,
                'linkToUserAgreement' => $linkToUserAgreement,
                'linkToPrivacyPolicy' => $linkToPrivacyPolicy,
                'getUrlAfterReg' => $getUrlAfterReg,
                'hloginData' => $this->getData(),
                'sendPasswordInMail' => (bool)$sendPasswordInMail,
            ]
        );
    }
}