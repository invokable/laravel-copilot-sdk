<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ModelBillingPromo;

describe('ModelBillingPromo', function () {
    it('can be created with required field', function () {
        $promo = new ModelBillingPromo(endsAt: '2026-12-31T23:59:59Z');

        expect($promo->endsAt)->toBe('2026-12-31T23:59:59Z')
            ->and($promo->discountPercent)->toBeNull()
            ->and($promo->id)->toBeNull()
            ->and($promo->message)->toBeNull();
    });

    it('can be created with all fields', function () {
        $promo = new ModelBillingPromo(
            endsAt: '2026-12-31T23:59:59Z',
            discountPercent: 20.5,
            id: 'promo-123',
            message: 'Holiday discount',
        );

        expect($promo->endsAt)->toBe('2026-12-31T23:59:59Z')
            ->and($promo->discountPercent)->toBe(20.5)
            ->and($promo->id)->toBe('promo-123')
            ->and($promo->message)->toBe('Holiday discount');
    });

    it('can be created from array', function () {
        $promo = ModelBillingPromo::fromArray([
            'endsAt' => '2026-06-30T00:00:00Z',
            'discountPercent' => 10.0,
            'id' => 'summer-promo',
            'message' => 'Summer savings',
        ]);

        expect($promo->endsAt)->toBe('2026-06-30T00:00:00Z')
            ->and($promo->discountPercent)->toBe(10.0)
            ->and($promo->id)->toBe('summer-promo')
            ->and($promo->message)->toBe('Summer savings');
    });

    it('can be created from array with only required fields', function () {
        $promo = ModelBillingPromo::fromArray(['endsAt' => '2026-01-01T00:00:00Z']);

        expect($promo->endsAt)->toBe('2026-01-01T00:00:00Z')
            ->and($promo->discountPercent)->toBeNull()
            ->and($promo->id)->toBeNull()
            ->and($promo->message)->toBeNull();
    });

    it('can convert to array', function () {
        $promo = new ModelBillingPromo(
            endsAt: '2026-12-31T23:59:59Z',
            discountPercent: 25.0,
            id: 'promo-abc',
            message: 'Discount offer',
        );

        expect($promo->toArray())->toBe([
            'endsAt' => '2026-12-31T23:59:59Z',
            'discountPercent' => 25.0,
            'id' => 'promo-abc',
            'message' => 'Discount offer',
        ]);
    });

    it('filters null values in toArray', function () {
        $promo = new ModelBillingPromo(endsAt: '2026-12-31T23:59:59Z');

        expect($promo->toArray())->toBe(['endsAt' => '2026-12-31T23:59:59Z']);
    });

    it('implements Arrayable interface', function () {
        $promo = new ModelBillingPromo(endsAt: '2026-12-31T23:59:59Z');

        expect($promo)->toBeInstanceOf(Arrayable::class);
    });
});
