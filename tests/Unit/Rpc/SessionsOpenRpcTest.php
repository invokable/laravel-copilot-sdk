<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\SessionsOpenCreateRequest;
use Revolution\Copilot\Types\Rpc\SessionsOpenResult;
use Revolution\Copilot\Types\SessionConfig;

describe('ServerRpc sessions.open', function () {
    it('calls the server-scoped RPC with typed request options and returns the session ID', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.open', [
                'kind' => 'create',
                'options' => ['sessionId' => 'new-session', 'model' => 'gpt-5'],
            ])
            ->andReturn([
                'status' => 'created',
                'sessionId' => 'new-session',
            ]);

        $result = (new ServerRpc($client))->sessions()->open(new SessionsOpenCreateRequest(
            options: new SessionConfig(sessionId: 'new-session', model: 'gpt-5'),
        ));

        expect($result)->toBeInstanceOf(SessionsOpenResult::class)
            ->and($result->sessionId)->toBe('new-session');
    });

    it('dispatches array requests through the same typed server RPC path', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sessions.open', [
                'kind' => 'resume',
                'sessionId' => 'resume-session',
            ])
            ->andReturn([
                'status' => 'resumed',
                'sessionId' => 'resume-session',
            ]);

        $result = (new ServerRpc($client))->sessions()->open([
            'kind' => 'resume',
            'sessionId' => 'resume-session',
            'sessionApi' => new stdClass,
        ]);

        expect($result->sessionId)->toBe('resume-session');
    });
});
