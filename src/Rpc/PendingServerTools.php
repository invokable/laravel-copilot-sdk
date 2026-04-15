<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ToolList;
use Revolution\Copilot\Types\Rpc\ToolsListRequest;

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
    public function list(ToolsListRequest|array $params = []): ToolList
    {
        $paramsArray = ($params instanceof ToolsListRequest ? $params : ToolsListRequest::fromArray($params))->toArray();

        return ToolList::fromArray(
            $this->client->request('tools.list', $paramsArray),
        );
    }
}
