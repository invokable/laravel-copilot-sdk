<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ToolsListParams;
use Revolution\Copilot\Types\Rpc\ToolsListResult;

/**
 * Pending tools RPC operations.
 */
class PendingServerTools
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * List available tools.
     */
    public function list(ToolsListParams|array $params = []): ToolsListResult
    {
        $paramsArray = ($params instanceof ToolsListParams ? $params : ToolsListParams::fromArray($params))->toArray();

        return ToolsListResult::fromArray(
            $this->client->request('tools.list', $paramsArray),
        );
    }
}
