<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin;

use Hleb\Static\Settings;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\Data\ConfigStorage;

/**
 * @internal
 */
trait SettingDataTrait
{
  protected function getData(): array
  {
      $config = ConfigStorage::getConfig();

      return [
          'version' => $config['version'],
          'ending' => Settings::isEndingUrl(),
          'lang' => Settings::getAutodetectLang(),
          'token' => csrf_token(),
      ];
  }

  protected function getTrans(string $lang): \Closure
  {
      return static function (string $text) use ($lang): string {
          return AuthLang::trans($lang, $text);
      };
  }
}