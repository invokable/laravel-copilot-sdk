<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\CanvasInstanceAvailability;
use Revolution\Copilot\Types\Rpc\OpenCanvasInstance;

describe('OpenCanvasInstance', function () {
    it('can be created from array with all fields', function () {
        $instance = OpenCanvasInstance::fromArray([
            'availability' => 'ready',
            'canvasId' => 'canvas-123',
            'extensionId' => 'ext-456',
            'instanceId' => 'instance-789',
            'reopen' => true,
            'extensionName' => 'Test Extension',
            'input' => ['config' => 'value'],
            'status' => 'active',
            'title' => 'Test Canvas',
            'url' => 'https://example.com/canvas',
        ]);

        expect($instance->availability)->toBe(CanvasInstanceAvailability::READY)
            ->and($instance->canvasId)->toBe('canvas-123')
            ->and($instance->extensionId)->toBe('ext-456')
            ->and($instance->instanceId)->toBe('instance-789')
            ->and($instance->reopen)->toBeTrue()
            ->and($instance->extensionName)->toBe('Test Extension')
            ->and($instance->input)->toBe(['config' => 'value'])
            ->and($instance->status)->toBe('active')
            ->and($instance->title)->toBe('Test Canvas')
            ->and($instance->url)->toBe('https://example.com/canvas');
    });

    it('can be created from array with minimal fields', function () {
        $instance = OpenCanvasInstance::fromArray([
            'availability' => 'stale',
            'canvasId' => 'canvas-abc',
            'extensionId' => 'ext-def',
            'instanceId' => 'instance-ghi',
            'reopen' => false,
        ]);

        expect($instance->availability)->toBe(CanvasInstanceAvailability::STALE)
            ->and($instance->canvasId)->toBe('canvas-abc')
            ->and($instance->extensionId)->toBe('ext-def')
            ->and($instance->instanceId)->toBe('instance-ghi')
            ->and($instance->reopen)->toBeFalse()
            ->and($instance->extensionName)->toBeNull()
            ->and($instance->input)->toBeNull()
            ->and($instance->status)->toBeNull()
            ->and($instance->title)->toBeNull()
            ->and($instance->url)->toBeNull();
    });

    it('converts to array correctly', function () {
        $instance = new OpenCanvasInstance(
            availability: CanvasInstanceAvailability::READY,
            canvasId: 'canvas-test',
            extensionId: 'ext-test',
            instanceId: 'instance-test',
            reopen: true,
            extensionName: 'Test',
            input: ['data' => 'test'],
            status: 'ready',
            title: 'Test',
            url: 'https://test.com'
        );

        $array = $instance->toArray();

        expect($array)->toHaveKey('availability', 'ready')
            ->and($array)->toHaveKey('canvasId', 'canvas-test')
            ->and($array)->toHaveKey('extensionId', 'ext-test')
            ->and($array)->toHaveKey('instanceId', 'instance-test')
            ->and($array)->toHaveKey('reopen', true);
    });
});
