<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AuthLoginMode;
use Revolution\Copilot\Enums\LoginProviderKind;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSessionAccounts;
use Revolution\Copilot\Types\Rpc\AuthLoginBeginRequest;
use Revolution\Copilot\Types\Rpc\AuthLoginIdentity;
use Revolution\Copilot\Types\Rpc\ProviderDescriptor;

describe('PendingSessionAccounts', function () {
    it('returns typed cached login identities and active metadata', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.accounts.enumerate', [
                'sessionId' => 'session-1',
                'query' => ['kind' => 'loginAccounts', 'provider' => 'entra'],
            ])
            ->andReturn([
                'kind' => 'loginAccounts',
                'items' => [[
                    'accountId' => 'entra:opaque-base-identity',
                    'label' => 'Work account',
                    'active' => true,
                ]],
            ]);

        $identities = (new PendingSessionAccounts($client, 'session-1'))
            ->enumerate('loginAccounts', provider: LoginProviderKind::ENTRA);

        expect($identities[0])
            ->toBeInstanceOf(AuthLoginIdentity::class)
            ->and($identities[0]->accountId)->toBe('entra:opaque-base-identity')
            ->and($identities[0]->label)->toBe('Work account')
            ->and($identities[0]->active)->toBeTrue();
    });

    it('requires a provider when enumerating login identities', function () {
        $client = Mockery::mock(JsonRpcClient::class);

        expect(fn () => (new PendingSessionAccounts($client, 'session-1'))->enumerate('loginAccounts'))
            ->toThrow(InvalidArgumentException::class, 'A provider is required');
    });

    it('preserves provider enumeration with broker availability', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.accounts.enumerate', [
                'sessionId' => 'session-1',
                'query' => ['kind' => 'providers', 'brokerAvailable' => false],
            ])
            ->andReturn([
                'items' => [['kind' => 'entra', 'label' => 'Microsoft Entra', 'available' => true]],
            ]);

        $providers = (new PendingSessionAccounts($client, 'session-1'))
            ->enumerate('providers', brokerAvailable: false);

        expect($providers[0])
            ->toBeInstanceOf(ProviderDescriptor::class)
            ->and($providers[0]->kind)->toBe(LoginProviderKind::ENTRA);
    });

    it('sends login mode and opaque account ID unchanged', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.accounts.login.begin', [
                'sessionId' => 'session-1',
                'kind' => 'entra',
                'mode' => 'default',
                'accountId' => 'entra:opaque-base-identity',
            ])
            ->andReturn(['flowId' => 'flow-1', 'step' => ['kind' => 'awaiting']]);

        $result = (new PendingSessionAccounts($client, 'session-1'))->begin(new AuthLoginBeginRequest(
            LoginProviderKind::ENTRA,
            AuthLoginMode::Default,
            'entra:opaque-base-identity',
        ));

        expect($result->flowId)->toBe('flow-1');
    });

    it('keeps the existing provider-only login call shape', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.accounts.login.begin', [
                'sessionId' => 'session-1',
                'kind' => 'entra',
            ])
            ->andReturn(['flowId' => 'flow-2', 'step' => ['kind' => 'awaiting']]);

        $result = (new PendingSessionAccounts($client, 'session-1'))->begin(LoginProviderKind::ENTRA);

        expect($result->flowId)->toBe('flow-2');
    });
});
