<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ProviderAddRequest;
use Revolution\Copilot\Types\Rpc\ProviderAddResult;
use Revolution\Copilot\Types\Rpc\ProviderEndpoint;
use Revolution\Copilot\Types\Rpc\ProviderGetEndpointRequest;

/**
 * Pending session-scoped provider RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingProvider
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Returns the provider endpoint and credentials the session is currently configured to
     * talk to, so the caller can make inference calls directly against the same backend.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function getEndpoint(ProviderGetEndpointRequest|array $params = []): ProviderEndpoint
    {
        $paramsArray = ($params instanceof ProviderGetEndpointRequest
            ? $params
            : ProviderGetEndpointRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ProviderEndpoint::fromArray(
            $this->client->request('session.provider.getEndpoint', $paramsArray),
        );
    }

    /**
     * Add BYOK providers and/or models to the session's registry at runtime.
     *
     * @param  ProviderAddRequest|array  $params  Providers and/or models to register.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function add(ProviderAddRequest|array $params = []): ProviderAddResult
    {
        $paramsArray = ($params instanceof ProviderAddRequest
            ? $params
            : ProviderAddRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return ProviderAddResult::fromArray(
            $this->client->request('session.provider.add', $paramsArray),
        );
    }
}
