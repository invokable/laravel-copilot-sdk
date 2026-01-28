<?php

declare(strict_types=1);

namespace Revolution\Copilot\Transport;

use Closure;
use Revolution\Copilot\Contracts\Transport;
use RuntimeException;

class TcpTransport implements Transport
{
    /**
     * @var resource|null
     */
    protected mixed $socket = null;

    public function __construct(
        protected string $host = '127.0.0.1',
        protected int $port = 12345,
        protected float $connectTimeout = 5.0,
    ) {
        //
    }

    /**
     * Create a TcpTransport from a URL string.
     *
     * @param  string  $url  e.g., "tcp://127.0.0.1:12345", "127.0.0.1:12345", "12345"
     */
    public static function fromUrl(string $url): static
    {
        // Check if it's just a port number
        if (is_numeric($url)) {
            return new static(
                host: '127.0.0.1',
                port: (int) $url,
            );
        }

        // Check if it's localhost or IP without protocol and port
        if (in_array($url, ['localhost', '127.0.0.1'], true)) {
            return new static(
                host: $url,
                port: 12345,
            );
        }

        $parsed = parse_url($url);

        if ($parsed === false || ! isset($parsed['host'])) {
            throw new RuntimeException("Invalid TCP URL: {$url}");
        }

        return new static(
            host: $parsed['host'],
            port: $parsed['port'] ?? 12345,
        );
    }

    public function host(): string
    {
        return $this->host;
    }

    public function port(): int
    {
        return $this->port;
    }

    public function start(): void
    {
        $address = "tcp://{$this->host}:{$this->port}";

        $this->socket = @stream_socket_client(
            $address,
            $errno,
            $errstr,
            $this->connectTimeout,
        );

        if (! is_resource($this->socket)) {
            throw new RuntimeException("Failed to connect to {$address}: [{$errno}] {$errstr}");
        }

        stream_set_blocking($this->socket, false);
    }

    public function stop(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    public function send(string $message): void
    {
        if (! is_resource($this->socket)) {
            throw new RuntimeException('TCP connection not established');
        }

        fwrite($this->socket, $message);
        fflush($this->socket);
    }

    public function read(float $timeout = 0.1): string
    {
        if (! is_resource($this->socket)) {
            return '';
        }

        // Check if data is available using stream_select
        $read = [$this->socket];
        $write = null;
        $except = null;
        $tvSec = (int) $timeout;
        $tvUsec = (int) (($timeout - $tvSec) * 1000000);

        $ready = @stream_select($read, $write, $except, $tvSec, $tvUsec);

        if ($ready === false || $ready === 0) {
            return '';
        }

        return $this->readContent();
    }

    /**
     * Try to read content without waiting (non-blocking).
     */
    public function tryRead(): string
    {
        if (! is_resource($this->socket)) {
            return '';
        }

        // Check if data is available immediately
        $read = [$this->socket];
        $write = null;
        $except = null;

        $ready = @stream_select($read, $write, $except, 0, 0);

        if ($ready === false || $ready === 0) {
            return '';
        }

        return $this->readContent();
    }

    /**
     * Read content from the socket using Content-Length header protocol.
     *
     * Note: If using EventLoop::onReadable() callback in the future, fread() should
     * always read a multiple of 8192 bytes to work correctly with loop backends
     * other than stream_select (e.g., ext-uv, ext-ev).
     *
     * @see https://revolt.run/streams
     */
    protected function readContent(): string
    {
        if (! is_resource($this->socket)) {
            return '';
        }

        // Read header line
        $headerLine = fgets($this->socket);

        if ($headerLine === false || $headerLine === '') {
            return '';
        }

        // Parse Content-Length
        $headerLine = trim($headerLine);

        if (! str_starts_with($headerLine, 'Content-Length:')) {
            return '';
        }

        $contentLength = (int) trim(substr($headerLine, 15));

        if ($contentLength <= 0) {
            return '';
        }

        // Read empty line (header/body separator)
        fgets($this->socket);

        // Read exact content length
        $content = '';
        $remaining = $contentLength;

        while ($remaining > 0) {
            $chunk = fread($this->socket, $remaining);

            if ($chunk === false || $chunk === '') {
                return '';
            }

            $content .= $chunk;
            $remaining -= strlen($chunk);
        }

        return $content;
    }

    /**
     * Get the readable stream resource for EventLoop integration.
     *
     * @return resource|null
     */
    public function getReadableStream(): mixed
    {
        return $this->socket;
    }

    /**
     * Check if the connection is established.
     */
    public function isConnected(): bool
    {
        return is_resource($this->socket);
    }
}
