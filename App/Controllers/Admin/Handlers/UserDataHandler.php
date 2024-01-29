<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Controllers\Admin\Handlers;

use Hleb\Static\Request;

/**
 * @internal
 */
final readonly class UserDataHandler extends BaseHandler
{
    #[\Override]
    public function index(): array
    {
        $data = Request::post('json_data')->asArray();
        // ,,, //

        return $this->successResponse(['lang' => $this->lang]);
    }
}