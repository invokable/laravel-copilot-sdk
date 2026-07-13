<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ToolSearchConfig;

describe('ToolSearchConfig', function () {
    it('can be created with defaults', function () {
        $config = new ToolSearchConfig;

        expect($config->enabled)->toBeNull()
            ->and($config->deferThreshold)->toBeNull();
    });

    it('can be created with all fields', function () {
        $config = new ToolSearchConfig(enabled: true, deferThreshold: 50);

        expect($config->enabled)->toBeTrue()
            ->and($config->deferThreshold)->toBe(50);
    });

    it('can be created from array', function () {
        $config = ToolSearchConfig::fromArray([
            'enabled' => true,
            'deferThreshold' => 30,
        ]);

        expect($config->enabled)->toBeTrue()
            ->and($config->deferThreshold)->toBe(30);
    });

    it('can be created from empty array with defaults', function () {
        $config = ToolSearchConfig::fromArray([]);

        expect($config->enabled)->toBeNull()
            ->and($config->deferThreshold)->toBeNull();
    });

    it('can convert to array', function () {
        $config = new ToolSearchConfig(enabled: false, deferThreshold: 20);

        expect($config->toArray())->toBe([
            'enabled' => false,
            'deferThreshold' => 20,
        ]);
    });

    it('filters null values in toArray', function () {
        $config = new ToolSearchConfig;

        expect($config->toArray())->toBe([]);
    });

    it('implements Arrayable interface', function () {
        $config = new ToolSearchConfig;

        expect($config)->toBeInstanceOf(Arrayable::class);
    });
});
