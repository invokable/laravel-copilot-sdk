<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\SessionLimitsConfig;

describe('SessionLimitsConfig', function () {
    it('can be created with no arguments', function () {
        $config = new SessionLimitsConfig;

        expect($config->maxAiCredits)->toBeNull();
    });

    it('can be created with maxAiCredits', function () {
        $config = new SessionLimitsConfig(maxAiCredits: 100.0);

        expect($config->maxAiCredits)->toBe(100.0);
    });

    it('can be created from array', function () {
        $config = SessionLimitsConfig::fromArray(['maxAiCredits' => 50.5]);

        expect($config->maxAiCredits)->toBe(50.5);
    });

    it('creates from empty array with null values', function () {
        $config = SessionLimitsConfig::fromArray([]);

        expect($config->maxAiCredits)->toBeNull();
    });

    it('converts to array filtering nulls', function () {
        $config = new SessionLimitsConfig(maxAiCredits: 75.0);

        expect($config->toArray())->toBe(['maxAiCredits' => 75.0]);
    });

    it('converts to empty array when no limits', function () {
        $config = new SessionLimitsConfig;

        expect($config->toArray())->toBe([]);
    });

    it('implements Arrayable', function () {
        expect(new SessionLimitsConfig)->toBeInstanceOf(Arrayable::class);
    });
});
