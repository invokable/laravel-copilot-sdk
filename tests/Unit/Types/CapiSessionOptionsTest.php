<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\CapiSessionOptions;

describe('CapiSessionOptions', function () {
    it('can be created with no arguments', function () {
        $options = new CapiSessionOptions;

        expect($options->enableWebSocketResponses)->toBeNull();
    });

    it('can be created with enableWebSocketResponses true', function () {
        $options = new CapiSessionOptions(enableWebSocketResponses: true);

        expect($options->enableWebSocketResponses)->toBeTrue();
    });

    it('can be created with enableWebSocketResponses false', function () {
        $options = new CapiSessionOptions(enableWebSocketResponses: false);

        expect($options->enableWebSocketResponses)->toBeFalse();
    });

    it('can be created from array', function () {
        $options = CapiSessionOptions::fromArray(['enableWebSocketResponses' => false]);

        expect($options->enableWebSocketResponses)->toBeFalse();
    });

    it('can be created from empty array', function () {
        $options = CapiSessionOptions::fromArray([]);

        expect($options->enableWebSocketResponses)->toBeNull();
    });

    it('converts to array with set value', function () {
        $options = new CapiSessionOptions(enableWebSocketResponses: false);

        expect($options->toArray())->toBe(['enableWebSocketResponses' => false]);
    });

    it('filters null values in toArray', function () {
        $options = new CapiSessionOptions;

        expect($options->toArray())->toBe([]);
    });

    it('roundtrips through array', function () {
        $original = new CapiSessionOptions(enableWebSocketResponses: true);
        $restored = CapiSessionOptions::fromArray($original->toArray());

        expect($restored->enableWebSocketResponses)->toBe($original->enableWebSocketResponses);
    });

    it('implements Arrayable interface', function () {
        $options = new CapiSessionOptions;

        expect($options)->toBeInstanceOf(Arrayable::class);
    });
});
