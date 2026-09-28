<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingProvider;
use Revolution\Copilot\Rpc\PendingServerAgents;
use Revolution\Copilot\Rpc\PendingServerInstructions;
use Revolution\Copilot\Types\Rpc\AgentDiscoveryPathList;
use Revolution\Copilot\Types\Rpc\AgentsGetDiscoveryPathsRequest;
use Revolution\Copilot\Types\Rpc\InstructionDiscoveryPathList;
use Revolution\Copilot\Types\Rpc\InstructionsGetDiscoveryPathsRequest;
use Revolution\Copilot\Types\Rpc\ProviderEndpoint;
use Revolution\Copilot\Types\Rpc\ProviderGetEndpointRequest;
use Revolution\Copilot\Types\Rpc\ServerAgentList;
use Revolution\Copilot\Types\Rpc\ServerInstructionSourceList;

describe('PendingServerAgents', function () {
    it('calls agents.discover and returns ServerAgentList', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('agents.discover', Mockery::type('array'))
            ->andReturn(['agents' => []]);

        $pending = new PendingServerAgents($client);
        $result = $pending->discover();

        expect($result)->toBeInstanceOf(ServerAgentList::class);
    });

    it('calls agents.getDiscoveryPaths and returns AgentDiscoveryPathList', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'agents.getDiscoveryPaths',
                Mockery::type('array'),
            )
            ->andReturn(['paths' => []]);

        $pending = new PendingServerAgents($client);
        $result = $pending->getDiscoveryPaths();

        expect($result)->toBeInstanceOf(AgentDiscoveryPathList::class);
    });

    it('accepts AgentsGetDiscoveryPathsRequest object', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('agents.getDiscoveryPaths', Mockery::type('array'))
            ->andReturn(['paths' => []]);

        $pending = new PendingServerAgents($client);
        $req = new AgentsGetDiscoveryPathsRequest(excludeHostAgents: true);
        $result = $pending->getDiscoveryPaths($req);

        expect($result)->toBeInstanceOf(AgentDiscoveryPathList::class);
    });
});

describe('PendingServerInstructions', function () {
    it('calls instructions.discover and returns ServerInstructionSourceList', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('instructions.discover', Mockery::type('array'))
            ->andReturn(['sources' => []]);

        $pending = new PendingServerInstructions($client);
        $result = $pending->discover();

        expect($result)->toBeInstanceOf(ServerInstructionSourceList::class);
    });

    it('calls instructions.getDiscoveryPaths and returns InstructionDiscoveryPathList', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'instructions.getDiscoveryPaths',
                Mockery::type('array'),
            )
            ->andReturn(['paths' => []]);

        $pending = new PendingServerInstructions($client);
        $result = $pending->getDiscoveryPaths();

        expect($result)->toBeInstanceOf(InstructionDiscoveryPathList::class);
    });

    it('accepts InstructionsGetDiscoveryPathsRequest object', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('instructions.getDiscoveryPaths', Mockery::type('array'))
            ->andReturn(['paths' => []]);

        $pending = new PendingServerInstructions($client);
        $req = new InstructionsGetDiscoveryPathsRequest(excludeHostInstructions: true);
        $result = $pending->getDiscoveryPaths($req);

        expect($result)->toBeInstanceOf(InstructionDiscoveryPathList::class);
    });
});

describe('PendingProvider', function () {
    it('calls session.provider.getEndpoint and returns ProviderEndpoint', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.provider.getEndpoint',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-xyz'),
            )
            ->andReturn([
                'baseUrl' => 'https://api.openai.com/v1',
                'type' => 'openai',
                'headers' => [],
            ]);

        $pending = new PendingProvider($client, 'session-xyz');
        $result = $pending->getEndpoint();

        expect($result)->toBeInstanceOf(ProviderEndpoint::class)
            ->and($result->baseUrl)->toBe('https://api.openai.com/v1')
            ->and($result->type)->toBe('openai');
    });

    it('accepts ProviderGetEndpointRequest object', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.provider.getEndpoint',
                Mockery::on(fn ($params) => isset($params['sessionId']) && isset($params['modelId'])),
            )
            ->andReturn([
                'baseUrl' => 'https://api.example.com',
                'type' => 'custom',
                'headers' => [],
            ]);

        $pending = new PendingProvider($client, 'session-abc');
        $req = new ProviderGetEndpointRequest(modelId: 'gpt-5');
        $result = $pending->getEndpoint($req);

        expect($result)->toBeInstanceOf(ProviderEndpoint::class);
    });

    it('accepts array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.provider.getEndpoint',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-def'),
            )
            ->andReturn(['baseUrl' => 'https://api.example.com', 'type' => 'custom', 'headers' => []]);

        $pending = new PendingProvider($client, 'session-def');
        $result = $pending->getEndpoint(['model' => 'claude-3']);

        expect($result)->toBeInstanceOf(ProviderEndpoint::class);
    });
});
