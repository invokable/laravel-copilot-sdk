<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Revolution\Copilot\Exceptions\JsonRpcException;

trait HasDeprecated
{
    /**
     * @deprecated Use disconnect() instead. This method will be removed in a future release.
     *
     * Disconnect this session and release all in-memory resources.
     * Session data on disk is preserved for later resumption.
     *
     * @throws JsonRpcException
     */
    #[\Deprecated(message: 'Use disconnect() instead. This method will be removed in a future release.', since: '0.2.28')]
    public function destroy(): void
    {
        $this->disconnect();
    }
}
