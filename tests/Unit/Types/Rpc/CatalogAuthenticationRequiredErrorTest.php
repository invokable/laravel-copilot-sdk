<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogAuthenticationRequiredReason;
use Revolution\Copilot\Types\Rpc\CatalogAuthenticationRequiredError;

describe('CatalogAuthenticationRequiredError', function () {
    it('can be created with required fields', function () {
        $error = new CatalogAuthenticationRequiredError(
            reason: CatalogAuthenticationRequiredReason::NoCredential,
            message: 'Sign in to continue.',
        );

        expect($error->kind)->toBe('authentication-required')
            ->and($error->reason)->toBe(CatalogAuthenticationRequiredReason::NoCredential)
            ->and($error->message)->toBe('Sign in to continue.');
    });

    it('can be created from array', function () {
        $error = CatalogAuthenticationRequiredError::fromArray([
            'reason' => 'credential-expired',
            'message' => 'Your credential has expired.',
        ]);

        expect($error->reason)->toBe(CatalogAuthenticationRequiredReason::CredentialExpired)
            ->and($error->message)->toBe('Your credential has expired.');
    });

    it('converts to array using wire values', function () {
        $error = new CatalogAuthenticationRequiredError(
            reason: CatalogAuthenticationRequiredReason::CredentialRejected,
            message: 'The credential was rejected.',
        );

        expect($error->toArray())->toBe([
            'kind' => 'authentication-required',
            'reason' => 'credential-rejected',
            'message' => 'The credential was rejected.',
        ]);
    });
});
