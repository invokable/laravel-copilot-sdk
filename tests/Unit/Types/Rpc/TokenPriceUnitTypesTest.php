<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\TokenPriceUnit;
use Revolution\Copilot\Types\Rpc\ModelsListRequest;
use Revolution\Copilot\Types\Rpc\ModelsListTokenPriceUnitsRequest;
use Revolution\Copilot\Types\Rpc\ModelTokenPriceUnit;
use Revolution\Copilot\Types\Rpc\ModelTokenPriceUnitList;

describe('TokenPriceUnit RPC types', function () {
    it('serializes token price unit values', function () {
        $unit = ModelTokenPriceUnit::fromArray([
            'modelId' => 'gpt-5',
            'unit' => 'nano_aiu',
        ]);

        expect($unit->unit)->toBe(TokenPriceUnit::NanoAiu)
            ->and($unit->toArray())->toBe([
                'modelId' => 'gpt-5',
                'unit' => 'nano_aiu',
            ]);
    });

    it('preserves unknown token price units as strings', function () {
        $unit = ModelTokenPriceUnit::fromArray([
            'modelId' => 'future-model',
            'unit' => 'future_unit',
        ]);

        expect($unit->unit)->toBe('future_unit')
            ->and($unit->toArray()['unit'])->toBe('future_unit');
    });

    it('converts model token price unit lists', function () {
        $units = ModelTokenPriceUnitList::fromArray([
            'units' => [
                ['modelId' => 'gpt-5', 'unit' => 'ai_credits'],
            ],
        ]);

        expect($units->units)->toHaveCount(1)
            ->and($units->units[0])->toBeInstanceOf(ModelTokenPriceUnit::class)
            ->and($units->units[0]->unit)->toBe(TokenPriceUnit::AiCredits)
            ->and($units->toArray())->toBe([
                'units' => [
                    ['modelId' => 'gpt-5', 'unit' => 'ai_credits'],
                ],
            ]);
    });

    it('converts account selection requests and omits absent values', function () {
        $params = ModelsListTokenPriceUnitsRequest::fromArray([
            'selectionId' => 'selection',
            'gitHubToken' => 'ghs_token',
        ]);

        expect($params->toArray())->toBe([
            'selectionId' => 'selection',
            'gitHubToken' => 'ghs_token',
        ])
            ->and((new ModelsListTokenPriceUnitsRequest)->toArray())->toBe([]);
    });

    it('supports selection ids for model listing', function () {
        $params = ModelsListRequest::fromArray([
            'selectionId' => 'selection',
            'gitHubToken' => 'ghs_token',
        ]);

        expect($params->toArray())->toBe([
            'gitHubToken' => 'ghs_token',
            'selectionId' => 'selection',
        ]);
    });
});
