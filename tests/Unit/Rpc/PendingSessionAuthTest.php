<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AuthInfoType;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSessionAuth;
use Revolution\Copilot\Types\Rpc\AuthInfo;
use Revolution\Copilot\Types\Rpc\SessionAuthStatus;
use Revolution\Copilot\Types\Rpc\SessionSetCredentialsParams;
use Revolution\Copilot\Types\Rpc\SessionSetCredentialsResult;

describe('PendingSessionAuth', function () {
    it('calls session.auth.getStatus and returns authenticated status', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.auth.getStatus', ['sessionId' => 'session-abc'])
            ->andReturn([
                'isAuthenticated' => true,
                'authType' => 'gh-cli',
                'copilotPlan' => 'individual_pro',
                'host' => 'https://github.com',
                'login' => 'octocat',
                'statusMessage' => 'Authenticated via GitHub CLI',
            ]);

        $pending = new PendingSessionAuth($client, 'session-abc');
        $result = $pending->getStatus();

        expect($result)->toBeInstanceOf(SessionAuthStatus::class)
            ->and($result->isAuthenticated)->toBeTrue()
            ->and($result->authType)->toBe(AuthInfoType::GH_CLI)
            ->and($result->copilotPlan)->toBe('individual_pro')
            ->and($result->host)->toBe('https://github.com')
            ->and($result->login)->toBe('octocat')
            ->and($result->statusMessage)->toBe('Authenticated via GitHub CLI');
    });

    it('calls session.auth.getStatus and returns unauthenticated status', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.auth.getStatus', ['sessionId' => 'session-abc'])
            ->andReturn([
                'isAuthenticated' => false,
            ]);

        $pending = new PendingSessionAuth($client, 'session-abc');
        $result = $pending->getStatus();

        expect($result)->toBeInstanceOf(SessionAuthStatus::class)
            ->and($result->isAuthenticated)->toBeFalse()
            ->and($result->authType)->toBeNull()
            ->and($result->login)->toBeNull();
    });

    it('calls session.auth.setCredentials with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.auth.setCredentials', [
                'credentials' => [
                    'host' => 'https://github.com',
                    'type' => 'token',
                    'token' => 'ghp_test',
                    'copilotUser' => ['login' => 'octocat'],
                ],
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'success' => true,
            ]);

        $pending = new PendingSessionAuth($client, 'session-abc');
        $result = $pending->setCredentials(
            new SessionSetCredentialsParams(
                credentials: new AuthInfo(
                    host: 'https://github.com',
                    type: AuthInfoType::TOKEN,
                    token: 'ghp_test',
                    copilotUser: ['login' => 'octocat'],
                ),
            ),
        );

        expect($result)->toBeInstanceOf(SessionSetCredentialsResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('calls session.auth.setCredentials without params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.auth.setCredentials', [
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'success' => true,
            ]);

        $pending = new PendingSessionAuth($client, 'session-abc');
        $result = $pending->setCredentials();

        expect($result)->toBeInstanceOf(SessionSetCredentialsResult::class)
            ->and($result->success)->toBeTrue();
    });
});
