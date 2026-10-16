<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogContractViolationReason;
use Revolution\Copilot\Types\Rpc\CatalogContractViolationError;

describe('CatalogContractViolationError', function () {
    it('can be created with required fields', function () {
        $error = new CatalogContractViolationError(
            reason: CatalogContractViolationReason::BothUrlAndData,
            message: 'The result includes both a URL and embedded data.',
        );

        expect($error->kind)->toBe('contract-violation')
            ->and($error->reason)->toBe(CatalogContractViolationReason::BothUrlAndData)
            ->and($error->message)->toBe('The result includes both a URL and embedded data.');
    });

    it('can be created from array', function () {
        $error = CatalogContractViolationError::fromArray([
            'reason' => 'unknown-media-type',
            'message' => 'The media type is not supported.',
        ]);

        expect($error->kind)->toBe('contract-violation')
            ->and($error->reason)->toBe(CatalogContractViolationReason::UnknownMediaType)
            ->and($error->message)->toBe('The media type is not supported.');
    });

    it('converts to array using wire values', function () {
        $error = new CatalogContractViolationError(
            reason: CatalogContractViolationReason::NeitherUrlNorData,
            message: 'The result does not include a URL or embedded data.',
        );

        expect($error->toArray())->toBe([
            'kind' => 'contract-violation',
            'reason' => 'neither-url-nor-data',
            'message' => 'The result does not include a URL or embedded data.',
        ]);
    });
});
