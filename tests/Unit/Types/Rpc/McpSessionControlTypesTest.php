<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\McpCancelSamplingExecutionParams;
use Revolution\Copilot\Types\Rpc\McpCancelSamplingExecutionResult;
use Revolution\Copilot\Types\Rpc\McpExecuteSamplingParams;
use Revolution\Copilot\Types\Rpc\McpOauthProbeRequest;
use Revolution\Copilot\Types\Rpc\McpOauthProbeResult;
use Revolution\Copilot\Types\Rpc\McpRemoveGitHubResult;
use Revolution\Copilot\Types\Rpc\McpRestartServerRequest;
use Revolution\Copilot\Types\Rpc\McpSamplingExecutionResult;
use Revolution\Copilot\Types\Rpc\McpServerValue;
use Revolution\Copilot\Types\Rpc\McpSetEnvValueModeParams;
use Revolution\Copilot\Types\Rpc\McpSetEnvValueModeResult;
use Revolution\Copilot\Types\Rpc\McpStartServerRequest;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthCancelLoginRequest;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthCancelLoginResult;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthPrepareLoginRequest;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthPrepareLoginResult;

describe('MCP session control RPC types', function () {
    it('preserves configuration and installation identity in server lifecycle requests', function () {
        $config = ['type' => 'http', 'url' => 'https://mcp.example.com', 'custom' => ['enabled' => true]];
        $start = McpStartServerRequest::fromArray([
            'serverName' => 'docs',
            'config' => $config,
            'expectedInstallationId' => 'installation-1',
        ]);
        $restart = new McpRestartServerRequest(
            serverName: 'docs',
            config: new McpServerValue(type: 'http', url: 'https://mcp.example.com'),
            expectedInstallationId: 'installation-1',
        );

        expect($start->toArray())->toBe([
            'serverName' => 'docs',
            'config' => $config,
            'expectedInstallationId' => 'installation-1',
        ])->and($restart->toArray())->toBe([
            'serverName' => 'docs',
            'config' => ['type' => 'http', 'url' => 'https://mcp.example.com'],
            'expectedInstallationId' => 'installation-1',
        ]);
    });

    it('omits nullable server lifecycle request fields', function () {
        $start = McpStartServerRequest::fromArray(['serverName' => 'docs']);
        $restart = McpRestartServerRequest::fromArray(['serverName' => 'docs']);

        expect($start->toArray())->toBe(['serverName' => 'docs'])
            ->and($restart->toArray())->toBe(['serverName' => 'docs']);
    });

    it('round trips environment value mode parameters and results', function () {
        $params = McpSetEnvValueModeParams::fromArray(['mode' => 'indirect']);
        $result = McpSetEnvValueModeResult::fromArray(['mode' => 'direct']);

        expect($params->mode)->toBe('indirect')
            ->and($params->toArray())->toBe(['mode' => 'indirect'])
            ->and($result->mode)->toBe('direct')
            ->and($result->toArray())->toBe(['mode' => 'direct']);
    });

    it('maps the GitHub MCP removal result', function () {
        $result = McpRemoveGitHubResult::fromArray(['removed' => false]);

        expect($result->removed)->toBeFalse()
            ->and($result->toArray())->toBe(['removed' => false]);
    });

    it('omits an unspecified installation identity from OAuth probe requests', function () {
        $request = McpOauthProbeRequest::fromArray(['serverName' => 'docs']);

        expect($request->toArray())->toBe(['serverName' => 'docs']);
    });

    it('round trips OAuth probe outcomes and optional details', function () {
        $result = McpOauthProbeResult::fromArray([
            'status' => 'needs-auth',
            'httpResponse' => ['status' => 401],
            'reason' => 'missing-token',
            'wwwAuthenticateParams' => ['scope' => 'read'],
        ]);

        expect($result->status)->toBe('needs-auth')
            ->and($result->httpResponse)->toBe(['status' => 401])
            ->and($result->reason)->toBe('missing-token')
            ->and($result->wwwAuthenticateParams)->toBe(['scope' => 'read'])
            ->and($result->toArray())->toBe([
                'status' => 'needs-auth',
                'httpResponse' => ['status' => 401],
                'reason' => 'missing-token',
                'wwwAuthenticateParams' => ['scope' => 'read'],
            ]);
    });

    it('round trips prepared OAuth login requests and results', function () {
        $request = SessionMcpOauthPrepareLoginRequest::fromArray([
            'serverName' => 'docs',
            'expectedInstallationId' => 'installation-1',
            'forceReauth' => false,
            'clientName' => 'Docs',
            'callbackSuccessMessage' => 'Signed in',
        ]);
        $result = SessionMcpOauthPrepareLoginResult::fromArray([
            'loginId' => 'login-1',
            'expiresAt' => '2026-10-13T14:00:00.000Z',
        ]);

        expect($request->toArray())->toBe([
            'serverName' => 'docs',
            'expectedInstallationId' => 'installation-1',
            'forceReauth' => false,
            'clientName' => 'Docs',
            'callbackSuccessMessage' => 'Signed in',
        ])->and($result->loginId)->toBe('login-1')
            ->and($result->expiresAt)->toBe('2026-10-13T14:00:00.000Z')
            ->and($result->toArray())->toBe([
                'loginId' => 'login-1',
                'expiresAt' => '2026-10-13T14:00:00.000Z',
            ]);
    });

    it('round trips OAuth login cancellation requests and results', function () {
        $request = SessionMcpOauthCancelLoginRequest::fromArray([
            'expectedInstallationId' => 'installation-1',
            'loginId' => 'login-1',
        ]);
        $result = SessionMcpOauthCancelLoginResult::fromArray(['cancelled' => true]);

        expect($request->toArray())->toBe([
            'expectedInstallationId' => 'installation-1',
            'loginId' => 'login-1',
        ])->and($result->cancelled)->toBeTrue()
            ->and($result->toArray())->toBe(['cancelled' => true]);
    });

    it('round trips MCP sampling requests and execution outcomes', function () {
        $request = McpExecuteSamplingParams::fromArray([
            'requestId' => 'sampling-1',
            'serverName' => 'docs',
            'mcpRequestId' => 42,
            'request' => ['messages' => [['role' => 'user', 'content' => 'Summarize']]],
        ]);
        $result = McpSamplingExecutionResult::fromArray([
            'action' => 'success',
            'result' => ['model' => 'gpt-5', 'content' => ['type' => 'text', 'text' => 'Summary']],
        ]);

        expect($request->toArray())->toBe([
            'requestId' => 'sampling-1',
            'serverName' => 'docs',
            'mcpRequestId' => 42,
            'request' => ['messages' => [['role' => 'user', 'content' => 'Summarize']]],
        ])->and($result->action)->toBe('success')
            ->and($result->result)->toBe(['model' => 'gpt-5', 'content' => ['type' => 'text', 'text' => 'Summary']])
            ->and($result->toArray())->toBe([
                'action' => 'success',
                'result' => ['model' => 'gpt-5', 'content' => ['type' => 'text', 'text' => 'Summary']],
            ]);
    });

    it('preserves JSON-valued MCP request IDs and requires the ID field', function () {
        $nullIdRequest = McpExecuteSamplingParams::fromArray([
            'requestId' => 'sampling-2',
            'serverName' => 'docs',
            'mcpRequestId' => null,
            'request' => [],
        ]);
        $structuredIdRequest = McpExecuteSamplingParams::fromArray([
            'requestId' => 'sampling-3',
            'serverName' => 'docs',
            'mcpRequestId' => ['request' => 1],
            'request' => [],
        ]);

        expect($nullIdRequest->toArray())->toBe([
            'requestId' => 'sampling-2',
            'serverName' => 'docs',
            'mcpRequestId' => null,
            'request' => [],
        ]);
        expect($structuredIdRequest->mcpRequestId)->toBe(['request' => 1]);
        expect(fn () => McpExecuteSamplingParams::fromArray([
            'requestId' => 'sampling-2',
            'serverName' => 'docs',
            'request' => [],
        ]))->toThrow(InvalidArgumentException::class, 'The mcpRequestId field is required.');
    });

    it('round trips sampling cancellation requests and results', function () {
        $request = McpCancelSamplingExecutionParams::fromArray(['requestId' => 'sampling-1']);
        $result = McpCancelSamplingExecutionResult::fromArray(['cancelled' => false]);

        expect($request->toArray())->toBe(['requestId' => 'sampling-1'])
            ->and($result->cancelled)->toBeFalse()
            ->and($result->toArray())->toBe(['cancelled' => false]);
    });
});
