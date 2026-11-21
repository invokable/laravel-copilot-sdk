<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesRequest;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesResult;
use Revolution\Copilot\Types\Rpc\SecretsFilterRequest;
use Revolution\Copilot\Types\Rpc\SecretsFilterResult;
use Revolution\Copilot\Types\Rpc\SecretsRemoveFilterValuesRequest;
use Revolution\Copilot\Types\Rpc\SecretsRemoveFilterValuesResult;

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

    /**
     * Filters text or JSON using the runtime's process filter.
     *
     * @experimental
     */
    public function filter(SecretsFilterRequest|array $params): SecretsFilterResult
    {
        $paramsArray = ($params instanceof SecretsFilterRequest
            ? $params
            : SecretsFilterRequest::fromArray($params))->toArray();

        return SecretsFilterResult::fromArray(
            $this->client->request('secrets.filter', $paramsArray),
        );
    }

    /**
     * Releases secret values registered by this client.
     *
     * @experimental
     */
    public function removeFilterValues(SecretsRemoveFilterValuesRequest|array $params): SecretsRemoveFilterValuesResult
    {
        $paramsArray = ($params instanceof SecretsRemoveFilterValuesRequest
            ? $params
            : SecretsRemoveFilterValuesRequest::fromArray($params))->toArray();

        return SecretsRemoveFilterValuesResult::fromArray(
            $this->client->request('secrets.removeFilterValues', $paramsArray),
        );
    }
}
