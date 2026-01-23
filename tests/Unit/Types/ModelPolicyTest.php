<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ModelPolicy;

describe('ModelPolicy', function () {
    it('can be created from array', function () {
        $policy = ModelPolicy::fromArray([
            'state' => 'enabled',
            'terms' => 'standard',
        ]);

        expect($policy->state)->toBe('enabled')
            ->and($policy->terms)->toBe('standard');
    });

    it('can check if model is enabled', function () {
        $policy = new ModelPolicy(state: 'enabled', terms: 'standard');

        expect($policy->isEnabled())->toBeTrue();
    });

    it('returns false when model is not enabled', function () {
        $policy = new ModelPolicy(state: 'disabled', terms: 'standard');

        expect($policy->isEnabled())->toBeFalse();
    });

    it('returns false when state is unconfigured', function () {
        $policy = new ModelPolicy(state: 'unconfigured', terms: 'none');

        expect($policy->isEnabled())->toBeFalse();
    });

    it('can convert to array', function () {
        $policy = new ModelPolicy(
            state: 'enabled',
            terms: 'premium',
        );

        expect($policy->toArray())->toBe([
            'state' => 'enabled',
            'terms' => 'premium',
        ]);
    });

    it('implements Arrayable interface', function () {
        $policy = new ModelPolicy(state: 'enabled', terms: 'standard');

        expect($policy)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
