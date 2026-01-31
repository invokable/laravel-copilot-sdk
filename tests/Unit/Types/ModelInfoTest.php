<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Types\ModelBilling;
use Revolution\Copilot\Types\ModelCapabilities;
use Revolution\Copilot\Types\ModelInfo;
use Revolution\Copilot\Types\ModelPolicy;

describe('ModelInfo', function () {
    it('can be created from array with all fields', function () {
        $modelInfo = ModelInfo::fromArray([
            'id' => 'claude-sonnet-4.5',
            'name' => 'Claude Sonnet 4.5',
            'capabilities' => [
                'supports' => ['vision' => true],
                'limits' => ['max_context_window_tokens' => 200000],
            ],
            'policy' => [
                'state' => 'enabled',
                'terms' => 'standard',
            ],
            'billing' => [
                'multiplier' => 1.5,
            ],
        ]);

        expect($modelInfo->id)->toBe('claude-sonnet-4.5')
            ->and($modelInfo->name)->toBe('Claude Sonnet 4.5')
            ->and($modelInfo->capabilities)->toBeInstanceOf(ModelCapabilities::class)
            ->and($modelInfo->policy)->toBeInstanceOf(ModelPolicy::class)
            ->and($modelInfo->billing)->toBeInstanceOf(ModelBilling::class);
    });

    it('can be created from array with minimal fields', function () {
        $modelInfo = ModelInfo::fromArray([
            'id' => 'gpt-4',
            'name' => 'GPT-4',
            'capabilities' => [
                'supports' => ['vision' => false],
                'limits' => ['max_context_window_tokens' => 8000],
            ],
        ]);

        expect($modelInfo->id)->toBe('gpt-4')
            ->and($modelInfo->name)->toBe('GPT-4')
            ->and($modelInfo->capabilities)->toBeInstanceOf(ModelCapabilities::class)
            ->and($modelInfo->policy)->toBeNull()
            ->and($modelInfo->billing)->toBeNull();
    });

    it('can convert to array with all fields', function () {
        $modelInfo = new ModelInfo(
            id: 'test-model',
            name: 'Test Model',
            capabilities: new ModelCapabilities(
                supports: ['vision' => true],
                limits: ['max_context_window_tokens' => 100000],
            ),
            policy: new ModelPolicy(state: 'enabled', terms: 'premium'),
            billing: new ModelBilling(multiplier: 2.0),
        );

        expect($modelInfo->toArray())->toBe([
            'id' => 'test-model',
            'name' => 'Test Model',
            'capabilities' => [
                'supports' => ['vision' => true],
                'limits' => ['max_context_window_tokens' => 100000],
            ],
            'policy' => [
                'state' => 'enabled',
                'terms' => 'premium',
            ],
            'billing' => [
                'multiplier' => 2.0,
            ],
        ]);
    });

    it('filters null values in toArray', function () {
        $modelInfo = new ModelInfo(
            id: 'test-model',
            name: 'Test Model',
            capabilities: new ModelCapabilities(
                supports: [],
                limits: ['max_context_window_tokens' => 50000],
            ),
        );

        $array = $modelInfo->toArray();

        expect($array)->not->toHaveKey('policy')
            ->and($array)->not->toHaveKey('billing');
    });

    it('implements Arrayable interface', function () {
        $modelInfo = new ModelInfo(
            id: 'test',
            name: 'Test',
            capabilities: new ModelCapabilities(supports: [], limits: []),
        );

        expect($modelInfo)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });

    it('can handle supportedReasoningEfforts array', function () {
        $modelInfo = ModelInfo::fromArray([
            'id' => 'reasoning-model',
            'name' => 'Reasoning Model',
            'capabilities' => [
                'supports' => ['vision' => false, 'reasoningEffort' => true],
                'limits' => ['max_context_window_tokens' => 128000],
            ],
            'supportedReasoningEfforts' => ['low', 'medium', 'high'],
            'defaultReasoningEffort' => 'medium',
        ]);

        expect($modelInfo->supportedReasoningEfforts)->toBe(['low', 'medium', 'high'])
            ->and($modelInfo->defaultReasoningEffort)->toBe('medium');
    });

    it('can handle defaultReasoningEffort as enum', function () {
        $modelInfo = new ModelInfo(
            id: 'reasoning-model',
            name: 'Reasoning Model',
            capabilities: new ModelCapabilities(
                supports: ['reasoningEffort' => true],
                limits: ['max_context_window_tokens' => 128000],
            ),
            supportedReasoningEfforts: ['low', 'medium', 'high', 'xhigh'],
            defaultReasoningEffort: ReasoningEffort::HIGH,
        );

        expect($modelInfo->defaultReasoningEffort)->toBe(ReasoningEffort::HIGH);
    });

    it('converts defaultReasoningEffort enum to string in toArray', function () {
        $modelInfo = new ModelInfo(
            id: 'reasoning-model',
            name: 'Reasoning Model',
            capabilities: new ModelCapabilities(
                supports: ['reasoningEffort' => true],
                limits: ['max_context_window_tokens' => 128000],
            ),
            supportedReasoningEfforts: ['low', 'medium', 'high', 'xhigh'],
            defaultReasoningEffort: ReasoningEffort::XHIGH,
        );

        $array = $modelInfo->toArray();

        expect($array['defaultReasoningEffort'])->toBe('xhigh')
            ->and($array['supportedReasoningEfforts'])->toBe(['low', 'medium', 'high', 'xhigh']);
    });

    it('excludes reasoning effort fields when null', function () {
        $modelInfo = new ModelInfo(
            id: 'basic-model',
            name: 'Basic Model',
            capabilities: new ModelCapabilities(
                supports: ['vision' => false],
                limits: ['max_context_window_tokens' => 100000],
            ),
        );

        $array = $modelInfo->toArray();

        expect($array)->not->toHaveKey('supportedReasoningEfforts')
            ->and($array)->not->toHaveKey('defaultReasoningEffort');
    });
});
