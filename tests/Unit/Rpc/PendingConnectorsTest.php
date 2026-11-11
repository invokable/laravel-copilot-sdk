<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingConnectors;
use Revolution\Copilot\Types\Rpc\AuthIdentityMetadata;
use Revolution\Copilot\Types\Rpc\ConnectorSessionAccount;

describe('PendingConnectors', function () {
    it('gets connector capabilities and status for its session', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.connectors.getCapabilities', ['sessionId' => 'session-connectors-1'])
            ->andReturn(['supportsOAuth' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.connectors.getStatus', ['sessionId' => 'session-connectors-1'])
            ->andReturn(['connected' => true]);

        $connectors = new PendingConnectors($client, 'session-connectors-1');

        expect($connectors->getCapabilities())->toBe(['supportsOAuth' => true])
            ->and($connectors->getStatus())->toBe(['connected' => true]);
    });

    it('maps a current account and returns null when no account is available', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.connectors.getAccount', ['sessionId' => 'session-connectors-2'])
            ->andReturn([
                'accountId' => 'account-1',
                'authInfo' => [
                    'type' => 'github',
                    'host' => 'github.com',
                    'login' => 'octocat',
                ],
            ]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.connectors.getAccount', ['sessionId' => 'session-connectors-2'])
            ->andReturn(null);

        $connectors = new PendingConnectors($client, 'session-connectors-2');
        $account = $connectors->getAccount();

        expect($account)->toBeInstanceOf(ConnectorSessionAccount::class)
            ->and($account->accountId)->toBe('account-1')
            ->and($account->authInfo)->toBeInstanceOf(AuthIdentityMetadata::class)
            ->and($account->authInfo->login)->toBe('octocat')
            ->and($connectors->getAccount())->toBeNull();
    });

    it('lists and refreshes connectors with optional parameters', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.connectors.list', ['sessionId' => 'session-connectors-3'])
            ->andReturn(['connectors' => []]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.connectors.refresh', [
                'connectorId' => 'github',
                'sessionId' => 'session-connectors-3',
            ])
            ->andReturn(['refreshed' => true]);

        $connectors = new PendingConnectors($client, 'session-connectors-3');

        expect($connectors->list())->toBe(['connectors' => []])
            ->and($connectors->refresh(['connectorId' => 'github']))->toBe(['refreshed' => true]);
    });

    it('sends connection operations with their parameters and bound session id', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        foreach (['connect', 'reconnect', 'continueConnection', 'disconnect'] as $operation) {
            $client->shouldReceive('request')
                ->once()
                ->with("session.connectors.{$operation}", [
                    'connectorId' => 'github',
                    'sessionId' => 'session-connectors-4',
                ])
                ->andReturn(['operation' => $operation]);
        }

        $connectors = new PendingConnectors($client, 'session-connectors-4');
        $params = ['connectorId' => 'github'];

        expect($connectors->connect($params))->toBe(['operation' => 'connect'])
            ->and($connectors->reconnect($params))->toBe(['operation' => 'reconnect'])
            ->and($connectors->continueConnection($params))->toBe(['operation' => 'continueConnection'])
            ->and($connectors->disconnect($params))->toBe(['operation' => 'disconnect']);
    });

    it('reconciles connector state and supports internal startup and projection operations', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.connectors.reconcile', ['sessionId' => 'session-connectors-5'])
            ->andReturn(['reconciled' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.connectors.reconcileForStartup', [
                'reason' => 'startup',
                'sessionId' => 'session-connectors-5',
            ])
            ->andReturn(['started' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.connectors.withdrawProjection', [
                'connectorId' => 'github',
                'sessionId' => 'session-connectors-5',
            ])
            ->andReturn(['withdrawn' => true]);

        $connectors = new PendingConnectors($client, 'session-connectors-5');

        expect($connectors->reconcile())->toBe(['reconciled' => true])
            ->and($connectors->reconcileForStartup(['reason' => 'startup']))->toBe(['started' => true])
            ->and($connectors->withdrawProjection(['connectorId' => 'github']))->toBe(['withdrawn' => true]);
    });
});
