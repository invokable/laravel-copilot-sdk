<?php

declare(strict_types=1);

use Revolution\Copilot\Support\McpCallToolResult;
use Revolution\Copilot\Types\ToolResultObject;

describe('McpCallToolResult', function () {
    it('converts text content blocks', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                ['type' => 'text', 'text' => 'Hello'],
                ['type' => 'text', 'text' => 'World'],
            ],
        ]);

        expect($result)->toBeInstanceOf(ToolResultObject::class)
            ->and($result->textResultForLlm)->toBe("Hello\nWorld")
            ->and($result->resultType)->toBe('success')
            ->and($result->binaryResultsForLlm)->toBeNull();
    });

    it('converts image content blocks', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                ['type' => 'image', 'data' => 'base64png', 'mimeType' => 'image/png'],
            ],
        ]);

        expect($result->textResultForLlm)->toBe('')
            ->and($result->binaryResultsForLlm)->toHaveCount(1)
            ->and($result->binaryResultsForLlm[0])->toBe([
                'data' => 'base64png',
                'mimeType' => 'image/png',
                'type' => 'image',
            ]);
    });

    it('converts resource content blocks with text', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                [
                    'type' => 'resource',
                    'resource' => [
                        'uri' => 'file:///test.txt',
                        'text' => 'File content here',
                    ],
                ],
            ],
        ]);

        expect($result->textResultForLlm)->toBe('File content here')
            ->and($result->binaryResultsForLlm)->toBeNull();
    });

    it('converts resource content blocks with blob', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                [
                    'type' => 'resource',
                    'resource' => [
                        'uri' => 'file:///image.bin',
                        'mimeType' => 'application/octet-stream',
                        'blob' => 'binarydata',
                    ],
                ],
            ],
        ]);

        expect($result->textResultForLlm)->toBe('')
            ->and($result->binaryResultsForLlm)->toHaveCount(1)
            ->and($result->binaryResultsForLlm[0])->toBe([
                'data' => 'binarydata',
                'mimeType' => 'application/octet-stream',
                'type' => 'resource',
                'description' => 'file:///image.bin',
            ]);
    });

    it('converts resource with both text and blob', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                [
                    'type' => 'resource',
                    'resource' => [
                        'uri' => 'file:///mixed.txt',
                        'mimeType' => 'text/plain',
                        'text' => 'Readable text',
                        'blob' => 'blobdata',
                    ],
                ],
            ],
        ]);

        expect($result->textResultForLlm)->toBe('Readable text')
            ->and($result->binaryResultsForLlm)->toHaveCount(1)
            ->and($result->binaryResultsForLlm[0]['data'])->toBe('blobdata');
    });

    it('handles mixed content types', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                ['type' => 'text', 'text' => 'Some text'],
                ['type' => 'image', 'data' => 'imgdata', 'mimeType' => 'image/jpeg'],
                [
                    'type' => 'resource',
                    'resource' => [
                        'uri' => 'file:///doc.pdf',
                        'blob' => 'pdfblob',
                        'mimeType' => 'application/pdf',
                    ],
                ],
            ],
        ]);

        expect($result->textResultForLlm)->toBe('Some text')
            ->and($result->binaryResultsForLlm)->toHaveCount(2)
            ->and($result->binaryResultsForLlm[0]['type'])->toBe('image')
            ->and($result->binaryResultsForLlm[1]['type'])->toBe('resource');
    });

    it('sets failure result type when isError is true', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                ['type' => 'text', 'text' => 'Something went wrong'],
            ],
            'isError' => true,
        ]);

        expect($result->resultType)->toBe('failure')
            ->and($result->textResultForLlm)->toBe('Something went wrong');
    });

    it('sets success result type when isError is false', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                ['type' => 'text', 'text' => 'OK'],
            ],
            'isError' => false,
        ]);

        expect($result->resultType)->toBe('success');
    });

    it('sets success result type when isError is not present', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                ['type' => 'text', 'text' => 'OK'],
            ],
        ]);

        expect($result->resultType)->toBe('success');
    });

    it('handles empty content array', function () {
        $result = McpCallToolResult::convert([
            'content' => [],
        ]);

        expect($result->textResultForLlm)->toBe('')
            ->and($result->resultType)->toBe('success')
            ->and($result->binaryResultsForLlm)->toBeNull();
    });

    it('handles missing content key', function () {
        $result = McpCallToolResult::convert([]);

        expect($result->textResultForLlm)->toBe('')
            ->and($result->resultType)->toBe('success')
            ->and($result->binaryResultsForLlm)->toBeNull();
    });

    it('skips image blocks with empty data', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                ['type' => 'image', 'data' => '', 'mimeType' => 'image/png'],
            ],
        ]);

        expect($result->binaryResultsForLlm)->toBeNull();
    });

    it('skips resource blocks without resource field', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                ['type' => 'resource'],
            ],
        ]);

        expect($result->textResultForLlm)->toBe('')
            ->and($result->binaryResultsForLlm)->toBeNull();
    });

    it('skips resource blocks with non-array resource', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                ['type' => 'resource', 'resource' => 'invalid'],
            ],
        ]);

        expect($result->textResultForLlm)->toBe('')
            ->and($result->binaryResultsForLlm)->toBeNull();
    });

    it('handles unknown content block types gracefully', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                ['type' => 'unknown', 'data' => 'something'],
                ['type' => 'text', 'text' => 'Valid text'],
            ],
        ]);

        expect($result->textResultForLlm)->toBe('Valid text');
    });

    it('uses default mimeType for resource blobs without mimeType', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                [
                    'type' => 'resource',
                    'resource' => [
                        'uri' => 'file:///data.bin',
                        'blob' => 'rawdata',
                    ],
                ],
            ],
        ]);

        expect($result->binaryResultsForLlm)->toHaveCount(1)
            ->and($result->binaryResultsForLlm[0]['mimeType'])->toBe('application/octet-stream');
    });

    it('guards against non-string text in text blocks', function () {
        $result = McpCallToolResult::convert([
            'content' => [
                ['type' => 'text', 'text' => 123],
                ['type' => 'text', 'text' => 'Valid'],
            ],
        ]);

        expect($result->textResultForLlm)->toBe('Valid');
    });
});
