<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingCommands;
use Revolution\Copilot\Types\Rpc\CommandsHandlePendingCommandRequest;
use Revolution\Copilot\Types\Rpc\CommandsHandlePendingCommandResult;
use Revolution\Copilot\Types\Rpc\EnqueueCommandParams;
use Revolution\Copilot\Types\Rpc\EnqueueCommandResult;
use Revolution\Copilot\Types\Rpc\ExecuteCommandParams;
use Revolution\Copilot\Types\Rpc\ExecuteCommandResult;

describe('PendingCommands', function () {
    it('enqueues a command with display text', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.commands.enqueue',
                ['command' => '/compact', 'displayText' => 'Compact context', 'sessionId' => 'session-abc'],
            )
            ->andReturn(['queued' => true]);

        $result = (new PendingCommands($client, 'session-abc'))->enqueue(
            new EnqueueCommandParams('/compact', 'Compact context'),
        );

        expect($result)->toBeInstanceOf(EnqueueCommandResult::class)
            ->and($result->queued)->toBeTrue();
    });

    it('executes a command with typed params and returns errors', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.commands.execute', [
                'commandName' => 'review',
                'args' => 'src/',
                'sessionId' => 'session-abc',
            ], 90.0)
            ->andReturn(['error' => 'Command failed']);

        $result = (new PendingCommands($client, 'session-abc'))->execute(
            new ExecuteCommandParams(commandName: 'review', args: 'src/'),
            timeout: 90.0,
        );

        expect($result)->toBeInstanceOf(ExecuteCommandResult::class)
            ->and($result->error)->toBe('Command failed');
    });

    it('executes a command with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.commands.execute', [
                'commandName' => 'help',
                'args' => '',
                'sessionId' => 'session-abc',
            ], 30.0)
            ->andReturn([]);

        $result = (new PendingCommands($client, 'session-abc'))->execute([
            'commandName' => 'help',
            'args' => '',
        ]);

        expect($result)->toBeInstanceOf(ExecuteCommandResult::class)
            ->and($result->error)->toBeNull();
    });

    it('calls session.commands.handlePendingCommand with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.commands.handlePendingCommand',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['requestId'] === 'req-123'),
            )
            ->andReturn(['success' => true]);

        $pending = new PendingCommands($client, 'session-abc');
        $result = $pending->handlePendingCommand(new CommandsHandlePendingCommandRequest(requestId: 'req-123'));

        expect($result)->toBeInstanceOf(CommandsHandlePendingCommandResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('calls session.commands.handlePendingCommand with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.commands.handlePendingCommand',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['requestId'] === 'req-456'),
            )
            ->andReturn(['success' => true]);

        $pending = new PendingCommands($client, 'session-abc');
        $result = $pending->handlePendingCommand(['requestId' => 'req-456']);

        expect($result)->toBeInstanceOf(CommandsHandlePendingCommandResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('calls session.commands.handlePendingCommand with error', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.commands.handlePendingCommand',
                Mockery::on(fn ($params) => $params['sessionId'] === 'session-abc'
                    && $params['requestId'] === 'req-789'
                    && $params['error'] === 'Command failed'),
            )
            ->andReturn(['success' => false]);

        $pending = new PendingCommands($client, 'session-abc');
        $result = $pending->handlePendingCommand(new CommandsHandlePendingCommandRequest(
            requestId: 'req-789',
            error: 'Command failed',
        ));

        expect($result)->toBeInstanceOf(CommandsHandlePendingCommandResult::class)
            ->and($result->success)->toBeFalse();
    });
});
