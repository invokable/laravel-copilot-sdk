<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ModelBilling;
use Revolution\Copilot\Types\Rpc\ModelBillingTokenPrices;

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

        expect($billing)->toBeInstanceOf(Arrayable::class);
    });

    it('can be created with tokenPrices', function () {
        $billing = ModelBilling::fromArray([
            'multiplier' => 1.0,
            'tokenPrices' => [
                'inputPrice' => 3.0,
                'outputPrice' => 10.0,
            ],
        ]);

        expect($billing->tokenPrices)->toBeInstanceOf(ModelBillingTokenPrices::class)
            ->and($billing->tokenPrices->inputPrice)->toBe(3.0);
    });

    it('includes tokenPrices in toArray when set', function () {
        $billing = new ModelBilling(
            multiplier: 1.0,
            tokenPrices: new ModelBillingTokenPrices(inputPrice: 3.0, outputPrice: 10.0),
        );

        expect($billing->toArray())->toHaveKey('tokenPrices');
    });
});
