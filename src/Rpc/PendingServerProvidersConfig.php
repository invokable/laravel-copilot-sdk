<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ProvidersConfigAddModelRequest;
use Revolution\Copilot\Types\Rpc\ProvidersConfigAddModelResult;
use SensitiveParameter;

/** @experimental */
class PendingServerProvidersConfig
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

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
