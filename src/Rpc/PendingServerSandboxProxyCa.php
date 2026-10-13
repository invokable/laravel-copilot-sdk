<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SandboxConfig;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaCreateResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaStatus;

/** Server-side sandbox credential-proxy certificate authority RPCs. */
class PendingServerSandboxProxyCa
{
    public function __construct(protected JsonRpcClient $client) {}

    public function getStatus(SandboxConfig|array|null $sandboxConfig = null): SandboxProxyCaStatus
    {
        return SandboxProxyCaStatus::fromArray($this->client->request(
            'sandbox.proxyCa.getStatus',
            $this->params($sandboxConfig),
        ));
    }

    public function create(SandboxConfig|array|null $sandboxConfig = null): SandboxProxyCaCreateResult
    {
        return SandboxProxyCaCreateResult::fromArray($this->client->request(
            'sandbox.proxyCa.create',
            $this->params($sandboxConfig),
        ));
    }

    public function rotate(SandboxConfig|array|null $sandboxConfig = null): SandboxProxyCaStatus
    {
        return SandboxProxyCaStatus::fromArray($this->client->request(
            'sandbox.proxyCa.rotate',
            $this->params($sandboxConfig),
        ));
    }

    public function trust(SandboxConfig|array|null $sandboxConfig = null): SandboxProxyCaStatus
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

    /**
     * Check whether this sandbox configuration is relevant to proxy CA management.
     *
     * @experimental
     */
    public function checkRelevance(SandboxConfig|array|null $sandboxConfig = null): bool
    {
        return (bool) $this->client->request(
            'sandbox.proxyCa.checkRelevance',
            $this->params($sandboxConfig),
        );
    }

    /**
     * Check whether this host supports installing the proxy CA.
     *
     * @experimental
     */
    public function checkInstallationSupport(): bool
    {
        return (bool) $this->client->request('sandbox.proxyCa.checkInstallationSupport', []);
    }

    private function params(SandboxConfig|array|null $sandboxConfig): array
    {
        if ($sandboxConfig === null) {
            return [];
        }

        return [
            'sandboxConfig' => $sandboxConfig instanceof SandboxConfig
                ? $sandboxConfig->toArray()
                : $sandboxConfig,
        ];
    }
}
