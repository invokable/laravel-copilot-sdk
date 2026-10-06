<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingCustomizations;
use Revolution\Copilot\Rpc\PendingMcp;
use Revolution\Copilot\Rpc\PendingProviderModels;
use Revolution\Copilot\Rpc\PendingProviders;
use Revolution\Copilot\Rpc\PendingServerAgents;
use Revolution\Copilot\Rpc\PendingServerConnectors;
use Revolution\Copilot\Rpc\PendingServerGit;
use Revolution\Copilot\Rpc\PendingServerGitHubOwners;
use Revolution\Copilot\Rpc\PendingServerGitHubRepository;
use Revolution\Copilot\Rpc\PendingServerSandboxProxyCa;
use Revolution\Copilot\Rpc\PendingSessions;
use Revolution\Copilot\Rpc\PendingUi;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\AgentsCustomAgentInitialModelDecisionRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetBuiltinDefinitionRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetBuiltinListingDefinitionRequest;
use Revolution\Copilot\Types\Rpc\AgentsGetAvailableBuiltinsRequest;
use Revolution\Copilot\Types\Rpc\CustomizationsReloadResult;
use Revolution\Copilot\Types\Rpc\ModelProviderDiscoverRequest;
use Revolution\Copilot\Types\Rpc\ModelProviderInstanceReference;
use Revolution\Copilot\Transport\StdioTransport;

describe('upstream generated RPC additions', function () {
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

    it('returns the detailed customization reload outcome', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.customizations.reload', ['sessionId' => 'session-3'])
            ->andReturn([
                'errors' => [],
                'outcomes' => [['subsystem' => 'agents', 'status' => 'reloaded']],
                'warnings' => ['No project instructions found'],
            ]);

        $result = (new PendingCustomizations($client, 'session-3'))->reload();

        expect($result)->toBeInstanceOf(CustomizationsReloadResult::class)
            ->and($result->outcomes[0]->subsystem)->toBe('agents')
            ->and($result->warnings)->toHaveCount(1);
    });

    it('exposes new server groups and dispatches their RPC methods', function () {
        $rpc = new ServerRpc(new JsonRpcClient(new StdioTransport(
            fopen('php://memory', 'r'),
            fopen('php://memory', 'w'),
        )));

        expect($rpc->connectors())->toBeInstanceOf(PendingServerConnectors::class)
            ->and($rpc->git())->toBeInstanceOf(PendingServerGit::class)
            ->and($rpc->gitHubRepository())->toBeInstanceOf(PendingServerGitHubRepository::class)
            ->and($rpc->gitHubOwners())->toBeInstanceOf(PendingServerGitHubOwners::class)
            ->and($rpc->sandboxProxyCa())->toBeInstanceOf(PendingServerSandboxProxyCa::class);
    });

    it('dispatches GitHub owner and server connector discovery requests', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()->with('gitHubOwners.nextRequestId', [])->andReturn(['requestId' => 42]);
        $client->shouldReceive('request')
            ->once()
            ->with('gitHubOwners.list', ['requestId' => 42, 'authInfo' => ['token' => 'redacted']])
            ->andReturn(['owners' => [['login' => 'org', 'type' => 'Organization']]]);
        $client->shouldReceive('request')
            ->once()
            ->with('connectors.list', ['accountId' => 'account-1'])
            ->andReturn(['connectors' => []]);

        $owners = new PendingServerGitHubOwners($client);
        $id = $owners->nextRequestId();
        $list = $owners->list(['requestId' => $id->requestId, 'authInfo' => ['token' => 'redacted']]);
        $catalog = (new PendingServerConnectors($client))->list('account-1');

        expect($list->owners[0]->login)->toBe('org')
            ->and($catalog)->toBe(['connectors' => []]);
    });

    it('routes Git, GitHub, connector, and sandbox certificate server operations', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('git.workingDirectoryContext', ['cwd' => '/repo'])
            ->andReturn(['cwd' => '/repo', 'branch' => 'main']);
        $client->shouldReceive('request')->once()
            ->with('git.reposFromRemotes', ['cwd' => '/repo'])
            ->andReturn(['repositories' => [['host' => 'github.com', 'owner' => 'org', 'name' => 'repo', 'remoteName' => 'origin']]]);
        $client->shouldReceive('request')->once()
            ->with('gitHubRepository.atPath', ['path' => '/repo'])
            ->andReturn(['repository' => ['host' => 'github.com', 'owner' => 'org', 'name' => 'repo']]);
        $client->shouldReceive('request')->once()
            ->with('gitHubOwners.cancel', ['requestId' => 42])
            ->andReturn(['canceled' => true]);
        $client->shouldReceive('request')->once()
            ->with('connectors.getCapabilities', [])
            ->andReturn(['availability' => 'available']);
        $client->shouldReceive('request')->once()
            ->with('connectors.getAccounts', [])
            ->andReturn(['accounts' => []]);
        $client->shouldReceive('request')->once()
            ->with('connectors.refresh', ['accountId' => 'account-1'])
            ->andReturn(['connectors' => []]);
        $client->shouldReceive('request')->once()
            ->with('sandbox.proxyCa.create', ['sandboxConfig' => ['enabled' => true]])
            ->andReturn(['certificatePath' => '/tmp/ca.pem']);
        $client->shouldReceive('request')->once()
            ->with('sandbox.proxyCa.rotate', [])
            ->andReturn(['state' => 'present']);
        $client->shouldReceive('request')->once()
            ->with('sandbox.proxyCa.trust', [])
            ->andReturn(['state' => 'trusted']);
        $client->shouldReceive('request')->once()
            ->with('sandbox.proxyCa.remove', [])
            ->andReturn(['state' => 'absent']);

        $git = new PendingServerGit($client);
        $context = $git->workingDirectoryContext('/repo');
        $repositories = $git->reposFromRemotes('/repo');
        $repository = (new PendingServerGitHubRepository($client))->atPath(['path' => '/repo']);
        $canceled = (new PendingServerGitHubOwners($client))->cancel(['requestId' => 42]);
        $connectors = new PendingServerConnectors($client);
        $capabilities = $connectors->getCapabilities();
        $accounts = $connectors->getAccounts();
        $connectors->refresh('account-1');
        $proxyCa = new PendingServerSandboxProxyCa($client);
        $created = $proxyCa->create(['enabled' => true]);
        $rotated = $proxyCa->rotate();
        $trusted = $proxyCa->trust();
        $removed = $proxyCa->remove();

        expect($context->branch)->toBe('main')
            ->and($repositories->repositories[0]->remoteName)->toBe('origin')
            ->and($repository->repository?->name)->toBe('repo')
            ->and($canceled->canceled)->toBeTrue()
            ->and($capabilities['availability'])->toBe('available')
            ->and($accounts['accounts'])->toBe([])
            ->and($created->certificatePath)->toBe('/tmp/ca.pem')
            ->and($rotated->state)->toBe('present')
            ->and($trusted->state)->toBe('trusted')
            ->and($removed->state)->toBe('absent');
    });

    it('forwards new server and session-scoped method params without losing session scope', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.getStatus', ['sandboxConfig' => ['enabled' => true]])
            ->andReturn(['state' => 'present', 'canInstall' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('git.currentBranchRemote', ['cwd' => '/repo'])
            ->andReturn(['remote' => 'origin']);
        $client->shouldReceive('request')
            ->once()
            ->with('session.ui.handleHumanAskUser', ['requestId' => 'ask-1', 'sessionId' => 'session-4'])
            ->andReturn(['success' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.loadWorkspace', ['sessionId' => 'session-4', 'sessionsHome' => '/sessions'])
            ->andReturn(['workspaceJson' => '{"id":"workspace-1"}']);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.createWorkspace', [
                'sessionId' => 'session-5',
                'convention' => 'copilot',
                'context' => ['cwd' => '/repo'],
                'sessionStatePath' => '/sessions/session-5',
            ])
            ->andReturn(['workspaceJson' => '{"id":"workspace-2"}']);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.updateWorkspaceFields', [
                'sessionId' => 'session-5',
                'sessionsHome' => '/sessions',
                'fieldsJson' => '{"branch":"main"}',
            ])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.listConfigured', ['sessionId' => 'session-4'])
            ->andReturn(['servers' => []]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.setConnectedIdeInfo', [
                'ideInfo' => ['name' => 'editor'],
                'sessionId' => 'session-4',
            ])
            ->andReturn([]);

        $status = (new PendingServerSandboxProxyCa($client))->getStatus(['enabled' => true]);
        $remote = (new PendingServerGit($client))->currentBranchRemote('/repo');
        $ui = (new PendingUi($client, 'session-4'))->handleHumanAskUser(['requestId' => 'ask-1']);
        $workspace = (new PendingSessions($client))->loadWorkspace([
            'sessionId' => 'session-4',
            'sessionsHome' => '/sessions',
        ]);
        $sessions = new PendingSessions($client);
        $createdWorkspace = $sessions->createWorkspace([
            'sessionId' => 'session-5',
            'convention' => 'copilot',
            'context' => ['cwd' => '/repo'],
            'sessionStatePath' => '/sessions/session-5',
        ]);
        $sessions->updateWorkspaceFields([
            'sessionId' => 'session-5',
            'sessionsHome' => '/sessions',
            'fieldsJson' => '{"branch":"main"}',
        ]);
        $mcp = new PendingMcp($client, 'session-4');
        $configured = $mcp->listConfigured();
        $mcp->setConnectedIdeInfo(['ideInfo' => ['name' => 'editor']]);

        expect($status->state)->toBe('present')
            ->and($remote->remote)->toBe('origin')
            ->and($ui['success'])->toBeTrue()
            ->and($workspace->workspaceJson)->toBe('{"id":"workspace-1"}')
            ->and($createdWorkspace->workspaceJson)->toBe('{"id":"workspace-2"}')
            ->and($configured)->toBe(['servers' => []]);
    });
});
