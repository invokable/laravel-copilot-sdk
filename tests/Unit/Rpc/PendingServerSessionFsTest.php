<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerSessionFs;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderParams;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderResult;

describe('PendingServerSessionFs', function () {
    it('calls sessionFs.setProvider with typed params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'sessionFs.setProvider',
                Mockery::on(fn ($params) => $params['initialCwd'] === '/app'
                    && $params['sessionStatePath'] === '.copilot/sessions'
                    && $params['conventions'] === 'posix'),
            )
            ->andReturn(['success' => true]);

        $pending = new PendingServerSessionFs($client);
        $result = $pending->setProvider(new SessionFsSetProviderParams(
            initialCwd: '/app',
            sessionStatePath: '.copilot/sessions',
            conventions: 'posix',
        ));

        expect($result)->toBeInstanceOf(SessionFsSetProviderResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('calls sessionFs.setProvider with array params', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with(
                'sessionFs.setProvider',
                Mockery::on(fn ($params) => $params['initialCwd'] === 'C:\\project'
                    && $params['conventions'] === 'windows'),
            )
            ->andReturn(['success' => true]);

        $pending = new PendingServerSessionFs($client);
        $result = $pending->setProvider([
            'initialCwd' => 'C:\\project',
            'sessionStatePath' => '.state',
            'conventions' => 'windows',
        ]);

        expect($result)->toBeInstanceOf(SessionFsSetProviderResult::class)
            ->and($result->success)->toBeTrue();
    });

    it('handles failure result', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->andReturn(['success' => false]);

        $pending = new PendingServerSessionFs($client);
        $result = $pending->setProvider(new SessionFsSetProviderParams(
            initialCwd: '/app',
            sessionStatePath: '.state',
        ));

        expect($result->success)->toBeFalse();
    });
});
