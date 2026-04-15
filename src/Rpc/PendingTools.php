<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\HandleToolCallResult;
use Revolution\Copilot\Types\Rpc\ToolsHandlePendingToolCallRequest;

/**
 * Pending session-scoped tools RPC operations.
 *
 * Used to respond to tool call requests received as session events (protocol v3+).
 * For protocol v2, tool calls are handled automatically by the permission handler.
 */
class PendingTools
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Handle a pending tool call by providing its result or error.
     */
    public function handlePendingToolCall(ToolsHandlePendingToolCallRequest|array $params): HandleToolCallResult
    {
        $paramsArray = ($params instanceof ToolsHandlePendingToolCallRequest ? $params : ToolsHandlePendingToolCallRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return HandleToolCallResult::fromArray(
            $this->client->request('session.tools.handlePendingToolCall', $paramsArray),
        );
    }
}
