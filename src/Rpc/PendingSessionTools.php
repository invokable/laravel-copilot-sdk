<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionToolsHandlePendingToolCallParams;
use Revolution\Copilot\Types\Rpc\SessionToolsHandlePendingToolCallResult;

/**
 * Pending session-scoped tools RPC operations.
 *
 * Used to respond to tool call requests received as session events (protocol v3+).
 * For protocol v2, tool calls are handled automatically by the permission handler.
 */
class PendingSessionTools
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Handle a pending tool call by providing its result or error.
     */
    public function handlePendingToolCall(SessionToolsHandlePendingToolCallParams|array $params): SessionToolsHandlePendingToolCallResult
    {
        $paramsArray = $params instanceof SessionToolsHandlePendingToolCallParams ? $params->toArray() : $params;
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionToolsHandlePendingToolCallResult::fromArray(
            $this->client->request('session.tools.handlePendingToolCall', $paramsArray),
        );
    }
}
