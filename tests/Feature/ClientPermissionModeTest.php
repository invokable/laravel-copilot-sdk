<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Enums\PermissionMode;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;

beforeEach(function () {
    Copilot::clearResolvedInstances();
    // Don't use preventStrayRequests here as Client tests use mocked RPC client
});

describe('Client permissionMode', function () {
    it('forwards permissionMode assisted through create and resume requests', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $rpc->shouldReceive('request')
            ->with('session.create', Mockery::on(fn (array $params) => ($params['permissionMode'] ?? null) === 'assisted'))
            ->once()
            ->andReturn(['sessionId' => 'created-session']);
        $rpc->shouldReceive('request')
            ->with('session.resume', Mockery::on(fn (array $params) => ($params['permissionMode'] ?? null) === 'assisted'))
            ->once()
            ->andReturn(['sessionId' => 'resumed-session']);

        $client = new Client;
        $reflection = new ReflectionClass($client);
        $reflection->getProperty('rpcClient')->setValue($client, $rpc);
        $reflection->getProperty('state')->setValue($client, ConnectionState::CONNECTED);

        $created = $client->createSession(new SessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
            permissionMode: PermissionMode::Assisted,
        ));
        $resumed = $client->resumeSession('resumed-session', new ResumeSessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
            permissionMode: 'assisted',
        ));

        expect($created->id())->toBe('created-session')
            ->and($resumed->id())->toBe('resumed-session');
    });

    it('omits permissionMode from create and resume requests when unspecified', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $rpc->shouldReceive('request')
            ->with('session.create', Mockery::on(fn (array $params) => ! array_key_exists('permissionMode', $params)))
            ->once()
            ->andReturn(['sessionId' => 'created-session']);
        $rpc->shouldReceive('request')
            ->with('session.resume', Mockery::on(fn (array $params) => ! array_key_exists('permissionMode', $params)))
            ->once()
            ->andReturn(['sessionId' => 'resumed-session']);

        $client = new Client;
        $reflection = new ReflectionClass($client);
        $reflection->getProperty('rpcClient')->setValue($client, $rpc);
        $reflection->getProperty('state')->setValue($client, ConnectionState::CONNECTED);

        $created = $client->createSession(new SessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
        ));
        $resumed = $client->resumeSession('resumed-session', new ResumeSessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
        ));

        expect($created->id())->toBe('created-session')
            ->and($resumed->id())->toBe('resumed-session');
    });

    it('exposes permissionModeResult from the create response on the session', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $rpc->shouldReceive('request')
            ->with('session.create', Mockery::any())
            ->once()
            ->andReturn([
                'sessionId' => 'created-session',
                'permissionModeResult' => ['success' => true, 'mode' => 'assisted'],
            ]);

        $client = new Client;
        $reflection = new ReflectionClass($client);
        $reflection->getProperty('rpcClient')->setValue($client, $rpc);
        $reflection->getProperty('state')->setValue($client, ConnectionState::CONNECTED);

        $session = $client->createSession(new SessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
            permissionMode: PermissionMode::Assisted,
        ));

        expect($session->permissionModeResult())->not->toBeNull()
            ->and($session->permissionModeResult()->success)->toBeTrue()
            ->and($session->permissionModeResult()->mode)->toBe(PermissionMode::Assisted);
    });

    it('exposes permissionModeResult from the resume response on the session', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $rpc->shouldReceive('request')
            ->with('session.resume', Mockery::any())
            ->once()
            ->andReturn([
                'sessionId' => 'resumed-session',
                'permissionModeResult' => ['success' => true, 'mode' => 'allow-all'],
            ]);

        $client = new Client;
        $reflection = new ReflectionClass($client);
        $reflection->getProperty('rpcClient')->setValue($client, $rpc);
        $reflection->getProperty('state')->setValue($client, ConnectionState::CONNECTED);

        $session = $client->resumeSession('resumed-session', new ResumeSessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
            permissionMode: PermissionMode::AllowAll,
        ));

        expect($session->permissionModeResult())->not->toBeNull()
            ->and($session->permissionModeResult()->success)->toBeTrue()
            ->and($session->permissionModeResult()->mode)->toBe(PermissionMode::AllowAll);
    });

    it('returns null permissionModeResult when the response omits it', function () {
        $rpc = Mockery::mock(JsonRpcClient::class);
        $rpc->shouldReceive('request')
            ->with('session.create', Mockery::any())
            ->once()
            ->andReturn(['sessionId' => 'created-session']);

        $client = new Client;
        $reflection = new ReflectionClass($client);
        $reflection->getProperty('rpcClient')->setValue($client, $rpc);
        $reflection->getProperty('state')->setValue($client, ConnectionState::CONNECTED);

        $session = $client->createSession(new SessionConfig(
            onPermissionRequest: PermissionHandler::approveAll(),
        ));

        expect($session->permissionModeResult())->toBeNull();
    });
});
