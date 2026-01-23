<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ModelCapabilities;

describe('ModelCapabilities', function () {
    it('can be created from array', function () {
        $capabilities = ModelCapabilities::fromArray([
            'supports' => ['vision' => true],
            'limits' => ['max_context_window_tokens' => 100000],
        ]);

        expect($capabilities->supports)->toBe(['vision' => true])
            ->and($capabilities->limits)->toBe(['max_context_window_tokens' => 100000]);
    });

    it('can check if vision is supported', function () {
        $capabilities = ModelCapabilities::fromArray([
            'supports' => ['vision' => true],
            'limits' => ['max_context_window_tokens' => 100000],
        ]);

        expect($capabilities->supportsVision())->toBeTrue();
    });

    it('returns false when vision is not supported', function () {
        $capabilities = ModelCapabilities::fromArray([
            'supports' => ['vision' => false],
            'limits' => ['max_context_window_tokens' => 100000],
        ]);

        expect($capabilities->supportsVision())->toBeFalse();
    });

    it('returns false when vision key is missing', function () {
        $capabilities = ModelCapabilities::fromArray([
            'supports' => [],
            'limits' => ['max_context_window_tokens' => 100000],
        ]);

        expect($capabilities->supportsVision())->toBeFalse();
    });

    it('can get max context window tokens', function () {
        $capabilities = ModelCapabilities::fromArray([
            'supports' => ['vision' => false],
            'limits' => ['max_context_window_tokens' => 128000],
        ]);

        expect($capabilities->maxContextWindowTokens())->toBe(128000);
    });

    it('can convert to array', function () {
        $capabilities = new ModelCapabilities(
            supports: ['vision' => true],
            limits: ['max_context_window_tokens' => 50000],
        );

        expect($capabilities->toArray())->toBe([
            'supports' => ['vision' => true],
            'limits' => ['max_context_window_tokens' => 50000],
        ]);
    });

    it('implements Arrayable interface', function () {
        $capabilities = new ModelCapabilities(supports: [], limits: []);

        expect($capabilities)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
