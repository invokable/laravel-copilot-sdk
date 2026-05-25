<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\CanvasAction;

describe('CanvasAction', function () {
    it('can be created from array with all fields', function () {
        $action = CanvasAction::fromArray([
            'name' => 'test_action',
            'description' => 'Test action description',
            'inputSchema' => ['type' => 'object'],
        ]);

        expect($action->name)->toBe('test_action')
            ->and($action->description)->toBe('Test action description')
            ->and($action->inputSchema)->toBe(['type' => 'object']);
    });

    it('can be created from array with minimal fields', function () {
        $action = CanvasAction::fromArray([
            'name' => 'test_action',
        ]);

        expect($action->name)->toBe('test_action')
            ->and($action->description)->toBeNull()
            ->and($action->inputSchema)->toBeNull();
    });

    it('converts to array correctly', function () {
        $action = new CanvasAction(
            name: 'test_action',
            description: 'Test description',
            inputSchema: ['type' => 'string']
        );

        $array = $action->toArray();

        expect($array)->toHaveKey('name', 'test_action')
            ->and($array)->toHaveKey('description', 'Test description')
            ->and($array)->toHaveKey('inputSchema', ['type' => 'string']);
    });

    it('excludes null values from array', function () {
        $action = new CanvasAction(name: 'test_action');

        $array = $action->toArray();

        expect($array)->toHaveKey('name', 'test_action')
            ->and($array)->not->toHaveKey('description')
            ->and($array)->not->toHaveKey('inputSchema');
    });
});
