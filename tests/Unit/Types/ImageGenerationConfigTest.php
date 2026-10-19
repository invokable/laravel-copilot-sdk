<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ImageGenerationConfig;

describe('ImageGenerationConfig', function () {
    it('can be created with defaults', function () {
        $config = new ImageGenerationConfig;

        expect($config->enabled)->toBeNull()
            ->and($config->watermark)->toBeNull()
            ->and($config->toArray())->toBe([]);
    });

    it('can be created from array', function () {
        $config = ImageGenerationConfig::fromArray(['enabled' => true, 'watermark' => false]);

        expect($config->enabled)->toBeTrue()
            ->and($config->watermark)->toBeFalse()
            ->and($config)->toBeInstanceOf(Arrayable::class);
    });

    it('keeps explicit false values when converting to array', function () {
        $config = new ImageGenerationConfig(enabled: false, watermark: false);

        expect($config->toArray())->toBe(['enabled' => false, 'watermark' => false]);
    });

    it('round trips through array', function () {
        $data = ['enabled' => true, 'watermark' => true];

        expect(ImageGenerationConfig::fromArray($data)->toArray())->toBe($data);
    });
});
