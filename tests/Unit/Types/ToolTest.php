<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Tool;

describe('Tool', function () {
    it('can be created from array', function () {
        $handler = fn () => 'result';

        $tool = Tool::fromArray([
            'name' => 'test_tool',
            'description' => 'A test tool',
            'parameters' => ['type' => 'object', 'properties' => []],
            'handler' => $handler,
        ]);

        expect($tool->name)->toBe('test_tool')
            ->and($tool->description)->toBe('A test tool')
            ->and($tool->parameters)->toBe(['type' => 'object', 'properties' => []])
            ->and($tool->handler)->toBe($handler);
    });

    it('can be created from array with minimal fields', function () {
        $handler = fn () => null;

        $tool = Tool::fromArray([
            'name' => 'simple_tool',
            'handler' => $handler,
        ]);

        expect($tool->name)->toBe('simple_tool')
            ->and($tool->description)->toBeNull()
            ->and($tool->parameters)->toBeNull()
            ->and($tool->handler)->toBe($handler);
    });

    it('can define a tool statically', function () {
        $handler = fn (string $message) => "Echo: $message";

        $toolArray = Tool::define(
            name: 'echo',
            description: 'Echoes a message',
            parameters: [
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string'],
                ],
                'required' => ['message'],
            ],
            handler: $handler,
        );

        expect($toolArray)->toBeArray()
            ->and($toolArray['name'])->toBe('echo')
            ->and($toolArray['description'])->toBe('Echoes a message')
            ->and($toolArray['parameters'])->toBe([
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string'],
                ],
                'required' => ['message'],
            ])
            ->and($toolArray['handler'])->toBe($handler);
    });

    it('can convert to array', function () {
        $handler = fn () => 'test';

        $tool = new Tool(
            name: 'my_tool',
            description: 'My tool description',
            parameters: ['type' => 'object'],
            handler: $handler,
        );

        $array = $tool->toArray();

        expect($array['name'])->toBe('my_tool')
            ->and($array['description'])->toBe('My tool description')
            ->and($array['parameters'])->toBe(['type' => 'object'])
            ->and($array['handler'])->toBe($handler);
    });

    it('includes null values in toArray', function () {
        $handler = fn () => null;

        $tool = new Tool(
            name: 'minimal_tool',
            description: null,
            parameters: null,
            handler: $handler,
        );

        $array = $tool->toArray();

        expect($array)->toHaveKey('description')
            ->and($array['description'])->toBeNull()
            ->and($array)->toHaveKey('parameters')
            ->and($array['parameters'])->toBeNull();
    });

    it('handler can be executed', function () {
        $tool = new Tool(
            name: 'add',
            description: 'Adds two numbers',
            parameters: null,
            handler: fn (int $a, int $b) => $a + $b,
        );

        $result = ($tool->handler)(2, 3);

        expect($result)->toBe(5);
    });

    it('implements Arrayable interface', function () {
        $tool = new Tool(
            name: 'test',
            description: null,
            parameters: null,
            handler: fn () => null,
        );

        expect($tool)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
