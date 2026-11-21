<?php

declare(strict_types=1);

use Revolt\EventLoop;
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

    it('can stop without being started', function () {
        $transport = new TcpTransport;
        $transport->stop();

        expect($transport->isConnected())->toBeFalse();
    });

    it('closes the socket and cancels pending reads when stopped', function () {
        [$socket, $peer] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        $transport = new TcpTransport;
        $callbackId = EventLoop::onReadable($socket, fn () => null);
        (new ReflectionProperty(TcpTransport::class, 'socket'))->setValue($transport, $socket);
        (new ReflectionProperty(TcpTransport::class, 'callbackId'))->setValue($transport, $callbackId);

        $transport->stop();
        fclose($peer);

        expect($transport->isConnected())->toBeFalse()
            ->and(is_resource($socket))->toBeFalse();
    });

    it('can send messages over an established connection', function () {
        [$socket, $peer] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        $transport = new TcpTransport;
        (new ReflectionProperty(TcpTransport::class, 'socket'))->setValue($transport, $socket);

        $transport->send('hello');
        $message = fread($peer, 5);
        $transport->stop();
        fclose($peer);

        expect($message)->toBe('hello');
    });

    it('returns empty content when no connection is established', function () {
        $transport = new TcpTransport;
        $readContent = new ReflectionMethod(TcpTransport::class, 'readContent');

        expect($readContent->invoke($transport))->toBe('');
    });

    it('returns empty content for invalid or empty content-length headers', function () {
        $readContent = new ReflectionMethod(TcpTransport::class, 'readContent');

        foreach (["Invalid header\r\n", "Content-Length: 0\r\n"] as $header) {
            [$socket, $peer] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
            fwrite($peer, $header);
            (new ReflectionProperty(TcpTransport::class, 'socket'))->setValue($transport = new TcpTransport, $socket);

            expect($readContent->invoke($transport))->toBe('');

            $transport->stop();
            fclose($peer);
        }
    });

    it('reads content-length framed messages from an established connection', function () {
        [$socket, $peer] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        fwrite($peer, "Content-Length: 5\r\n\r\nhello");
        $transport = new TcpTransport;
        (new ReflectionProperty(TcpTransport::class, 'socket'))->setValue($transport, $socket);

        $message = (new ReflectionMethod(TcpTransport::class, 'readContent'))->invoke($transport);
        $isBlocking = stream_get_meta_data($socket)['blocked'];
        $transport->stop();
        fclose($peer);

        expect($message)->toBe('hello')
            ->and($isBlocking)->toBeFalse();
    });

    it('can set onReceive handler', function () {
        $transport = new TcpTransport;
        $called = false;

        $transport->onReceive(function (string $content) use (&$called) {
            $called = true;
        });

        // Handler should be set but not called yet (no connection)
        expect($called)->toBeFalse();
    });
});
