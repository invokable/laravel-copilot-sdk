<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ModelPickerPriceCategory;
use Revolution\Copilot\Types\Rpc\ModelListRequest;
use Revolution\Copilot\Types\Rpc\SessionModelList;
use Revolution\Copilot\Types\Rpc\SessionModelPriceCategory;

describe('SessionModelList', function () {
    it('can be created from array with all fields', function () {
        $result = SessionModelList::fromArray([
            'list' => [['id' => 'gpt-4'], ['id' => 'claude-3']],
            'modelPriceCategories' => [['id' => 'gpt-4', 'priceCategory' => 'high']],
            'quotaSnapshots' => ['daily' => ['remaining' => 100]],
            'auto' => [
                'defaultTier' => 'provider-default',
                'providerId' => 'copilot',
                'tiers' => [[
                    'description' => 'Balanced routing',
                    'displayName' => 'Balance',
                    'id' => 'balance',
                    'status' => ['enabled' => true],
                    'type' => 'auto',
                ]],
            ],
        ]);

        expect($result->list)->toHaveCount(2)
            ->and($result->modelPriceCategories)->toHaveCount(1)
            ->and($result->modelPriceCategories[0])->toBeInstanceOf(SessionModelPriceCategory::class)
            ->and($result->modelPriceCategories[0]->priceCategory)->toBe(ModelPickerPriceCategory::High)
            ->and($result->quotaSnapshots)->toBe(['daily' => ['remaining' => 100]])
            ->and($result->auto->defaultTier)->toBe('provider-default')
            ->and($result->auto->tiers[0]->status->enabled)->toBeTrue()
            ->and($result->auto->tiers[0]->status->toArray())->toBe(['enabled' => true]);
    });

    it('can be created from empty array', function () {
        $result = SessionModelList::fromArray([]);

        expect($result->list)->toBe([])
            ->and($result->modelPriceCategories)->toBeNull()
            ->and($result->quotaSnapshots)->toBeNull()
            ->and($result->auto)->toBeNull();
    });

    it('converts to array correctly', function () {
        $result = SessionModelList::fromArray([
            'list' => [['id' => 'gpt-4']],
            'modelPriceCategories' => [['id' => 'gpt-4', 'priceCategory' => 'low']],
            'auto' => ['defaultTier' => 'provider-default', 'tiers' => []],
        ]);

        $array = $result->toArray();

        expect($array)->toHaveKey('list')
            ->and($array['list'])->toHaveCount(1)
            ->and($array['modelPriceCategories'])->toBe([['id' => 'gpt-4', 'priceCategory' => 'low']])
            ->and($array['auto'])->toBe(['defaultTier' => 'provider-default', 'tiers' => []]);
    });
});

describe('ModelListRequest', function () {
    it('can be created with skipCache', function () {
        $request = new ModelListRequest(skipCache: true);

        expect($request->toArray())->toBe(['skipCache' => true]);
    });

    it('filters null skipCache', function () {
        $request = new ModelListRequest;

        expect($request->toArray())->toBe([]);
    });

    it('can be created from array', function () {
        $request = ModelListRequest::fromArray(['skipCache' => true]);

        expect($request->skipCache)->toBeTrue();
    });
});
