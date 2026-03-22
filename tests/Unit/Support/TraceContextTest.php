<?php

declare(strict_types=1);

use Revolution\Copilot\Support\TraceContext;

beforeEach(function () {
    TraceContext::useProvider(null);
});

describe('TraceContext', function () {
    it('returns empty array when no provider and no OpenTelemetry', function () {
        // open-telemetry/api is not installed in test environment
        expect(TraceContext::get())->toBe([]);
    });

    it('uses custom provider when set', function () {
        TraceContext::useProvider(fn () => [
            'traceparent' => '00-abc123-def456-01',
            'tracestate' => 'vendor=value',
        ]);

        expect(TraceContext::get())->toBe([
            'traceparent' => '00-abc123-def456-01',
            'tracestate' => 'vendor=value',
        ]);
    });

    it('returns empty array when provider throws', function () {
        TraceContext::useProvider(fn () => throw new RuntimeException('provider error'));

        expect(TraceContext::get())->toBe([]);
    });

    it('can clear provider', function () {
        TraceContext::useProvider(fn () => ['traceparent' => '00-test-01']);

        expect(TraceContext::get())->toHaveKey('traceparent');

        TraceContext::useProvider(null);

        expect(TraceContext::get())->toBe([]);
    });

    it('returns null scope when traceparent is null', function () {
        expect(TraceContext::restore(null))->toBeNull();
    });

    it('returns null scope when OpenTelemetry not available', function () {
        // open-telemetry/api is not installed in test environment
        $scope = TraceContext::restore('00-abc123-def456-01', 'vendor=value');

        expect($scope)->toBeNull();
    });

    it('detach handles null scope gracefully', function () {
        TraceContext::detach(null);

        expect(true)->toBeTrue(); // no exception thrown
    });

    it('reports OpenTelemetry as not available in test environment', function () {
        expect(TraceContext::isOpenTelemetryAvailable())->toBeFalse();
    });

    it('provider can return partial context with only traceparent', function () {
        TraceContext::useProvider(fn () => [
            'traceparent' => '00-traceid-spanid-01',
        ]);

        $ctx = TraceContext::get();

        expect($ctx)->toBe(['traceparent' => '00-traceid-spanid-01'])
            ->and($ctx)->not->toHaveKey('tracestate');
    });

    it('provider can return empty array', function () {
        TraceContext::useProvider(fn () => []);

        expect(TraceContext::get())->toBe([]);
    });
});
