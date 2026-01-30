<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Contracts\Transport;
use Revolution\Copilot\Enums\ConnectionState;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Transport\TcpTransport;

beforeEach(function () {
    Copilot::clearResolvedInstances();
});

describe('Client TCP Mode', function () {
    it('can be instantiated with cli_url option', function () {
        $client = new Client([
            'cli_url' => 'tcp://127.0.0.1:12345',
        ]);

        expect($client)->toBeInstanceOf(Client::class)
            ->and($client->getState())->toBe(ConnectionState::DISCONNECTED)
            ->and($client->isTcpMode())->toBeTrue();
    });

    it('is not tcp mode when cli_url is not set', function () {
        $client = new Client([
            'cli_path' => '/test/copilot',
        ]);

        expect($client->isTcpMode())->toBeFalse();
    });

    it('start method connects via TCP transport', function () {
        $mockTransport = Mockery::mock(TcpTransport::class, Transport::class);
        $mockTransport->shouldReceive('start')->once();

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->twice();
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => 2]);

        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        // Use reflection to inject the mock transport
        $client = new Client(['cli_url' => 'tcp://127.0.0.1:12345']);
        $reflection = new ReflectionClass($client);
        $transportProperty = $reflection->getProperty('transport');
        $transportProperty->setValue($client, $mockTransport);

        $client->start();

        expect($client->getState())->toBe(ConnectionState::CONNECTED)
            ->and($client->isTcpMode())->toBeTrue();
    });

    it('stop method closes TCP transport', function () {
        $mockTransport = Mockery::mock(TcpTransport::class, Transport::class);
        $mockTransport->shouldReceive('start')->once();
        $mockTransport->shouldReceive('stop')->once();

        $mockRpcClient = Mockery::mock(JsonRpcClient::class);
        $mockRpcClient->shouldReceive('start')->once();
        $mockRpcClient->shouldReceive('stop')->once();
        $mockRpcClient->shouldReceive('setNotificationHandler')->once();
        $mockRpcClient->shouldReceive('setRequestHandler')->twice();
        $mockRpcClient->shouldReceive('request')
            ->with('status.get')
            ->once()
            ->andReturn(['version' => '', 'protocolVersion' => 2]);

        $this->app->bind(JsonRpcClient::class, fn () => $mockRpcClient);

        $client = new Client(['cli_url' => 'tcp://127.0.0.1:12345']);
        $reflection = new ReflectionClass($client);
        $transportProperty = $reflection->getProperty('transport');
        $transportProperty->setValue($client, $mockTransport);

        $client->start();
        $errors = $client->stop();

        expect($errors)->toBe([])
            ->and($client->getState())->toBe(ConnectionState::DISCONNECTED);
    });

    it('throws when github_token is used with cli_url', function () {
        expect(fn () => new Client([
            'cli_url' => 'tcp://127.0.0.1:12345',
            'github_token' => 'gho_test_token',
        ]))->toThrow(InvalidArgumentException::class, 'github_token and use_logged_in_user cannot be used with cli_url');
    });

    it('throws when use_logged_in_user is used with cli_url', function () {
        expect(fn () => new Client([
            'cli_url' => 'tcp://127.0.0.1:12345',
            'use_logged_in_user' => false,
        ]))->toThrow(InvalidArgumentException::class, 'github_token and use_logged_in_user cannot be used with cli_url');
    });
});
