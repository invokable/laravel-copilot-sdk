<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Events\Client\ClientStarted;
use Revolution\Copilot\Events\Client\PingPong;
use Revolution\Copilot\Events\Client\ToolCall;

describe('PingPong', function () {
    it('stores response array', function () {
        $response = ['id' => 'test-id', 'result' => ['message' => 'pong']];
        $event = new PingPong($response);

        expect($event->response)->toBe($response);
    });

    it('stores empty response array', function () {
        $event = new PingPong([]);

        expect($event->response)->toBe([]);
    });
});

describe('ToolCall', function () {
    it('stores arguments, invocation and result', function () {
        $arguments = ['param' => 'value'];
        $invocation = ['tool' => 'my_tool', 'id' => 'call-1'];
        $result = ['output' => 'success'];

        $event = new ToolCall($arguments, $invocation, $result);

        expect($event->arguments)->toBe($arguments)
            ->and($event->invocation)->toBe($invocation)
            ->and($event->result)->toBe($result);
    });

    it('accepts null result', function () {
        $event = new ToolCall([], [], null);

        expect($event->result)->toBeNull();
    });

    it('accepts scalar result', function () {
        $event = new ToolCall(['x' => 1], ['tool' => 'calc'], 42);

        expect($event->result)->toBe(42);
    });
});

describe('ClientStarted', function () {
    it('stores client reference', function () {
        $client = Mockery::mock(Client::class);
        $event = new ClientStarted($client);

        expect($event->client)->toBe($client);
    });
});
