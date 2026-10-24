<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CatalogMalformedCardReason;
use Revolution\Copilot\Enums\CatalogMediaType;
use Revolution\Copilot\Types\Rpc\CatalogMalformedCardError;

describe('CatalogMalformedCardError', function () {
    it('can be created with required fields and a media type', function () {
        $error = new CatalogMalformedCardError(
            reason: CatalogMalformedCardReason::SchemaViolation,
            mediaType: CatalogMediaType::McpServerCard,
            message: 'The card does not match its schema.',
        );

        expect($error->kind)->toBe('malformed-card')
            ->and($error->reason)->toBe(CatalogMalformedCardReason::SchemaViolation)
            ->and($error->mediaType)->toBe(CatalogMediaType::McpServerCard)
            ->and($error->message)->toBe('The card does not match its schema.');
    });

    it('can be created from array without an optional media type', function () {
        $error = CatalogMalformedCardError::fromArray([
            'reason' => 'invalid-json',
            'message' => 'The card is not valid JSON.',
        ]);

        expect($error->reason)->toBe(CatalogMalformedCardReason::InvalidJson)
            ->and($error->mediaType)->toBeNull()
            ->and($error->message)->toBe('The card is not valid JSON.');
    });

    it('converts to array and omits a null media type', function () {
        $error = new CatalogMalformedCardError(
            reason: CatalogMalformedCardReason::UnsupportedMediaType,
            mediaType: null,
            message: 'The media type is unsupported.',
        );

        expect($error->toArray())->toBe([
            'kind' => 'malformed-card',
            'reason' => 'unsupported-media-type',
            'message' => 'The media type is unsupported.',
        ]);
    });

    it('roundtrips a media type through the array representation', function () {
        $error = CatalogMalformedCardError::fromArray([
            'reason' => 'missing-required-field',
            'mediaType' => 'application/ai-skill',
            'message' => 'The card is missing a required field.',
        ]);

        expect($error->mediaType)->toBe(CatalogMediaType::AiSkill)
            ->and($error->toArray())->toBe([
                'kind' => 'malformed-card',
                'reason' => 'missing-required-field',
                'message' => 'The card is missing a required field.',
                'mediaType' => 'application/ai-skill',
            ]);
    });
});
