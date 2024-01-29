<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

/**
 * @internal
 */
final class AdminzoneEnterAction extends AbstractBaseAction
{
    /** @inheritDoc */
    #[\Override]
   public function execute(array $params): array
   {
       return $this->getSuccessResponse(
           [
               'data' => null,
               'action' => ['type' => 'AdminzoneEnter'],
               'captcha' => true,
           ],
           'Redirect to admin panel',
       );
   }
}