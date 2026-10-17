<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ElicitationAction;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\ElicitationContext;

function invokeHasElicitationHandler(Session $session, ElicitationContext $context, string $requestId): void
{
    (new ReflectionMethod(Session::class, 'handleElicitationRequest'))->invoke($session, $context, $requestId);
}

describe('HasElicitationHandler', function () {
    it('does not respond when no handler is registered', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldNotReceive('request');
        $session = new Session('test-session', $client);

        invokeHasElicitationHandler($session, new ElicitationContext('test-session', 'Continue?'), 'request-1');
    });

    it('sends an array returned by the handler', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.ui.handlePendingElicitation', [
                'requestId' => 'request-1',
                'result' => ['action' => 'accept', 'content' => ['confirmed' => true]],
                'sessionId' => 'test-session',
            ])
            ->andReturn(['success' => true]);
        $session = new Session('test-session', $client);
        $context = new ElicitationContext('test-session', 'Continue?');
        $session->registerElicitationHandler(fn (ElicitationContext $receivedContext) => [
            'action' => 'accept',
            'content' => ['confirmed' => $receivedContext->message === 'Continue?'],
        ]);

        invokeHasElicitationHandler($session, $context, 'request-1');
    });

    it('converts an Arrayable handler result to an array', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.ui.handlePendingElicitation', [
                'requestId' => 'request-2',
                'result' => ['action' => 'decline'],
                'sessionId' => 'test-session',
            ])
            ->andReturn(['success' => true]);
        $result = Mockery::mock(Arrayable::class);
        $result->shouldReceive('toArray')->once()->andReturn(['action' => 'decline']);
        $session = new Session('test-session', $client);
        $session->registerElicitationHandler(fn () => $result);

        invokeHasElicitationHandler($session, new ElicitationContext('test-session', 'Continue?'), 'request-2');
    });

    it('cancels when the handler returns an unsupported result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.ui.handlePendingElicitation', [
                'requestId' => 'request-3',
                'result' => ['action' => ElicitationAction::CANCEL->value],
                'sessionId' => 'test-session',
            ])
            ->andReturn(['success' => true]);
        $session = new Session('test-session', $client);
        $session->registerElicitationHandler(fn () => null);

        invokeHasElicitationHandler($session, new ElicitationContext('test-session', 'Continue?'), 'request-3');
    });

    it('cancels when the handler throws', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.ui.handlePendingElicitation', [
                'requestId' => 'request-4',
                'result' => ['action' => ElicitationAction::CANCEL->value],
                'sessionId' => 'test-session',
            ])
            ->andReturn(['success' => true]);
        $session = new Session('test-session', $client);
        $session->registerElicitationHandler(function (): never {
            throw new RuntimeException('Handler failed');
        });

        invokeHasElicitationHandler($session, new ElicitationContext('test-session', 'Continue?'), 'request-4');
    });

    it('swallows an error while sending the cancellation response', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->andThrow(new RuntimeException('Connection lost'));
        $session = new Session('test-session', $client);
        $session->registerElicitationHandler(function (): never {
            throw new RuntimeException('Handler failed');
        });

        invokeHasElicitationHandler($session, new ElicitationContext('test-session', 'Continue?'), 'request-5');
    });
});
