<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesRequest;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesResult;

/**
 * Server-scoped secret redaction RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerSecrets
{
    public function __construct(protected JsonRpcClient $client) {}

    /**
     * Adds host-generated secret values to the runtime's redaction filter.
     *
     * @experimental
     */
    public function addFilterValues(SecretsAddFilterValuesRequest|array $params): SecretsAddFilterValuesResult
    {
        $paramsArray = ($params instanceof SecretsAddFilterValuesRequest
            ? $params
            : SecretsAddFilterValuesRequest::fromArray($params))->toArray();

        return SecretsAddFilterValuesResult::fromArray(
            $this->client->request('secrets.addFilterValues', $paramsArray),
        );
    }
}
