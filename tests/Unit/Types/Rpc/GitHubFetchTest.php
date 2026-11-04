<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\GitHubFetchOperation;
use Revolution\Copilot\Enums\GitHubFetchResultKind;
use Revolution\Copilot\Types\Rpc\GitHubFetchHeader;
use Revolution\Copilot\Types\Rpc\GitHubFetchHttpError;
use Revolution\Copilot\Types\Rpc\GitHubFetchOperationRequest;
use Revolution\Copilot\Types\Rpc\GitHubFetchPinnedAddress;
use Revolution\Copilot\Types\Rpc\GitHubFetchProxyEnvironment;
use Revolution\Copilot\Types\Rpc\GitHubFetchRequest;
use Revolution\Copilot\Types\Rpc\GitHubFetchRequestId;
use Revolution\Copilot\Types\Rpc\GitHubFetchResult;
use Revolution\Copilot\Types\Rpc\GitHubFetchRetryConfig;

describe('GitHubFetchRequest', function () {
    it('round-trips optional fetch configuration and nested types', function () {
        $request = new GitHubFetchRequest(
            requestId: 42,
            method: 'GET',
            url: 'https://api.github.com/repos/octo/demo',
            headers: [new GitHubFetchHeader('accept', 'application/json')],
            apiName: 'repositories.get',
            sessionId: 'session-1',
            authOwnerId: 'owner-1',
            bodyBase64: 'e30=',
            retryOptions: new GitHubFetchRetryConfig(
                maxRetries: 0,
                defaultRetryDelaySeconds: 1.5,
                backoffFactor: 2.0,
                enableCircuitBreaker: false,
            ),
            redirect: 'manual',
            timeoutMs: 0,
            readTimeoutMs: 150,
            bodyLimitBytes: 2048,
            disableConnectionReuse: false,
            proxyEnv: new GitHubFetchProxyEnvironment('http://proxy', 'https://proxy', 'localhost'),
            pinnedAddresses: [new GitHubFetchPinnedAddress('192.0.2.1')],
            allowLocalhost: false,
            proxyNegotiateAuth: true,
            proxyNegotiateSpn: 'HTTP/proxy',
        );

        $decoded = GitHubFetchRequest::fromArray($request->toArray());

        expect($decoded->toArray())->toBe($request->toArray())
            ->and($decoded->headers[0])->toBeInstanceOf(GitHubFetchHeader::class)
            ->and($decoded->retryOptions->defaultRetryDelaySeconds)->toBe(1.5)
            ->and($decoded->proxyEnv)->toBeInstanceOf(GitHubFetchProxyEnvironment::class)
            ->and($decoded->pinnedAddresses[0])->toBeInstanceOf(GitHubFetchPinnedAddress::class);
    });

    it('omits unset optional fields', function () {
        $request = new GitHubFetchRequest(42, 'GET', 'https://api.github.com', [], 'root');

        expect($request->toArray())->toBe([
            'requestId' => 42,
            'method' => 'GET',
            'url' => 'https://api.github.com',
            'headers' => [],
            'apiName' => 'root',
        ]);
    });
});

describe('GitHubFetchOperationRequest', function () {
    it('round-trips the operation enum and arguments', function () {
        $request = GitHubFetchOperationRequest::fromArray([
            'requestId' => 42,
            'sessionId' => 'session-1',
            'operation' => 'job-status',
            'params' => ['jobId' => 'job-1', 'optional' => null],
            'bodyJson' => '{"state":"queued"}',
        ]);

        expect($request->operation)->toBe(GitHubFetchOperation::JobStatus)
            ->and($request->toArray())->toBe([
                'requestId' => 42,
                'sessionId' => 'session-1',
                'operation' => 'job-status',
                'params' => ['jobId' => 'job-1'],
                'bodyJson' => '{"state":"queued"}',
            ]);
    });
});

describe('GitHubFetchResult', function () {
    it('round-trips response and classified failure payloads', function () {
        $response = GitHubFetchResult::fromArray([
            'kind' => 'response',
            'status' => 200,
            'statusText' => 'OK',
            'url' => 'https://api.github.com/repos/octo/demo',
            'headers' => [['name' => 'content-type', 'value' => 'application/json']],
            'bodyBase64' => 'e30=',
            'httpError' => ['message' => 'HTTP failure', 'responseBodyJson' => '{"message":"failure"}'],
        ]);
        $failure = GitHubFetchResult::fromArray([
            'kind' => 'circuit-breaker',
            'message' => 'Retry later.',
            'host' => 'api.github.com',
            'timeUntilRetryMs' => 500,
        ]);

        expect($response->kind)->toBe(GitHubFetchResultKind::Response)
            ->and($response->headers[0])->toBeInstanceOf(GitHubFetchHeader::class)
            ->and($response->httpError)->toBeInstanceOf(GitHubFetchHttpError::class)
            ->and($response->toArray())->toBe([
                'kind' => 'response',
                'bodyBase64' => 'e30=',
                'headers' => [['name' => 'content-type', 'value' => 'application/json']],
                'httpError' => ['message' => 'HTTP failure', 'responseBodyJson' => '{"message":"failure"}'],
                'status' => 200,
                'statusText' => 'OK',
                'url' => 'https://api.github.com/repos/octo/demo',
            ]);
        expect($failure->toArray())->toBe([
            'kind' => 'circuit-breaker',
            'host' => 'api.github.com',
            'message' => 'Retry later.',
            'timeUntilRetryMs' => 500,
        ]);
    });

    it('round-trips request ids', function () {
        expect(GitHubFetchRequestId::fromArray(['requestId' => 15])->toArray())
            ->toBe(['requestId' => 15]);
    });
});
