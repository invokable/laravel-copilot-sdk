<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Revolution\Copilot\Enums\ShellAuthenticationInputKind;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Types\Rpc\ShellAuthenticationRequest;

describe('HasShellAuthenticationHandler', function () {
    it('passes the request and cancellation signal to the handler', function () {
        $session = new Session('test-session', Mockery::mock(JsonRpcClient::class));
        $request = new ShellAuthenticationRequest(
            requestId: 'request-1',
            program: 'ssh',
            prompt: 'Password:',
            inputKind: ShellAuthenticationInputKind::Secret,
            command: 'ssh example.com',
            cwd: '/workspace',
        );
        $requestToken = new CancellationToken;
        $session->registerShellAuthenticationHandler(
            function (ShellAuthenticationRequest $receivedRequest, array $invocation) use ($request): array {
                expect($receivedRequest)->toBe($request)
                    ->and($invocation['sessionId'])->toBe('test-session')
                    ->and($invocation['signal'])->toBeInstanceOf(CancellationToken::class)
                    ->and($invocation['signal']->isCancellationRequested())->toBeFalse();

                return ['action' => 'accept', 'value' => 'secret answer'];
            },
        );

        $response = $session->handleShellAuthenticationRequest($request, $requestToken);

        expect($response->toArray())->toBe([
            'action' => 'accept',
            'value' => 'secret answer',
        ]);
    });

    it('cancels when no handler is registered', function () {
        $session = new Session('test-session', Mockery::mock(JsonRpcClient::class));
        $request = new ShellAuthenticationRequest(
            requestId: 'request-1',
            program: 'sudo',
            prompt: 'Password:',
            inputKind: ShellAuthenticationInputKind::Secret,
            command: 'sudo -A',
            cwd: '/workspace',
        );

        expect($session->handleShellAuthenticationRequest($request, new CancellationToken)->toArray())
            ->toBe(['action' => 'cancel']);
    });

    it('cancels when the handler returns a cancellation or invalid answer', function () {
        $session = new Session('test-session', Mockery::mock(JsonRpcClient::class));
        $request = new ShellAuthenticationRequest(
            requestId: 'request-1',
            program: 'ssh',
            prompt: 'Password:',
            inputKind: ShellAuthenticationInputKind::Secret,
            command: 'ssh example.com',
            cwd: '/workspace',
        );

        foreach ([
            ['action' => 'cancel'],
            ['action' => 'accept'],
            ['action' => 'accept', 'value' => 123],
            ['action' => 'unexpected', 'value' => 'secret answer'],
        ] as $result) {
            $session->registerShellAuthenticationHandler(fn () => $result);

            expect($session->handleShellAuthenticationRequest($request, new CancellationToken)->toArray())
                ->toBe(['action' => 'cancel']);
        }
    });

    it('cancels on handler exceptions without logging the answer', function () {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                $loggedContext = json_encode($context) ?: '';

                return $message === 'Shell authentication handler failed'
                    && $context['exceptionType'] === RuntimeException::class
                    && ! str_contains($loggedContext, 'secret answer');
            });
        $session = new Session('test-session', Mockery::mock(JsonRpcClient::class));
        $request = new ShellAuthenticationRequest(
            requestId: 'request-1',
            program: 'ssh',
            prompt: 'Password:',
            inputKind: ShellAuthenticationInputKind::Secret,
            command: 'ssh example.com',
            cwd: '/workspace',
        );
        $session->registerShellAuthenticationHandler(function (): never {
            throw new RuntimeException('secret answer');
        });

        $response = $session->handleShellAuthenticationRequest($request, new CancellationToken);

        expect($response->toArray())->toBe(['action' => 'cancel']);
    });
});
