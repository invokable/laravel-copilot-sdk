<?php

declare(strict_types=1);

use Revolution\Copilot\AhpHost;
use Revolution\Copilot\Rpc\PendingServerHost;
use Revolution\Copilot\Types\Rpc\HostDisposeRequest;
use Revolution\Copilot\Types\Rpc\HostDisposeResult;
use Revolution\Copilot\Types\Rpc\HostListSessionsRequest;
use Revolution\Copilot\Types\Rpc\HostListSessionsResult;
use Revolution\Copilot\Types\Rpc\HostPublishSessionRequest;
use Revolution\Copilot\Types\Rpc\HostPublishSessionResult;
use Revolution\Copilot\Types\Rpc\HostSetSessionCreationPolicyRequest;
use Revolution\Copilot\Types\Rpc\HostSetSessionCreationPolicyResult;
use Revolution\Copilot\Types\Rpc\HostStartResult;
use Revolution\Copilot\Types\Rpc\HostUnpublishSessionRequest;
use Revolution\Copilot\Types\Rpc\HostUnpublishSessionResult;

describe('AhpHost', function () {
    it('exposes the host details returned when it starts', function () {
        $host = new AhpHost(
            new HostStartResult(
                hostId: 'host-1',
                url: 'wss://host.example',
                token: 'host-token',
                environmentId: 'environment-1',
                pid: 1234,
            ),
            Mockery::mock(PendingServerHost::class),
            fn () => null,
        );

        expect($host->hostId)->toBe('host-1')
            ->and($host->url)->toBe('wss://host.example')
            ->and($host->token)->toBe('host-token')
            ->and($host->environmentId)->toBe('environment-1')
            ->and($host->pid)->toBe(1234);
    });

    it('preserves absent optional host details', function () {
        $host = new AhpHost(
            new HostStartResult(hostId: 'host-1'),
            Mockery::mock(PendingServerHost::class),
            fn () => null,
        );

        expect($host->url)->toBeNull()
            ->and($host->token)->toBeNull()
            ->and($host->environmentId)->toBeNull()
            ->and($host->pid)->toBeNull();
    });

    it('forwards host operations and returns their results', function () {
        $rpc = Mockery::mock(PendingServerHost::class);
        $policyResult = new HostSetSessionCreationPolicyResult;
        $publishResult = new HostPublishSessionResult('session-1', 'ahp://host/session-1');
        $unpublishResult = new HostUnpublishSessionResult(true);
        $listResult = new HostListSessionsResult;

        $rpc->shouldReceive('setSessionCreationPolicy')
            ->once()
            ->with(Mockery::on(fn (HostSetSessionCreationPolicyRequest $request): bool => $request->hostId === 'host-1' && $request->allowCreatingNewSessions === false
            ))
            ->andReturn($policyResult);
        $rpc->shouldReceive('publishSession')
            ->once()
            ->with(Mockery::on(fn (HostPublishSessionRequest $request): bool => $request->hostId === 'host-1' && $request->sessionId === 'session-1'
            ))
            ->andReturn($publishResult);
        $rpc->shouldReceive('unpublishSession')
            ->once()
            ->with(Mockery::on(fn (HostUnpublishSessionRequest $request): bool => $request->hostId === 'host-1' && $request->sessionId === 'session-1'
            ))
            ->andReturn($unpublishResult);
        $rpc->shouldReceive('listSessions')
            ->once()
            ->with(Mockery::on(fn (HostListSessionsRequest $request): bool => $request->hostId === 'host-1'))
            ->andReturn($listResult);

        $host = new AhpHost(new HostStartResult(hostId: 'host-1'), $rpc, fn () => null);

        expect($host->setSessionCreationPolicy(false))->toBe($policyResult)
            ->and($host->publishSession('session-1'))->toBe($publishResult)
            ->and($host->unpublishSession('session-1'))->toBe($unpublishResult)
            ->and($host->listSessions())->toBe($listResult);
    });

    it('calls the disposal callback after disposing the host', function () {
        $rpc = Mockery::mock(PendingServerHost::class);
        $result = new HostDisposeResult;
        $callbackCalled = false;

        $rpc->shouldReceive('dispose')
            ->once()
            ->with(Mockery::on(fn (HostDisposeRequest $request): bool => $request->hostId === 'host-1'))
            ->andReturnUsing(function () use (&$callbackCalled, $result): HostDisposeResult {
                expect($callbackCalled)->toBeFalse();

                return $result;
            });

        $host = new AhpHost(
            new HostStartResult(hostId: 'host-1'),
            $rpc,
            function () use (&$callbackCalled): void {
                $callbackCalled = true;
            },
        );

        expect($host->dispose())->toBe($result)
            ->and($callbackCalled)->toBeTrue();
    });
});
