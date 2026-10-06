<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaCreateResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaStatus;

/** Server-side sandbox credential-proxy certificate authority RPCs. */
class PendingServerSandboxProxyCa
{
    public function __construct(protected JsonRpcClient $client) {}

    public function getStatus(?array $sandboxConfig = null): SandboxProxyCaStatus
    {
        return SandboxProxyCaStatus::fromArray($this->client->request(
            'sandbox.proxyCa.getStatus',
            $this->params($sandboxConfig),
        ));
    }

    public function create(?array $sandboxConfig = null): SandboxProxyCaCreateResult
    {
        return SandboxProxyCaCreateResult::fromArray($this->client->request(
            'sandbox.proxyCa.create',
            $this->params($sandboxConfig),
        ));
    }

    public function rotate(?array $sandboxConfig = null): SandboxProxyCaStatus
    {
        return SandboxProxyCaStatus::fromArray($this->client->request(
            'sandbox.proxyCa.rotate',
            $this->params($sandboxConfig),
        ));
    }

    public function trust(?array $sandboxConfig = null): SandboxProxyCaStatus
    {
        return SandboxProxyCaStatus::fromArray($this->client->request(
            'sandbox.proxyCa.trust',
            $this->params($sandboxConfig),
        ));
    }

    public function remove(): SandboxProxyCaStatus
    {
        return SandboxProxyCaStatus::fromArray(
            $this->client->request('sandbox.proxyCa.remove', []),
        );
    }

    private function params(?array $sandboxConfig): array
    {
        return $sandboxConfig === null ? [] : ['sandboxConfig' => $sandboxConfig];
    }
}
