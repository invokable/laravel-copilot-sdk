<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingShell;
use Revolution\Copilot\Types\Rpc\ShellExecRequest;
use Revolution\Copilot\Types\Rpc\ShellExecResult;
use Revolution\Copilot\Types\Rpc\ShellExecuteUserRequestedRequest;
use Revolution\Copilot\Types\Rpc\ShellKillRequest;
use Revolution\Copilot\Types\Rpc\ShellKillResult;
use Revolution\Copilot\Types\Rpc\UserRequestedShellCommandResult;

describe('PendingShell', function () {
    it('calls session.shell.exec with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.shell.exec',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['command'] === 'ls -la'
                    && ! isset($params['cwd'])
                    && ! isset($params['timeout'])),
            )
            ->andReturn(['processId' => 'proc-123']);

        $pending = new PendingShell($client, 'test-session-id');
        $result = $pending->exec(new ShellExecRequest(command: 'ls -la'));

        expect($result)->toBeInstanceOf(ShellExecResult::class)
            ->and($result->processId)->toBe('proc-123');
    });

    it('calls session.shell.exec with cwd and timeout', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.shell.exec',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['command'] === 'npm test'
                    && $params['cwd'] === '/app'
                    && $params['timeout'] === 60000),
            )
            ->andReturn(['processId' => 'proc-456']);

        $pending = new PendingShell($client, 'test-session-id');
        $result = $pending->exec(new ShellExecRequest(
            command: 'npm test',
            cwd: '/app',
            timeout: 60000,
        ));

        expect($result)->toBeInstanceOf(ShellExecResult::class)
            ->and($result->processId)->toBe('proc-456');
    });

    it('calls session.shell.exec with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.shell.exec',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['command'] === 'echo hello'),
            )
            ->andReturn(['processId' => 'proc-789']);

        $pending = new PendingShell($client, 'test-session-id');
        $result = $pending->exec(['command' => 'echo hello']);

        expect($result)->toBeInstanceOf(ShellExecResult::class)
            ->and($result->processId)->toBe('proc-789');
    });

    it('calls session.shell.kill with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.shell.kill',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['processId'] === 'proc-123'
                    && ! isset($params['signal'])),
            )
            ->andReturn(['killed' => true]);

        $pending = new PendingShell($client, 'test-session-id');
        $result = $pending->kill(new ShellKillRequest(processId: 'proc-123'));

        expect($result)->toBeInstanceOf(ShellKillResult::class)
            ->and($result->killed)->toBeTrue();
    });

    it('calls session.shell.kill with signal param', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.shell.kill',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['processId'] === 'proc-456'
                    && $params['signal'] === 'SIGKILL'),
            )
            ->andReturn(['killed' => true]);

        $pending = new PendingShell($client, 'test-session-id');
        $result = $pending->kill(new ShellKillRequest(
            processId: 'proc-456',
            signal: 'SIGKILL',
        ));

        expect($result)->toBeInstanceOf(ShellKillResult::class)
            ->and($result->killed)->toBeTrue();
    });

    it('calls session.shell.kill with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.shell.kill',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'
                    && $params['processId'] === 'proc-789'),
            )
            ->andReturn(['killed' => false]);

        $pending = new PendingShell($client, 'test-session-id');
        $result = $pending->kill(['processId' => 'proc-789']);

        expect($result)->toBeInstanceOf(ShellKillResult::class)
            ->and($result->killed)->toBeFalse();
    });

    it('overrides sessionId when provided in array params for exec', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'session.shell.exec',
                Mockery::on(fn ($params) => $params['sessionId'] === 'test-session-id'),
            )
            ->andReturn(['processId' => 'proc-abc']);

        $pending = new PendingShell($client, 'test-session-id');
        $result = $pending->exec([
            'command' => 'pwd',
            'sessionId' => 'some-other-session',
        ]);

        expect($result)->toBeInstanceOf(ShellExecResult::class);
    });

    it('executes a user-requested command with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.shell.executeUserRequested', [
                'requestId' => 'req-123',
                'command' => 'pwd',
                'sessionId' => 'test-session-id',
            ], 30.0)
            ->andReturn([
                'toolCallId' => 'tool-123',
                'success' => true,
                'output' => '/workspace',
                'exitCode' => null,
            ]);

        $result = (new PendingShell($client, 'test-session-id'))->executeUserRequested(
            new ShellExecuteUserRequestedRequest(requestId: 'req-123', command: 'pwd'),
        );

        expect($result)->toBeInstanceOf(UserRequestedShellCommandResult::class)
            ->and($result->toolCallId)->toBe('tool-123')
            ->and($result->success)->toBeTrue()
            ->and($result->exitCode)->toBeNull()
            ->and($result->error)->toBeNull();
    });

    it('executes a user-requested command with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.shell.executeUserRequested', [
                'requestId' => 'req-456',
                'command' => 'missing-command',
                'sessionId' => 'test-session-id',
            ], 120.0)
            ->andReturn([
                'toolCallId' => 'tool-456',
                'success' => false,
                'output' => '',
                'exitCode' => 127,
                'error' => 'Command not found',
            ]);

        $result = (new PendingShell($client, 'test-session-id'))->executeUserRequested([
            'requestId' => 'req-456',
            'command' => 'missing-command',
        ], timeout: 120.0);

        expect($result)->toBeInstanceOf(UserRequestedShellCommandResult::class)
            ->and($result->success)->toBeFalse()
            ->and($result->exitCode)->toBe(127)
            ->and($result->error)->toBe('Command not found');
    });
});
