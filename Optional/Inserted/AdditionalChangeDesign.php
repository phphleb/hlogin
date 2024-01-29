<?php

declare(strict_types=1);

namespace App\Bootstrap\Auth\Handlers;

use Phphleb\Hlogin\App\Actions\BaseAdditional;

/**
 * When adding this class to the `\app\Bootstrap\Auth\Handlers` folder of the project
 * it adds additional design change handling.
 *
 * При добавлении этого класса в папку `\app\Bootstrap\Auth\Handlers` проекта
 * он добавляет дополнительную обработку смены дизайна.
 */
final class AdditionalChangeDesign extends BaseAdditional
{
    protected string $errorMessage = 'Self-made error when parameters do not match.';

    /**
     * The method is executed before changing the design,
     * the current design can be obtained as $params['value'].
     *
     * Метод выполняется перед сменой дизайна, текущий дизайн
     * можно получить как $params['value'].
     *
     * @param array $params - parameters sent with the request.
     *                      - параметры, переданные с запросом.
     * @return bool - if false, returns an error to the form with the message from $this->errorMessage.
     *              - при значении false возвращает в форму ошибку с сообщением из $this->errorMessage.
     */
    #[\Override]
    public function insert(array $params): bool
    {
        // ... //

        return true;
    }

    /**
     * Performed after a design change.
     * If the user is authorized, then contains the ID.
     *
     * Выполняется после изменения дизайна.
     * Если пользователь авторизован, то содержит ID.
     *
     * @param int|null $userId - user ID.
     *                         - идентификатор пользователя.
     * @return void
     */
    #[\Override]
    public function afterAction(?int $userId)
    {
        // ... //
    }
}