<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogInvalidRequestField;
use Revolution\Copilot\Types\Rpc\CatalogInvalidRequestError;

describe('CatalogInvalidRequestError', function () {
    it('can be created with required fields', function () {
        $error = new CatalogInvalidRequestError(
            field: CatalogInvalidRequestField::Query,
            message: 'The query is empty.',
        );

        expect($error->kind)->toBe('invalid-request')
            ->and($error->field)->toBe(CatalogInvalidRequestField::Query)
            ->and($error->message)->toBe('The query is empty.');
    });

    it('can be created from array', function () {
        $error = CatalogInvalidRequestError::fromArray([
            'field' => 'limit',
            'message' => 'The limit is out of range.',
        ]);

        expect($error->kind)->toBe('invalid-request')
            ->and($error->field)->toBe(CatalogInvalidRequestField::Limit)
            ->and($error->message)->toBe('The limit is out of range.');
    });

    it('converts to array using the field wire value', function () {
        $error = new CatalogInvalidRequestError(
            field: CatalogInvalidRequestField::PolicySessionId,
            message: 'The policy session is unusable.',
        );

        expect($error->toArray())->toBe([
            'kind' => 'invalid-request',
            'field' => 'policySessionId',
            'message' => 'The policy session is unusable.',
        ]);
    });

    it('rejects an unknown field when created from array', function () {
        expect(fn () => CatalogInvalidRequestError::fromArray([
            'field' => 'unknown',
            'message' => 'Invalid request.',
        ]))->toThrow(ValueError::class);
    });
});
