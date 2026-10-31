<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SandboxHostSupport;
use Revolution\Copilot\Types\Rpc\SandboxPathGrantWarningParams;
use Revolution\Copilot\Types\Rpc\SandboxPathGrantWarningResult;

/**
 * Pending server-scoped sandbox RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerSandbox
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Check whether the host can run the command sandbox.
     *
     * @experimental This API is experimental and may change or be removed.
     */
    public function getHostSupport(): SandboxHostSupport
    {
        return SandboxHostSupport::fromArray(
            $this->client->request('sandbox.getHostSupport', []),
        );
    }

    /**
     * Get the runtime's advice for a sandbox path grant.
     *
     * @experimental
     */
    public function getPathGrantWarning(SandboxPathGrantWarningParams|array $params): SandboxPathGrantWarningResult
    {
        $paramsArray = ($params instanceof SandboxPathGrantWarningParams
            ? $params
            : SandboxPathGrantWarningParams::fromArray($params))->toArray();

        return SandboxPathGrantWarningResult::fromArray(
            $this->client->request('sandbox.getPathGrantWarning', $paramsArray),
        );
    }
}
