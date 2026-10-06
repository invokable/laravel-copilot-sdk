<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\CustomizationsReloadResult;

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

    public function reload(): CustomizationsReloadResult
    {
        return CustomizationsReloadResult::fromArray(
            $this->client->request('session.customizations.reload', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }
}
