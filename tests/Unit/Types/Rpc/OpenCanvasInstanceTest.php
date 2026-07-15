<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\OpenCanvasInstance;

describe('OpenCanvasInstance', function () {
    it('can be created from array with all fields', function () {
        $instance = OpenCanvasInstance::fromArray([
            'canvasId' => 'canvas-123',
            'extensionId' => 'ext-456',
            'instanceId' => 'instance-789',
            'extensionName' => 'Test Extension',
            'icon' => '/path/to/icon.png',
            'input' => ['config' => 'value'],
            'status' => 'active',
            'title' => 'Test Canvas',
            'url' => 'https://example.com/canvas',
        ]);

        expect($instance->canvasId)->toBe('canvas-123')
            ->and($instance->extensionId)->toBe('ext-456')
            ->and($instance->instanceId)->toBe('instance-789')
            ->and($instance->extensionName)->toBe('Test Extension')
            ->and($instance->icon)->toBe('/path/to/icon.png')
            ->and($instance->input)->toBe(['config' => 'value'])
            ->and($instance->status)->toBe('active')
            ->and($instance->title)->toBe('Test Canvas')
            ->and($instance->url)->toBe('https://example.com/canvas');
    });

    it('can be created from array with minimal fields', function () {
        $instance = OpenCanvasInstance::fromArray([
            'canvasId' => 'canvas-abc',
            'extensionId' => 'ext-def',
            'instanceId' => 'instance-ghi',
        ]);

        expect($instance->canvasId)->toBe('canvas-abc')
            ->and($instance->extensionId)->toBe('ext-def')
            ->and($instance->instanceId)->toBe('instance-ghi')
            ->and($instance->extensionName)->toBeNull()
            ->and($instance->icon)->toBeNull()
            ->and($instance->input)->toBeNull()
            ->and($instance->status)->toBeNull()
            ->and($instance->title)->toBeNull()
            ->and($instance->url)->toBeNull();
    });

    it('converts to array correctly', function () {
        $instance = new OpenCanvasInstance(
            canvasId: 'canvas-test',
            extensionId: 'ext-test',
            instanceId: 'instance-test',
            extensionName: 'Test',
            input: ['data' => 'test'],
            status: 'ready',
            title: 'Test',
            url: 'https://test.com'
        );

        $array = $instance->toArray();

        expect($array)->toHaveKey('canvasId', 'canvas-test')
            ->and($array)->toHaveKey('extensionId', 'ext-test')
            ->and($array)->toHaveKey('instanceId', 'instance-test')
            ->and($array)->not->toHaveKey('reopen')
            ->and($array)->not->toHaveKey('availability');
    });

    it('excludes null optional fields from array', function () {
        $instance = new OpenCanvasInstance(
            canvasId: 'canvas-minimal',
            extensionId: 'ext-minimal',
            instanceId: 'instance-minimal',
        );

        $array = $instance->toArray();

        expect($array)->toHaveKeys(['canvasId', 'extensionId', 'instanceId'])
            ->and($array)->not->toHaveKey('extensionName')
            ->and($array)->not->toHaveKey('url');
    });

    it('implements Arrayable', function () {
        expect(new OpenCanvasInstance(canvasId: 'c', extensionId: 'e', instanceId: 'i'))->toBeInstanceOf(Arrayable::class);
    });
});
