<?php

declare(strict_types=1);

namespace Phphleb\Hlogin\App\Actions;

/**
 * Base class for assigning action handlers to the HLOGIN library.
 *
 * Базовый класс для назначения обработчиков действий библиотеки HLOGIN.
 */
abstract class BaseAdditional
{
    protected string $errorMessage = 'Self-made error when parameters do not match.';

    /**
     * @internal
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Called after validating the format of the action's incoming parameters.
     *
     * Вызывается после валидации формата входящих параметров действия.
     */
    abstract public function insert(array $params): bool;

    /**
     * Called immediately after the action is completed.
     *
     * Вызывается сразу после выполнения действия.
     */
    abstract public function afterAction(int $userId);
}