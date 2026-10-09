<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ImageGenerationConfig;

describe('ImageGenerationConfig', function () {
    it('can be created with defaults', function () {
        $config = new ImageGenerationConfig;

        expect($config->enabled)->toBeNull()
            ->and($config->toArray())->toBe([]);
    });

    it('can be created from array', function () {
        $config = ImageGenerationConfig::fromArray(['enabled' => true]);

        expect($config->enabled)->toBeTrue()
            ->and($config)->toBeInstanceOf(Arrayable::class);
    });

    it('keeps an explicit false when converting to array', function () {
        $config = new ImageGenerationConfig(enabled: false);

        expect($config->toArray())->toBe(['enabled' => false]);
    });

    it('round trips through array', function () {
        $data = ['enabled' => true];

        expect(ImageGenerationConfig::fromArray($data)->toArray())->toBe($data);
    });
});
