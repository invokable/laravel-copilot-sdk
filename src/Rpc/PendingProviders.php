<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ModelProviderAdapterCatalog;
use Revolution\Copilot\Types\Rpc\ModelProviderDiscoverRequest;
use Revolution\Copilot\Types\Rpc\ModelProviderDiscoverResult;
use Revolution\Copilot\Types\Rpc\ModelProviderInstanceReference;
use Revolution\Copilot\Types\Rpc\ModelProviderStatus;

/**
 * Session-scoped model-provider adapter discovery operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingProviders
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    public function getCatalog(): ModelProviderAdapterCatalog
    {
        return ModelProviderAdapterCatalog::fromArray(
            $this->client->request('session.providers.getCatalog', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    public function discover(ModelProviderDiscoverRequest|array $params): ModelProviderDiscoverResult
    {
        $paramsArray = ($params instanceof ModelProviderDiscoverRequest
            ? $params
            : ModelProviderDiscoverRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ModelProviderDiscoverResult::fromArray(
            $this->client->request('session.providers.discover', $paramsArray),
        );
    }

    public function getStatus(ModelProviderInstanceReference|array $instance): ModelProviderStatus
    {
        $reference = $instance instanceof ModelProviderInstanceReference
            ? $instance->toArray()
            : ModelProviderInstanceReference::fromArray($instance)->toArray();

        return ModelProviderStatus::fromArray(
            $this->client->request('session.providers.getStatus', [
                'sessionId' => $this->sessionId,
                'instance' => $reference,
            ]),
        );
    }

    public function models(): PendingProviderModels
    {
        return new PendingProviderModels($this->client, $this->sessionId);
    }
}
