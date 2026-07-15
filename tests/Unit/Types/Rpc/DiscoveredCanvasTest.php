<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\DiscoveredCanvas;

describe('DiscoveredCanvas', function () {
    it('can be created from array with all fields', function () {
        $canvas = DiscoveredCanvas::fromArray([
            'canvasId' => 'canvas-123',
            'description' => 'A test canvas',
            'displayName' => 'Test Canvas',
            'extensionId' => 'ext-456',
            'extensionName' => 'My Extension',
            'icon' => '/path/to/icon.png',
        ]);

        expect($canvas->canvasId)->toBe('canvas-123')
            ->and($canvas->description)->toBe('A test canvas')
            ->and($canvas->displayName)->toBe('Test Canvas')
            ->and($canvas->extensionId)->toBe('ext-456')
            ->and($canvas->extensionName)->toBe('My Extension')
            ->and($canvas->icon)->toBe('/path/to/icon.png');
    });

    it('can be created from array with minimal fields', function () {
        $canvas = DiscoveredCanvas::fromArray([
            'canvasId' => 'canvas-abc',
            'description' => 'Minimal canvas',
            'displayName' => 'Minimal',
            'extensionId' => 'ext-def',
        ]);

        expect($canvas->canvasId)->toBe('canvas-abc')
            ->and($canvas->extensionName)->toBeNull()
            ->and($canvas->icon)->toBeNull()
            ->and($canvas->actions)->toBeNull()
            ->and($canvas->inputSchema)->toBeNull();
    });

    it('converts to array correctly', function () {
        $canvas = new DiscoveredCanvas(
            canvasId: 'canvas-xyz',
            description: 'Test',
            displayName: 'Test Canvas',
            extensionId: 'ext-123',
            icon: '/icon.png',
        );

        $array = $canvas->toArray();

        expect($array)->toHaveKey('canvasId', 'canvas-xyz')
            ->and($array)->toHaveKey('icon', '/icon.png')
            ->and($array)->not->toHaveKey('extensionName')
            ->and($array)->not->toHaveKey('actions');
    });

    it('implements Arrayable', function () {
        expect(new DiscoveredCanvas(
            canvasId: 'c',
            description: 'd',
            displayName: 'dn',
            extensionId: 'e',
        ))->toBeInstanceOf(Arrayable::class);
    });
});
