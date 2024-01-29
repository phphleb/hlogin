<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

use App\Bootstrap\Auth\Handlers\AdditionalChangeDesign;
use Hleb\HttpMethods\Intelligence\AsyncConsolidator;
use Phphleb\Hlogin\App\Content\AuthDesign;
use Phphleb\Hlogin\App\CurrentUser;


/**
 * Changing the design and adding it to Cookies.
 *
 * Изменение дизайна с занесением его в Cookies.
 *
 * @internal
 */
final class ChangeDesignAction extends AbstractBaseAction
{
    /** @inheritDoc */
    #[\Override]
   public function execute(array $params): array
   {
       // The session is forcibly initiated.
       // Принудительно инициируется сессия.
       AsyncConsolidator::initAllCookies();

       $insertHandler = \class_exists(AdditionalChangeDesign::class);
       if ($insertHandler) {
           $handler = (new AdditionalChangeDesign());
           if (!\is_subclass_of($handler, BaseAdditional::class)) {
               throw new \RuntimeException('The action class must inherit from ' . BaseAdditional::class);
           }
           if ($handler->insert($params) === false) {
               return $this->getErrorResponse(['data' => null, 'captcha' => true, 'system_message' => $handler->getErrorMessage()], 'These forms have not been verified');
           }
       }

       AuthDesign::set((string)$params['value']);

       if ($insertHandler) {
           $user = CurrentUser::get();
           if ($user) {
               $handler->afterAction($user['id']);
           } else {
               $handler->afterAction(null);
           }
       }

       return $this->getSuccessResponse([], AuthDesign::getActual());
   }
}