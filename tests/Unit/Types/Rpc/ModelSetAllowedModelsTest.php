<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ModelSetAllowedModelsRequest;
use Revolution\Copilot\Types\Rpc\ModelSetAllowedModelsResult;

describe('ModelSetAllowedModelsRequest', function () {
    it('can be created with allowed models', function () {
        $request = new ModelSetAllowedModelsRequest(allowedModels: ['gpt-5', 'claude-opus']);

        expect($request->allowedModels)->toBe(['gpt-5', 'claude-opus']);
    });

    it('handles default null value', function () {
        $request = new ModelSetAllowedModelsRequest;

        expect($request->allowedModels)->toBeNull();
    });

    it('always includes allowedModels key in toArray even when null', function () {
        $request = new ModelSetAllowedModelsRequest;

        expect($request->toArray())->toBe(['allowedModels' => null]);
    });

    it('can be created from array', function () {
        $request = ModelSetAllowedModelsRequest::fromArray([
            'allowedModels' => ['gpt-5'],
        ]);

        expect($request->allowedModels)->toBe(['gpt-5']);
    });

    it('roundtrips through fromArray/toArray', function () {
        $data = ['allowedModels' => ['gpt-5', 'claude-opus']];

        expect(ModelSetAllowedModelsRequest::fromArray($data)->toArray())->toBe($data);
    });
});

describe('ModelSetAllowedModelsResult', function () {
    it('can be created with all fields', function () {
        $result = new ModelSetAllowedModelsResult(
            allowedModels: ['gpt-5'],
            effectiveAllowedModels: ['gpt-5'],
            fallbackModel: 'gpt-4',
            modelId: 'gpt-5',
        );

        expect($result->allowedModels)->toBe(['gpt-5'])
            ->and($result->effectiveAllowedModels)->toBe(['gpt-5'])
            ->and($result->fallbackModel)->toBe('gpt-4')
            ->and($result->modelId)->toBe('gpt-5');
    });

    it('handles default values', function () {
        $result = new ModelSetAllowedModelsResult;

        expect($result->allowedModels)->toBeNull()
            ->and($result->effectiveAllowedModels)->toBeNull()
            ->and($result->fallbackModel)->toBeNull()
            ->and($result->modelId)->toBeNull();
    });

    it('can be created from array', function () {
        $result = ModelSetAllowedModelsResult::fromArray([
            'allowedModels' => ['gpt-5'],
            'modelId' => 'gpt-5',
        ]);

        expect($result->allowedModels)->toBe(['gpt-5'])
            ->and($result->modelId)->toBe('gpt-5')
            ->and($result->fallbackModel)->toBeNull();
    });

    it('serializes to array omitting null values', function () {
        $result = new ModelSetAllowedModelsResult;

        expect($result->toArray())->toBe([]);
    });

    it('roundtrips through fromArray/toArray', function () {
        $data = [
            'allowedModels' => ['gpt-5'],
            'effectiveAllowedModels' => ['gpt-5'],
            'fallbackModel' => 'gpt-4',
            'modelId' => 'gpt-5',
        ];

        expect(ModelSetAllowedModelsResult::fromArray($data)->toArray())->toBe($data);
    });
});
