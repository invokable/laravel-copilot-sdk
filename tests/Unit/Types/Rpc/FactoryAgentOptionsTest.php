<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\FactoryAgentOptions;

describe('FactoryAgentOptions', function () {
    it('can be created from array', function () {
        $options = FactoryAgentOptions::fromArray([
            'label' => 'my-label',
            'model' => 'gpt-5',
            'reasoningEffort' => 'high',
            'contextTier' => 'long_context',
            'agent' => 'reviewer',
        ]);

        expect($options->label)->toBe('my-label')
            ->and($options->model)->toBe('gpt-5')
            ->and($options->schema)->toBeNull()
            ->and($options->reasoningEffort)->toBe('high')
            ->and($options->contextTier)->toBe('long_context')
            ->and($options->agent)->toBe('reviewer');
    });

    it('defaults to null values', function () {
        $options = FactoryAgentOptions::fromArray([]);

        expect($options->label)->toBeNull()
            ->and($options->model)->toBeNull()
            ->and($options->schema)->toBeNull()
            ->and($options->reasoningEffort)->toBeNull()
            ->and($options->contextTier)->toBeNull()
            ->and($options->agent)->toBeNull();
    });

    it('converts to array correctly', function () {
        $options = new FactoryAgentOptions(
            label: 'my-label',
            model: 'gpt-5',
            reasoningEffort: 'high',
            contextTier: 'long_context',
            agent: 'reviewer',
        );

        expect($options->toArray())->toBe([
            'label' => 'my-label',
            'model' => 'gpt-5',
            'reasoningEffort' => 'high',
            'contextTier' => 'long_context',
            'agent' => 'reviewer',
        ]);
    });
});
