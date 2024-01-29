<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin;

use Hleb\Base\Controller;
use Hleb\Constructor\Data\View;
use Hleb\Helpers\ArrayHelper;
use Hleb\Static\Request;
use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Helpers\EmailHelper;
use Phphleb\Muller\Src\DefaultMail;

/**
 * @internal
 */
final class EmailPageController extends Controller
{
    use SettingDataTrait;

    public function index(): View
    {
        $config = ConfigStorage::getConfig();
        $lang = Settings::getAutodetectLang();
        $mail = (string)$config['mail']['from'];
        $mailPlaceholder = EmailHelper::default(Request::getHost());

        $sendToEmail = (bool)$config['mail']['send-to-email'];
        $savePostToLog = (bool)$config['mail']['save-to-file'];
        $duplicateInEnglish = (bool)$config['mail']['duplicate'];

        $design = $config['mail']['design'] ?? DefaultMail::ALL_DESIGN;
        $designList = DefaultMail::ALL_DESIGN;
        if (!in_array($design, $designList)) {
            $design = DefaultMail::ALL_DESIGN[0];
        }
        $designList = ArrayHelper::moveFirstByValue($designList, $design);

        return view(
            'hlogin/adminzone/email',
            [
                'config' => $config,
                'lang' => $lang,
                'trans' => $this->getTrans($lang),
                'hloginData' => $this->getData(),
                'mail' => $mail,
                'mailPlaceholder' => $mailPlaceholder,
                'sendToEmail' => $sendToEmail,
                'savePostToLog' => $savePostToLog,
                'duplicateInEnglish' => $duplicateInEnglish,
                'designList' => $designList,
            ]
        );
    }
}