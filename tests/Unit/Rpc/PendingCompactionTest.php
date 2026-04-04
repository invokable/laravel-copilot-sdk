<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingCompaction;
use Revolution\Copilot\Types\Rpc\SessionCompactionCompactResult;

describe('PendingCompaction', function () {
    it('calls session.compaction.compact and returns result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.compaction.compact',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'),
            )
            ->andReturn([
                'success' => true,
                'tokensRemoved' => 1500,
                'messagesRemoved' => 10,
            ]);

        $pending = new PendingCompaction($client, 'session-abc');
        $result = $pending->compact();

        expect($result)->toBeInstanceOf(SessionCompactionCompactResult::class)
            ->and($result->success)->toBeTrue()
            ->and($result->tokensRemoved)->toBe(1500)
            ->and($result->messagesRemoved)->toBe(10);
    });
});
