<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\PermissionsCapabilities;

describe('PermissionsCapabilities', function () {
    it('can be created from array', function () {
        $capabilities = PermissionsCapabilities::fromArray([
            'assistedApproval' => true,
            'bypassPermissions' => false,
        ]);

        expect($capabilities->assistedApproval)->toBeTrue()
            ->and($capabilities->bypassPermissions)->toBeFalse();
    });

    it('can convert to array', function () {
        $capabilities = new PermissionsCapabilities(
            assistedApproval: false,
            bypassPermissions: true,
        );

        expect($capabilities->toArray())->toBe([
            'assistedApproval' => false,
            'bypassPermissions' => true,
        ]);
    });

    it('round trips through array', function () {
        $data = [
            'assistedApproval' => true,
            'bypassPermissions' => true,
        ];

        expect(PermissionsCapabilities::fromArray($data)->toArray())->toBe($data);
    });

    it('requires both flags', function () {
        PermissionsCapabilities::fromArray([
            'assistedApproval' => true,
        ]);
    })->throws(InvalidArgumentException::class);
});
