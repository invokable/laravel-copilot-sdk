<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AutoTier;
use Revolution\Copilot\Enums\FusionTier;
use Revolution\Copilot\Enums\ModelChangeSource;
use Revolution\Copilot\Types\CapiSessionOptions;
use Revolution\Copilot\Types\Rpc\CurrentModel;
use Revolution\Copilot\Types\Rpc\ModelSwitchAutoTierResult;
use Revolution\Copilot\Types\Rpc\ModelSwitchFusionTierRequest;
use Revolution\Copilot\Types\Rpc\ModelSwitchToRequest;

describe('FusionTier enum', function () {
    it('has the upstream string values', function () {
        expect(FusionTier::STANDARD->value)->toBe('standard')
            ->and(FusionTier::HIGH->value)->toBe('high')
            ->and(FusionTier::MAX->value)->toBe('max');
    });
});

describe('ModelSwitchFusionTierRequest', function () {
    it('serializes the fusion tier and omits an unset source', function () {
        $request = new ModelSwitchFusionTierRequest(fusionTier: FusionTier::MAX);

        expect($request->toArray())->toBe(['fusionTier' => 'max']);
    });

    it('serializes an explicit source', function () {
        $request = new ModelSwitchFusionTierRequest(
            fusionTier: FusionTier::HIGH,
            source: ModelChangeSource::MODEL_COMMAND,
        );

        expect($request->toArray())->toBe([
            'fusionTier' => 'high',
            'source' => ModelChangeSource::MODEL_COMMAND->value,
        ]);
    });

    it('roundtrips through fromArray and toArray', function () {
        $data = ['fusionTier' => 'standard', 'source' => 'sdk'];

        expect(ModelSwitchFusionTierRequest::fromArray($data)->toArray())->toBe($data);
    });

    it('keeps unknown provider-defined tiers as strings', function () {
        $request = ModelSwitchFusionTierRequest::fromArray(['fusionTier' => 'provider-tier']);

        expect($request->fusionTier)->toBe('provider-tier')
            ->and($request->toArray())->toBe(['fusionTier' => 'provider-tier']);
    });
});

describe('ModelSwitchAutoTierResult fusion fields', function () {
    it('roundtrips effective and pending fusion tiers', function () {
        $data = [
            'status' => 'pending',
            'effectiveAutoTier' => 'balance',
            'pendingAutoTier' => 'intelligence',
            'effectiveFusionTier' => 'standard',
            'pendingFusionTier' => 'max',
        ];

        expect(ModelSwitchAutoTierResult::fromArray($data)->toArray())->toBe($data);
    });

    it('defaults fusion fields to null and omits them', function () {
        $result = ModelSwitchAutoTierResult::fromArray(['status' => 'unchanged']);

        expect($result->effectiveFusionTier)->toBeNull()
            ->and($result->pendingFusionTier)->toBeNull()
            ->and($result->toArray())->toBe(['status' => 'unchanged']);
    });
});

describe('CurrentModel fusion tier', function () {
    it('roundtrips the resolved fusion tier', function () {
        $data = [
            'modelId' => 'fusion',
            'autoTier' => 'intelligence',
            'fusionTier' => 'high',
        ];

        expect(CurrentModel::fromArray($data)->toArray())->toBe($data);
    });

    it('defaults the fusion tier to null', function () {
        $model = CurrentModel::fromArray(['modelId' => 'gpt-4o']);

        expect($model->fusionTier)->toBeNull()
            ->and($model->toArray())->toBe(['modelId' => 'gpt-4o']);
    });
});

describe('ModelSwitchToRequest fusion tier', function () {
    it('serializes fusionTier when provided', function () {
        $request = new ModelSwitchToRequest(modelId: 'fusion', fusionTier: FusionTier::HIGH);

        expect($request->toArray())->toBe([
            'modelId' => 'fusion',
            'fusionTier' => 'high',
        ]);
    });

    it('omits fusionTier when not provided', function () {
        $request = new ModelSwitchToRequest(modelId: 'fusion');

        expect($request->toArray())->toBe(['modelId' => 'fusion']);
    });

    it('accepts a fusionTier string and roundtrips through fromArray', function () {
        $data = ['modelId' => 'auto', 'fusionTier' => 'max'];

        expect(ModelSwitchToRequest::fromArray($data)->fusionTier)->toBe(FusionTier::MAX)
            ->and(ModelSwitchToRequest::fromArray($data)->toArray())->toBe($data);
    });
});

describe('CapiSessionOptions fusion tier', function () {
    it('serializes fusionTier alongside autoTier', function () {
        $options = new CapiSessionOptions(fusionTier: FusionTier::STANDARD);

        expect($options->toArray())->toBe(['fusionTier' => 'standard'])
            ->and($options)->toBeInstanceOf(Arrayable::class);
    });

    it('restores fusionTier from array data', function () {
        $options = CapiSessionOptions::fromArray(['fusionTier' => 'max']);

        expect($options->fusionTier)->toBe(FusionTier::MAX);
    });

    it('keeps autoTier and fusionTier independent in serialization', function () {
        $options = new CapiSessionOptions(autoTier: AutoTier::BALANCE);

        expect($options->fusionTier)->toBeNull()
            ->and($options->toArray())->toBe(['autoTier' => 'balance']);
    });
});
