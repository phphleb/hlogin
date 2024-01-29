<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin;

use Hleb\Base\Controller;
use Hleb\Constructor\Data\View;
use Hleb\Static\Request;
use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Helpers\EmailHelper;

/**
 * @internal
 */
final class ContactPageController extends Controller
{
    use SettingDataTrait;

    public function index(): View
    {
        $config = ConfigStorage::getConfig();
        $lang = Settings::getAutodetectLang();
        $contactActive = (bool)$config['contact']['active'];

        $mail = (string)$config['contact']['for-email'];
        $mailPlaceholder = EmailHelper::default(Request::getHost());

        return view(
            'hlogin/adminzone/contact',
            [
                'config' => $config,
                'lang' => $lang,
                'trans' => $this->getTrans($lang),
                'hloginData' => $this->getData(),
                'contactActive' => $contactActive,
                'mail' => $mail,
                'mailPlaceholder' => $mailPlaceholder,
            ]
        );
    }
}