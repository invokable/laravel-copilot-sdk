<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\ToolNamespace;

describe('ToolNamespace', function () {
    it('round-trips the namespace name and optional description', function () {
        $namespace = ToolNamespace::fromArray([
            'name' => 'fruit',
            'description' => 'Fruit lookups',
        ]);

        expect($namespace->name)->toBe('fruit')
            ->and($namespace->description)->toBe('Fruit lookups')
            ->and($namespace->toArray())->toBe([
                'name' => 'fruit',
                'description' => 'Fruit lookups',
            ]);
    });

    it('omits a missing description when serialized', function () {
        expect((new ToolNamespace('fruit'))->toArray())->toBe(['name' => 'fruit']);
    });

    it('implements Arrayable', function () {
        expect(new ToolNamespace('fruit'))->toBeInstanceOf(Arrayable::class);
    });
});
