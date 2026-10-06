<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Support\PermissionHandler;

function clientWithMockRpc(JsonRpcClient $rpc): Client
{
    $client = new Client;
    (new ReflectionProperty(Client::class, 'state'))->setValue($client, ConnectionState::CONNECTED);
    (new ReflectionProperty(Client::class, 'rpcClient'))->setValue($client, $rpc);

    return $client;
}

describe('session provider lifecycle', function () {
    it('rejects skill providers for cloud sessions before issuing a create request', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $rpc->shouldNotReceive('request');
        $client = clientWithMockRpc($rpc);

        expect(fn () => $client->createSession([
            'cloud' => [],
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'skillProvider' => [
                'listSkills' => fn () => [],
                'readSkill' => fn () => null,
            ],
        ]))->toThrow(InvalidArgumentException::class, 'Skill providers are not supported for cloud sessions');
    });

    it('removes pre-registered provider sessions when create fails', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $rpc->shouldReceive('request')
            ->once()
            ->with('session.create', Mockery::type('array'))
            ->andThrow(new JsonRpcException(-32000, 'create failed'));
        $client = clientWithMockRpc($rpc);

        expect(fn () => $client->createSession([
            'onPermissionRequest' => PermissionHandler::approveAll(),
            'skillProvider' => [
                'listSkills' => fn () => [],
                'readSkill' => fn () => null,
            ],
        ]))->toThrow(JsonRpcException::class, 'create failed');

        expect((new ReflectionProperty(Client::class, 'sessions'))->getValue($client))->toBe([]);
    });
});
