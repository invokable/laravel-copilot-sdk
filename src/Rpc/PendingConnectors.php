<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Connector account and runtime RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingConnectors
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function getCapabilities(): array
    {
        return $this->request('session.connectors.getCapabilities');
    }

    public function getStatus(): array
    {
        return $this->request('session.connectors.getStatus');
    }

    public function list(array $params = []): array
    {
        return $this->request('session.connectors.list', $params);
    }

    public function refresh(array $params = []): array
    {
        return $this->request('session.connectors.refresh', $params);
    }

    public function connect(array $params): array
    {
        return $this->request('session.connectors.connect', $params);
    }

    public function reconnect(array $params): array
    {
        return $this->request('session.connectors.reconnect', $params);
    }

    public function continueConnection(array $params): array
    {
        return $this->request('session.connectors.continueConnection', $params);
    }

    public function disconnect(array $params): array
    {
        return $this->request('session.connectors.disconnect', $params);
    }

    public function reconcile(array $params = []): array
    {
        return $this->request('session.connectors.reconcile', $params);
    }

    /** @internal */
    public function reconcileForStartup(array $params = []): array
    {
        return $this->request('session.connectors.reconcileForStartup', $params);
    }

    /** @internal */
    public function withdrawProjection(array $params = []): array
    {
        return $this->request('session.connectors.withdrawProjection', $params);
    }

    protected function request(string $method, array $params = []): array
    {
        $params['sessionId'] = $this->sessionId;

        return $this->client->request($method, $params);
    }
}
