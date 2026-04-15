<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerModels;
use Revolution\Copilot\Types\Rpc\ModelList;

describe('PendingServerModels', function () {
    it('calls models.list and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('models.list', [])
            ->andReturn([
                'models' => [
                    [
                        'id' => 'claude-sonnet-4.5',
                        'name' => 'Claude Sonnet 4.5',
                        'capabilities' => [
                            'supports' => ['vision' => false],
                            'limits' => ['max_context_window_tokens' => 200000],
                        ],
                    ],
                ],
            ]);

        $pending = new PendingServerModels($client);
        $result = $pending->list();

        expect($result)->toBeInstanceOf(ModelList::class)
            ->and($result->models)->toHaveCount(1)
            ->and($result->models[0]->id)->toBe('claude-sonnet-4.5')
            ->and($result->models[0]->name)->toBe('Claude Sonnet 4.5');
    });

    it('calls models.list and returns empty models', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('models.list', [])
            ->andReturn(['models' => []]);

        $pending = new PendingServerModels($client);
        $result = $pending->list();

        expect($result)->toBeInstanceOf(ModelList::class)
            ->and($result->models)->toBeEmpty();
    });
});
