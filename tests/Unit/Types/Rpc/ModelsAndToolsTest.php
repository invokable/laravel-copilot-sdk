<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ModelInfo;
use Revolution\Copilot\Types\Rpc\ModelsListResult;
use Revolution\Copilot\Types\Rpc\ToolsListParams;
use Revolution\Copilot\Types\Rpc\ToolsListResult;

describe('ModelsListResult', function () {
    it('can be created from array', function () {
        $result = ModelsListResult::fromArray([
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

    it('handles empty models list', function () {
        $result = ModelsListResult::fromArray(['models' => []]);

        expect($result->models)->toBe([]);
    });

    it('can convert to array', function () {
        $result = ModelsListResult::fromArray([
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

        $array = $result->toArray();

        expect($array['models'])->toHaveCount(1)
            ->and($array['models'][0]['id'])->toBe('gpt-4');
    });
});

describe('ToolsListResult', function () {
    it('can be created from array', function () {
        $result = ToolsListResult::fromArray([
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
        $result = ToolsListResult::fromArray([]);

        expect($result->tools)->toBe([]);
    });
});

describe('ToolsListParams', function () {
    it('can be created with model', function () {
        $params = new ToolsListParams(model: 'gpt-4');

        expect($params->toArray())->toBe(['model' => 'gpt-4']);
    });

    it('filters null model', function () {
        $params = new ToolsListParams;

        expect($params->toArray())->toBe([]);
    });
});
