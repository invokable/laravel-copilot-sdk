<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingQueue;
use Revolution\Copilot\Types\Rpc\QueueBeginDeferredIdleDrainResult;
use Revolution\Copilot\Types\Rpc\QueueFinishDeferredIdleDrainResult;

describe('PendingQueue', function () {
    it('begins a deferred idle drain', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.beginDeferredIdleDrain', [
                'activeBackgroundWork' => false,
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['shouldDrain' => true]);

        $result = (new PendingQueue($client, 'session-abc'))->beginDeferredIdleDrain([
            'activeBackgroundWork' => false,
        ]);

        expect($result)->toBeInstanceOf(QueueBeginDeferredIdleDrainResult::class)
            ->and($result->shouldDrain)->toBeTrue();
    });

    it('finishes a deferred idle drain', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.finishDeferredIdleDrain', [
                'activeBackgroundWork' => false,
                'hasPending' => true,
                'sessionId' => 'session-abc',
            ])
            ->andReturn([
                'action' => 'processQueue',
                'aborted' => false,
            ]);

        $result = (new PendingQueue($client, 'session-abc'))->finishDeferredIdleDrain([
            'activeBackgroundWork' => false,
            'hasPending' => true,
        ]);

        expect($result)->toBeInstanceOf(QueueFinishDeferredIdleDrainResult::class)
            ->and($result->action)->toBe('processQueue')
            ->and($result->aborted)->toBeFalse();
    });
});
