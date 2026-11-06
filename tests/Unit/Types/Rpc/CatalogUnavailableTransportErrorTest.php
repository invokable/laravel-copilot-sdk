<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogUnavailableTransportReason;
use Revolution\Copilot\Types\Rpc\CatalogUnavailableTransportError;

describe('CatalogUnavailableTransportError', function () {
    it('can be created with required fields', function () {
        $error = new CatalogUnavailableTransportError(
            reason: CatalogUnavailableTransportReason::NoEligibleTransport,
            message: 'No eligible transport is available.',
        );

        expect($error->kind)->toBe('unavailable-transport')
            ->and($error->reason)->toBe(CatalogUnavailableTransportReason::NoEligibleTransport)
            ->and($error->message)->toBe('No eligible transport is available.');
    });

    it('can be created from array', function () {
        $error = CatalogUnavailableTransportError::fromArray([
            'reason' => 'transport-not-supported',
            'message' => 'The advertised transport is not supported.',
        ]);

        expect($error->kind)->toBe('unavailable-transport')
            ->and($error->reason)->toBe(CatalogUnavailableTransportReason::TransportNotSupported)
            ->and($error->message)->toBe('The advertised transport is not supported.');
    });

    it('converts to array using wire values', function () {
        $error = new CatalogUnavailableTransportError(
            reason: CatalogUnavailableTransportReason::RemoteEnumerationUnavailable,
            message: 'Remote transports could not be enumerated.',
        );

        expect($error->toArray())->toBe([
            'kind' => 'unavailable-transport',
            'reason' => 'remote-enumeration-unavailable',
            'message' => 'Remote transports could not be enumerated.',
        ]);
    });

    it('rejects an unknown reason when created from array', function () {
        expect(fn () => CatalogUnavailableTransportError::fromArray([
            'reason' => 'unknown',
            'message' => 'Transport unavailable.',
        ]))->toThrow(ValueError::class);
    });
});
