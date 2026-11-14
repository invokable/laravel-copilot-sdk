<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ProvidersConfigAddModelRequest;
use Revolution\Copilot\Types\Rpc\ProvidersConfigAddModelResult;
use Revolution\Copilot\Types\Rpc\ProvidersConfigReadResult;
use Revolution\Copilot\Types\Rpc\ProvidersConfigRemoveModelRequest;
use Revolution\Copilot\Types\Rpc\ProvidersConfigRemoveModelResult;
use Revolution\Copilot\Types\Rpc\ProvidersConfigUpdateModelRequest;
use Revolution\Copilot\Types\Rpc\ProvidersConfigUpdateModelResult;
use Revolution\Copilot\Types\Rpc\ProvidersConfigUpdateProviderRequest;
use Revolution\Copilot\Types\Rpc\ProvidersConfigUpdateProviderResult;
use SensitiveParameter;

/** @experimental */
class PendingServerProvidersConfig
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    public function read(): ProvidersConfigReadResult
    {
        return ProvidersConfigReadResult::fromArray(
            $this->client->request('providers.config.read'),
        );
    }

    public function updateProvider(#[SensitiveParameter] ProvidersConfigUpdateProviderRequest|array $params): ProvidersConfigUpdateProviderResult
    {
        $paramsArray = ($params instanceof ProvidersConfigUpdateProviderRequest
            ? $params
            : ProvidersConfigUpdateProviderRequest::fromArray($params))->toArray();

        return ProvidersConfigUpdateProviderResult::fromArray(
            $this->client->request('providers.config.updateProvider', $paramsArray),
        );
    }

    public function updateModel(ProvidersConfigUpdateModelRequest|array $params): ProvidersConfigUpdateModelResult
    {
        $paramsArray = ($params instanceof ProvidersConfigUpdateModelRequest
            ? $params
            : ProvidersConfigUpdateModelRequest::fromArray($params))->toArray();

        return ProvidersConfigUpdateModelResult::fromArray(
            $this->client->request('providers.config.updateModel', $paramsArray),
        );
    }

    public function removeModel(ProvidersConfigRemoveModelRequest|array $params): ProvidersConfigRemoveModelResult
    {
        $paramsArray = ($params instanceof ProvidersConfigRemoveModelRequest
            ? $params
            : ProvidersConfigRemoveModelRequest::fromArray($params))->toArray();

        return ProvidersConfigRemoveModelResult::fromArray(
            $this->client->request('providers.config.removeModel', $paramsArray),
        );
    }

    public function addModel(#[SensitiveParameter] ProvidersConfigAddModelRequest|array $params): ProvidersConfigAddModelResult
    {
        $paramsArray = ($params instanceof ProvidersConfigAddModelRequest
            ? $params
            : ProvidersConfigAddModelRequest::fromArray($params))->toArray();

        return ProvidersConfigAddModelResult::fromArray(
            $this->client->request('providers.config.addModel', $paramsArray),
        );
    }
}
