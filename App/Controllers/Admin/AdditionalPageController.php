<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin;

use Hleb\Base\Controller;
use Hleb\Constructor\Data\View;
use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Data\ConfigStorage;

/**
 * @internal
 */
final class AdditionalPageController extends Controller
{
    use SettingDataTrait;

    public function index(): View
    {
        $config = ConfigStorage::getConfig();
        $lang = Settings::getAutodetectLang();
        $getHeaderText = $config['registration']['reg-page-head'] ?? '';
        $letterCaption = (string)$config['mail']['letter-caption'];
        $sourcesList = $config['mail']['email-sources'];

        return view(
            'hlogin/adminzone/additional',
            [
                'config' => $config,
                'lang' => $lang,
                'trans' => $this->getTrans($lang),
                'hloginData' => $this->getData(),
                'getHeaderText' => $getHeaderText,
                'letterCaption' => $letterCaption,
                'sourcesList' => $sourcesList,
            ]
        );
    }
}