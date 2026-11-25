<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\McpSecretsDeleteParams;
use Revolution\Copilot\Types\Rpc\McpSecretsDeleteServerParams;
use Revolution\Copilot\Types\Rpc\McpSecretsSaveParams;
use Revolution\Copilot\Types\Rpc\McpSecretsSaveResult;

describe('MCP secrets RPC', function () {
    it('saves credentials with typed and array parameters and returns the selected backend', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('mcp.secrets.save', ['secretId' => 'server:key', 'value' => 'example-value', 'configDir' => '/config'])
            ->andReturn(['usedKeychain' => true]);
        $client->shouldReceive('request')->once()
            ->with('mcp.secrets.save', ['secretId' => 'server:key', 'value' => 'example-value'])
            ->andReturn(['usedKeychain' => false]);

        $secrets = (new ServerRpc($client))->mcp()->secrets();
        $keychain = $secrets->save(new McpSecretsSaveParams('server:key', 'example-value', '/config'));
        $file = $secrets->save(['secretId' => 'server:key', 'value' => 'example-value']);

        expect($keychain)->toBeInstanceOf(McpSecretsSaveResult::class)
            ->and($keychain->usedKeychain)->toBeTrue()
            ->and($file->usedKeychain)->toBeFalse();
    });

    it('deletes credentials with typed and array parameters and returns void', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('mcp.secrets.delete', ['secretId' => 'server:key', 'configDir' => '/config'])
            ->andReturn([]);
        $client->shouldReceive('request')->once()
            ->with('mcp.secrets.delete', ['secretId' => 'server:key'])
            ->andReturn([]);
        $client->shouldReceive('request')->once()
            ->with('mcp.secrets.deleteServer', ['serverName' => 'server', 'configDir' => '/config'])
            ->andReturn([]);
        $client->shouldReceive('request')->once()
            ->with('mcp.secrets.deleteServer', ['serverName' => 'server'])
            ->andReturn([]);

        $secrets = (new ServerRpc($client))->mcp()->secrets();

        expect($secrets->delete(new McpSecretsDeleteParams('server:key', '/config')))->toBeNull()
            ->and($secrets->delete(['secretId' => 'server:key']))->toBeNull()
            ->and($secrets->deleteServer(new McpSecretsDeleteServerParams('server', '/config')))->toBeNull()
            ->and($secrets->deleteServer(['serverName' => 'server']))->toBeNull();
    });

    it('propagates JSON-RPC errors', function (string $method, array $params, string $wireMethod) {
        $client = Mockery::mock(JsonRpcClient::class);
        $exception = new JsonRpcException(-32000, 'Storage error');
        $client->shouldReceive('request')->once()->with($wireMethod, $params)->andThrow($exception);

        try {
            (new ServerRpc($client))->mcp()->secrets()->{$method}($params);
            test()->fail('Expected the JSON-RPC error to propagate.');
        } catch (JsonRpcException $caught) {
            expect($caught)->toBe($exception);
        }
    })->with([
        ['save', ['secretId' => 'server:key', 'value' => 'example-value'], 'mcp.secrets.save'],
        ['delete', ['secretId' => 'server:key'], 'mcp.secrets.delete'],
        ['deleteServer', ['serverName' => 'server'], 'mcp.secrets.deleteServer'],
    ]);
});
