<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\UserInputRequest;
use Revolution\Copilot\Types\UserInputResponse;

describe('HasUserInputHandler', function () {
    it('throws when no handler is registered', function () {
        $session = new Session('test-session', Mockery::mock(JsonRpcClient::class));

        expect(fn () => $session->handleUserInputRequest(new UserInputRequest('What is your name?')))
            ->toThrow(RuntimeException::class, 'User input requested but no handler registered');
    });

    it('passes the request and session context to the handler', function () {
        $session = new Session('test-session', Mockery::mock(JsonRpcClient::class));
        $request = new UserInputRequest('Choose an environment', ['staging', 'production'], false);
        $response = new UserInputResponse('production');
        $session->registerUserInputHandler(function (UserInputRequest $receivedRequest, array $context) use ($request, $response): UserInputResponse {
            expect($receivedRequest)->toBe($request)
                ->and($context)->toBe(['sessionId' => 'test-session']);

            return $response;
        });

        expect($session->handleUserInputRequest($request))->toBe($response);
    });

    it('converts an array handler result to a response', function () {
        $session = new Session('test-session', Mockery::mock(JsonRpcClient::class));
        $session->registerUserInputHandler(fn () => ['answer' => 'Laravel', 'wasFreeform' => true]);

        $response = $session->handleUserInputRequest(new UserInputRequest('Which framework?'));

        expect($response)->toBeInstanceOf(UserInputResponse::class)
            ->and($response->answer)->toBe('Laravel')
            ->and($response->wasFreeform)->toBeTrue();
    });

    it('can clear a registered handler', function () {
        $session = new Session('test-session', Mockery::mock(JsonRpcClient::class));
        $session->registerUserInputHandler(fn () => ['answer' => 'yes']);
        $session->registerUserInputHandler(null);

        expect(fn () => $session->handleUserInputRequest(new UserInputRequest('Continue?')))
            ->toThrow(RuntimeException::class, 'User input requested but no handler registered');
    });
});
