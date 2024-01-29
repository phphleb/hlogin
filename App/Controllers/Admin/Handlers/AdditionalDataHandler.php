<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin\Handlers;

use Hleb\Static\Request;
use Phphleb\Hlogin\App\Content\AuthLang;
use Phphleb\Hlogin\App\Content\ConfigCellNormalizer;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\RegData;

/**
 * @internal
 */
final readonly class AdditionalDataHandler  extends BaseHandler
{
    #[\Override]
    public function index(): array
    {
        $data = Request::post('json_data')->asArray();

        $config = ConfigStorage::getRegConfig();

        if (isset($data['reg_page_head'])){
            $config['reg-page-head'] = \htmlspecialchars((string)$data['reg_page_head']);
        }

        $config['cells'] = (new ConfigCellNormalizer())->update($config['cells']);

        ConfigStorage::saveRegConfig($config);

        $config = ConfigStorage::getMailConfig();

        if (isset($data['letter_caption'])){
            $config['letter-caption'] = $data['letter_caption'];
        }
        if (isset($data['email_sources'])){
            $sources = trim((string)$data['email_sources']);
            $resultSources = [];
            if ($sources) {
                $sources = explode(',', $sources);
                foreach ($sources as $source) {
                    $source = trim($source);
                    if ($source) {
                        if (!preg_match(RegData::DOMAIN_PATTERN, $source)) {
                            return $this->errorResponse(AuthLang::trans($this->lang, 'data_validation_failed'));
                        }
                        $resultSources[] = $source;
                    }
                }
            }
            $config['email-sources'] = $resultSources;
        }

        ConfigStorage::saveMailConfig($config);

        $this->afterSettingUpdate('additional');

        return $this->successResponse(['lang' => $this->lang]);
    }
}