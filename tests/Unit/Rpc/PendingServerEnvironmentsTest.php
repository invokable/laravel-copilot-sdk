<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\EnvironmentKind;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerEnvironments;
use Revolution\Copilot\Types\Rpc\EnvironmentsDeleteRequest;
use Revolution\Copilot\Types\Rpc\EnvironmentsDeleteResult;
use Revolution\Copilot\Types\Rpc\EnvironmentsGetRequest;
use Revolution\Copilot\Types\Rpc\EnvironmentsGetResult;
use Revolution\Copilot\Types\Rpc\EnvironmentsListRequest;
use Revolution\Copilot\Types\Rpc\EnvironmentsListResult;
use Revolution\Copilot\Types\Rpc\GitHubEnvironment;

describe('PendingServerEnvironments', function () {
    it('lists environments without filters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('environments.list', [])
            ->andReturn(['environments' => []]);

        $result = (new PendingServerEnvironments($client))->list();

        expect($result)
            ->toBeInstanceOf(EnvironmentsListResult::class)
            ->and($result->environments)->toBeEmpty();
    });

    it('lists environments with typed filters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('environments.list', ['kind' => 'managed-cca', 'status' => 'online'])
            ->andReturn([
                'environments' => [
                    ['id' => 'env-1', 'kind' => 'managed-cca', 'name' => 'CI', 'status' => 'online'],
                ],
            ]);

        $result = (new PendingServerEnvironments($client))->list(
            new EnvironmentsListRequest(kind: EnvironmentKind::MANAGED_CCA, status: 'online'),
        );

        expect($result->environments[0])
            ->toBeInstanceOf(GitHubEnvironment::class)
            ->and($result->environments[0]->id)->toBe('env-1')
            ->and($result->environments[0]->kind)->toBe(EnvironmentKind::MANAGED_CCA);
    });

    it('lists environments with array filters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('environments.list', ['status' => 'offline'])
            ->andReturn(['environments' => []]);

        $result = (new PendingServerEnvironments($client))->list(['status' => 'offline']);

        expect($result)->toBeInstanceOf(EnvironmentsListResult::class);
    });

    it('gets an environment with a typed request', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('environments.get', ['environmentId' => 'env-1'])
            ->andReturn([
                'environment' => ['id' => 'env-1', 'kind' => 'user-local', 'name' => 'Local', 'status' => 'online'],
            ]);

        $result = (new PendingServerEnvironments($client))->get(new EnvironmentsGetRequest('env-1'));

        expect($result)
            ->toBeInstanceOf(EnvironmentsGetResult::class)
            ->and($result->environment)->toBeInstanceOf(GitHubEnvironment::class)
            ->and($result->environment->id)->toBe('env-1');
    });

    it('gets an environment with array parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('environments.get', ['environmentId' => 'env-2'])
            ->andReturn([
                'environment' => ['id' => 'env-2', 'kind' => 'user-codespace', 'name' => 'Codespace', 'status' => 'offline'],
            ]);

        $result = (new PendingServerEnvironments($client))->get(['environmentId' => 'env-2']);

        expect($result->environment->id)->toBe('env-2');
    });

    it('deletes an environment with a typed request', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('environments.delete', ['environmentId' => 'env-1'])
            ->andReturn([]);

        $result = (new PendingServerEnvironments($client))->delete(new EnvironmentsDeleteRequest('env-1'));

        expect($result)->toBeInstanceOf(EnvironmentsDeleteResult::class);
    });

    it('deletes an environment with array parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('environments.delete', ['environmentId' => 'env-2'])
            ->andReturn([]);

        $result = (new PendingServerEnvironments($client))->delete(['environmentId' => 'env-2']);

        expect($result)->toBeInstanceOf(EnvironmentsDeleteResult::class);
    });
});
