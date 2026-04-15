<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ModelInfo;
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

    it('handles empty models list', function () {
        $result = ModelList::fromArray(['models' => []]);

        expect($result->models)->toBe([]);
    });

    it('can convert to array', function () {
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

        $array = $result->toArray();

        expect($array['models'])->toHaveCount(1)
            ->and($array['models'][0]['id'])->toBe('gpt-4');
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
