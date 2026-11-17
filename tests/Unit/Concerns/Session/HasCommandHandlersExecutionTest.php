<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\CommandContext;

function invokeCommandHandler(Session $session, string $requestId, string $commandName, string $command, string $args): void
{
    (new ReflectionMethod(Session::class, 'executeCommandAndRespond'))
        ->invoke($session, $requestId, $commandName, $command, $args);
}

describe('HasCommandHandlers execution', function () {
    it('runs a registered handler and responds successfully', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.commands.handlePendingCommand', [
                'requestId' => 'request-1',
                'sessionId' => 'test-session',
            ])
            ->andReturn(['success' => true]);
        $session = new Session('test-session', $client);
        $session->registerCommands([
            ['name' => 'deploy', 'handler' => function (CommandContext $context): void {
                expect($context->sessionId)->toBe('test-session')
                    ->and($context->commandName)->toBe('deploy')
                    ->and($context->command)->toBe('/deploy staging')
                    ->and($context->args)->toBe('staging');
            }],
        ]);

        invokeCommandHandler($session, 'request-1', 'deploy', '/deploy staging', 'staging');
    });

    it('responds with an error when the command is not registered', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.commands.handlePendingCommand', [
                'requestId' => 'request-2',
                'error' => 'Unknown command: missing',
                'sessionId' => 'test-session',
            ])
            ->andReturn(['success' => true]);
        $session = new Session('test-session', $client);

        invokeCommandHandler($session, 'request-2', 'missing', '/missing', '');
    });

    it('responds with a generic error when a command handler throws', function () {
        Log::shouldReceive('error')
            ->once()
            ->with('Command handler failed', Mockery::on(fn (array $context): bool => $context['command'] === 'deploy'
                && $context['exception'] instanceof RuntimeException));
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.commands.handlePendingCommand', [
                'requestId' => 'request-3',
                'error' => 'Command failed',
                'sessionId' => 'test-session',
            ])
            ->andReturn(['success' => true]);
        $session = new Session('test-session', $client);
        $session->registerCommands([
            ['name' => 'deploy', 'handler' => function (): never {
                throw new RuntimeException('Sensitive handler detail');
            }],
        ]);

        invokeCommandHandler($session, 'request-3', 'deploy', '/deploy', '');
    });

    it('swallows RPC failures while responding to an unknown command', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()->andThrow(new RuntimeException('Connection lost'));
        $session = new Session('test-session', $client);

        invokeCommandHandler($session, 'request-4', 'missing', '/missing', '');

        expect($session->getCommandHandler('missing'))->toBeNull();
    });

    it('swallows RPC failures after a command handler throws', function () {
        Log::shouldReceive('error')->once();
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')->once()->andThrow(new RuntimeException('Connection lost'));
        $session = new Session('test-session', $client);
        $session->registerCommands([
            ['name' => 'deploy', 'handler' => function (): never {
                throw new RuntimeException('Handler failed');
            }],
        ]);

        invokeCommandHandler($session, 'request-5', 'deploy', '/deploy', '');

        expect($session->getCommandHandler('deploy'))->not->toBeNull();
    });

    it('ignores malformed command registrations', function () {
        $session = new Session('test-session', Mockery::mock(JsonRpcClient::class));
        $handler = fn () => null;
        $session->registerCommands([
            new stdClass,
            ['name' => 42, 'handler' => $handler],
            ['name' => 'invalid-handler', 'handler' => 'not callable'],
            ['name' => 'valid', 'handler' => $handler],
        ]);

        expect($session->getCommandHandler('valid'))->toBe($handler)
            ->and($session->getCommandHandler('invalid-handler'))->toBeNull()
            ->and($session->getCommandHandler('42'))->toBeNull();
    });
});
