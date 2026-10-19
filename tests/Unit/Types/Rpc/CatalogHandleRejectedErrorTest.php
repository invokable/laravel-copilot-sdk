<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogHandleRejectionReason;
use Revolution\Copilot\Enums\CatalogHandleType;
use Revolution\Copilot\Types\Rpc\CatalogHandleRejectedError;

describe('CatalogHandleRejectedError', function () {
    it('can be created with required fields', function () {
        $error = new CatalogHandleRejectedError(
            handleType: CatalogHandleType::Candidate,
            reason: CatalogHandleRejectionReason::Stale,
            message: 'The candidate handle has expired.',
        );

        expect($error->kind)->toBe('handle-rejected')
            ->and($error->handleType)->toBe(CatalogHandleType::Candidate)
            ->and($error->reason)->toBe(CatalogHandleRejectionReason::Stale)
            ->and($error->message)->toBe('The candidate handle has expired.');
    });

    it('can be created from an array', function () {
        $error = CatalogHandleRejectedError::fromArray([
            'handleType' => 'plan',
            'reason' => 'replayed',
            'message' => 'The plan handle has already been used.',
        ]);

        expect($error->kind)->toBe('handle-rejected')
            ->and($error->handleType)->toBe(CatalogHandleType::Plan)
            ->and($error->reason)->toBe(CatalogHandleRejectionReason::Replayed)
            ->and($error->message)->toBe('The plan handle has already been used.');
    });

    it('converts to array using wire values', function () {
        $error = new CatalogHandleRejectedError(
            handleType: CatalogHandleType::Candidate,
            reason: CatalogHandleRejectionReason::Foreign,
            message: 'The handle belongs to another runtime.',
        );

        expect($error->toArray())->toBe([
            'kind' => 'handle-rejected',
            'handleType' => 'candidate',
            'reason' => 'foreign',
            'message' => 'The handle belongs to another runtime.',
        ]);
    });

    it('rejects unknown enum values when created from an array', function () {
        expect(fn () => CatalogHandleRejectedError::fromArray([
            'handleType' => 'unknown',
            'reason' => 'invalid',
            'message' => 'The handle was rejected.',
        ]))->toThrow(ValueError::class);
    });
});
