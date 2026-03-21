<?php

declare(strict_types=1);

use Revolution\Copilot\Events\JsonRpc\MessageReceived;
use Revolution\Copilot\Events\JsonRpc\MessageSending;
use Revolution\Copilot\Events\JsonRpc\ResponseReceived;
use Revolution\Copilot\JsonRpc\JsonRpcMessage;

describe('MessageReceived', function () {
    it('stores the received message', function () {
        $message = JsonRpcMessage::notification('session.event', ['type' => 'assistant_message']);
        $event = new MessageReceived($message);

        expect($event->message)->toBe($message)
            ->and($event->message->method)->toBe('session.event');
    });
});

describe('MessageSending', function () {
    it('stores the outgoing message', function () {
        $message = JsonRpcMessage::request('req-1', 'session.create', ['model' => 'gpt-4']);
        $event = new MessageSending($message);

        expect($event->message)->toBe($message)
            ->and($event->message->id)->toBe('req-1');
    });
});

describe('ResponseReceived', function () {
    it('stores request ID and response message', function () {
        $message = JsonRpcMessage::response('req-1', ['status' => 'ok']);
        $event = new ResponseReceived('req-1', $message);

        expect($event->requestId)->toBe('req-1')
            ->and($event->message)->toBe($message);
    });

    it('accepts null message', function () {
        $event = new ResponseReceived('req-timeout', null);

        expect($event->requestId)->toBe('req-timeout')
            ->and($event->message)->toBeNull();
    });
});
