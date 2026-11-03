<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ProviderStatus;

describe('ProviderStatus', function () {
    it('can be created from array with a custom provider', function () {
        $status = ProviderStatus::fromArray([
            'hasCustomProvider' => true,
        ]);

        expect($status->hasCustomProvider)->toBeTrue();
    });

    it('can convert to array', function () {
        $status = new ProviderStatus(hasCustomProvider: false);

        expect($status->toArray())->toBe([
            'hasCustomProvider' => false,
        ]);
    });

    it('round trips through array', function () {
        $data = ['hasCustomProvider' => true];

        expect(ProviderStatus::fromArray($data)->toArray())->toBe($data);
    });

    it('requires hasCustomProvider', function () {
        ProviderStatus::fromArray([]);
    })->throws(InvalidArgumentException::class);
});
