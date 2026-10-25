<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionTelemetryEngagement;
use Revolution\Copilot\Types\Rpc\TelemetrySetFeatureOverridesRequest;

describe('SessionTelemetryEngagement', function () {
    it('round trips an available engagement ID', function () {
        $engagement = SessionTelemetryEngagement::fromArray([
            'engagementId' => 'engagement-1',
        ]);

        expect($engagement->engagementId)->toBe('engagement-1')
            ->and($engagement->toArray())->toBe(['engagementId' => 'engagement-1']);
    });

    it('omits the engagement ID when it is unavailable', function () {
        $engagement = SessionTelemetryEngagement::fromArray([]);

        expect($engagement->engagementId)->toBeNull()
            ->and($engagement->toArray())->toBe([]);
    });
});

describe('TelemetrySetFeatureOverridesRequest', function () {
    it('round trips the string feature map', function () {
        $request = TelemetrySetFeatureOverridesRequest::fromArray([
            'features' => ['feature-a' => 'enabled', 'feature-b' => 'custom'],
        ]);

        expect($request->features)->toBe(['feature-a' => 'enabled', 'feature-b' => 'custom'])
            ->and($request->toArray())->toBe([
                'features' => ['feature-a' => 'enabled', 'feature-b' => 'custom'],
            ]);
    });
});
