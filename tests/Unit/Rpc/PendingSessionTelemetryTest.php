<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSessionTelemetry;
use Revolution\Copilot\Types\Rpc\SessionTelemetryEngagement;
use Revolution\Copilot\Types\Rpc\TelemetrySetFeatureOverridesRequest;

describe('PendingSessionTelemetry', function () {
    it('returns the typed engagement ID for its session', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.telemetry.getEngagementId', [
                'sessionId' => 'session-1',
            ])
            ->andReturn(['engagementId' => 'engagement-1']);

        $engagement = (new PendingSessionTelemetry($client, 'session-1'))->getEngagementId();

        expect($engagement)
            ->toBeInstanceOf(SessionTelemetryEngagement::class)
            ->and($engagement->engagementId)->toBe('engagement-1');
    });

    it('returns a null engagement ID when it is unavailable', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.telemetry.getEngagementId', [
                'sessionId' => 'session-1',
            ])
            ->andReturn([]);

        $engagement = (new PendingSessionTelemetry($client, 'session-1'))->getEngagementId();

        expect($engagement->engagementId)->toBeNull();
    });

    it('reloads telemetry configuration for its session', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.telemetry.reload', [
                'sessionId' => 'session-1',
            ])
            ->andReturn(null);

        $result = (new PendingSessionTelemetry($client, 'session-1'))->reload();

        expect($result)->toBeNull();
    });

    it('propagates telemetry reload failures', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.telemetry.reload', [
                'sessionId' => 'session-1',
            ])
            ->andThrow(new JsonRpcException(-32000, 'Telemetry reload failed'));

        (new PendingSessionTelemetry($client, 'session-1'))->reload();
    })->throws(JsonRpcException::class, 'Telemetry reload failed');

    it('sends feature overrides with the session-scoped wire shape', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.telemetry.setFeatureOverrides', [
                'features' => ['feature-a' => 'enabled', 'feature-b' => 'custom'],
                'sessionId' => 'session-1',
            ])
            ->andReturn(null);

        (new PendingSessionTelemetry($client, 'session-1'))->setFeatureOverrides([
            'features' => ['feature-a' => 'enabled', 'feature-b' => 'custom'],
            'sessionId' => 'untrusted-session',
        ]);
    });

    it('sends feature overrides from the request DTO', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.telemetry.setFeatureOverrides', [
                'features' => ['feature-a' => 'enabled'],
                'sessionId' => 'session-1',
            ])
            ->andReturn(null);

        (new PendingSessionTelemetry($client, 'session-1'))->setFeatureOverrides(
            new TelemetrySetFeatureOverridesRequest(features: ['feature-a' => 'enabled']),
        );
    });
});
