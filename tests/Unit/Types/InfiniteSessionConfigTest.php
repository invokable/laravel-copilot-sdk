<?php

declare(strict_types=1);

use Revolution\Copilot\Types\InfiniteSessionConfig;

describe('InfiniteSessionConfig', function () {
    it('can be created with all fields', function () {
        $config = new InfiniteSessionConfig(
            enabled: true,
            backgroundCompactionThreshold: 0.80,
            bufferExhaustionThreshold: 0.95,
        );

        expect($config->enabled)->toBeTrue()
            ->and($config->backgroundCompactionThreshold)->toBe(0.80)
            ->and($config->bufferExhaustionThreshold)->toBe(0.95);
    });

    it('can be created with minimal fields', function () {
        $config = new InfiniteSessionConfig;

        expect($config->enabled)->toBeNull()
            ->and($config->backgroundCompactionThreshold)->toBeNull()
            ->and($config->bufferExhaustionThreshold)->toBeNull();
    });

    it('can be created from array with all fields', function () {
        $config = InfiniteSessionConfig::fromArray([
            'enabled' => false,
            'backgroundCompactionThreshold' => 0.70,
            'bufferExhaustionThreshold' => 0.90,
        ]);

        expect($config->enabled)->toBeFalse()
            ->and($config->backgroundCompactionThreshold)->toBe(0.70)
            ->and($config->bufferExhaustionThreshold)->toBe(0.90);
    });

    it('can be created from array with minimal fields', function () {
        $config = InfiniteSessionConfig::fromArray([]);

        expect($config->enabled)->toBeNull()
            ->and($config->backgroundCompactionThreshold)->toBeNull()
            ->and($config->bufferExhaustionThreshold)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $config = new InfiniteSessionConfig(
            enabled: true,
            backgroundCompactionThreshold: 0.80,
            bufferExhaustionThreshold: 0.95,
        );

        expect($config->toArray())->toBe([
            'enabled' => true,
            'backgroundCompactionThreshold' => 0.80,
            'bufferExhaustionThreshold' => 0.95,
        ]);
    });

    it('filters null values in toArray', function () {
        $config = new InfiniteSessionConfig(
            enabled: false,
        );

        expect($config->toArray())->toBe([
            'enabled' => false,
        ]);
    });

    it('returns empty array when all fields are null', function () {
        $config = new InfiniteSessionConfig;

        expect($config->toArray())->toBe([]);
    });

    it('implements Arrayable interface', function () {
        $config = new InfiniteSessionConfig;

        expect($config)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
