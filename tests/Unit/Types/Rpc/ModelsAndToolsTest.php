<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ImageToolModelAvailability;
use Revolution\Copilot\Enums\ImageToolModelOperation;
use Revolution\Copilot\Types\ModelInfo;
use Revolution\Copilot\Types\Rpc\ImageToolModel;
use Revolution\Copilot\Types\Rpc\ImageToolModelPricing;
use Revolution\Copilot\Types\Rpc\ModelList;
use Revolution\Copilot\Types\Rpc\ToolList;
use Revolution\Copilot\Types\Rpc\ToolsListRequest;

describe('ModelList', function () {
    it('can be created from array', function () {
        $result = ModelList::fromArray([
            'models' => [
                [
                    'id' => 'gpt-4',
                    'name' => 'GPT-4',
                    'capabilities' => [
                        'supports' => ['vision' => true, 'reasoningEffort' => false],
                        'limits' => ['max_context_window_tokens' => 128000],
                    ],
                ],
            ],
        ]);

        expect($result->models)->toHaveCount(1)
            ->and($result->models[0])->toBeInstanceOf(ModelInfo::class)
            ->and($result->models[0]->id)->toBe('gpt-4');
    });

    it('preserves absent and empty image model catalogs distinctly', function () {
        $result = ModelList::fromArray(['models' => []]);

        expect($result->models)->toBe([])
            ->and($result->imageModels)->toBeNull()
            ->and($result->toArray())->not->toHaveKey('imageModels');

        $withEmptyCatalog = ModelList::fromArray(['models' => [], 'imageModels' => []]);

        expect($withEmptyCatalog->imageModels)->toBe([])
            ->and($withEmptyCatalog->toArray())->toHaveKey('imageModels', []);
    });

    it('parses and serializes image model metadata separately from chat models', function () {
        $result = ModelList::fromArray([
            'models' => [
                [
                    'id' => 'gpt-4',
                    'name' => 'GPT-4',
                    'capabilities' => [
                        'supports' => ['vision' => true, 'reasoningEffort' => false],
                        'limits' => ['max_context_window_tokens' => 128000],
                    ],
                ],
            ],
            'imageModels' => [
                [
                    'id' => 'gpt-image-1',
                    'name' => 'GPT Image 1',
                    'supportedOperations' => ['generate', 'edit'],
                    'isDefault' => true,
                    'availability' => 'available',
                    'pricing' => [
                        'batchSize' => 1000000,
                        'textInputPrice' => 0,
                        'textOutputPrice' => 2.5,
                        'imageInputPrice' => 0.0,
                    ],
                ],
                [
                    'id' => 'image-unavailable',
                    'name' => 'Unavailable Image Model',
                    'supportedOperations' => ['generate'],
                    'isDefault' => false,
                    'availability' => 'unavailable',
                ],
            ],
        ]);

        $imageModel = $result->imageModels[0];
        $unavailableModel = $result->imageModels[1];
        $array = $result->toArray();

        expect($array['models'])->toHaveCount(1)
            ->and($result->models[0]->id)->toBe('gpt-4')
            ->and($imageModel)->toBeInstanceOf(ImageToolModel::class)
            ->and($imageModel->id)->toBe('gpt-image-1')
            ->and($imageModel->supportedOperations)->toBe([
                ImageToolModelOperation::Generate,
                ImageToolModelOperation::Edit,
            ])
            ->and($imageModel->isDefault)->toBeTrue()
            ->and($imageModel->availability)->toBe(ImageToolModelAvailability::Available)
            ->and($imageModel->pricing)->toBeInstanceOf(ImageToolModelPricing::class)
            ->and($imageModel->pricing->textInputPrice)->toBe(0.0)
            ->and($imageModel->pricing->imageInputPrice)->toBe(0.0)
            ->and($imageModel->pricing->imageOutputPrice)->toBeNull()
            ->and($unavailableModel->availability)->toBe(ImageToolModelAvailability::Unavailable)
            ->and($unavailableModel->isDefault)->toBeFalse()
            ->and($unavailableModel->pricing)->toBeNull()
            ->and($array['imageModels'])->toBe([
                [
                    'id' => 'gpt-image-1',
                    'name' => 'GPT Image 1',
                    'supportedOperations' => ['generate', 'edit'],
                    'isDefault' => true,
                    'availability' => 'available',
                    'pricing' => [
                        'batchSize' => 1000000,
                        'textInputPrice' => 0.0,
                        'textOutputPrice' => 2.5,
                        'imageInputPrice' => 0.0,
                    ],
                ],
                [
                    'id' => 'image-unavailable',
                    'name' => 'Unavailable Image Model',
                    'supportedOperations' => ['generate'],
                    'isDefault' => false,
                    'availability' => 'unavailable',
                ],
            ]);
    });
});

describe('ToolList', function () {
    it('can be created from array', function () {
        $result = ToolList::fromArray([
            'tools' => [
                [
                    'name' => 'bash',
                    'description' => 'Execute bash commands',
                    'parameters' => ['type' => 'object'],
                ],
            ],
        ]);

        expect($result->tools)->toHaveCount(1)
            ->and($result->tools[0]['name'])->toBe('bash');
    });

    it('handles empty tools list', function () {
        $result = ToolList::fromArray([]);

        expect($result->tools)->toBe([]);
    });
});

describe('ToolsListRequest', function () {
    it('can be created with model', function () {
        $params = new ToolsListRequest(model: 'gpt-4');

        expect($params->toArray())->toBe(['model' => 'gpt-4']);
    });

    it('filters null model', function () {
        $params = new ToolsListRequest;

        expect($params->toArray())->toBe([]);
    });
});
