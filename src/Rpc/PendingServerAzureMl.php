<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\AzureMlAcquireFamilyRequest;
use Revolution\Copilot\Types\Rpc\AzureMlAcquireFamilyResult;
use Revolution\Copilot\Types\Rpc\AzureMlCancelRequest;
use Revolution\Copilot\Types\Rpc\AzureMlCatalogRequest;
use Revolution\Copilot\Types\Rpc\AzureMlCatalogResult;
use Revolution\Copilot\Types\Rpc\AzureMlDiscardRequest;
use Revolution\Copilot\Types\Rpc\AzureMlDownloadRequest;
use Revolution\Copilot\Types\Rpc\AzureMlDownloadResult;
use Revolution\Copilot\Types\Rpc\AzureMlDownloadStatus;
use Revolution\Copilot\Types\Rpc\AzureMlLatestRequest;
use Revolution\Copilot\Types\Rpc\AzureMlLatestResult;
use Revolution\Copilot\Types\Rpc\AzureMlReleaseFamilyRequest;
use Revolution\Copilot\Types\Rpc\AzureMlResolveRequest;
use Revolution\Copilot\Types\Rpc\AzureMlResolveResult;
use Revolution\Copilot\Types\Rpc\AzureMlStatusRequest;

/**
 * Experimental server-level Azure ML local-model RPC operations.
 *
 * @experimental
 */
class PendingServerAzureMl
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    public function catalog(AzureMlCatalogRequest|array $params): AzureMlCatalogResult
    {
        $paramsArray = ($params instanceof AzureMlCatalogRequest
            ? $params
            : AzureMlCatalogRequest::fromArray($params))->toArray();

        return AzureMlCatalogResult::fromArray(
            $this->client->request('azureMl.catalog', $paramsArray),
        );
    }

    public function latest(AzureMlLatestRequest|array $params): AzureMlLatestResult
    {
        $paramsArray = ($params instanceof AzureMlLatestRequest
            ? $params
            : AzureMlLatestRequest::fromArray($params))->toArray();

        return AzureMlLatestResult::fromArray(
            $this->client->request('azureMl.latest', $paramsArray),
        );
    }

    public function resolve(AzureMlResolveRequest|array $params): AzureMlResolveResult
    {
        $paramsArray = ($params instanceof AzureMlResolveRequest
            ? $params
            : AzureMlResolveRequest::fromArray($params))->toArray();

        return AzureMlResolveResult::fromArray(
            $this->client->request('azureMl.resolve', $paramsArray),
        );
    }

    /**
     * Downloads and verifies a model version in the server-owned model library.
     *
     * The download can take longer than the standard JSON-RPC timeout, so no
     * timeout is applied unless one is explicitly provided.
     */
    public function download(AzureMlDownloadRequest|array $params, ?float $timeout = null): AzureMlDownloadResult
    {
        $paramsArray = ($params instanceof AzureMlDownloadRequest
            ? $params
            : AzureMlDownloadRequest::fromArray($params))->toArray();

        return AzureMlDownloadResult::fromArray(
            $this->client->request('azureMl.download', $paramsArray, $timeout),
        );
    }

    public function acquireFamily(AzureMlAcquireFamilyRequest|array $params): AzureMlAcquireFamilyResult
    {
        $paramsArray = ($params instanceof AzureMlAcquireFamilyRequest
            ? $params
            : AzureMlAcquireFamilyRequest::fromArray($params))->toArray();

        return AzureMlAcquireFamilyResult::fromArray(
            $this->client->request('azureMl.acquireFamily', $paramsArray),
        );
    }

    public function releaseFamily(AzureMlReleaseFamilyRequest|array $params): void
    {
        $paramsArray = ($params instanceof AzureMlReleaseFamilyRequest
            ? $params
            : AzureMlReleaseFamilyRequest::fromArray($params))->toArray();

        $this->client->request('azureMl.releaseFamily', $paramsArray);
    }

    public function status(AzureMlStatusRequest|array $params): ?AzureMlDownloadStatus
    {
        $paramsArray = ($params instanceof AzureMlStatusRequest
            ? $params
            : AzureMlStatusRequest::fromArray($params))->toArray();
        $result = $this->client->request('azureMl.status', $paramsArray);

        return is_array($result) ? AzureMlDownloadStatus::fromArray($result) : null;
    }

    public function cancel(AzureMlCancelRequest|array $params): bool
    {
        $paramsArray = ($params instanceof AzureMlCancelRequest
            ? $params
            : AzureMlCancelRequest::fromArray($params))->toArray();

        return (bool) $this->client->request('azureMl.cancel', $paramsArray);
    }

    public function discard(AzureMlDiscardRequest|array $params): void
    {
        $paramsArray = ($params instanceof AzureMlDiscardRequest
            ? $params
            : AzureMlDiscardRequest::fromArray($params))->toArray();

        $this->client->request('azureMl.discard', $paramsArray);
    }
}
