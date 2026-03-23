<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\SessionCapabilities;

describe('SessionCapabilities', function () {
    it('can be created with elicitation support', function () {
        $caps = new SessionCapabilities(ui: ['elicitation' => true]);

        expect($caps->supportsElicitation())->toBeTrue()
            ->and($caps->ui)->toBe(['elicitation' => true]);
    });

    it('reports no elicitation when ui is null', function () {
        $caps = new SessionCapabilities;

        expect($caps->supportsElicitation())->toBeFalse()
            ->and($caps->ui)->toBeNull();
    });

    it('reports no elicitation when elicitation is false', function () {
        $caps = new SessionCapabilities(ui: ['elicitation' => false]);

        expect($caps->supportsElicitation())->toBeFalse();
    });

    it('reports no elicitation when elicitation key is missing', function () {
        $caps = new SessionCapabilities(ui: []);

        expect($caps->supportsElicitation())->toBeFalse();
    });

    it('can be created from array', function () {
        $caps = SessionCapabilities::fromArray([
            'ui' => ['elicitation' => true],
        ]);

        expect($caps->supportsElicitation())->toBeTrue();
    });

    it('can be created from array without ui', function () {
        $caps = SessionCapabilities::fromArray([]);

        expect($caps->supportsElicitation())->toBeFalse()
            ->and($caps->ui)->toBeNull();
    });

    it('can convert to array', function () {
        $caps = new SessionCapabilities(ui: ['elicitation' => true]);

        expect($caps->toArray())->toBe(['ui' => ['elicitation' => true]]);
    });

    it('filters null values in toArray', function () {
        $caps = new SessionCapabilities;

        expect($caps->toArray())->toBe([]);
    });

    it('implements Arrayable interface', function () {
        expect(new SessionCapabilities)->toBeInstanceOf(Arrayable::class);
    });
});
