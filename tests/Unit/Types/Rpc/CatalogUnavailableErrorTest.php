<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogUnavailableReason;
use Revolution\Copilot\Types\Rpc\CatalogUnavailableError;

describe('CatalogUnavailableError', function () {
    it('can be created with required fields', function () {
        $error = new CatalogUnavailableError(
            reason: CatalogUnavailableReason::SearchUnavailable,
            message: 'Search is not available.',
        );

        expect($error->kind)->toBe('unavailable')
            ->and($error->reason)->toBe(CatalogUnavailableReason::SearchUnavailable)
            ->and($error->message)->toBe('Search is not available.');
    });

    it('can be created from array', function () {
        $error = CatalogUnavailableError::fromArray([
            'reason' => 'feed-not-found',
            'message' => 'The requested feed is not available.',
        ]);

        expect($error->kind)->toBe('unavailable')
            ->and($error->reason)->toBe(CatalogUnavailableReason::FeedNotFound)
            ->and($error->message)->toBe('The requested feed is not available.');
    });

    it('converts to array using wire values', function () {
        $error = new CatalogUnavailableError(
            reason: CatalogUnavailableReason::DisabledByPolicy,
            message: 'This catalog is disabled by policy.',
        );

        expect($error->toArray())->toBe([
            'kind' => 'unavailable',
            'reason' => 'disabled-by-policy',
            'message' => 'This catalog is disabled by policy.',
        ]);
    });

    it('rejects an unknown reason when created from array', function () {
        expect(fn () => CatalogUnavailableError::fromArray([
            'reason' => 'unknown',
            'message' => 'Catalog unavailable.',
        ]))->toThrow(ValueError::class);
    });
});
