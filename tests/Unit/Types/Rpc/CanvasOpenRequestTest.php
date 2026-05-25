<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\CanvasOpenRequest;

describe('CanvasOpenRequest', function () {
    it('can be created from array with all fields', function () {
        $request = CanvasOpenRequest::fromArray([
            'canvasId' => 'canvas-123',
            'instanceId' => 'instance-456',
            'extensionId' => 'ext-789',
            'input' => ['config' => 'value'],
        ]);

        expect($request->canvasId)->toBe('canvas-123')
            ->and($request->instanceId)->toBe('instance-456')
            ->and($request->extensionId)->toBe('ext-789')
            ->and($request->input)->toBe(['config' => 'value']);
    });

    it('can be created from array with minimal fields', function () {
        $request = CanvasOpenRequest::fromArray([
            'canvasId' => 'canvas-abc',
            'instanceId' => 'instance-def',
        ]);

        expect($request->canvasId)->toBe('canvas-abc')
            ->and($request->instanceId)->toBe('instance-def')
            ->and($request->extensionId)->toBeNull()
            ->and($request->input)->toBeNull();
    });

    it('converts to array correctly', function () {
        $request = new CanvasOpenRequest(
            canvasId: 'canvas-xyz',
            instanceId: 'instance-123',
            extensionId: 'ext-456',
            input: ['data' => 'test']
        );

        $array = $request->toArray();

        expect($array)->toHaveKey('canvasId', 'canvas-xyz')
            ->and($array)->toHaveKey('instanceId', 'instance-123')
            ->and($array)->toHaveKey('extensionId', 'ext-456')
            ->and($array)->toHaveKey('input', ['data' => 'test']);
    });

    it('excludes null optional fields from array', function () {
        $request = new CanvasOpenRequest(
            canvasId: 'canvas-test',
            instanceId: 'instance-test'
        );

        $array = $request->toArray();

        expect($array)->toHaveKey('canvasId')
            ->and($array)->toHaveKey('instanceId')
            ->and($array)->not->toHaveKey('extensionId')
            ->and($array)->not->toHaveKey('input');
    });
});
