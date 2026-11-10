<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingFeatureFlags;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\FeatureFlagAssignmentsUpdate;
use Revolution\Copilot\Types\Rpc\FeatureFlagConfigPatch;
use Revolution\Copilot\Types\Rpc\FeatureFlagEnabledOverride;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsConfigureRequest;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsConfigureResult;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsReadResult;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsUpdateRequest;
use Revolution\Copilot\Types\Rpc\SessionFeatureFlagsUpdateResult;

function featureFlagsRpcSnapshot(): array
{
    return [
        'serviceId' => 'service-1',
        'revision' => 3,
        'active' => true,
        'expResponseResolved' => false,
        'flags' => ['feature-a' => true],
        'expFlags' => ['experiment-a' => 0.5],
        'response' => ['assignments' => ['experiment-a' => 0.5]],
        'assignmentContext' => 'user-context',
        'secondaryAssignmentContext' => 'workspace-context',
        'autoApprovalTargeted' => false,
        'reasoningSummariesArm' => ['offByDefault' => true, 'hintEnabled' => false],
        'gptDefaultModelEnabled' => true,
        'dynamicInstructionsRetrievalArm' => 'control',
    ];
}

describe('PendingFeatureFlags', function () {
    it('configures the bound session and returns a typed result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.featureFlags.configure', [
                'isStaff' => false,
                'isExperimental' => true,
                'isTeam' => false,
                'online' => true,
                'streamerMode' => false,
                'flagOverrides' => ['feature-a' => true],
                'sessionId' => 'session-123',
            ])
            ->andReturn(featureFlagsRpcSnapshot());

        $result = (new SessionRpc($client, 'session-123'))->featureFlags()->configure(
            new SessionFeatureFlagsConfigureRequest(
                isStaff: false,
                isExperimental: true,
                isTeam: false,
                online: true,
                streamerMode: false,
                flagOverrides: ['feature-a' => true],
            ),
        );

        expect($result)->toBeInstanceOf(SessionFeatureFlagsConfigureResult::class)
            ->and($result->serviceId)->toBe('service-1')
            ->and($result->flags)->toBe(['feature-a' => true]);
    });

    it('reads feature flags from an array request and returns its typed snapshot', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.featureFlags.read', [
                'flags' => ['feature-a'],
                'sessionId' => 'session-123',
            ])
            ->andReturn([
                'snapshot' => featureFlagsRpcSnapshot(),
                'flags' => ['feature-a' => true],
                'overrides' => [null],
            ]);

        $result = (new PendingFeatureFlags($client, 'session-123'))->read([
            'flags' => ['feature-a'],
        ]);

        expect($result)->toBeInstanceOf(SessionFeatureFlagsReadResult::class)
            ->and($result->snapshot->serviceId)->toBe('service-1')
            ->and($result->flags)->toBe(['feature-a' => true])
            ->and($result->overrides)->toBe([null]);
    });

    it('updates the bound session and returns a typed result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.featureFlags.update', [
                'configPatch' => ['values' => ['enabled' => true], 'undefinedKeys' => []],
                'enabledOverride' => ['name' => 'feature-a', 'enabled' => null],
                'assignments' => ['response' => null],
                'secondaryAssignmentContext' => 'workspace-context',
                'sessionId' => 'session-123',
            ])
            ->andReturn(featureFlagsRpcSnapshot());

        $result = (new PendingFeatureFlags($client, 'session-123'))->update(new SessionFeatureFlagsUpdateRequest(
            configPatch: new FeatureFlagConfigPatch(values: ['enabled' => true]),
            enabledOverride: new FeatureFlagEnabledOverride(name: 'feature-a', enabled: null),
            assignments: new FeatureFlagAssignmentsUpdate(response: null),
            secondaryAssignmentContext: 'workspace-context',
        ));

        expect($result)->toBeInstanceOf(SessionFeatureFlagsUpdateResult::class)
            ->and($result->serviceId)->toBe('service-1')
            ->and($result->revision)->toBe(3);
    });

    it('creates a pending group for the owning session', function () {
        $client = Mockery::mock(JsonRpcClient::class);

        expect((new SessionRpc($client, 'session-123'))->featureFlags())
            ->toBeInstanceOf(PendingFeatureFlags::class);
    });
});
