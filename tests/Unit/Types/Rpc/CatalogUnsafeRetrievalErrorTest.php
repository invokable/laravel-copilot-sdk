<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogUnsafeRetrievalReason;
use Revolution\Copilot\Types\Rpc\CatalogUnsafeRetrievalError;

describe('CatalogUnsafeRetrievalError', function () {
    it('can be created from array with a reason and message', function () {
        $error = CatalogUnsafeRetrievalError::fromArray([
            'reason' => 'blocked-address',
            'message' => 'The address is not permitted.',
        ]);

        expect($error->kind)->toBe('unsafe-retrieval')
            ->and($error->reason)->toBe(CatalogUnsafeRetrievalReason::BlockedAddress)
            ->and($error->message)->toBe('The address is not permitted.');
    });

    it('converts to array using wire values', function () {
        $error = new CatalogUnsafeRetrievalError(
            reason: CatalogUnsafeRetrievalReason::HostNotPermitted,
            message: 'The host is not permitted.',
        );

        expect($error->toArray())->toBe([
            'kind' => 'unsafe-retrieval',
            'reason' => 'host-not-permitted',
            'message' => 'The host is not permitted.',
        ]);
    });

    it('rejects an unknown retrieval reason', function () {
        expect(fn () => CatalogUnsafeRetrievalError::fromArray([
            'reason' => 'unknown-reason',
            'message' => 'Not permitted.',
        ]))->toThrow(ValueError::class);
    });
});
