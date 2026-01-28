<?php

declare(strict_types=1);

use Revolution\Copilot\Transport\TcpTransport;

describe('TcpTransport', function () {
    it('can be instantiated with default values', function () {
        $transport = new TcpTransport;

        expect($transport)->toBeInstanceOf(TcpTransport::class)
            ->and($transport->isConnected())->toBeFalse();
    });

    it('can be instantiated with custom values', function () {
        $transport = new TcpTransport(
            host: '192.168.1.100',
            port: 54321,
            connectTimeout: 10.0,
        );

        expect($transport)->toBeInstanceOf(TcpTransport::class)
            ->and($transport->host())->toBe('192.168.1.100')
            ->and($transport->port())->toBe(54321);
    });

    it('can be created from URL', function () {
        $transport = TcpTransport::fromUrl('tcp://127.0.0.1:12345');

        expect($transport)->toBeInstanceOf(TcpTransport::class)
            ->and($transport->host())->toBe('127.0.0.1')
            ->and($transport->port())->toBe(12345);
    });

    it('can parse URL with protocol://host', function () {
        $transport = TcpTransport::fromUrl('tcp://localhost');

        expect($transport)->toBeInstanceOf(TcpTransport::class)
            ->and($transport->host())->toBe('localhost')
            ->and($transport->port())->toBe(12345);
    });

    it('can parse URL with host:port', function () {
        $transport = TcpTransport::fromUrl('tcp://localhost:12345');

        expect($transport)->toBeInstanceOf(TcpTransport::class)
            ->and($transport->host())->toBe('localhost')
            ->and($transport->port())->toBe(12345);
    });

    it('can parse URL without protocol', function () {
        $transport = TcpTransport::fromUrl('localhost:12345');

        expect($transport)->toBeInstanceOf(TcpTransport::class)
            ->and($transport->host())->toBe('localhost')
            ->and($transport->port())->toBe(12345);
    });

    it('can parse URL with only localhost', function () {
        $transport = TcpTransport::fromUrl('localhost');

        expect($transport)->toBeInstanceOf(TcpTransport::class)
            ->and($transport->host())->toBe('localhost')
            ->and($transport->port())->toBe(12345);
    });

    it('can parse URL with only local ip', function () {
        $transport = TcpTransport::fromUrl('127.0.0.1');

        expect($transport)->toBeInstanceOf(TcpTransport::class)
            ->and($transport->host())->toBe('127.0.0.1')
            ->and($transport->port())->toBe(12345);
    });

    it('can parse URL just only port', function () {
        $transport = TcpTransport::fromUrl('12345');

        expect($transport)->toBeInstanceOf(TcpTransport::class)
            ->and($transport->host())->toBe('127.0.0.1')
            ->and($transport->port())->toBe(12345);
    });

    it('throws exception for invalid URL', function () {
        TcpTransport::fromUrl('invalid');
    })->throws(RuntimeException::class, 'Invalid TCP URL');

    it('throws exception when sending without connection', function () {
        $transport = new TcpTransport;
        $transport->send('test');
    })->throws(RuntimeException::class, 'TCP connection not established');

    it('returns empty string when reading without connection', function () {
        $transport = new TcpTransport;

        expect($transport->read())->toBe('');
    });

    it('can stop without being started', function () {
        $transport = new TcpTransport;
        $transport->stop();

        expect($transport->isConnected())->toBeFalse();
    });

    it('executes stream callback', function () {
        $transport = new TcpTransport;
        $called = false;

        $transport->stream(function () use (&$called) {
            $called = true;
        });

        expect($called)->toBeTrue();
    });
});
