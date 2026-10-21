<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\MemoryMode;
use Revolution\Copilot\Types\MemoryConfiguration;

describe('MemoryConfiguration', function () {
    it('can be created from array with memory enabled', function () {
        $configuration = MemoryConfiguration::fromArray(['enabled' => true]);

        expect($configuration->enabled)->toBeTrue();
    });

    it('defaults memory to disabled and uses the runtime mode when fields are absent', function () {
        $configuration = MemoryConfiguration::fromArray([]);

        expect($configuration->enabled)->toBeFalse()
            ->and($configuration->mode)->toBeNull();
    });

    it('rejects non-boolean enabled values', function () {
        expect(fn () => MemoryConfiguration::fromArray(['enabled' => 1]))
            ->toThrow(InvalidArgumentException::class, 'Array value for key [enabled] must be a boolean, integer found.');
    });

    it('rejects null enabled values', function () {
        expect(fn () => MemoryConfiguration::fromArray(['enabled' => null]))
            ->toThrow(InvalidArgumentException::class, 'Array value for key [enabled] must be a boolean, NULL found.');
    });

    it('parses and serializes the user prompt mode', function () {
        $configuration = MemoryConfiguration::fromArray([
            'enabled' => true,
            'mode' => 'user-prompt',
        ]);

        expect($configuration->mode)->toBe(MemoryMode::UserPrompt)
            ->and($configuration->toArray())->toBe([
                'enabled' => true,
                'mode' => 'user-prompt',
            ]);
    });

    it('accepts the enum mode in array input', function () {
        $configuration = MemoryConfiguration::fromArray([
            'enabled' => true,
            'mode' => MemoryMode::SystemPrompt,
        ]);

        expect($configuration->mode)->toBe(MemoryMode::SystemPrompt)
            ->and($configuration->toArray()['mode'])->toBe('system-prompt');
    });

    it('preserves unknown mode strings for forward compatibility', function () {
        $configuration = MemoryConfiguration::fromArray([
            'enabled' => true,
            'mode' => 'future-mode',
        ]);

        expect($configuration->mode)->toBe('future-mode')
            ->and($configuration->toArray()['mode'])->toBe('future-mode');
    });

    it('converts an enum mode to an array', function () {
        $configuration = new MemoryConfiguration(enabled: false, mode: MemoryMode::SystemPrompt);

        expect($configuration->toArray())->toBe([
            'enabled' => false,
            'mode' => 'system-prompt',
        ]);
    });

    it('implements Arrayable', function () {
        expect(new MemoryConfiguration(enabled: false))->toBeInstanceOf(Arrayable::class);
    });
});
