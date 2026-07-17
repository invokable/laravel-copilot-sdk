<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ModelPickerPriceCategory;
use Revolution\Copilot\Types\Rpc\SessionModelPriceCategory;

describe('SessionModelPriceCategory', function () {
    it('can be created from array', function () {
        $category = SessionModelPriceCategory::fromArray([
            'id' => 'gpt-4',
            'priceCategory' => 'high',
        ]);

        expect($category->id)->toBe('gpt-4')
            ->and($category->priceCategory)->toBe(ModelPickerPriceCategory::High);
    });

    it('converts to array correctly', function () {
        $category = new SessionModelPriceCategory(
            id: 'gpt-4',
            priceCategory: ModelPickerPriceCategory::VeryHigh,
        );

        expect($category->toArray())->toBe([
            'id' => 'gpt-4',
            'priceCategory' => 'very_high',
        ]);
    });
});
