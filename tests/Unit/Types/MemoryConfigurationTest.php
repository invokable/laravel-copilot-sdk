<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\MemoryConfiguration;

describe('MemoryConfiguration', function () {
    it('can be created from array with memory enabled', function () {
        $configuration = MemoryConfiguration::fromArray(['enabled' => true]);

        expect($configuration->enabled)->toBeTrue();
    });

    it('defaults memory to disabled when the field is absent', function () {
        $configuration = MemoryConfiguration::fromArray([]);

        expect($configuration->enabled)->toBeFalse();
    });

    it('rejects non-boolean enabled values', function () {
        expect(fn () => MemoryConfiguration::fromArray(['enabled' => 1]))
            ->toThrow(InvalidArgumentException::class, 'Array value for key [enabled] must be a boolean, integer found.');
    });

    it('rejects null enabled values', function () {
        expect(fn () => MemoryConfiguration::fromArray(['enabled' => null]))
            ->toThrow(InvalidArgumentException::class, 'Array value for key [enabled] must be a boolean, NULL found.');
    });

    it('converts to an array', function () {
        $configuration = new MemoryConfiguration(enabled: true);

        expect($configuration->toArray())->toBe(['enabled' => true]);
    });

    it('implements Arrayable', function () {
        expect(new MemoryConfiguration(enabled: false))->toBeInstanceOf(Arrayable::class);
    });
});
