<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\SessionTelemetryEngagement;
use Revolution\Copilot\Types\Rpc\TelemetrySetFeatureOverridesRequest;

/**
 * Pending session telemetry RPC operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingSessionTelemetry
{
    public function __construct(
        protected JsonRpcClient $client,
        protected string $sessionId,
    ) {}

    /**
     * Get the telemetry engagement ID associated with this session, when available.
     *
     * @experimental
     */
    public function getEngagementId(): SessionTelemetryEngagement
    {
        return SessionTelemetryEngagement::fromArray(
            $this->client->request('session.telemetry.getEngagementId', [
                'sessionId' => $this->sessionId,
            ]),
        );
    }

    /**
     * Replace the feature overrides attached to subsequent telemetry events.
     *
     * @experimental
     *
     * @param  TelemetrySetFeatureOverridesRequest|array{features: array<string, string>}  $params
     */
    public function setFeatureOverrides(TelemetrySetFeatureOverridesRequest|array $params): void
    {
        $paramsArray = ($params instanceof TelemetrySetFeatureOverridesRequest
            ? $params
            : TelemetrySetFeatureOverridesRequest::fromArray($params))->toArray();
        $paramsArray['sessionId'] = $this->sessionId;

        $this->client->request('session.telemetry.setFeatureOverrides', $paramsArray);
    }
}
