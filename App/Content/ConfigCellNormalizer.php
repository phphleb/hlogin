<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Content;

/**
 * @internal
 */
final class ConfigCellNormalizer
{
    /**
     * Normalization of form fields.
     *
     * Нормализация полей форм.
     */
    public function update(array $cells): array
    {
        if (!isset($cells['personal-data-consent'])) {
            $cells['personal-data-consent'] = ['req' => 0, 'on' => 0, 'prof' => 0];
        }

        foreach($cells as $name => &$cell) {
            if (!isset($cell['req'])) {
                $cell['req'] = 0;
            }
            if (!isset($cell['prof'])) {
                $cell['prof'] = 0;
            }
            if (!isset($cell['on'])) {
                $cell['on'] = 0;
            }
            if ($name === 'password') {
                $cell['prof'] = 1;
            }
            if ($name === 'promocode') {
                $cell['prof'] = 0;
                $cell['req'] = 0;
            }
            if (($name === 'privacy-policy' || $name === 'terms-of-use' || $name === 'personal-data-consent') && $cell['on']) {
                $cell['req'] = 1;
            }
            if ($name === 'personal-data-consent') {
                $cell['prof'] = 0;
            }
            if ($name === 'subscription') {
                $cell['req'] = 0;
            }
        }
        return $cells;
    }
}