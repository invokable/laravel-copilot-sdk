<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingProvider;
use Revolution\Copilot\Rpc\PendingProviderModels;
use Revolution\Copilot\Rpc\PendingProviders;
use Revolution\Copilot\Rpc\PendingServerAgents;
use Revolution\Copilot\Rpc\PendingServerInstructions;
use Revolution\Copilot\Types\Rpc\AgentDiscoveryPathList;
use Revolution\Copilot\Types\Rpc\AgentsCustomAgentInitialModelDecisionRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetAvailableBuiltinsRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetBuiltinDefinitionRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetBuiltinListingDefinitionRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetDiscoveryPathsRequest;
use Revolution\Copilot\Types\Rpc\InstructionDiscoveryPathList;
use Revolution\Copilot\Types\Rpc\InstructionsGetDiscoveryPathsRequest;
use Revolution\Copilot\Types\Rpc\ModelProviderDiscoverRequest;
use Revolution\Copilot\Types\Rpc\ModelProviderInstanceReference;
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

    it('returns built-in agent metadata and applies custom-agent model decisions', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('agents.getBuiltins', [])
            ->andReturn([
                'names' => ['explore'],
                'disableableNames' => ['explore'],
                'yamlBasedNames' => ['explore'],
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('agents.getAvailableBuiltins', ['featureFlags' => ['flag' => true], 'context' => 'chat'])
            ->andReturn(['agents' => [['name' => 'explore', 'description' => 'Explore code']]]);
        $client->shouldReceive('request')
            ->once()
            ->with('agents.getBuiltinDefinition', ['name' => 'explore'])
            ->andReturn(['definitionJson' => '{"name":"explore"}']);
        $client->shouldReceive('request')
            ->once()
            ->with('agents.getBuiltinListingDefinition', ['name' => 'explore'])
            ->andReturn(['definitionJson' => '{"name":"explore"}']);
        $client->shouldReceive('request')
            ->once()
            ->with('agents.customAgentInitialModelDecision', [
                'agentModelsJson' => '["model-a","model-b"]',
                'availableModelsJson' => '[{"id":"model-b"}]',
            ])
            ->andReturn(['targetModel' => 'model-b', 'reasoningEffort' => 'high']);

        $pending = new PendingServerAgents($client);
        $builtins = $pending->getBuiltins();
        $available = $pending->getAvailableBuiltins(new AgentsGetAvailableBuiltinsRequest(
            featureFlags: ['flag' => true],
            context: 'chat',
        ));
        $definition = $pending->getBuiltinDefinition(new AgentsGetBuiltinDefinitionRequest('explore'));
        $listing = $pending->getBuiltinListingDefinition(new AgentsGetBuiltinListingDefinitionRequest('explore'));
        $decision = $pending->customAgentInitialModelDecision(new AgentsCustomAgentInitialModelDecisionRequest(
            agentModelsJson: '["model-a","model-b"]',
            availableModelsJson: '[{"id":"model-b"}]',
        ));

        expect($builtins->disableableNames)->toBe(['explore'])
            ->and($available->agents[0]->name)->toBe('explore')
            ->and($definition->definitionJson)->toBe('{"name":"explore"}')
            ->and($listing->definitionJson)->toBe('{"name":"explore"}')
            ->and($decision->targetModel)->toBe('model-b')
            ->and($decision->reasoningEffort)->toBe('high');
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

describe('PendingProviders', function () {
    it('discovers a provider and prepares a model configuration with the session id', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.providers.discover', [
                'adapterId' => 'ollama',
                'input' => ['endpoint' => 'http://localhost:11434'],
                'sessionId' => 'session-1',
            ])
            ->andReturn(['instances' => [], 'outcome' => ['code' => 'absent']]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.providers.models.prepareConfiguration', Mockery::on(
                fn (array $params) => $params['sessionId'] === 'session-1'
                    && $params['instance']['displayName'] === 'Local'
                    && $params['model']['id'] === 'model-x',
            ))
            ->andReturn([
                'provider' => ['name' => 'local'],
                'model' => ['id' => 'model-x'],
                'providerDisposition' => 'add',
                'modelDisposition' => 'add',
                'selectionId' => 'local/model-x',
            ]);

        $providers = new PendingProviders($client, 'session-1');
        $discovery = $providers->discover(new ModelProviderDiscoverRequest(
            adapterId: 'ollama',
            input: ['endpoint' => 'http://localhost:11434'],
        ));
        $plan = (new PendingProviderModels($client, 'session-1'))->prepareConfiguration(
            ['displayName' => 'Local', 'reference' => [
                'adapterId' => 'ollama',
                'id' => 'instance-1',
                'managementEndpoint' => 'http://localhost:11434',
                'providerKind' => 'ollama',
            ]],
            ['id' => 'model-x'],
        );

        expect($discovery->outcome?->code)->toBe('absent')
            ->and($plan->selectionId)->toBe('local/model-x');
    });

    it('sends provider instance references to status and model-list methods', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $reference = [
            'adapterId' => 'ollama',
            'id' => 'instance-1',
            'managementEndpoint' => 'http://localhost:11434',
            'providerKind' => 'ollama',
        ];
        $client->shouldReceive('request')
            ->once()
            ->with('session.providers.getStatus', [
                'sessionId' => 'session-2',
                'instance' => $reference,
            ])
            ->andReturn(['status' => 'ready', 'version' => '1.2']);
        $client->shouldReceive('request')
            ->once()
            ->with('session.providers.models.list', [
                'sessionId' => 'session-2',
                'instance' => $reference,
            ])
            ->andReturn(['models' => [['id' => 'model-x']]]);

        $providers = new PendingProviders($client, 'session-2');
        $providerReference = new ModelProviderInstanceReference(...$reference);
        $status = $providers->getStatus($providerReference);
        $models = $providers->models()->list($providerReference);

        expect($status->status)->toBe('ready')
            ->and($models->models[0]->id)->toBe('model-x');
    });
});
