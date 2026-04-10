<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

use Revolution\Copilot\Types\ToolBinaryResult;
use Revolution\Copilot\Types\ToolResultObject;

/**
 * Converts an MCP CallToolResult into the SDK's ToolResultObject format.
 *
 * MCP tools return results as an array of content blocks (text, image, resource).
 * This class converts that format into the ToolResultObject expected by the Copilot SDK.
 *
 * @see https://spec.modelcontextprotocol.io/specification/2024-11-05/server/tools/#tool-result
 */
final readonly class McpCallToolResult
{
    /**
     * Convert an MCP CallToolResult array into a ToolResultObject.
     *
     * @param  array{content: array<array{type: string, text?: string, data?: string, mimeType?: string, resource?: array{uri?: string, mimeType?: string, text?: string, blob?: string}}>, isError?: bool}  $callResult
     */
    public static function convert(array $callResult): ToolResultObject
    {
        $textParts = [];
        $binaryResults = [];

        foreach ($callResult['content'] ?? [] as $block) {
            $blockType = $block['type'] ?? null;

            match ($blockType) {
                'text' => self::handleTextBlock($block, $textParts),
                'image' => self::handleImageBlock($block, $binaryResults),
                'resource' => self::handleResourceBlock($block, $textParts, $binaryResults),
                default => null,
            };
        }

        return new ToolResultObject(
            textResultForLlm: implode("\n", $textParts),
            resultType: ($callResult['isError'] ?? false) === true ? 'failure' : 'success',
            binaryResultsForLlm: $binaryResults !== [] ? array_map(
                fn (ToolBinaryResult $r) => $r->toArray(),
                $binaryResults,
            ) : null,
        );
    }

    /**
     * @param  array<string>  $textParts
     */
    private static function handleTextBlock(array $block, array &$textParts): void
    {
        $text = $block['text'] ?? null;
        if (is_string($text)) {
            $textParts[] = $text;
        }
    }

    /**
     * @param  array<ToolBinaryResult>  $binaryResults
     */
    private static function handleImageBlock(array $block, array &$binaryResults): void
    {
        $data = $block['data'] ?? '';
        $mimeType = $block['mimeType'] ?? '';

        if (is_string($data) && $data !== '' && is_string($mimeType)) {
            $binaryResults[] = new ToolBinaryResult(
                data: $data,
                mimeType: $mimeType,
                type: 'image',
            );
        }
    }

    /**
     * @param  array<string>  $textParts
     * @param  array<ToolBinaryResult>  $binaryResults
     */
    private static function handleResourceBlock(array $block, array &$textParts, array &$binaryResults): void
    {
        $resource = $block['resource'] ?? null;
        if (! is_array($resource)) {
            return;
        }

        $text = $resource['text'] ?? null;
        if (is_string($text) && $text !== '') {
            $textParts[] = $text;
        }

        $blob = $resource['blob'] ?? null;
        if (is_string($blob) && $blob !== '') {
            $mimeType = $resource['mimeType'] ?? 'application/octet-stream';
            $uri = $resource['uri'] ?? '';

            $binaryResults[] = new ToolBinaryResult(
                data: $blob,
                mimeType: is_string($mimeType) ? $mimeType : 'application/octet-stream',
                type: 'resource',
                description: is_string($uri) ? $uri : '',
            );
        }
    }
}
