<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin\Handlers;

use App\Bootstrap\Auth\Handlers\AdditionalAdminPanel;
use Phphleb\Hlogin\App\CurrentUser;
use Phphleb\Hlogin\App\Data\ConfigStorage;
use Phphleb\Hlogin\App\Models\UserModel;

/**
 * @internal
 */
abstract readonly class BaseHandler
{
    public const HLOGIN_MESSAGE = 'HLOGIN_ADMINZONE_MESSAGE';

    protected array|null $originConfig;

    protected int|null $moderatorId;

    public function __construct(protected string $lang)
    {
        $this->originConfig = ConfigStorage::getConfig();
        $this->moderatorId = CurrentUser::get()['id'] ?? null;
    }

    abstract public function index(): array;

    final protected function successResponse(array $data): array
    {
        return [
            'status' => 'ok',
            'data' => $data,
        ];
    }

    final protected function errorResponse(string $message): array
    {
        return [
            'status' => 'error',
            'message' => $message,
        ];
    }

    final protected function afterSettingUpdate(string $pageType): void
    {
        if (class_exists(AdditionalAdminPanel::class)) {
            (new AdditionalAdminPanel())->afterChangingSettings(
                $pageType,
                $this->lang,
                $this->originConfig,
                ConfigStorage::getConfig(),
                $this->moderatorId,
            );
        }
    }

    final protected function afterUserUpdate(array $originUserData, array $newUserData): void
    {
        if (class_exists(AdditionalAdminPanel::class)) {
            (new AdditionalAdminPanel())->afterChangingUserData(
                $this->lang,
                $originUserData,
                $newUserData,
                $this->moderatorId,
            );
        }
    }

    final protected function getSpecialUserData(int $userId): array
    {
        return (array)UserModel::getCells('id', $userId, [
            'id',
            'email',
            'regtype',
            'subscription',
            'confirm',
            'login',
            'name',
            'phone',
            'address',
            'surname',
        ]);
    }
}