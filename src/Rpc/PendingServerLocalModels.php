<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Types\Rpc\LocalModelsCancelRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsConfigureRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsConfigureResult;
use Revolution\Copilot\Types\Rpc\LocalModelsDeleteModelRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsDismissNoticeRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsInspectResult;
use Revolution\Copilot\Types\Rpc\LocalModelsPrepareRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsPrepareResult;
use Revolution\Copilot\Types\Rpc\LocalModelsRefreshRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsSetupRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsWatchRequest;
use Revolution\Copilot\Types\Rpc\LocalModelsWatchResult;

/**
 * Experimental server-level local model management operations.
 *
 * @experimental
 */
class PendingServerLocalModels
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    public function configure(LocalModelsConfigureRequest|array $params): LocalModelsConfigureResult
    {
        $paramsArray = ($params instanceof LocalModelsConfigureRequest
            ? $params
            : LocalModelsConfigureRequest::fromArray($params))->toArray();

        return LocalModelsConfigureResult::fromArray(
            $this->client->request('localModels.configure', $paramsArray),
        );
    }

    public function inspect(): LocalModelsInspectResult
    {
        return LocalModelsInspectResult::fromArray(
            $this->client->request('localModels.inspect', []),
        );
    }

    public function watch(
        LocalModelsWatchRequest|array $params,
        ?CancellationToken $signal = null,
    ): LocalModelsWatchResult {
        $paramsArray = ($params instanceof LocalModelsWatchRequest
            ? $params
            : LocalModelsWatchRequest::fromArray($params))->toArray();

        return LocalModelsWatchResult::fromArray(
            $this->client->request('localModels.watch', $paramsArray, null, $signal),
        );
    }

    public function refresh(LocalModelsRefreshRequest|array $params): void
    {
        $paramsArray = ($params instanceof LocalModelsRefreshRequest
            ? $params
            : LocalModelsRefreshRequest::fromArray($params))->toArray();

        $this->client->request('localModels.refresh', $paramsArray);
    }

    public function prefetch(): void
    {
        $this->client->request('localModels.prefetch', []);
    }

    public function prepare(LocalModelsPrepareRequest|array $params): LocalModelsPrepareResult
    {
        $paramsArray = ($params instanceof LocalModelsPrepareRequest
            ? $params
            : LocalModelsPrepareRequest::fromArray($params))->toArray();

        return LocalModelsPrepareResult::fromArray(
            $this->client->request('localModels.prepare', $paramsArray),
        );
    }

    public function setup(LocalModelsSetupRequest|array $params): void
    {
        $paramsArray = ($params instanceof LocalModelsSetupRequest
            ? $params
            : LocalModelsSetupRequest::fromArray($params))->toArray();

        $this->client->request('localModels.setup', $paramsArray);
    }

    public function deleteModel(LocalModelsDeleteModelRequest|array $params): void
    {
        $paramsArray = ($params instanceof LocalModelsDeleteModelRequest
            ? $params
            : LocalModelsDeleteModelRequest::fromArray($params))->toArray();

        $this->client->request('localModels.deleteModel', $paramsArray);
    }

    public function cancel(LocalModelsCancelRequest|array $params): void
    {
        $paramsArray = ($params instanceof LocalModelsCancelRequest
            ? $params
            : LocalModelsCancelRequest::fromArray($params))->toArray();

        $this->client->request('localModels.cancel', $paramsArray);
    }

    public function cancelAll(): void
    {
        $this->client->request('localModels.cancelAll', []);
    }

    public function dismissNotice(LocalModelsDismissNoticeRequest|array $params): void
    {
        $paramsArray = ($params instanceof LocalModelsDismissNoticeRequest
            ? $params
            : LocalModelsDismissNoticeRequest::fromArray($params))->toArray();

        $this->client->request('localModels.dismissNotice', $paramsArray);
    }
}
