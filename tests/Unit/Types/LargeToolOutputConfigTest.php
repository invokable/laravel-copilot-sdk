<?php

declare(strict_types=1);

use Revolution\Copilot\Types\LargeToolOutputConfig;

describe('LargeToolOutputConfig', function () {
    it('can be created from array with all fields', function () {
        $config = LargeToolOutputConfig::fromArray([
            'enabled' => true,
            'maxSizeBytes' => 51200,
            'outputDirectory' => '/tmp/outputs',
        ]);

        expect($config->enabled)->toBeTrue()
            ->and($config->maxSizeBytes)->toBe(51200)
            ->and($config->outputDirectory)->toBe('/tmp/outputs');
    });

    it('can be created from empty array', function () {
        $config = LargeToolOutputConfig::fromArray([]);

        expect($config->enabled)->toBeNull()
            ->and($config->maxSizeBytes)->toBeNull()
            ->and($config->outputDirectory)->toBeNull();
    });

    it('converts to array correctly', function () {
        $config = new LargeToolOutputConfig(
            enabled: true,
            maxSizeBytes: 51200,
            outputDirectory: '/tmp/outputs',
        );

        expect($config->toArray())->toBe([
            'enabled' => true,
            'maxSizeBytes' => 51200,
            'outputDirectory' => '/tmp/outputs',
        ]);
    });

    it('filters null values from array', function () {
        $config = new LargeToolOutputConfig(enabled: false);

        $array = $config->toArray();

        expect($array)->toHaveKey('enabled', false)
            ->and($array)->not->toHaveKey('maxSizeBytes')
            ->and($array)->not->toHaveKey('outputDirectory');
    });
});
