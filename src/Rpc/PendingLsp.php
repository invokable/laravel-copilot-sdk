<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\LspInitializeRequest;

/**
 * Language-server protocol operations for a session.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingLsp
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function initialize(LspInitializeRequest|array $params = []): void
    {
        $paramsArray = $params instanceof LspInitializeRequest
            ? $params->toArray()
            : LspInitializeRequest::fromArray($params)->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.lsp.initialize', $paramsArray);
    }
}
