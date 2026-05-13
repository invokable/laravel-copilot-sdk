<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ModelPickerCategory;
use Revolution\Copilot\Enums\ModelPickerPriceCategory;

describe('ModelPickerCategory', function () {
    it('has correct string values', function () {
        expect(ModelPickerCategory::Lightweight->value)->toBe('lightweight')
            ->and(ModelPickerCategory::Powerful->value)->toBe('powerful')
            ->and(ModelPickerCategory::Versatile->value)->toBe('versatile');
    });

    it('can be created from string', function () {
        expect(ModelPickerCategory::from('lightweight'))->toBe(ModelPickerCategory::Lightweight)
            ->and(ModelPickerCategory::from('powerful'))->toBe(ModelPickerCategory::Powerful)
            ->and(ModelPickerCategory::from('versatile'))->toBe(ModelPickerCategory::Versatile);
    });

    it('has all expected cases', function () {
        expect(ModelPickerCategory::cases())->toHaveCount(3);
    });
});

describe('ModelPickerPriceCategory', function () {
    it('has correct string values', function () {
        expect(ModelPickerPriceCategory::High->value)->toBe('high')
            ->and(ModelPickerPriceCategory::Low->value)->toBe('low')
            ->and(ModelPickerPriceCategory::Medium->value)->toBe('medium')
            ->and(ModelPickerPriceCategory::VeryHigh->value)->toBe('very_high');
    });

    it('can be created from string', function () {
        expect(ModelPickerPriceCategory::from('high'))->toBe(ModelPickerPriceCategory::High)
            ->and(ModelPickerPriceCategory::from('low'))->toBe(ModelPickerPriceCategory::Low)
            ->and(ModelPickerPriceCategory::from('medium'))->toBe(ModelPickerPriceCategory::Medium)
            ->and(ModelPickerPriceCategory::from('very_high'))->toBe(ModelPickerPriceCategory::VeryHigh);
    });

    it('has all expected cases', function () {
        expect(ModelPickerPriceCategory::cases())->toHaveCount(4);
    });
});
