<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogCapability;
use Revolution\Copilot\Enums\CatalogInvalidRequestField;

it('exposes catalog recommendation enum values', function () {
    expect(CatalogCapability::CatalogRecommend->value)->toBe('catalog-recommend')
        ->and(CatalogInvalidRequestField::Product->value)->toBe('product');
});
