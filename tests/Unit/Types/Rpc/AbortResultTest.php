<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AbortResult;

describe('AbortResult', function () {
    it('can be created from array with error', function () {
        $result = AbortResult::fromArray([
            'success' => false,
            'error' => 'Abort failed',
        ]);

        expect($result->success)->toBeFalse()
            ->and($result->error)->toBe('Abort failed');
    });

    it('can be created from array without error', function () {
        $result = AbortResult::fromArray([
            'success' => true,
        ]);

        expect($result->success)->toBeTrue()
            ->and($result->error)->toBeNull();
    });

    it('can convert to array with error', function () {
        $result = new AbortResult(success: false, error: 'Abort failed');

        expect($result->toArray())->toBe([
            'success' => false,
            'error' => 'Abort failed',
        ]);
    });

    it('excludes null error from array', function () {
        $result = new AbortResult(success: true);

        expect($result->toArray())->toBe([
            'success' => true,
        ]);
    });
});
