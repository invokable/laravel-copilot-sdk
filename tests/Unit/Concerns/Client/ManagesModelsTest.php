<?php

declare(strict_types=1);

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Revolution\Copilot\Client;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\ModelInfo;

function createConnectedManagesModelsClient(JsonRpcClient $rpcClient, array $options = []): Client
{
    $client = new Client($options);

    (new ReflectionProperty(Client::class, 'rpcClient'))->setValue($client, $rpcClient);
    (new ReflectionProperty(Client::class, 'state'))->setValue($client, ConnectionState::CONNECTED);

    return $client;
}

describe('ManagesModels', function () {
    it('returns RPC models as model information objects', function () {
        $rpcClient = Mockery::mock(JsonRpcClient::class);
        $rpcClient->shouldReceive('request')
            ->once()
            ->with('models.list')
            ->andReturn(['models' => [[
                'id' => 'gpt-4.1',
                'name' => 'GPT-4.1',
                'capabilities' => [
                    'supports' => ['vision' => true],
                    'limits' => ['max_context_window_tokens' => 100000],
                ],
            ]]]);
        $lock = Mockery::mock(Lock::class);
        $lock->shouldReceive('block')
            ->once()
            ->with(5, Mockery::type(Closure::class))
            ->andReturnUsing(fn (int $seconds, Closure $callback): array => $callback());
        Cache::shouldReceive('lock')
            ->once()
            ->with('copilot-models-lock', 10)
            ->andReturn($lock);
        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(fn (string $key, DateTimeInterface $expiration, Closure $callback): bool => str_starts_with($key, 'copilot-models-cache:'))
            ->andReturnUsing(fn (string $key, DateTimeInterface $expiration, Closure $callback): array => $callback());
        Cache::shouldReceive('forget')->zeroOrMoreTimes();

        $client = createConnectedManagesModelsClient($rpcClient, ['model' => 'gpt-4.1']);
        $models = $client->listModels();

        expect($models)->toHaveCount(1)
            ->and($models[0])->toBeInstanceOf(ModelInfo::class)
            ->and($models[0]->id)->toBe('gpt-4.1')
            ->and($models[0]->capabilities->supportsVision())->toBeTrue();
    });

    it('returns an empty list when the RPC response has no models', function () {
        $rpcClient = Mockery::mock(JsonRpcClient::class);
        $rpcClient->shouldReceive('request')
            ->once()
            ->with('models.list')
            ->andReturn([]);
        $lock = Mockery::mock(Lock::class);
        $lock->shouldReceive('block')
            ->once()
            ->with(5, Mockery::type(Closure::class))
            ->andReturnUsing(fn (int $seconds, Closure $callback): array => $callback());
        Cache::shouldReceive('lock')
            ->once()
            ->with('copilot-models-lock', 10)
            ->andReturn($lock);
        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(fn (string $key, DateTimeInterface $expiration, Closure $callback): bool => str_starts_with($key, 'copilot-models-cache:'))
            ->andReturnUsing(fn (string $key, DateTimeInterface $expiration, Closure $callback): array => $callback());
        Cache::shouldReceive('forget')->zeroOrMoreTimes();

        $client = createConnectedManagesModelsClient($rpcClient);

        expect($client->listModels())->toBeEmpty();
    });
});
