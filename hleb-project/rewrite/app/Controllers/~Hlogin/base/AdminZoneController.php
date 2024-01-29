<?php
declare(strict_types=1);

namespace App\Controllers\Hlogin;

use Phphleb\Hlogin\App\Controllers\BaseAdminZoneController;

/**
 * Settings and display of pages for the administrative panel.
 *
 * Настройки и вывод страниц для административной панели.
 */
class AdminZoneController extends BaseAdminZoneController
{
    /** @inheritDoc */
    #[\Override]
    public function userPage(): string
    {
       return parent::userPage();
    }

    /** @inheritDoc */
    #[\Override]
    public function rightPage(): string
    {
        return parent::rightPage();
    }

    /** @inheritDoc */
    #[\Override]
    public function settingPage(): string
    {
        return parent::settingPage();
    }

    /** @inheritDoc */
    #[\Override]
    public function captchaPage(): string
    {
        return parent::captchaPage();
    }

    /** @inheritDoc */
    #[\Override]
    public function emailPage(): string
    {
        return parent::emailPage();
    }

    /** @inheritDoc */
    #[\Override]
    public function additionalPage(): string
    {
        return parent::additionalPage();
    }

    /** @inheritDoc */
    #[\Override]
    public function contactPage(): string
    {
        return parent::contactPage();
    }

    /** @inheritDoc */
    #[\Override]
    public function profilePage(): string
    {
        return parent::profilePage();
    }

    /** @inheritDoc */
    #[\Override]
    public function data(): array
    {
        return parent::data();
    }
}
