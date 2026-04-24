<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerAccount;
use Revolution\Copilot\Types\Rpc\AccountGetQuotaRequest;
use Revolution\Copilot\Types\Rpc\AccountGetQuotaResult;
use Revolution\Copilot\Types\Rpc\QuotaSnapshot;

describe('PendingServerAccount', function () {
    it('calls account.getQuota and returns result with snapshots', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('account.getQuota', [])
            ->andReturn([
                'quotaSnapshots' => [
                    'chat' => [
                        'entitlementRequests' => 1000,
                        'usedRequests' => 250,
                        'remainingPercentage' => 75.0,
                        'overage' => 0,
                        'overageAllowedWithExhaustedQuota' => false,
                        'resetDate' => '2026-05-01',
                    ],
                ],
            ]);

        $pending = new PendingServerAccount($client);
        $result = $pending->getQuota();

        expect($result)->toBeInstanceOf(AccountGetQuotaResult::class)
            ->and($result->quotaSnapshots)->toHaveKey('chat')
            ->and($result->quotaSnapshots['chat'])->toBeInstanceOf(QuotaSnapshot::class)
            ->and($result->quotaSnapshots['chat']->entitlementRequests)->toBe(1000)
            ->and($result->quotaSnapshots['chat']->usedRequests)->toBe(250)
            ->and($result->quotaSnapshots['chat']->remainingPercentage)->toBe(75.0);
    });

    it('calls account.getQuota and returns empty snapshots', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('account.getQuota', [])
            ->andReturn(['quotaSnapshots' => []]);

        $pending = new PendingServerAccount($client);
        $result = $pending->getQuota();

        expect($result)->toBeInstanceOf(AccountGetQuotaResult::class)
            ->and($result->quotaSnapshots)->toBeEmpty();
    });

    it('calls account.getQuota with gitHubToken param', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('account.getQuota', ['gitHubToken' => 'ghs_abc'])
            ->andReturn(['quotaSnapshots' => []]);

        $pending = new PendingServerAccount($client);
        $result = $pending->getQuota(new AccountGetQuotaRequest(gitHubToken: 'ghs_abc'));

        expect($result)->toBeInstanceOf(AccountGetQuotaResult::class);
    });

    it('calls account.getQuota with array param containing gitHubToken', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('account.getQuota', ['gitHubToken' => 'ghs_xyz'])
            ->andReturn(['quotaSnapshots' => []]);

        $pending = new PendingServerAccount($client);
        $result = $pending->getQuota(['gitHubToken' => 'ghs_xyz']);

        expect($result)->toBeInstanceOf(AccountGetQuotaResult::class);
    });
});
