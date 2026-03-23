<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\CommandDefinition;

describe('CommandDefinition', function () {
    it('can be created with constructor', function () {
        $handler = fn () => null;

        $def = new CommandDefinition(
            name: 'deploy',
            handler: $handler,
            description: 'Deploy the application',
        );

        expect($def->name)->toBe('deploy')
            ->and($def->handler)->toBe($handler)
            ->and($def->description)->toBe('Deploy the application');
    });

    it('can be created without description', function () {
        $handler = fn () => null;

        $def = new CommandDefinition(
            name: 'test',
            handler: $handler,
        );

        expect($def->name)->toBe('test')
            ->and($def->handler)->toBe($handler)
            ->and($def->description)->toBeNull();
    });

    it('can be created from array', function () {
        $handler = fn () => null;

        $def = CommandDefinition::fromArray([
            'name' => 'build',
            'handler' => $handler,
            'description' => 'Build the project',
        ]);

        expect($def->name)->toBe('build')
            ->and($def->handler)->toBe($handler)
            ->and($def->description)->toBe('Build the project');
    });

    it('can be created from array without description', function () {
        $handler = fn () => null;

        $def = CommandDefinition::fromArray([
            'name' => 'lint',
            'handler' => $handler,
        ]);

        expect($def->description)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $handler = fn () => null;

        $def = new CommandDefinition(
            name: 'deploy',
            handler: $handler,
            description: 'Deploy the app',
        );

        $array = $def->toArray();

        expect($array['name'])->toBe('deploy')
            ->and($array['handler'])->toBe($handler)
            ->and($array['description'])->toBe('Deploy the app');
    });

    it('filters null description in toArray', function () {
        $handler = fn () => null;

        $def = new CommandDefinition(
            name: 'test',
            handler: $handler,
        );

        expect($def->toArray())->not->toHaveKey('description');
    });

    it('implements Arrayable interface', function () {
        $def = new CommandDefinition(
            name: 'x',
            handler: fn () => null,
        );

        expect($def)->toBeInstanceOf(Arrayable::class);
    });
});
