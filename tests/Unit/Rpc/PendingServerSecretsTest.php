<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SecretFilterFormat;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerSecrets;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesRequest;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesResult;
use Revolution\Copilot\Types\Rpc\SecretsFilterRequest;
use Revolution\Copilot\Types\Rpc\SecretsFilterResult;
use Revolution\Copilot\Types\Rpc\SecretsRemoveFilterValuesRequest;
use Revolution\Copilot\Types\Rpc\SecretsRemoveFilterValuesResult;

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

    it('filters typed JSON content through ServerRpc', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('secrets.filter', ['text' => '{"token":"example"}', 'format' => 'json'])
            ->andReturn(['value' => '{"token":"***"}', 'changed' => true]);

        $result = (new ServerRpc($client))->secrets()->filter(
            new SecretsFilterRequest('{"token":"example"}', SecretFilterFormat::Json),
        );

        expect($result)->toBeInstanceOf(SecretsFilterResult::class)
            ->and($result->value)->toBe('{"token":"***"}')
            ->and($result->changed)->toBeTrue();
    });

    it('filters array content without sending an omitted format', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('secrets.filter', ['text' => 'safe'])
            ->andReturn(['value' => 'safe', 'changed' => false]);

        $result = (new PendingServerSecrets($client))->filter(['text' => 'safe']);

        expect($result)->toBeInstanceOf(SecretsFilterResult::class)
            ->and($result->value)->toBe('safe')
            ->and($result->changed)->toBeFalse();
    });

    it('removes typed and array values through the secrets RPC', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->twice()
            ->with('secrets.removeFilterValues', ['values' => ['example']])
            ->andReturn(['ok' => true]);

        $secrets = (new ServerRpc($client))->secrets();
        $typed = $secrets->removeFilterValues(new SecretsRemoveFilterValuesRequest(['example']));
        $array = $secrets->removeFilterValues(['values' => ['example']]);

        expect($typed)->toBeInstanceOf(SecretsRemoveFilterValuesResult::class)
            ->and($typed->ok)->toBeTrue()
            ->and($array)->toBeInstanceOf(SecretsRemoveFilterValuesResult::class)
            ->and($array->ok)->toBeTrue();
    });
});
