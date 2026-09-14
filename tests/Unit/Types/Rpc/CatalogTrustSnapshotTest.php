<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogTrustEligibility;
use Revolution\Copilot\Enums\CatalogTrustSource;
use Revolution\Copilot\Enums\CatalogTrustTier;
use Revolution\Copilot\Types\Rpc\CatalogTrustProvenance;
use Revolution\Copilot\Types\Rpc\CatalogTrustSnapshot;
use Revolution\Copilot\Types\Rpc\CatalogTrustSnapshotAbsent;
use Revolution\Copilot\Types\Rpc\CatalogTrustSnapshotCurrent;
use Revolution\Copilot\Types\Rpc\CatalogTrustSnapshotDowngraded;
use Revolution\Copilot\Types\Rpc\CatalogTrustSnapshotMalformed;
use Revolution\Copilot\Types\Rpc\CatalogTrustSnapshotRevoked;
use Revolution\Copilot\Types\Rpc\CatalogTrustSnapshotStale;
use Revolution\Copilot\Types\Rpc\CatalogTrustSnapshotUnsupported;

describe('CatalogTrustTier', function () {
    it('has T1 and T2 cases', function () {
        expect(CatalogTrustTier::T1->value)->toBe('T1')
            ->and(CatalogTrustTier::T2->value)->toBe('T2');
    });
});

describe('CatalogTrustEligibility', function () {
    it('has expected cases', function () {
        expect(CatalogTrustEligibility::Default->value)->toBe('default')
            ->and(CatalogTrustEligibility::Expanded->value)->toBe('expanded')
            ->and(CatalogTrustEligibility::Hidden->value)->toBe('hidden')
            ->and(CatalogTrustEligibility::Unknown->value)->toBe('unknown');
    });
});

describe('CatalogTrustSource', function () {
    it('has agent-finder case', function () {
        expect(CatalogTrustSource::AgentFinder->value)->toBe('agent-finder');
    });
});

describe('CatalogTrustProvenance', function () {
    it('can be created from array', function () {
        $provenance = CatalogTrustProvenance::fromArray([
            'source' => 'agent-finder',
            'observedAt' => '2024-01-01T00:00:00Z',
        ]);

        expect($provenance->source)->toBe(CatalogTrustSource::AgentFinder)
            ->and($provenance->observedAt)->toBe('2024-01-01T00:00:00Z');
    });

    it('converts to array correctly', function () {
        $provenance = new CatalogTrustProvenance(
            source: CatalogTrustSource::AgentFinder,
            observedAt: '2024-01-01T00:00:00Z',
        );

        expect($provenance->toArray())->toBe([
            'source' => 'agent-finder',
            'observedAt' => '2024-01-01T00:00:00Z',
        ]);
    });
});

describe('CatalogTrustSnapshotCurrent', function () {
    it('can be created from array', function () {
        $snapshot = CatalogTrustSnapshotCurrent::fromArray([
            'tier' => 'T1',
            'eligibility' => 'default',
            'provenance' => ['source' => 'agent-finder', 'observedAt' => '2024-01-01T00:00:00Z'],
        ]);

        expect($snapshot->schemaVersion)->toBe('v1')
            ->and($snapshot->status)->toBe('current')
            ->and($snapshot->tier)->toBe(CatalogTrustTier::T1)
            ->and($snapshot->eligibility)->toBe(CatalogTrustEligibility::Default)
            ->and($snapshot->provenance)->toBeInstanceOf(CatalogTrustProvenance::class);
    });

    it('converts to array correctly', function () {
        $snapshot = new CatalogTrustSnapshotCurrent(
            tier: CatalogTrustTier::T2,
            eligibility: CatalogTrustEligibility::Expanded,
            provenance: new CatalogTrustProvenance(source: CatalogTrustSource::AgentFinder, observedAt: '2024-01-01T00:00:00Z'),
        );

        expect($snapshot->toArray())->toBe([
            'schemaVersion' => 'v1',
            'status' => 'current',
            'tier' => 'T2',
            'eligibility' => 'expanded',
            'provenance' => ['source' => 'agent-finder', 'observedAt' => '2024-01-01T00:00:00Z'],
        ]);
    });
});

describe('CatalogTrustSnapshot dispatcher', function () {
    it('creates a current variant', function () {
        $snapshot = CatalogTrustSnapshot::fromArray([
            'status' => 'current',
            'tier' => 'T1',
            'eligibility' => 'default',
            'provenance' => ['source' => 'agent-finder', 'observedAt' => '2024-01-01T00:00:00Z'],
        ]);

        expect($snapshot)->toBeInstanceOf(CatalogTrustSnapshotCurrent::class);
    });

    it('creates an absent variant', function () {
        $snapshot = CatalogTrustSnapshot::fromArray([
            'status' => 'absent',
            'eligibility' => 'unknown',
            'provenance' => ['source' => 'agent-finder', 'observedAt' => '2024-01-01T00:00:00Z'],
        ]);

        expect($snapshot)->toBeInstanceOf(CatalogTrustSnapshotAbsent::class)
            ->and($snapshot->status)->toBe('absent');
    });

    it('creates a stale variant', function () {
        $snapshot = CatalogTrustSnapshot::fromArray([
            'status' => 'stale',
            'eligibility' => 'unknown',
            'provenance' => ['source' => 'agent-finder', 'observedAt' => '2024-01-01T00:00:00Z'],
        ]);

        expect($snapshot)->toBeInstanceOf(CatalogTrustSnapshotStale::class);
    });

    it('creates a downgraded variant', function () {
        $snapshot = CatalogTrustSnapshot::fromArray([
            'status' => 'downgraded',
            'eligibility' => 'hidden',
            'provenance' => ['source' => 'agent-finder', 'observedAt' => '2024-01-01T00:00:00Z'],
        ]);

        expect($snapshot)->toBeInstanceOf(CatalogTrustSnapshotDowngraded::class);
    });

    it('creates a revoked variant', function () {
        $snapshot = CatalogTrustSnapshot::fromArray([
            'status' => 'revoked',
            'eligibility' => 'hidden',
            'provenance' => ['source' => 'agent-finder', 'observedAt' => '2024-01-01T00:00:00Z'],
        ]);

        expect($snapshot)->toBeInstanceOf(CatalogTrustSnapshotRevoked::class);
    });

    it('creates an unsupported variant', function () {
        $snapshot = CatalogTrustSnapshot::fromArray([
            'status' => 'unsupported',
            'eligibility' => 'unknown',
            'provenance' => ['source' => 'agent-finder', 'observedAt' => '2024-01-01T00:00:00Z'],
        ]);

        expect($snapshot)->toBeInstanceOf(CatalogTrustSnapshotUnsupported::class);
    });

    it('falls back to malformed variant for unknown status', function () {
        $snapshot = CatalogTrustSnapshot::fromArray([
            'status' => 'something-else',
            'eligibility' => 'unknown',
            'provenance' => ['source' => 'agent-finder', 'observedAt' => '2024-01-01T00:00:00Z'],
        ]);

        expect($snapshot)->toBeInstanceOf(CatalogTrustSnapshotMalformed::class);
    });
});
