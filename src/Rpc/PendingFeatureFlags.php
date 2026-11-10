<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsConfigureRequest;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsConfigureResult;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsReadRequest;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsReadResult;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsUpdateRequest;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsUpdateResult;

/**
 * Pending session feature-flag RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingFeatureFlags
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Configure the feature-flag service bound to this session.
     */
    public function configure(SessionFeatureFlagsConfigureRequest|array $params): SessionFeatureFlagsConfigureResult
    {
        $paramsArray = ($params instanceof SessionFeatureFlagsConfigureRequest
            ? $params
            : SessionFeatureFlagsConfigureRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionFeatureFlagsConfigureResult::fromArray(
            $this->client->request('session.featureFlags.configure', $paramsArray),
        );
    }

    /**
     * Read the feature-flag service snapshot and requested evaluations.
     */
    public function read(SessionFeatureFlagsReadRequest|array $params = []): SessionFeatureFlagsReadResult
    {
        $paramsArray = ($params instanceof SessionFeatureFlagsReadRequest
            ? $params
            : SessionFeatureFlagsReadRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionFeatureFlagsReadResult::fromArray(
            $this->client->request('session.featureFlags.read', $paramsArray),
        );
    }

    /**
     * Update the feature-flag service bound to this session.
     */
    public function update(SessionFeatureFlagsUpdateRequest|array $params = []): SessionFeatureFlagsUpdateResult
    {
        $paramsArray = ($params instanceof SessionFeatureFlagsUpdateRequest
            ? $params
            : SessionFeatureFlagsUpdateRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        return SessionFeatureFlagsUpdateResult::fromArray(
            $this->client->request('session.featureFlags.update', $paramsArray),
        );
    }
}
