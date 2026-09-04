<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Enums\ModelChangeSource;
use Revolution\Copilot\Enums\ModelSwitchAutoTierStatus;
use Revolution\Copilot\Types\Rpc\ModelSwitchAutoTierRequest;
use Revolution\Copilot\Types\Rpc\ModelSwitchAutoTierResult;

describe('ModelSwitchAutoTierRequest', function () {
    it('can be created with all fields', function () {
        $request = new ModelSwitchAutoTierRequest(
            autoTier: AutoTier::BALANCE,
            source: ModelChangeSource::MODEL_COMMAND,
        );

        expect($request->autoTier)->toBe(AutoTier::BALANCE)
            ->and($request->source)->toBe(ModelChangeSource::MODEL_COMMAND);
    });

    it('handles default values', function () {
        $request = new ModelSwitchAutoTierRequest;

        expect($request->autoTier)->toBeNull()
            ->and($request->source)->toBeNull();
    });

    it('always includes autoTier key in toArray even when null', function () {
        $request = new ModelSwitchAutoTierRequest;

        expect($request->toArray())->toBe(['autoTier' => null]);
    });

    it('serializes to array with source', function () {
        $request = new ModelSwitchAutoTierRequest(
            autoTier: AutoTier::EFFICIENCY,
            source: ModelChangeSource::MODEL_PICKER,
        );

        expect($request->toArray())->toBe([
            'autoTier' => 'efficiency',
            'source' => 'model_picker',
        ]);
    });

    it('can be created from array', function () {
        $request = ModelSwitchAutoTierRequest::fromArray([
            'autoTier' => 'intelligence',
            'source' => 'automatic',
        ]);

        expect($request->autoTier)->toBe(AutoTier::INTELLIGENCE)
            ->and($request->source)->toBe(ModelChangeSource::AUTOMATIC);
    });

    it('falls back to raw string for unknown autoTier and source values', function () {
        $request = ModelSwitchAutoTierRequest::fromArray([
            'autoTier' => 'unknown-tier',
            'source' => 'unknown-source',
        ]);

        expect($request->autoTier)->toBe('unknown-tier')
            ->and($request->source)->toBe('unknown-source');
    });

    it('roundtrips through fromArray/toArray', function () {
        $data = ['autoTier' => 'balance', 'source' => 'startup'];

        expect(ModelSwitchAutoTierRequest::fromArray($data)->toArray())->toBe($data);
    });
});

describe('ModelSwitchAutoTierResult', function () {
    it('can be created with all fields', function () {
        $result = new ModelSwitchAutoTierResult(
            status: ModelSwitchAutoTierStatus::PENDING,
            activatingAutoTier: AutoTier::BALANCE,
            effectiveAutoTier: AutoTier::EFFICIENCY,
            pendingAutoTier: AutoTier::INTELLIGENCE,
            supersededAutoTier: AutoTier::EFFICIENCY,
        );

        expect($result->status)->toBe(ModelSwitchAutoTierStatus::PENDING)
            ->and($result->activatingAutoTier)->toBe(AutoTier::BALANCE)
            ->and($result->effectiveAutoTier)->toBe(AutoTier::EFFICIENCY)
            ->and($result->pendingAutoTier)->toBe(AutoTier::INTELLIGENCE)
            ->and($result->supersededAutoTier)->toBe(AutoTier::EFFICIENCY);
    });

    it('handles default values', function () {
        $result = new ModelSwitchAutoTierResult(status: ModelSwitchAutoTierStatus::UNCHANGED);

        expect($result->status)->toBe(ModelSwitchAutoTierStatus::UNCHANGED)
            ->and($result->activatingAutoTier)->toBeNull()
            ->and($result->effectiveAutoTier)->toBeNull()
            ->and($result->pendingAutoTier)->toBeNull()
            ->and($result->supersededAutoTier)->toBeNull();
    });

    it('can be created from array', function () {
        $result = ModelSwitchAutoTierResult::fromArray([
            'status' => 'pending',
            'effectiveAutoTier' => 'balance',
        ]);

        expect($result->status)->toBe(ModelSwitchAutoTierStatus::PENDING)
            ->and($result->effectiveAutoTier)->toBe(AutoTier::BALANCE)
            ->and($result->activatingAutoTier)->toBeNull();
    });

    it('serializes to array omitting null values', function () {
        $result = new ModelSwitchAutoTierResult(status: ModelSwitchAutoTierStatus::UNCHANGED);

        expect($result->toArray())->toBe(['status' => 'unchanged']);
    });

    it('roundtrips through fromArray/toArray', function () {
        $data = [
            'status' => 'pending',
            'activatingAutoTier' => 'balance',
            'effectiveAutoTier' => 'efficiency',
            'pendingAutoTier' => 'intelligence',
            'supersededAutoTier' => 'efficiency',
        ];

        expect(ModelSwitchAutoTierResult::fromArray($data)->toArray())->toBe($data);
    });
});
