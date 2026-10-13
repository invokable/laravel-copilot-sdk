<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\BuiltInModelCatalog;
use Revolution\Copilot\Types\Rpc\BuiltInModelCatalogEntry;

it('round trips built-in model catalog entries', function () {
    $catalog = BuiltInModelCatalog::fromArray([
        'models' => [
            ['id' => 'gpt-5'],
        ],
    ]);

    expect($catalog->models[0])->toBeInstanceOf(BuiltInModelCatalogEntry::class)
        ->and($catalog->toArray())->toBe([
            'models' => [
                ['id' => 'gpt-5'],
            ],
        ]);
});

it('defaults a missing built-in model catalog to an empty list', function () {
    expect(BuiltInModelCatalog::fromArray([])->toArray())->toBe(['models' => []])
        ->and(BuiltInModelCatalogEntry::fromArray([])->toArray())->toBe(['id' => '']);
});
