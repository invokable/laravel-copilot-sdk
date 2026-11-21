<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AccountKind;
use Revolution\Copilot\Enums\LoginProviderKind;
use Revolution\Copilot\Types\Rpc\Account;
use Revolution\Copilot\Types\Rpc\AccountsGetActiveResult;
use Revolution\Copilot\Types\Rpc\AccountsListRequest;
use Revolution\Copilot\Types\Rpc\AccountsLogoutRequest;
use Revolution\Copilot\Types\Rpc\AccountsSetCredentialsRequest;
use Revolution\Copilot\Types\Rpc\AccountsSwitchActiveRequest;
use Revolution\Copilot\Types\Rpc\AuthIdentity;
use Revolution\Copilot\Types\Rpc\AuthLoginListRequest;
use Revolution\Copilot\Types\Rpc\AuthProviderDescriptor;
use Revolution\Copilot\Types\Rpc\AuthReadValue;

describe('account RPC types', function () {
    it('round trips every account field and preserves false values and unknown kinds', function () {
        $data = [
            'accountId' => 'account-1',
            'host' => 'github.com',
            'login' => 'octocat',
            'label' => 'Octocat',
            'kind' => 'future-account-kind',
            'authSource' => 'gh',
            'derivedFrom' => 'base-1',
            'active' => false,
            'selectionId' => 'selection-1',
            'switchable' => false,
            'health' => 'future-health',
            'statusMessage' => 'Unavailable',
        ];
        $account = Account::fromArray($data);

        expect($account->kind)->toBe('future-account-kind')
            ->and($account->toArray())->toEqual($data)
            ->and(Account::fromArray(['kind' => 'entra'])->kind)->toBe(AccountKind::ENTRA)
            ->and(Account::fromArray(['host' => 'h', 'login' => 'l', 'kind' => 'entra', 'active' => false])->toArray())
            ->toBe(['host' => 'h', 'login' => 'l', 'kind' => 'entra', 'active' => false]);
    });

    it('preserves the required null active account and nested identity metadata', function () {
        expect(AccountsGetActiveResult::fromArray(['account' => null])->toArray())->toBe(['account' => null]);

        $data = [
            'account' => ['host' => 'github.com', 'login' => 'octocat', 'kind' => 'entra', 'active' => true],
            'authInfo' => ['type' => 'future-auth', 'host' => 'github.com', 'copilotUser' => ['id' => '1']],
            'authSelectionId' => 'selection-1',
        ];
        $result = AccountsGetActiveResult::fromArray($data);
        $legacy = AuthReadValue::fromArray(['kind' => 'account', 'account' => $data['account']]);

        expect($result->account)->toBeInstanceOf(Account::class)
            ->and($result->authInfo)->toBeInstanceOf(AuthIdentity::class)
            ->and($result->toArray())->toBe($data)
            ->and($legacy->account)->toBeInstanceOf(Account::class)
            ->and($legacy->toArray()['account'])->toBe($data['account'])
            ->and(AccountsGetActiveResult::fromArray(['account' => null, 'authInfo' => null, 'authSelectionId' => null])->toArray())
            ->toBe(['account' => null]);
    });

    it('round trips account requests and omits only null options', function () {
        expect(AccountsListRequest::fromArray(['refresh' => false])->toArray())->toBe(['refresh' => false])
            ->and(AccountsListRequest::fromArray(['refresh' => null])->toArray())->toBe([])
            ->and(AccountsListRequest::fromArray(['refresh' => true])->toArray())->toBe(['refresh' => true])
            ->and(AccountsSwitchActiveRequest::fromArray(['selectionId' => 'selection-1'])->toArray())->toBe(['selectionId' => 'selection-1'])
            ->and(AccountsLogoutRequest::fromArray(['accountId' => 'account-1', 'selectionId' => 'selection-1'])->toArray())
            ->toBe(['accountId' => 'account-1', 'selectionId' => 'selection-1'])
            ->and(AccountsLogoutRequest::fromArray(['accountId' => null, 'selectionId' => ''])->toArray())->toBe(['selectionId' => ''])
            ->and(AccountsLogoutRequest::fromArray([])->toArray())->toBe([])
            ->and(AccountsSetCredentialsRequest::fromArray(['host' => 'github.com', 'login' => 'octocat', 'token' => 'example'])->toArray())
            ->toBe(['host' => 'github.com', 'login' => 'octocat', 'token' => 'example']);
    });

    it('round trips known and unknown login provider kinds', function () {
        $request = AuthLoginListRequest::fromArray(['provider' => 'entra']);
        $descriptor = AuthProviderDescriptor::fromArray(['kind' => 'entra', 'label' => 'Entra', 'available' => false]);

        expect($request->provider)->toBe(LoginProviderKind::ENTRA)
            ->and($request->toArray())->toBe(['provider' => 'entra'])
            ->and($descriptor->kind)->toBe(LoginProviderKind::ENTRA)
            ->and($descriptor->toArray())->toBe(['kind' => 'entra', 'label' => 'Entra', 'available' => false])
            ->and(AuthLoginListRequest::fromArray(['provider' => 'future-provider'])->toArray())->toBe(['provider' => 'future-provider'])
            ->and(AuthProviderDescriptor::fromArray(['kind' => 'future-provider', 'label' => 'Future', 'available' => true])->toArray())
            ->toBe(['kind' => 'future-provider', 'label' => 'Future', 'available' => true]);
    });
});
