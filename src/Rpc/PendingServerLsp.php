<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\LspListRequest;
use Revolution\Copilot\Types\Rpc\LspListResult;

/**
 * Process-wide language-server inventory operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerLsp
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * List language-server configurations for a working directory.
     */
    public function list(LspListRequest|array $params): LspListResult
    {
        $paramsArray = ($params instanceof LspListRequest ? $params : LspListRequest::fromArray($params))->toArray();

        return LspListResult::fromArray(
            $this->client->request('lsp.list', $paramsArray),
        );
    }
}
