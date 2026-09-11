<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SandboxDisableForSessionRequest;
use Revolution\Copilot\Types\Rpc\SandboxDisableForSessionResult;
use Revolution\Copilot\Types\Rpc\SandboxEnforcementStatus;

/**
 * Pending sandbox RPC operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingSandbox
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Return managed sandbox enforcement state for this session.
     */
    public function getEnforcementStatus(): SandboxEnforcementStatus
    {
        return SandboxEnforcementStatus::fromArray(
            $this->client->request('session.sandbox.getEnforcementStatus', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Disable sandboxing for the remainder of the current session and approve the
     * referenced pending sandbox-bypass permission request.
     */
    public function disableForSession(SandboxDisableForSessionRequest|array $params): SandboxDisableForSessionResult
    {
        $paramsArray = ($params instanceof SandboxDisableForSessionRequest ? $params : SandboxDisableForSessionRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SandboxDisableForSessionResult::fromArray(
            $this->client->request('session.sandbox.disableForSession', $paramsArray),
        );
    }
}
