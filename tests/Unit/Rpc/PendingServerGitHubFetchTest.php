<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\GitHubFetchOperation;
use Revolution\Copilot\Enums\GitHubFetchResultKind;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\GitHubFetchHeader;
use Revolution\Copilot\Types\Rpc\GitHubFetchOperationRequest;
use Revolution\Copilot\Types\Rpc\GitHubFetchRequest;
use Revolution\Copilot\Types\Rpc\GitHubFetchResult;

describe('PendingServerGitHubFetch', function () {
    it('dispatches authenticated GitHub fetch requests and returns typed results', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('gitHubFetch.allocateRequestId', [])
            ->andReturn(['requestId' => 42]);
        $client->shouldReceive('request')
            ->once()
            ->with('gitHubFetch.request', [
                'requestId' => 42,
                'sessionId' => 'session-1',
                'method' => 'GET',
                'url' => 'https://api.github.com/repos/octo/demo',
                'headers' => [['name' => 'accept', 'value' => 'application/json']],
                'apiName' => 'repositories.get',
            ])
            ->andReturn([
                'kind' => 'response',
                'status' => 200,
                'statusText' => 'OK',
                'url' => 'https://api.github.com/repos/octo/demo',
                'headers' => [['name' => 'content-type', 'value' => 'application/json']],
                'bodyBase64' => 'e30=',
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('gitHubFetch.executeOperation', [
                'requestId' => 43,
                'operation' => 'repository-access',
                'params' => ['owner' => 'octo', 'repo' => 'demo'],
                'sessionId' => 'session-1',
            ])
            ->andReturn([
                'kind' => 'failed',
                'message' => 'The operation failed.',
            ]);

        $fetch = (new ServerRpc($client))->gitHubFetch();
        $requestId = $fetch->allocateRequestId();
        $response = $fetch->request(new GitHubFetchRequest(
            requestId: $requestId->requestId,
            method: 'GET',
            url: 'https://api.github.com/repos/octo/demo',
            headers: [new GitHubFetchHeader('accept', 'application/json')],
            apiName: 'repositories.get',
            sessionId: 'session-1',
        ));
        $operation = $fetch->executeOperation(new GitHubFetchOperationRequest(
            requestId: 43,
            operation: GitHubFetchOperation::RepositoryAccess,
            params: ['owner' => 'octo', 'repo' => 'demo'],
            sessionId: 'session-1',
        ));

        expect($requestId->requestId)->toBe(42)
            ->and($response)->toBeInstanceOf(GitHubFetchResult::class)
            ->and($response->kind)->toBe(GitHubFetchResultKind::Response)
            ->and($response->headers[0]->name)->toBe('content-type')
            ->and($operation->kind)->toBe(GitHubFetchResultKind::Failed)
            ->and($operation->message)->toBe('The operation failed.');
    });
});
