<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin;

use Hleb\Base\Controller;
use Hleb\Constructor\Data\View;
use Hleb\Helpers\ArrayHelper;
use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Ucaptcha\Captcha;

/**
 * @internal
 */
final class CaptchaPageController extends Controller
{
   use SettingDataTrait;

   public function index(): View
   {
       $config = ConfigStorage::getConfig();
       $lang = Settings::getAutodetectLang();
       $design = $config['captcha']['design'] ?? Captcha::TYPE_BASE;
       $designList = Captcha::TYPES;
       $designList[] = 'auto';
       if (!in_array($design, $designList)) {
           $design = Captcha::TYPE_BASE;
       }
       $designList = ArrayHelper::moveFirstByValue($designList, $design);
       $captchaActive = (bool)$config['captcha']['active'];

       return view(
           'hlogin/adminzone/captcha',
           [
               'config' => $config,
               'lang' => $lang,
               'trans' => $this->getTrans($lang),
               'hloginData' => $this->getData(),
               'designList' => $designList,
               'captchaActive' => $captchaActive,
           ]
       );
   }
}