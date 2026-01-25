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
        // Use stream_select for non-blocking read with timeout
        $read = [$this->stdout];
        $write = null;
        $except = null;
        $tvSec = (int) $timeout;
        $tvUsec = (int) (($timeout - $tvSec) * 1000000);

        $ready = @stream_select($read, $write, $except, $tvSec, $tvUsec);

        if ($ready === false || $ready === 0) {
            return '';
        }

        // Switch to blocking mode for reliable reads
        stream_set_blocking($this->stdout, true);

        try {
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
        } finally {
            // Restore non-blocking mode
            stream_set_blocking($this->stdout, false);
        }
    }

    public function stream(Closure $stream): void
    {
        $stream();
    }
}
