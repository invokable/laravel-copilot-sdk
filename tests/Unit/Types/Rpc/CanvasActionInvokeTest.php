<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\CanvasActionInvokeRequest;
use Revolution\Copilot\Types\Rpc\CanvasActionInvokeResult;

describe('CanvasActionInvokeRequest', function () {
    it('can be created from array with all fields', function () {
        $request = CanvasActionInvokeRequest::fromArray([
            'instanceId' => 'instance-123',
            'actionName' => 'update',
            'input' => ['data' => 'test'],
        ]);

        expect($request->instanceId)->toBe('instance-123')
            ->and($request->actionName)->toBe('update')
            ->and($request->input)->toBe(['data' => 'test']);
    });

    it('can be created from array without input', function () {
        $request = CanvasActionInvokeRequest::fromArray([
            'instanceId' => 'instance-456',
            'actionName' => 'refresh',
        ]);

        expect($request->instanceId)->toBe('instance-456')
            ->and($request->actionName)->toBe('refresh')
            ->and($request->input)->toBeNull();
    });

    it('converts to array correctly', function () {
        $request = new CanvasActionInvokeRequest(
            instanceId: 'instance-789',
            actionName: 'execute',
            input: ['key' => 'value'],
        );

        $array = $request->toArray();

        expect($array)->toHaveKey('instanceId', 'instance-789')
            ->and($array)->toHaveKey('actionName', 'execute')
            ->and($array)->toHaveKey('input', ['key' => 'value']);
    });

    it('excludes null input from array', function () {
        $request = new CanvasActionInvokeRequest(
            instanceId: 'instance-123',
            actionName: 'test',
        );

        $array = $request->toArray();

        expect($array)->toHaveKey('instanceId')
            ->and($array)->toHaveKey('actionName')
            ->and($array)->not->toHaveKey('input');
    });
});

describe('CanvasActionInvokeResult', function () {
    it('can be created from array', function () {
        $result = CanvasActionInvokeResult::fromArray(['status' => 'ok']);

        expect($result->data)->toBe(['status' => 'ok']);
    });

    it('can be created from empty array', function () {
        $result = CanvasActionInvokeResult::fromArray([]);

        expect($result->data)->toBe([]);
    });

    it('converts to array correctly', function () {
        $result = CanvasActionInvokeResult::fromArray(['key' => 'value']);

        expect($result->toArray())->toBe(['key' => 'value']);
    });
});
