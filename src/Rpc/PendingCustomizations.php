<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Session customization reload operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingCustomizations
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function reload(): void
    {
        $this->client->request('session.customizations.reload', [
            'sessionId' => $this->sessionId,
        ]);
    }
}
