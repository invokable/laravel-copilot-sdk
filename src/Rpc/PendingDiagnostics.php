<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Session diagnostics RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingDiagnostics
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function configure(array $params): array
    {
        return $this->request('session.diagnostics.configure', $params);
    }

    public function read(array $params = []): array
    {
        return $this->request('session.diagnostics.read', $params);
    }

    protected function request(string $method, array $params): array
    {
        $params['sessionId'] = $this->sessionId;

        return $this->client->request($method, $params);
    }
}
