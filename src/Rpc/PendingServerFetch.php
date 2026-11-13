<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\FetchCancelParams;
use Revolution\Copilot\Types\Rpc\FetchCancelResult;
use Revolution\Copilot\Types\Rpc\FetchCreateResult;
use Revolution\Copilot\Types\Rpc\FetchReadParams;
use Revolution\Copilot\Types\Rpc\FetchReadResult;
use Revolution\Copilot\Types\Rpc\FetchSendParams;
use Revolution\Copilot\Types\Rpc\FetchSendResult;

/**
 * Connection-owned HTTP request operations through the shared network transport.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerFetch
{
    public function __construct(protected JsonRpcClient $client) {}

    public function create(): FetchCreateResult
    {
        return FetchCreateResult::fromArray(
            $this->client->request('fetch.create', []),
        );
    }

    public function send(FetchSendParams|array $params): FetchSendResult
    {
        $paramsArray = ($params instanceof FetchSendParams
            ? $params
            : FetchSendParams::fromArray($params))->toArray();

        return FetchSendResult::fromArray(
            $this->client->request('fetch.send', $paramsArray),
        );
    }

    public function read(FetchReadParams|array $params): FetchReadResult
    {
        $paramsArray = ($params instanceof FetchReadParams
            ? $params
            : FetchReadParams::fromArray($params))->toArray();

        return FetchReadResult::fromArray(
            $this->client->request('fetch.read', $paramsArray),
        );
    }

    public function cancel(FetchCancelParams|array $params): FetchCancelResult
    {
        $paramsArray = ($params instanceof FetchCancelParams
            ? $params
            : FetchCancelParams::fromArray($params))->toArray();

        return FetchCancelResult::fromArray(
            $this->client->request('fetch.cancel', $paramsArray),
        );
    }
}
