<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\HostUnpublishSessionRequest;
use Revolution\Copilot\Types\Rpc\HostUnpublishSessionResult;

describe('host publication withdrawal', function () {
    it('round trips typed request and result types', function () {
        $request = HostUnpublishSessionRequest::fromArray([
            'hostId' => 'host-1',
            'sessionId' => 'session-1',
        ]);
        $result = HostUnpublishSessionResult::fromArray(['unpublished' => false]);

        expect($request->toArray())->toBe([
            'hostId' => 'host-1',
            'sessionId' => 'session-1',
        ])->and($result->toArray())->toBe(['unpublished' => false]);
    });

    it('unpublishes with typed or array parameters', function () {
        $params = ['hostId' => 'host-1', 'sessionId' => 'session-1'];
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->twice()
            ->with('host.unpublishSession', $params)
            ->andReturn(['unpublished' => true], ['unpublished' => false]);

        $host = (new ServerRpc($client))->host();
        $typedResult = $host->unpublishSession(new HostUnpublishSessionRequest(
            hostId: 'host-1',
            sessionId: 'session-1',
        ));
        $arrayResult = $host->unpublishSession($params);

        expect($typedResult)->toBeInstanceOf(HostUnpublishSessionResult::class)
            ->and($typedResult->unpublished)->toBeTrue()
            ->and($arrayResult->unpublished)->toBeFalse();
    });
});
