<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ModelCapabilitiesOverride;
use Revolution\Copilot\Types\Rpc\ModelCapabilitiesOverrideLimits;
use Revolution\Copilot\Types\Rpc\ModelCapabilitiesOverrideLimitsVision;
use Revolution\Copilot\Types\Rpc\ModelCapabilitiesOverrideSupports;

describe('ModelCapabilitiesOverride', function () {
    it('can be created with all fields', function () {
        $supports = new ModelCapabilitiesOverrideSupports(vision: true, reasoningEffort: false);
        $limits = new ModelCapabilitiesOverrideLimits(max_prompt_tokens: 100000, max_output_tokens: 4096);

        $override = new ModelCapabilitiesOverride(
            supports: $supports,
            limits: $limits,
        );

        expect($override->supports)->toBe($supports)
            ->and($override->limits)->toBe($limits);
    });

    it('handles default values', function () {
        $override = ModelCapabilitiesOverride::fromArray([]);

        expect($override->supports)->toBeNull()
            ->and($override->limits)->toBeNull();
    });

    it('can be created from array with nested objects', function () {
        $override = ModelCapabilitiesOverride::fromArray([
            'supports' => [
                'vision' => true,
                'reasoningEffort' => false,
            ],
            'limits' => [
                'max_prompt_tokens' => 200000,
                'max_output_tokens' => 8192,
            ],
        ]);

        expect($override->supports)->toBeInstanceOf(ModelCapabilitiesOverrideSupports::class)
            ->and($override->supports->vision)->toBeTrue()
            ->and($override->supports->reasoningEffort)->toBeFalse()
            ->and($override->limits)->toBeInstanceOf(ModelCapabilitiesOverrideLimits::class)
            ->and($override->limits->max_prompt_tokens)->toBe(200000)
            ->and($override->limits->max_output_tokens)->toBe(8192);
    });

    it('converts to array', function () {
        $override = new ModelCapabilitiesOverride(
            supports: new ModelCapabilitiesOverrideSupports(vision: true),
            limits: new ModelCapabilitiesOverrideLimits(max_prompt_tokens: 100000),
        );

        $array = $override->toArray();

        expect($array)->toHaveKey('supports')
            ->and($array['supports'])->toBe(['vision' => true])
            ->and($array)->toHaveKey('limits')
            ->and($array['limits'])->toBe(['max_prompt_tokens' => 100000]);
    });

    it('omits null values from toArray', function () {
        $override = ModelCapabilitiesOverride::fromArray([]);

        expect($override->toArray())->toBe([]);
    });
});

describe('ModelCapabilitiesOverrideSupports', function () {
    it('can be created with all fields', function () {
        $supports = new ModelCapabilitiesOverrideSupports(
            vision: true,
            reasoningEffort: true,
        );

        expect($supports->vision)->toBeTrue()
            ->and($supports->reasoningEffort)->toBeTrue();
    });

    it('handles default null values', function () {
        $supports = ModelCapabilitiesOverrideSupports::fromArray([]);

        expect($supports->vision)->toBeNull()
            ->and($supports->reasoningEffort)->toBeNull();
    });

    it('can be created from array', function () {
        $supports = ModelCapabilitiesOverrideSupports::fromArray([
            'vision' => false,
            'reasoningEffort' => true,
        ]);

        expect($supports->vision)->toBeFalse()
            ->and($supports->reasoningEffort)->toBeTrue();
    });

    it('converts to array', function () {
        $supports = new ModelCapabilitiesOverrideSupports(vision: true, reasoningEffort: false);

        expect($supports->toArray())->toBe([
            'vision' => true,
            'reasoningEffort' => false,
        ]);
    });

    it('omits null values from toArray', function () {
        $supports = new ModelCapabilitiesOverrideSupports(vision: true);

        expect($supports->toArray())->toBe(['vision' => true]);
    });
});

describe('ModelCapabilitiesOverrideLimits', function () {
    it('can be created with all fields', function () {
        $vision = new ModelCapabilitiesOverrideLimitsVision(max_prompt_image_size: 1024, max_prompt_images: 5);
        $limits = new ModelCapabilitiesOverrideLimits(
            max_prompt_tokens: 200000,
            max_output_tokens: 8192,
            max_context_window_tokens: 500000,
            vision: $vision,
        );

        expect($limits->max_prompt_tokens)->toBe(200000)
            ->and($limits->max_output_tokens)->toBe(8192)
            ->and($limits->max_context_window_tokens)->toBe(500000)
            ->and($limits->vision)->toBe($vision);
    });

    it('handles default null values', function () {
        $limits = ModelCapabilitiesOverrideLimits::fromArray([]);

        expect($limits->max_prompt_tokens)->toBeNull()
            ->and($limits->max_output_tokens)->toBeNull()
            ->and($limits->max_context_window_tokens)->toBeNull()
            ->and($limits->vision)->toBeNull();
    });

    it('can be created from array with nested vision', function () {
        $limits = ModelCapabilitiesOverrideLimits::fromArray([
            'max_prompt_tokens' => 100000,
            'vision' => [
                'max_prompt_image_size' => 2048,
                'max_prompt_images' => 10,
            ],
        ]);

        expect($limits->max_prompt_tokens)->toBe(100000)
            ->and($limits->max_output_tokens)->toBeNull()
            ->and($limits->vision)->toBeInstanceOf(ModelCapabilitiesOverrideLimitsVision::class)
            ->and($limits->vision->max_prompt_image_size)->toBe(2048)
            ->and($limits->vision->max_prompt_images)->toBe(10);
    });

    it('converts to array', function () {
        $limits = new ModelCapabilitiesOverrideLimits(
            max_prompt_tokens: 100000,
            max_output_tokens: 4096,
            vision: new ModelCapabilitiesOverrideLimitsVision(max_prompt_image_size: 512),
        );

        $array = $limits->toArray();

        expect($array)->toBe([
            'max_prompt_tokens' => 100000,
            'max_output_tokens' => 4096,
            'vision' => ['max_prompt_image_size' => 512],
        ]);
    });
});

describe('ModelCapabilitiesOverrideLimitsVision', function () {
    it('can be created with all fields', function () {
        $vision = new ModelCapabilitiesOverrideLimitsVision(
            supported_media_types: ['image/png', 'image/jpeg'],
            max_prompt_images: 5,
            max_prompt_image_size: 1024,
        );

        expect($vision->supported_media_types)->toBe(['image/png', 'image/jpeg'])
            ->and($vision->max_prompt_images)->toBe(5)
            ->and($vision->max_prompt_image_size)->toBe(1024);
    });

    it('handles default null values', function () {
        $vision = ModelCapabilitiesOverrideLimitsVision::fromArray([]);

        expect($vision->supported_media_types)->toBeNull()
            ->and($vision->max_prompt_images)->toBeNull()
            ->and($vision->max_prompt_image_size)->toBeNull();
    });

    it('converts to array omitting nulls', function () {
        $vision = new ModelCapabilitiesOverrideLimitsVision(max_prompt_image_size: 2048);

        expect($vision->toArray())->toBe(['max_prompt_image_size' => 2048]);
    });
});
