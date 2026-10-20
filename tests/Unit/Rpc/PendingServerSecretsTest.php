<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerSecrets;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesRequest;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesResult;

describe('PendingServerSecrets', function () {
    it('registers typed values through ServerRpc and hydrates the result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('secrets.addFilterValues', ['values' => ['oauth-token']])
            ->andReturn(['ok' => true]);

        $result = (new ServerRpc($client))->secrets()->addFilterValues(
            new SecretsAddFilterValuesRequest(['oauth-token']),
        );

        expect($result)
            ->toBeInstanceOf(SecretsAddFilterValuesResult::class)
            ->and($result->ok)->toBeTrue();
    });

    it('accepts array values and hydrates the result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('secrets.addFilterValues', ['values' => ['mcp-token', 'oidc-token']])
            ->andReturn(['ok' => false]);

        $result = (new PendingServerSecrets($client))->addFilterValues([
            'values' => ['mcp-token', 'oidc-token'],
        ]);

        expect($result)
            ->toBeInstanceOf(SecretsAddFilterValuesResult::class)
            ->and($result->ok)->toBeFalse();
    });
});
