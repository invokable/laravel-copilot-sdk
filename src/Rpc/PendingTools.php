<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\HandlePendingToolCallRequest;
use Revolution\Copilot\Types\Rpc\HandlePendingToolCallResult;
use Revolution\Copilot\Types\Rpc\ToolsGetCurrentMetadataResult;

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
    public function handlePendingToolCall(HandlePendingToolCallRequest|array $params): HandlePendingToolCallResult
    {
        $paramsArray = ($params instanceof HandlePendingToolCallRequest ? $params : HandlePendingToolCallRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return HandlePendingToolCallResult::fromArray(
            $this->client->request('session.tools.handlePendingToolCall', $paramsArray),
        );
    }

    /**
     * Get current lightweight tool metadata snapshot for this session.
     *
     * @experimental This method is part of an experimental API and may change or be removed.
     */
    public function getCurrentMetadata(): ToolsGetCurrentMetadataResult
    {
        return ToolsGetCurrentMetadataResult::fromArray(
            $this->client->request('session.tools.getCurrentMetadata', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }
}
