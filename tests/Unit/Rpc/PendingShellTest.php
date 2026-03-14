<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingShell;
use Revolution\Copilot\Types\Rpc\SessionShellExecParams;
use Revolution\Copilot\Types\Rpc\SessionShellExecResult;
use Revolution\Copilot\Types\Rpc\SessionShellKillParams;
use Revolution\Copilot\Types\Rpc\SessionShellKillResult;

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
        $result = $pending->exec(new SessionShellExecParams(command: 'ls -la'));

        expect($result)->toBeInstanceOf(SessionShellExecResult::class)
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
        $result = $pending->exec(new SessionShellExecParams(
            command: 'npm test',
            cwd: '/app',
            timeout: 60000,
        ));

        expect($result)->toBeInstanceOf(SessionShellExecResult::class)
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

        expect($result)->toBeInstanceOf(SessionShellExecResult::class)
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
        $result = $pending->kill(new SessionShellKillParams(processId: 'proc-123'));

        expect($result)->toBeInstanceOf(SessionShellKillResult::class)
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
        $result = $pending->kill(new SessionShellKillParams(
            processId: 'proc-456',
            signal: 'SIGKILL',
        ));

        expect($result)->toBeInstanceOf(SessionShellKillResult::class)
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

        expect($result)->toBeInstanceOf(SessionShellKillResult::class)
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

        expect($result)->toBeInstanceOf(SessionShellExecResult::class);
    });
});
