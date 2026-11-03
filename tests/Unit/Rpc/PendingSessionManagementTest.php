<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionManagementScope;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingSessionManagement;
use Revolution\Copilot\Rpc\SessionRpc;
use Revolution\Copilot\Types\Rpc\ManagedSessionInfo;
use Revolution\Copilot\Types\Rpc\SessionManagementCloseResult;
use Revolution\Copilot\Types\Rpc\SessionManagementConfigureRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementListResult;
use Revolution\Copilot\Types\Rpc\SessionManagementMessageRequest;
use Revolution\Copilot\Types\Rpc\SessionManagementMessageResult;
use Revolution\Copilot\Types\Rpc\SessionManagementPolicy;
use Revolution\Copilot\Types\Rpc\SessionManagementToolOverrides;

describe('PendingSessionManagement', function () {
    it('configures the policy and decodes the typed result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.management.configure', [
                'enabled' => true,
                'scope' => 'in-process',
                'tools' => ['list' => true, 'message' => true],
                'sessionId' => 'session-parent',
            ])
            ->andReturn([
                'enabled' => true,
                'scope' => 'in-process',
                'list' => true,
                'create' => false,
                'message' => true,
                'close' => false,
            ]);

        $management = (new SessionRpc($client, 'session-parent'))->management();
        $result = $management->configure(new SessionManagementConfigureRequest(
            enabled: true,
            scope: SessionManagementScope::InProcess,
            tools: new SessionManagementToolOverrides(list: true, message: true),
        ));

        expect($management)->toBeInstanceOf(PendingSessionManagement::class)
            ->and($result)->toBeInstanceOf(SessionManagementPolicy::class)
            ->and($result->scope)->toBe(SessionManagementScope::InProcess)
            ->and($result->create)->toBeFalse();
    });

    it('lists peers with the bound session id and typed session metadata', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.management.list', [
                'cwd' => '/workspace',
                'sessionId' => 'session-parent',
            ])
            ->andReturn([
                'sessions' => [[
                    'sessionId' => 'session-child',
                    'name' => 'worker',
                    'cwd' => '/workspace',
                    'sessionStatePath' => '/state/child',
                    'state' => 'busy',
                    'model' => 'gpt-5',
                    'parentSessionId' => 'session-parent',
                ]],
            ]);

        $result = (new SessionRpc($client, 'session-parent'))->management()->list(['cwd' => '/workspace']);

        expect($result)->toBeInstanceOf(SessionManagementListResult::class)
            ->and($result->sessions[0])->toBeInstanceOf(ManagedSessionInfo::class)
            ->and($result->sessions[0]->sessionId)->toBe('session-child')
            ->and($result->sessions[0]->state)->toBe('busy');
    });

    it('sends peer text and returns its acceptance status', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.management.message', [
                'targetSessionId' => 'session-child',
                'message' => 'Progress update?',
                'sessionId' => 'session-parent',
            ])
            ->andReturn([
                'accepted' => true,
                'targetSessionName' => 'worker',
            ]);

        $result = (new SessionRpc($client, 'session-parent'))->management()->message(
            new SessionManagementMessageRequest(
                targetSessionId: 'session-child',
                message: 'Progress update?',
            ),
        );

        expect($result)->toBeInstanceOf(SessionManagementMessageResult::class)
            ->and($result->accepted)->toBeTrue()
            ->and($result->targetSessionName)->toBe('worker');
    });

    it('closes a target session and returns the close status', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.management.close', [
                'targetSessionId' => 'session-child',
                'sessionId' => 'session-parent',
            ])
            ->andReturn(['closed' => true]);

        $result = (new SessionRpc($client, 'session-parent'))->management()->close([
            'targetSessionId' => 'session-child',
        ]);

        expect($result)->toBeInstanceOf(SessionManagementCloseResult::class)
            ->and($result->closed)->toBeTrue();
    });
});
