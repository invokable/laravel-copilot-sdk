<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ModelBilling;

describe('ModelBilling', function () {
    it('can be created from array', function () {
        $billing = ModelBilling::fromArray([
            'multiplier' => 1.5,
        ]);

        expect($billing->multiplier)->toBe(1.5);
    });

    it('casts multiplier to float', function () {
        $billing = ModelBilling::fromArray([
            'multiplier' => 2,
        ]);

        expect($billing->multiplier)->toBe(2.0)
            ->and($billing->multiplier)->toBeFloat();
    });

    it('can convert to array', function () {
        $billing = new ModelBilling(multiplier: 0.75);

        expect($billing->toArray())->toBe([
            'multiplier' => 0.75,
        ]);
    });

    it('implements Arrayable interface', function () {
        $billing = new ModelBilling(multiplier: 1.0);

        expect($billing)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
