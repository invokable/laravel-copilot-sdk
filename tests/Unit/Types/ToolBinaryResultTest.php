<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ToolBinaryResult;

describe('ToolBinaryResult', function () {
    it('can be created with all fields', function () {
        $result = new ToolBinaryResult(
            data: 'base64encodeddata',
            mimeType: 'image/png',
            type: 'image',
            description: 'A screenshot',
        );

        expect($result->data)->toBe('base64encodeddata')
            ->and($result->mimeType)->toBe('image/png')
            ->and($result->type)->toBe('image')
            ->and($result->description)->toBe('A screenshot');
    });

    it('can be created with minimal fields', function () {
        $result = new ToolBinaryResult(
            data: 'abc',
            mimeType: 'text/plain',
        );

        expect($result->data)->toBe('abc')
            ->and($result->mimeType)->toBe('text/plain')
            ->and($result->type)->toBe('image')
            ->and($result->description)->toBeNull();
    });

    it('can be created from array with all fields', function () {
        $result = ToolBinaryResult::fromArray([
            'data' => 'base64data',
            'mimeType' => 'image/jpeg',
            'type' => 'resource',
            'description' => 'file://test.jpg',
        ]);

        expect($result->data)->toBe('base64data')
            ->and($result->mimeType)->toBe('image/jpeg')
            ->and($result->type)->toBe('resource')
            ->and($result->description)->toBe('file://test.jpg');
    });

    it('handles default values from empty array', function () {
        $result = ToolBinaryResult::fromArray([]);

        expect($result->data)->toBe('')
            ->and($result->mimeType)->toBe('')
            ->and($result->type)->toBe('image')
            ->and($result->description)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $result = new ToolBinaryResult(
            data: 'base64data',
            mimeType: 'image/png',
            type: 'image',
            description: 'An image',
        );

        expect($result->toArray())->toBe([
            'data' => 'base64data',
            'mimeType' => 'image/png',
            'type' => 'image',
            'description' => 'An image',
        ]);
    });

    it('filters null values in toArray', function () {
        $result = new ToolBinaryResult(
            data: 'data123',
            mimeType: 'application/octet-stream',
        );

        expect($result->toArray())->toBe([
            'data' => 'data123',
            'mimeType' => 'application/octet-stream',
            'type' => 'image',
        ]);
    });

    it('supports roundtrip via fromArray and toArray', function () {
        $original = [
            'data' => 'blobdata',
            'mimeType' => 'application/pdf',
            'type' => 'resource',
            'description' => 'https://example.com/doc.pdf',
        ];

        $result = ToolBinaryResult::fromArray($original);

        expect($result->toArray())->toBe($original);
    });

    it('implements Arrayable interface', function () {
        $result = new ToolBinaryResult(data: 'x', mimeType: 'y');

        expect($result)->toBeInstanceOf(Arrayable::class);
    });
});
