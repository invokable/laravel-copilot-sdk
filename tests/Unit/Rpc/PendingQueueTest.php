<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AgentMode;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingQueue;
use Revolution\Copilot\Types\Rpc\QueueBeginDeferredIdleDrainResult;
use Revolution\Copilot\Types\Rpc\QueueDuplicateAtRequest;
use Revolution\Copilot\Types\Rpc\QueueFinishDeferredIdleDrainResult;
use Revolution\Copilot\Types\Rpc\QueueInsertAtRequest;
use Revolution\Copilot\Types\Rpc\QueueInsertMessage;
use Revolution\Copilot\Types\Rpc\QueueMoveItemRequest;
use Revolution\Copilot\Types\Rpc\QueueRemoveAtRequest;
use Revolution\Copilot\Types\Rpc\QueueSendNowRequest;
use Revolution\Copilot\Types\Rpc\QueueSetDrainPausedRequest;
use Revolution\Copilot\Types\Rpc\QueueUpdateTextRequest;

describe('PendingQueue', function () {
    it('inserts queued messages with arrays or request types', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.insertAt', [
                'position' => 0,
                'message' => ['prompt' => 'first'],
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['id' => 'queue-first']);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.insertAt', [
                'position' => 2,
                'message' => [
                    'prompt' => 'second',
                    'agentMode' => 'plan',
                ],
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['id' => 'queue-second']);

        $queue = new PendingQueue($client, 'session-abc');
        $arrayResult = $queue->insertAt([
            'position' => 0,
            'message' => ['prompt' => 'first', 'displayPrompt' => null],
        ]);
        $requestResult = $queue->insertAt(new QueueInsertAtRequest(
            position: 2,
            message: new QueueInsertMessage(prompt: 'second', agentMode: AgentMode::PLAN),
        ));

        expect($arrayResult->id)->toBe('queue-first')
            ->and($requestResult->id)->toBe('queue-second');
    });

    it('moves queued items with arrays or request types', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.moveItem', [
                'id' => 'queue-first',
                'toPosition' => 1,
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['changed' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.moveItem', [
                'id' => 'queue-second',
                'toPosition' => 0,
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['changed' => false]);

        $queue = new PendingQueue($client, 'session-abc');
        $arrayResult = $queue->moveItem(['id' => 'queue-first', 'toPosition' => 1]);
        $requestResult = $queue->moveItem(new QueueMoveItemRequest(id: 'queue-second', toPosition: 0));

        expect($arrayResult->changed)->toBeTrue()
            ->and($requestResult->changed)->toBeFalse();
    });

    it('removes queued items by id with arrays or request types', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.removeAt', [
                'id' => 'queue-first',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['removed' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.removeAt', [
                'id' => 'queue-second',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['removed' => false]);

        $queue = new PendingQueue($client, 'session-abc');
        $arrayResult = $queue->removeAt(['id' => 'queue-first']);
        $requestResult = $queue->removeAt(new QueueRemoveAtRequest(id: 'queue-second'));

        expect($arrayResult->removed)->toBeTrue()
            ->and($requestResult->removed)->toBeFalse();
    });

    it('duplicates queued items by id with arrays or request types', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.duplicateAt', [
                'id' => 'queue-first',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['id' => 'queue-first-copy']);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.duplicateAt', [
                'id' => 'queue-second',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['id' => 'queue-second-copy']);

        $queue = new PendingQueue($client, 'session-abc');
        $arrayResult = $queue->duplicateAt(['id' => 'queue-first']);
        $requestResult = $queue->duplicateAt(new QueueDuplicateAtRequest(id: 'queue-second'));

        expect($arrayResult->id)->toBe('queue-first-copy')
            ->and($requestResult->id)->toBe('queue-second-copy');
    });

    it('sends queued items now with arrays or request types', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.sendNow', [
                'id' => 'queue-first',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['steered' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.sendNow', [
                'id' => 'queue-second',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['steered' => false]);

        $queue = new PendingQueue($client, 'session-abc');
        $arrayResult = $queue->sendNow(['id' => 'queue-first']);
        $requestResult = $queue->sendNow(new QueueSendNowRequest(id: 'queue-second'));

        expect($arrayResult->steered)->toBeTrue()
            ->and($requestResult->steered)->toBeFalse();
    });

    it('sets the drain pause with arrays or request types', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.setDrainPaused', [
                'paused' => true,
                'sessionId' => 'session-abc',
            ])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.setDrainPaused', [
                'paused' => false,
                'sessionId' => 'session-abc',
            ])
            ->andReturn([]);

        $queue = new PendingQueue($client, 'session-abc');

        expect($queue->setDrainPaused(['paused' => true]))->toBeNull()
            ->and($queue->setDrainPaused(new QueueSetDrainPausedRequest(paused: false)))->toBeNull();
    });

    it('updates queued text with arrays or request types', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.updateText', [
                'id' => 'queue-first',
                'prompt' => 'new prompt',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['updated' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('session.queue.updateText', [
                'id' => 'queue-second',
                'prompt' => 'another prompt',
                'displayPrompt' => 'Updated label',
                'sessionId' => 'session-abc',
            ])
            ->andReturn(['updated' => false]);

        $queue = new PendingQueue($client, 'session-abc');
        $arrayResult = $queue->updateText([
            'id' => 'queue-first',
            'prompt' => 'new prompt',
            'displayPrompt' => null,
        ]);
        $requestResult = $queue->updateText(new QueueUpdateTextRequest(
            id: 'queue-second',
            prompt: 'another prompt',
            displayPrompt: 'Updated label',
        ));

        expect($arrayResult->updated)->toBeTrue()
            ->and($requestResult->updated)->toBeFalse();
    });

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
