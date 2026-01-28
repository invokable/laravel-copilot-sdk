<?php

declare(strict_types=1);

namespace Revolution\Copilot\Transport;

use Closure;
use Revolution\Copilot\Contracts\Transport;

class StdioTransport implements Transport
{
    /**
     * @param  resource  $stdin  Input stream (write to server)
     * @param  resource  $stdout  Output stream (read from server)
     */
    public function __construct(
        protected mixed $stdin,
        protected mixed $stdout,
    ) {
        //
    }

    public function start(): void
    {
        stream_set_blocking($this->stdout, false);
    }

    public function stop(): void
    {
        // Stdio streams are managed by ProcessManager
    }

    public function send(string $message): void
    {
        fwrite($this->stdin, $message);
        fflush($this->stdin);
    }

    public function read(float $timeout = 0.1): string
    {
        // Check if data is available using stream_select
        $read = [$this->stdout];
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
        // Check if data is available immediately
        $read = [$this->stdout];
        $write = null;
        $except = null;

        $ready = @stream_select($read, $write, $except, 0, 0);

        if ($ready === false || $ready === 0) {
            return '';
        }

        return $this->readContent();
    }

    /**
     * Read content from the stream using Content-Length header protocol.
     */
    protected function readContent(): string
    {
        // Read header line
        $headerLine = fgets($this->stdout);

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
        fgets($this->stdout);

        // Read exact content length
        $content = '';
        $remaining = $contentLength;

        while ($remaining > 0) {
            $chunk = fread($this->stdout, $remaining);

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
     * @return resource
     */
    public function getReadableStream(): mixed
    {
        return $this->stdout;
    }

    public function stream(Closure $stream): void
    {
        $stream();
    }
}
