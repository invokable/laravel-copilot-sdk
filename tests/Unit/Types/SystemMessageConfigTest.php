<?php

declare(strict_types=1);

use Revolution\Copilot\Types\SystemMessageConfig;

describe('SystemMessageConfig', function () {
    it('can be created from array with all fields', function () {
        $config = SystemMessageConfig::fromArray([
            'mode' => 'append',
            'content' => 'Additional instructions',
        ]);

        expect($config->mode)->toBe('append')
            ->and($config->content)->toBe('Additional instructions');
    });

    it('can be created from array with minimal fields', function () {
        $config = SystemMessageConfig::fromArray([]);

        expect($config->mode)->toBeNull()
            ->and($config->content)->toBeNull();
    });

    it('can be created with replace mode', function () {
        $config = new SystemMessageConfig(
            mode: 'replace',
            content: 'Full custom system message',
        );

        expect($config->mode)->toBe('replace')
            ->and($config->content)->toBe('Full custom system message');
    });

    it('can convert to array with all fields', function () {
        $config = new SystemMessageConfig(
            mode: 'append',
            content: 'Extra content',
        );

        expect($config->toArray())->toBe([
            'mode' => 'append',
            'content' => 'Extra content',
        ]);
    });

    it('filters null values in toArray', function () {
        $config = new SystemMessageConfig();

        expect($config->toArray())->toBe([]);
    });

    it('can have only mode', function () {
        $config = new SystemMessageConfig(mode: 'append');

        expect($config->toArray())->toBe(['mode' => 'append']);
    });

    it('can have only content', function () {
        $config = new SystemMessageConfig(content: 'Some content');

        expect($config->toArray())->toBe(['content' => 'Some content']);
    });

    it('implements Arrayable interface', function () {
        $config = new SystemMessageConfig();

        expect($config)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });
});
