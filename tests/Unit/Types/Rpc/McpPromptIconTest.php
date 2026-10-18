<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\McpPromptIcon;

describe('McpPromptIcon', function () {
    it('can be created with only the required source', function () {
        $icon = new McpPromptIcon(src: 'https://example.com/icon.png');

        expect($icon->src)->toBe('https://example.com/icon.png')
            ->and($icon->mimeType)->toBeNull()
            ->and($icon->sizes)->toBeNull()
            ->and($icon->theme)->toBeNull()
            ->and($icon->additionalProperties)->toBeNull();
    });

    it('can be created from an array with all fields', function () {
        $icon = McpPromptIcon::fromArray([
            'src' => 'https://example.com/icon.svg',
            'mimeType' => 'image/svg+xml',
            'sizes' => ['any'],
            'theme' => 'dark',
            'additionalProperties' => ['label' => 'Prompt icon'],
        ]);

        expect($icon->src)->toBe('https://example.com/icon.svg')
            ->and($icon->mimeType)->toBe('image/svg+xml')
            ->and($icon->sizes)->toBe(['any'])
            ->and($icon->theme)->toBe('dark')
            ->and($icon->additionalProperties)->toBe(['label' => 'Prompt icon']);
    });

    it('uses defaults for missing array fields', function () {
        $icon = McpPromptIcon::fromArray([]);

        expect($icon->src)->toBe('')
            ->and($icon->mimeType)->toBeNull()
            ->and($icon->sizes)->toBeNull()
            ->and($icon->theme)->toBeNull()
            ->and($icon->additionalProperties)->toBeNull();
    });

    it('converts to an array without null optional fields', function () {
        $icon = new McpPromptIcon(src: 'https://example.com/icon.png');

        expect($icon->toArray())->toBe([
            'src' => 'https://example.com/icon.png',
        ]);
    });

    it('preserves empty optional values when converted to an array', function () {
        $icon = new McpPromptIcon(
            src: 'https://example.com/icon.png',
            mimeType: '',
            sizes: [],
            theme: '',
            additionalProperties: [],
        );

        expect($icon->toArray())->toBe([
            'src' => 'https://example.com/icon.png',
            'mimeType' => '',
            'sizes' => [],
            'theme' => '',
            'additionalProperties' => [],
        ]);
    });
});
