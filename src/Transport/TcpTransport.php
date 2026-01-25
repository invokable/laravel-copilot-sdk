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
     * @param  string  $url  e.g., "tcp://127.0.0.1:12345"
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

        // Use stream_select for non-blocking read with timeout
        $read = [$this->socket];
        $write = null;
        $except = null;
        $tvSec = (int) $timeout;
        $tvUsec = (int) (($timeout - $tvSec) * 1000000);

        $ready = @stream_select($read, $write, $except, $tvSec, $tvUsec);

        if ($ready === false || $ready === 0) {
            return '';
        }

        // Switch to blocking mode for reliable reads
        stream_set_blocking($this->socket, true);

        try {
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
        } finally {
            // Restore non-blocking mode
            stream_set_blocking($this->socket, false);
        }
    }

    public function stream(Closure $stream): void
    {
        $stream();
    }

    /**
     * Check if the connection is established.
     */
    public function isConnected(): bool
    {
        return is_resource($this->socket);
    }
}
