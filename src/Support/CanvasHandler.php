<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

use Revolution\Copilot\Types\Rpc\CanvasProviderCloseRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderInvokeActionRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderOpenRequest;
use Revolution\Copilot\Types\Rpc\CanvasProviderOpenResult;

/**
 * Handles provider-side canvas lifecycle and action requests.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
abstract class CanvasHandler
{
    abstract public function open(CanvasProviderOpenRequest $request): CanvasProviderOpenResult|array;

    public function close(CanvasProviderCloseRequest $request): void
    {
        //
    }

    public function invoke(CanvasProviderInvokeActionRequest $request): mixed
    {
        throw CanvasError::noHandler();
    }
}
