<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingCommands;
use Revolution\Copilot\Types\Rpc\CommandsHandlePendingCommandRequest;
use Revolution\Copilot\Types\Rpc\CommandsHandlePendingCommandResult;

describe('PendingCommands', function () {
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
