<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingMcp;
use Revolution\Copilot\Types\Rpc\McpCancelSamplingExecutionParams;
use Revolution\Copilot\Types\Rpc\McpDisableRequest;
use Revolution\Copilot\Types\Rpc\McpEnableRequest;
use Revolution\Copilot\Types\Rpc\McpExecuteSamplingParams;
use Revolution\Copilot\Types\Rpc\McpOauthCompleteRequest;
use Revolution\Copilot\Types\Rpc\McpOauthLoginRequest;
use Revolution\Copilot\Types\Rpc\McpOauthLoginResult;
use Revolution\Copilot\Types\Rpc\McpOauthProbeRequest;
use Revolution\Copilot\Types\Rpc\McpOauthRespondRequest;
use Revolution\Copilot\Types\Rpc\McpOauthRespondResult;
use Revolution\Copilot\Types\Rpc\McpPromptsGetRequest;
use Revolution\Copilot\Types\Rpc\McpPromptsListRequest;
use Revolution\Copilot\Types\Rpc\McpRestartServerRequest;
use Revolution\Copilot\Types\Rpc\McpServerList;
use Revolution\Copilot\Types\Rpc\McpSetEnvValueModeParams;
use Revolution\Copilot\Types\Rpc\McpStartServerRequest;
use Revolution\Copilot\Types\Rpc\MoveMcpLoadingToBackgroundResult;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthCancelLoginRequest;
use Revolution\Copilot\Types\Rpc\SessionMcpOauthPrepareLoginRequest;

describe('PendingMcp', function () {
    it('calls session.mcp.list and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.list', ['sessionId' => 'session-abc'])
            ->andReturn([
                'servers' => [
                    [
                        'name' => 'github',
                        'status' => 'connected',
                        'source' => 'workspace',
                    ],
                ],
            ]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(McpServerList::class)
            ->and($result->servers)->toHaveCount(1)
            ->and($result->servers[0]->name)->toBe('github');
    });

    it('calls session.mcp.list with empty result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.list', ['sessionId' => 'session-abc'])
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->list();

        expect($result)->toBeInstanceOf(McpServerList::class)
            ->and($result->servers)->toBe([]);
    });

    it('calls session.mcp.enable with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.enable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['serverName'] === 'github'),
            )
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->enable(new McpEnableRequest(serverName: 'github'));

        expect($result)->toBe([]);
    });

    it('calls session.mcp.enable with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.enable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['serverName'] === 'slack'),
            )
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->enable(['serverName' => 'slack']);

        expect($result)->toBe([]);
    });

    it('calls session.mcp.disable with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.disable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['serverName'] === 'github'),
            )
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->disable(new McpDisableRequest(serverName: 'github'));

        expect($result)->toBe([]);
    });

    it('calls session.mcp.disable with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.disable',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['serverName'] === 'slack'),
            )
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->disable(['serverName' => 'slack']);

        expect($result)->toBe([]);
    });

    it('calls session.mcp.reload', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.reload', ['sessionId' => 'session-abc'])
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->reload();

        expect($result)->toBe([]);
    });

    it('calls session.mcp.moveLoadingToBackground and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.moveLoadingToBackground', ['sessionId' => 'session-abc'])
            ->andReturn(['movedToBackground' => true]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->moveLoadingToBackground();

        expect($result)->toBeInstanceOf(MoveMcpLoadingToBackgroundResult::class)
            ->and($result->movedToBackground)->toBeTrue();
    });

    it('calls session.mcp.oauth.login with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.oauth.login',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['serverName'] === 'my-mcp-server'),
            )
            ->andReturn(['authorizationUrl' => 'https://github.com/login/oauth/authorize?client_id=abc']);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->login(new McpOauthLoginRequest(serverName: 'my-mcp-server'));

        expect($result)->toBeInstanceOf(McpOauthLoginResult::class)
            ->and($result->authorizationUrl)->toContain('github.com');
    });

    it('calls session.mcp.oauth.login with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.oauth.login',
                Mockery::on(fn ($params) => $params['serverName'] === 'remote-server'),
            )
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->login(['serverName' => 'remote-server']);

        expect($result)->toBeInstanceOf(McpOauthLoginResult::class)
            ->and($result->authorizationUrl)->toBeNull();
    });

    it('calls session.mcp.oauth.respond and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.oauth.respond', ['requestId' => 'oauth-1', 'sessionId' => 'session-abc'])
            ->andReturn(['success' => true]);

        $pending = new PendingMcp($client, 'session-abc');
        $result = $pending->respond(new McpOauthRespondRequest(requestId: 'oauth-1'));

        expect($result)->toBeInstanceOf(McpOauthRespondResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('completes host-managed MCP OAuth redirects', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.oauth.complete', [
                'authorizationId' => 'auth-1',
                'callbackUrl' => 'http://localhost/callback?code=abc',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([]);

        (new PendingMcp($client, 'session-abc'))->complete(new McpOauthCompleteRequest(
            authorizationId: 'auth-1',
            callbackUrl: 'http://localhost/callback?code=abc',
        ));
    });

    it('lists MCP prompts and retrieves rendered prompt messages', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('session.mcp.prompts.list', ['serverName' => 'docs', 'sessionId' => 'session-abc'])
            ->andReturn(['prompts' => [['name' => 'summarize', 'description' => 'Summarize a document']]]);
        $client->shouldReceive('request')->once()
            ->with('session.mcp.prompts.get', [
                'serverName' => 'docs',
                'promptName' => 'summarize',
                'arguments' => ['document' => 'readme.md'],
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['messages' => [['role' => 'user', 'content' => ['type' => 'text', 'text' => 'Summarize this']]]]);

        $pending = new PendingMcp($client, 'session-abc');
        $prompts = $pending->prompts()->list(new McpPromptsListRequest(serverName: 'docs'));
        $rendered = $pending->prompts()->get(new McpPromptsGetRequest(
            serverName: 'docs',
            promptName: 'summarize',
            arguments: ['document' => 'readme.md'],
        ));

        expect($prompts->prompts[0]->name)->toBe('summarize')
            ->and($rendered->messages[0]->content['text'])->toBe('Summarize this');
    });

    it('starts a session MCP server with optional configuration', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.mcp.startServer', [
                'serverName' => 'docs',
                'config' => ['type' => 'http', 'url' => 'https://mcp.example.com'],
                'expectedInstallationId' => 'installation-1',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $params = [
            'serverName' => 'docs',
            'config' => ['type' => 'http', 'url' => 'https://mcp.example.com'],
            'expectedInstallationId' => 'installation-1',
        ];

        $pending->startServer(new McpStartServerRequest(
            serverName: 'docs',
            config: ['type' => 'http', 'url' => 'https://mcp.example.com'],
            expectedInstallationId: 'installation-1',
        ));
        $pending->startServer($params);
    });

    it('restarts a session MCP server from array parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.mcp.restartServer', [
                'serverName' => 'docs',
                'expectedInstallationId' => 'installation-1',
                'sessionId' => 'session-abc',
            ])
            ->andReturn([]);

        $pending = new PendingMcp($client, 'session-abc');
        $params = [
            'serverName' => 'docs',
            'expectedInstallationId' => 'installation-1',
        ];

        $pending->restartServer($params);
        $pending->restartServer(new McpRestartServerRequest(
            serverName: 'docs',
            expectedInstallationId: 'installation-1',
        ));
    });

    it('maps the result of removing the auto-managed GitHub MCP server', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.removeGitHub', ['sessionId' => 'session-abc'])
            ->andReturn(['removed' => true]);

        $result = (new PendingMcp($client, 'session-abc'))->removeGitHub();

        expect($result->removed)->toBeTrue();
    });

    it('sets the MCP environment value mode and returns the recorded mode', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.mcp.setEnvValueMode', [
                'mode' => 'indirect',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['mode' => 'indirect']);

        $pending = new PendingMcp($client, 'session-abc');
        $typedResult = $pending->setEnvValueMode(new McpSetEnvValueModeParams(mode: 'indirect'));
        $arrayResult = $pending->setEnvValueMode(['mode' => 'indirect']);

        expect($typedResult->mode)->toBe('indirect')
            ->and($arrayResult->mode)->toBe('indirect');
    });

    it('executes MCP sampling and maps its outcome', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.mcp.executeSampling', [
                'requestId' => 'sampling-1',
                'serverName' => 'docs',
                'mcpRequestId' => 'mcp-request-1',
                'request' => ['messages' => []],
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['action' => 'success', 'result' => ['content' => []]]);

        $pending = new PendingMcp($client, 'session-abc');
        $params = [
            'requestId' => 'sampling-1',
            'serverName' => 'docs',
            'mcpRequestId' => 'mcp-request-1',
            'request' => ['messages' => []],
        ];
        $typedResult = $pending->executeSampling(McpExecuteSamplingParams::fromArray($params));
        $arrayResult = $pending->executeSampling($params);

        expect($typedResult->action)->toBe('success')
            ->and($typedResult->result)->toBe(['content' => []])
            ->and($arrayResult->action)->toBe('success')
            ->and($arrayResult->result)->toBe(['content' => []]);
    });

    it('cancels MCP sampling and maps whether an execution was found', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.mcp.cancelSamplingExecution', [
                'requestId' => 'sampling-1',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['cancelled' => false]);

        $pending = new PendingMcp($client, 'session-abc');
        $typedResult = $pending->cancelSamplingExecution(new McpCancelSamplingExecutionParams(requestId: 'sampling-1'));
        $arrayResult = $pending->cancelSamplingExecution(['requestId' => 'sampling-1']);

        expect($typedResult->cancelled)->toBeFalse()
            ->and($arrayResult->cancelled)->toBeFalse();
    });

    it('probes MCP OAuth through the grouped OAuth RPC API', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.oauth.probe', [
                'serverName' => 'docs',
                'expectedInstallationId' => 'installation-1',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['status' => 'authenticated', 'httpResponse' => ['status' => 200]]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.mcp.oauth.probe', [
                'serverName' => 'docs',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['status' => 'no-auth-required', 'httpResponse' => ['status' => 204]]);

        $oauth = (new PendingMcp($client, 'session-abc'))->oauth();
        $typedResult = $oauth->probe(new McpOauthProbeRequest(
            serverName: 'docs',
            expectedInstallationId: 'installation-1',
        ));
        $arrayResult = $oauth->probe(['serverName' => 'docs']);

        expect($typedResult->status)->toBe('authenticated')
            ->and($typedResult->httpResponse)->toBe(['status' => 200])
            ->and($arrayResult->status)->toBe('no-auth-required')
            ->and($arrayResult->httpResponse)->toBe(['status' => 204]);
    });

    it('prepares an owned OAuth login and returns its handle and expiry', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.mcp.oauth.prepareLogin', [
                'serverName' => 'docs',
                'expectedInstallationId' => 'installation-1',
                'forceReauth' => false,
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'loginId' => 'login-1',
                'expiresAt' => '2026-10-13T14:00:00.000Z',
            ]);

        $oauth = (new PendingMcp($client, 'session-abc'))->oauth();
        $request = new SessionMcpOauthPrepareLoginRequest(
            serverName: 'docs',
            expectedInstallationId: 'installation-1',
            forceReauth: false,
        );
        $typedResult = $oauth->prepareLogin($request);
        $arrayResult = $oauth->prepareLogin([
            'serverName' => 'docs',
            'expectedInstallationId' => 'installation-1',
            'forceReauth' => false,
        ]);

        expect($typedResult->loginId)->toBe('login-1')
            ->and($typedResult->expiresAt)->toBe('2026-10-13T14:00:00.000Z')
            ->and($arrayResult->loginId)->toBe('login-1')
            ->and($arrayResult->expiresAt)->toBe('2026-10-13T14:00:00.000Z');
    });

    it('cancels the matching owned OAuth login', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('session.mcp.oauth.cancelLogin', [
                'expectedInstallationId' => 'installation-1',
                'loginId' => 'login-1',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['cancelled' => true]);

        $oauth = (new PendingMcp($client, 'session-abc'))->oauth();
        $request = new SessionMcpOauthCancelLoginRequest(
            expectedInstallationId: 'installation-1',
            loginId: 'login-1',
        );
        $typedResult = $oauth->cancelLogin($request);
        $arrayResult = $oauth->cancelLogin([
            'expectedInstallationId' => 'installation-1',
            'loginId' => 'login-1',
        ]);

        expect($typedResult->cancelled)->toBeTrue()
            ->and($arrayResult->cancelled)->toBeTrue();
    });

});
