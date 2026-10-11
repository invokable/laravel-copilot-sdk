<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerConnectors;
use Revolution\Copilot\Rpc\PendingServerGit;
use Revolution\Copilot\Rpc\PendingServerGitHubOwners;
use Revolution\Copilot\Rpc\PendingServerGitHubRepository;
use Revolution\Copilot\Rpc\PendingServerSandboxProxyCa;

describe('server Git, GitHub, connector, and sandbox RPC operations', function () {
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
});
