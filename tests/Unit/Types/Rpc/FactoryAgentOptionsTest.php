<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryAgentOptions;

describe('FactoryAgentOptions', function () {
    it('can be created from array', function () {
        $options = FactoryAgentOptions::fromArray([
            'label' => 'my-label',
            'model' => 'gpt-5',
        ]);

        expect($options->label)->toBe('my-label')
            ->and($options->model)->toBe('gpt-5')
            ->and($options->schema)->toBeNull();
    });

    it('defaults to null values', function () {
        $options = FactoryAgentOptions::fromArray([]);

        expect($options->label)->toBeNull()
            ->and($options->model)->toBeNull()
            ->and($options->schema)->toBeNull();
    });

    it('converts to array correctly', function () {
        $options = new FactoryAgentOptions(label: 'my-label', model: 'gpt-5');

        expect($options->toArray())->toBe([
            'label' => 'my-label',
            'model' => 'gpt-5',
        ]);
    });
});
