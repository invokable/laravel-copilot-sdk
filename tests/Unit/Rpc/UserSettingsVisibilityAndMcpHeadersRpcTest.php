<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionVisibilityStatus;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingMcpHeaders;
use Revolution\Copilot\Rpc\PendingServerUserSettings;
use Revolution\Copilot\Rpc\PendingVisibility;
use Revolution\Copilot\Types\Rpc\McpHeadersHandlePendingHeadersRefreshRequest;
use Revolution\Copilot\Types\Rpc\McpHeadersHandlePendingHeadersRefreshRequestRequest;
use Revolution\Copilot\Types\Rpc\McpHeadersHandlePendingHeadersRefreshRequestResult;
use Revolution\Copilot\Types\Rpc\UserSettingsGetResult;
use Revolution\Copilot\Types\Rpc\UserSettingsSetRequest;
use Revolution\Copilot\Types\Rpc\UserSettingsSetResult;
use Revolution\Copilot\Types\Rpc\VisibilityGetResult;
use Revolution\Copilot\Types\Rpc\VisibilitySetRequest;
use Revolution\Copilot\Types\Rpc\VisibilitySetResult;

describe('PendingServerUserSettings', function () {
    it('calls user.settings.get and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('user.settings.get', [])
            ->andReturn([
                'settings' => [
                    'editor.wordWrap' => [
                        'value' => ['on'],
                        'default' => ['off'],
                        'isDefault' => false,
                    ],
                ],
            ]);

        $pending = new PendingServerUserSettings($client);
        $result = $pending->get();

        expect($result)->toBeInstanceOf(UserSettingsGetResult::class)
            ->and($result->settings)->toHaveKey('editor.wordWrap')
            ->and($result->settings['editor.wordWrap']->isDefault)->toBeFalse();
    });

    it('calls user.settings.set with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'user.settings.set',
                Mockery::on(fn ($p) => isset($p['settings'])),
            )
            ->andReturn(['shadowedKeys' => []]);

        $pending = new PendingServerUserSettings($client);
        $result = $pending->set(new UserSettingsSetRequest(settings: ['editor.wordWrap' => 'on']));

        expect($result)->toBeInstanceOf(UserSettingsSetResult::class)
            ->and($result->shadowedKeys)->toBe([]);
    });

    it('calls user.settings.set with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'user.settings.set',
                Mockery::on(fn ($p) => $p['settings']['theme'] === 'dark'),
            )
            ->andReturn(['shadowedKeys' => ['theme']]);

        $pending = new PendingServerUserSettings($client);
        $result = $pending->set(['settings' => ['theme' => 'dark']]);

        expect($result)->toBeInstanceOf(UserSettingsSetResult::class)
            ->and($result->shadowedKeys)->toBe(['theme']);
    });
});

describe('PendingVisibility', function () {
    it('calls session.visibility.get and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.visibility.get', ['sessionId' => 'session-abc'])
            ->andReturn([
                'synced' => true,
                'status' => 'repo',
                'shareUrl' => 'https://github.com/...',
            ]);

        $pending = new PendingVisibility($client, 'session-abc');
        $result = $pending->get();

        expect($result)->toBeInstanceOf(VisibilityGetResult::class)
            ->and($result->synced)->toBeTrue()
            ->and($result->status)->toBe(SessionVisibilityStatus::REPO)
            ->and($result->shareUrl)->toBe('https://github.com/...');
    });

    it('calls session.visibility.get with unsynced result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.visibility.get', ['sessionId' => 'session-abc'])
            ->andReturn(['synced' => false]);

        $pending = new PendingVisibility($client, 'session-abc');
        $result = $pending->get();

        expect($result->synced)->toBeFalse()
            ->and($result->status)->toBeNull()
            ->and($result->shareUrl)->toBeNull();
    });

    it('calls session.visibility.set with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.visibility.set',
                Mockery::on(fn ($p) => $p['sessionId'] === 'session-abc' && $p['status'] === 'unshared'),
            )
            ->andReturn(['synced' => true, 'status' => 'unshared']);

        $pending = new PendingVisibility($client, 'session-abc');
        $result = $pending->set(new VisibilitySetRequest(status: SessionVisibilityStatus::UNSHARED));

        expect($result)->toBeInstanceOf(VisibilitySetResult::class)
            ->and($result->status)->toBe(SessionVisibilityStatus::UNSHARED);
    });

    it('calls session.visibility.set with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.visibility.set',
                Mockery::on(fn ($p) => $p['status'] === 'repo'),
            )
            ->andReturn(['synced' => true, 'status' => 'repo']);

        $pending = new PendingVisibility($client, 'session-abc');
        $result = $pending->set(['status' => 'repo']);

        expect($result->status)->toBe(SessionVisibilityStatus::REPO);
    });
});

describe('PendingMcpHeaders', function () {
    it('calls session.mcp.headers.handlePendingHeadersRefreshRequest with headers', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.headers.handlePendingHeadersRefreshRequest',
                Mockery::on(fn ($p) => $p['sessionId'] === 'session-abc'
                    && $p['requestId'] === 'refresh-req-1'
                    && $p['result']['kind'] === 'headers'
                    && $p['result']['headers']['Authorization'] === 'Bearer tok'),
            )
            ->andReturn(['success' => true]);

        $pending = new PendingMcpHeaders($client, 'session-abc');
        $result = $pending->handlePendingHeadersRefreshRequest(
            new McpHeadersHandlePendingHeadersRefreshRequestRequest(
                requestId: 'refresh-req-1',
                result: new McpHeadersHandlePendingHeadersRefreshRequest(
                    kind: 'headers',
                    headers: ['Authorization' => 'Bearer tok'],
                ),
            ),
        );

        expect($result)->toBeInstanceOf(McpHeadersHandlePendingHeadersRefreshRequestResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('calls session.mcp.headers.handlePendingHeadersRefreshRequest with none', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.mcp.headers.handlePendingHeadersRefreshRequest',
                Mockery::on(fn ($p) => $p['result']['kind'] === 'none'),
            )
            ->andReturn(['success' => false]);

        $pending = new PendingMcpHeaders($client, 'session-abc');
        $result = $pending->handlePendingHeadersRefreshRequest([
            'requestId' => 'refresh-req-2',
            'result' => ['kind' => 'none'],
        ]);

        expect($result->success)->toBeFalse();
    });
});
