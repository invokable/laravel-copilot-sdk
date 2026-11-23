<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\MemoriesImportParams;
use Revolution\Copilot\Types\Rpc\MemoriesImportResult;

class PendingServerMemories
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Validate and import semantic memory JSONL under the server's policies.
     *
     * @experimental This method may change or be removed.
     */
    public function import(MemoriesImportParams|array $params): MemoriesImportResult
    {
        $paramsArray = ($params instanceof MemoriesImportParams
            ? $params
            : MemoriesImportParams::fromArray($params))->toArray();

        return MemoriesImportResult::fromArray(
            $this->client->request('memories.import', $paramsArray),
        );
    }
}
