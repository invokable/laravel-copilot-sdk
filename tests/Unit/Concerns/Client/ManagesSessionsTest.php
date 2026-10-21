<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Enums\SessionLifecycleEventType;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\SessionLifecycleEvent;
use Revolution\Copilot\Types\SessionListFilter;
use Revolution\Copilot\Types\SessionMetadata;
use RuntimeException;

function createConnectedManagesSessionsClient(JsonRpcClient $rpcClient): Client
{
    $client = new Client;

    (new ReflectionProperty(Client::class, 'rpcClient'))->setValue($client, $rpcClient);
    (new ReflectionProperty(Client::class, 'state'))->setValue($client, ConnectionState::CONNECTED);

    return $client;
}

describe('ManagesSessions', function () {
    it('gets the last session ID or null when none is available', function () {
        $rpcClient = Mockery::mock(JsonRpcClient::class);
        $rpcClient->shouldReceive('request')
            ->twice()
            ->with('session.getLastId', [])
            ->andReturn(['sessionId' => 'session-123'], []);

        $client = createConnectedManagesSessionsClient($rpcClient);

        expect($client->getLastSessionId())->toBe('session-123')
            ->and($client->getLastSessionId())->toBeNull();
    });

    it('deletes a session and removes it from the active session cache', function () {
        $rpcClient = Mockery::mock(JsonRpcClient::class);
        $rpcClient->shouldReceive('request')
            ->once()
            ->with('session.delete', ['sessionId' => 'session-123'])
            ->andReturn(['success' => true]);

        $client = createConnectedManagesSessionsClient($rpcClient);
        (new ReflectionProperty(Client::class, 'sessions'))->setValue($client, ['session-123' => new stdClass]);

        $client->deleteSession('session-123');

        expect((new ReflectionProperty(Client::class, 'sessions'))->getValue($client))->toBeEmpty();
    });

    it('throws when session deletion fails', function () {
        $rpcClient = Mockery::mock(JsonRpcClient::class);
        $rpcClient->shouldReceive('request')
            ->once()
            ->with('session.delete', ['sessionId' => 'session-123'])
            ->andReturn(['success' => false, 'error' => 'Session not found']);

        $client = createConnectedManagesSessionsClient($rpcClient);

        expect(fn () => $client->deleteSession('session-123'))
            ->toThrow(RuntimeException::class, 'Failed to delete session: Session not found');
    });

    it('lists sessions with optional filters', function () {
        $metadata = [
            'sessionId' => 'session-123',
            'startTime' => '2026-01-24T10:00:00Z',
            'modifiedTime' => '2026-01-24T10:30:00Z',
            'summary' => 'Session summary',
        ];
        $rpcClient = Mockery::mock(JsonRpcClient::class);
        $rpcClient->shouldReceive('request')
            ->once()
            ->with('session.list', [])
            ->andReturn(['sessions' => [$metadata]]);
        $rpcClient->shouldReceive('request')
            ->once()
            ->with('session.list', ['filter' => ['repository' => 'owner/repo']])
            ->andReturn(['sessions' => [$metadata]]);
        $rpcClient->shouldReceive('request')
            ->once()
            ->with('session.list', ['filter' => ['cwd' => '/workspace']])
            ->andReturn(['sessions' => [$metadata]]);

        $client = createConnectedManagesSessionsClient($rpcClient);
        $sessions = $client->listSessions();

        expect($sessions)->toHaveCount(1)
            ->and($sessions[0])->toBeInstanceOf(SessionMetadata::class)
            ->and($client->listSessions(['repository' => 'owner/repo', 'branch' => null]))->toHaveCount(1)
            ->and($client->listSessions(new SessionListFilter(cwd: '/workspace'))[0]->sessionId)->toBe('session-123');
    });

    it('returns null when session metadata is not found', function () {
        $rpcClient = Mockery::mock(JsonRpcClient::class);
        $rpcClient->shouldReceive('request')
            ->once()
            ->with('session.getMetadata', ['sessionId' => 'missing'])
            ->andReturn([]);

        $client = createConnectedManagesSessionsClient($rpcClient);

        expect($client->getSessionMetadata('missing'))->toBeNull();
    });

    it('returns metadata for a matching session', function () {
        $rpcClient = Mockery::mock(JsonRpcClient::class);
        $rpcClient->shouldReceive('request')
            ->once()
            ->with('session.getMetadata', ['sessionId' => 'session-123'])
            ->andReturn(['session' => [
                'sessionId' => 'session-123',
                'startTime' => '2026-01-24T10:00:00Z',
                'modifiedTime' => '2026-01-24T10:30:00Z',
            ]]);

        $client = createConnectedManagesSessionsClient($rpcClient);
        $metadata = $client->getSessionMetadata('session-123');

        expect($metadata)->toBeInstanceOf(SessionMetadata::class)
            ->and($metadata->sessionId)->toBe('session-123');
    });

    it('gets and sets the foreground session', function () {
        $rpcClient = Mockery::mock(JsonRpcClient::class);
        $rpcClient->shouldReceive('request')
            ->once()
            ->with('session.getForeground', [])
            ->andReturn(['sessionId' => 'session-123']);
        $rpcClient->shouldReceive('request')
            ->once()
            ->with('session.setForeground', ['sessionId' => 'session-456'])
            ->andReturn(['success' => true]);

        $client = createConnectedManagesSessionsClient($rpcClient);

        expect($client->getForegroundSessionId())->toBe('session-123');

        $client->setForegroundSessionId('session-456');
    });

    it('throws when changing the foreground session fails', function () {
        $rpcClient = Mockery::mock(JsonRpcClient::class);
        $rpcClient->shouldReceive('request')
            ->once()
            ->with('session.setForeground', ['sessionId' => 'session-456'])
            ->andReturn(['error' => 'Unable to switch']);

        $client = createConnectedManagesSessionsClient($rpcClient);

        expect(fn () => $client->setForegroundSessionId('session-456'))
            ->toThrow(RuntimeException::class, 'Unable to switch');
    });

    it('dispatches lifecycle events and supports unsubscribing handlers', function () {
        $client = createConnectedManagesSessionsClient(Mockery::mock(JsonRpcClient::class));
        $events = [];
        $handler = function (SessionLifecycleEvent $event) use (&$events): void {
            $events[] = $event;
        };
        $unsubscribe = $client->onLifecycle($handler);
        $dispatch = new ReflectionMethod(Client::class, 'handleLifecycleNotification');

        $dispatch->invoke($client, ['type' => 'session.created', 'sessionId' => 'session-123']);
        $unsubscribe();
        $dispatch->invoke($client, ['type' => 'session.deleted', 'sessionId' => 'session-123']);

        expect($events)->toHaveCount(1)
            ->and($events[0]->type)->toBe(SessionLifecycleEventType::SESSION_CREATED);
    });

    it('ignores invalid lifecycle notifications and handler exceptions', function () {
        $client = createConnectedManagesSessionsClient(Mockery::mock(JsonRpcClient::class));
        $events = [];
        $client->onLifecycle(function (): void {
            throw new RuntimeException('Handler failure');
        });
        $client->onLifecycle(function (SessionLifecycleEvent $event) use (&$events): void {
            $events[] = $event;
        });
        $dispatch = new ReflectionMethod(Client::class, 'handleLifecycleNotification');

        $dispatch->invoke($client, ['sessionId' => 'session-123']);
        $dispatch->invoke($client, ['type' => 'session.created']);
        $dispatch->invoke($client, ['type' => 'unknown.event', 'sessionId' => 'session-123']);
        $dispatch->invoke($client, ['type' => 'session.created', 'sessionId' => 'session-123']);

        expect($events)->toHaveCount(1)
            ->and($events[0]->type)->toBe(SessionLifecycleEventType::SESSION_CREATED);
    });

    it('requires a connected client before requesting sessions', function () {
        $client = new Client;

        expect(fn () => $client->getLastSessionId())
            ->toThrow(RuntimeException::class, 'Client not connected. Call start() first.');
    });
});
