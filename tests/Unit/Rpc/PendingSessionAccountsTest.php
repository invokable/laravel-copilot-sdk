<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AuthLoginMethod;
use Revolution\Copilot\Enums\AuthLoginMode;
use Revolution\Copilot\Enums\AuthLoginStepKind;
use Revolution\Copilot\Enums\LoginProviderKind;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSessionAccounts;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\Account;
use Revolution\Copilot\Types\Rpc\AccountsListRequest;
use Revolution\Copilot\Types\Rpc\AccountsLogoutRequest;
use Revolution\Copilot\Types\Rpc\AccountsSetCredentialsRequest;
use Revolution\Copilot\Types\Rpc\AccountsSwitchActiveRequest;
use Revolution\Copilot\Types\Rpc\AuthLoginBeginRequest;
use Revolution\Copilot\Types\Rpc\AuthLoginIdentity;
use Revolution\Copilot\Types\Rpc\AuthLoginListRequest;
use Revolution\Copilot\Types\Rpc\AuthProviderDescriptor;
use Revolution\Copilot\Types\Rpc\AuthValidationError;

describe('PendingSessionAccounts', function () {
    it('lists accounts with optional refresh and decodes the direct array', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.accounts.list', ['refresh' => false, 'sessionId' => 'session-1'])
            ->andReturn([['host' => 'github.com', 'login' => 'octocat', 'kind' => 'gitHub', 'active' => true]]);
        $client->shouldReceive('request')->once()
            ->with('session.accounts.list', ['sessionId' => 'session-1'])->andReturn([]);
        $client->shouldReceive('request')->once()
            ->with('session.accounts.list', ['refresh' => true, 'sessionId' => 'session-1'])->andReturn([]);

        $accounts = (new SessionRpc($client, 'session-1'))->accounts();
        $result = $accounts->list(new AccountsListRequest(refresh: false));
        expect($result[0])->toBeInstanceOf(Account::class)
            ->and($result[0]->login)->toBe('octocat')
            ->and($accounts->list())->toBe([])
            ->and($accounts->list(['refresh' => true, 'sessionId' => 'other']))->toBe([]);
    });

    it('gets active account metadata and recent authentication errors', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()->with('session.accounts.getActive', ['sessionId' => 'session-1'])
            ->andReturn(['account' => null, 'authInfo' => ['type' => 'token', 'host' => 'github.com'], 'authSelectionId' => 'selection']);
        $client->shouldReceive('request')->once()->with('session.accounts.lastErrors', ['sessionId' => 'session-1'])
            ->andReturn([['message' => 'Expired', 'githubMessage' => 'Token expired']]);

        $accounts = new PendingSessionAccounts($client, 'session-1');
        $active = $accounts->getActive();
        $errors = $accounts->lastErrors();
        expect($active->account)->toBeNull()
            ->and($active->authInfo->host)->toBe('github.com')
            ->and($active->authSelectionId)->toBe('selection')
            ->and($errors[0])->toBeInstanceOf(AuthValidationError::class)
            ->and($errors[0]->githubMessage)->toBe('Token expired');
    });

    it('sends account changes and preserves boolean logout results', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()->with('session.accounts.switchActive', ['selectionId' => 'sel', 'sessionId' => 'session-1'])->andReturnNull();
        $client->shouldReceive('request')->once()->with('session.accounts.logout', ['accountId' => 'id', 'sessionId' => 'session-1'])->andReturn(false);
        $client->shouldReceive('request')->once()->with('session.accounts.logout', ['sessionId' => 'session-1'])->andReturn(true);
        $client->shouldReceive('request')->once()->with('session.accounts.setCredentials', [
            'host' => 'github.com', 'login' => 'octocat', 'token' => 'example', 'sessionId' => 'session-1',
        ])->andReturnNull();

        $accounts = new PendingSessionAccounts($client, 'session-1');
        $accounts->switchActive(new AccountsSwitchActiveRequest('sel'));
        expect($accounts->logout(new AccountsLogoutRequest(accountId: 'id')))->toBeFalse()
            ->and($accounts->logout([]))->toBeTrue();
        $accounts->setCredentials(new AccountsSetCredentialsRequest('github.com', 'octocat', 'example'));
    });

    it('lists reusable login identities and authentication providers', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()
            ->with('session.accounts.login.list', ['provider' => 'entra', 'sessionId' => 'session-1'])
            ->andReturn([['accountId' => 'opaque', 'label' => 'Work', 'active' => true]]);
        $client->shouldReceive('request')->once()
            ->with('session.authProviders.list', ['sessionId' => 'session-1'])
            ->andReturn([['kind' => 'entra', 'label' => 'Microsoft Entra', 'available' => false]]);
        $rpc = new SessionRpc($client, 'session-1');
        $identities = $rpc->accounts()->loginList(new AuthLoginListRequest(LoginProviderKind::ENTRA));
        $providers = $rpc->authProviders()->list();
        expect($identities[0])->toBeInstanceOf(AuthLoginIdentity::class)
            ->and($identities[0]->accountId)->toBe('opaque')
            ->and($providers[0])->toBeInstanceOf(AuthProviderDescriptor::class)
            ->and($providers[0]->available)->toBeFalse();
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

    it('sends the device-code method and returns its typed login step', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.accounts.login.begin', [
                'sessionId' => 'session-1',
                'kind' => 'entra',
                'method' => 'device-code',
            ])
            ->andReturn([
                'flowId' => 'flow-3',
                'step' => [
                    'kind' => 'device-code',
                    'userCode' => 'ABCD-EFGH',
                    'verificationUri' => 'https://github.com/login/device',
                    'expiresIn' => 900,
                    'interval' => 5,
                ],
            ]);

        $result = (new PendingSessionAccounts($client, 'session-1'))->begin(new AuthLoginBeginRequest(
            LoginProviderKind::ENTRA,
            method: AuthLoginMethod::DEVICE_CODE,
        ));

        expect($result->step->kind)->toBe(AuthLoginStepKind::DEVICE_CODE)
            ->and($result->step->userCode)->toBe('ABCD-EFGH')
            ->and($result->step->verificationUri)->toBe('https://github.com/login/device')
            ->and($result->step->expiresIn)->toBe(900)
            ->and($result->step->interval)->toBe(5);
    });
});
