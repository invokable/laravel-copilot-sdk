<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\LifecycleDiagnosticsReadRequest;
use Revolution\Copilot\Types\Rpc\LifecycleDiagnosticsReadResult;

/**
 * Internal lifecycle diagnostics RPC operations for a session.
 *
 * @internal
 */
class PendingLifecycleDiagnostics
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Reads a bounded page of retained lifecycle diagnostic events.
     *
     * @internal
     */
    public function read(LifecycleDiagnosticsReadRequest|array $params = []): LifecycleDiagnosticsReadResult
    {
        $paramsArray = ($params instanceof LifecycleDiagnosticsReadRequest
            ? $params
            : LifecycleDiagnosticsReadRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return LifecycleDiagnosticsReadResult::fromArray(
            $this->client->request('session.diagnostics.lifecycle.read', $paramsArray),
        );
    }
}
