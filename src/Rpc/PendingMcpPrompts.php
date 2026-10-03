<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\McpPromptsGetRequest;
use Revolution\Copilot\Types\Rpc\McpPromptsGetResult;
use Revolution\Copilot\Types\Rpc\McpPromptsListRequest;
use Revolution\Copilot\Types\Rpc\McpPromptsListResult;

/** Prompt operations exposed by an MCP server. */
class PendingMcpPrompts
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /** List one page of prompts advertised by an MCP server. */
    public function list(McpPromptsListRequest|array $params): McpPromptsListResult
    {
        $paramsArray = ($params instanceof McpPromptsListRequest ? $params : McpPromptsListRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpPromptsListResult::fromArray(
            $this->client->request('session.mcp.prompts.list', $paramsArray),
        );
    }

    /** Fetch a rendered prompt without sending it to the model. */
    public function get(McpPromptsGetRequest|array $params): McpPromptsGetResult
    {
        $paramsArray = ($params instanceof McpPromptsGetRequest ? $params : McpPromptsGetRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return McpPromptsGetResult::fromArray(
            $this->client->request('session.mcp.prompts.get', $paramsArray),
        );
    }
}
