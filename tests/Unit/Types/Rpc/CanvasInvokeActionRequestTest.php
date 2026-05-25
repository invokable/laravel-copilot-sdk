<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\CanvasInvokeActionRequest;

describe('CanvasInvokeActionRequest', function () {
    it('can be created from array with all fields', function () {
        $request = CanvasInvokeActionRequest::fromArray([
            'actionName' => 'update',
            'instanceId' => 'instance-123',
            'input' => ['data' => 'test'],
        ]);

        expect($request->actionName)->toBe('update')
            ->and($request->instanceId)->toBe('instance-123')
            ->and($request->input)->toBe(['data' => 'test']);
    });

    it('can be created from array without input', function () {
        $request = CanvasInvokeActionRequest::fromArray([
            'actionName' => 'refresh',
            'instanceId' => 'instance-456',
        ]);

        expect($request->actionName)->toBe('refresh')
            ->and($request->instanceId)->toBe('instance-456')
            ->and($request->input)->toBeNull();
    });

    it('converts to array correctly', function () {
        $request = new CanvasInvokeActionRequest(
            actionName: 'execute',
            instanceId: 'instance-789',
            input: ['key' => 'value']
        );

        $array = $request->toArray();

        expect($array)->toHaveKey('actionName', 'execute')
            ->and($array)->toHaveKey('instanceId', 'instance-789')
            ->and($array)->toHaveKey('input', ['key' => 'value']);
    });

    it('excludes null input from array', function () {
        $request = new CanvasInvokeActionRequest(
            actionName: 'test',
            instanceId: 'instance-123'
        );

        $array = $request->toArray();

        expect($array)->toHaveKey('actionName')
            ->and($array)->toHaveKey('instanceId')
            ->and($array)->not->toHaveKey('input');
    });
});
