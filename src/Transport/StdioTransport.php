<?php

declare(strict_types=1);

namespace Revolution\Copilot\Transport;

use Closure;
use Revolt\EventLoop;
use Revolution\Copilot\Contracts\Transport;

class StdioTransport implements Transport
{
    /**
     * Handler for received data.
     *
     * @var Closure(string): void|null
     */
    protected ?Closure $handler = null;

    /**
     * EventLoop callback ID for readable event.
     */
    protected ?string $callbackId = null;

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

        $this->callbackId = EventLoop::onReadable($this->stdout, function (): void {
            $content = $this->readContent();

            if ($content !== '' && $this->handler !== null) {
                ($this->handler)($content);
            }
        });
    }

    public function stop(): void
    {
        if ($this->callbackId !== null) {
            EventLoop::cancel($this->callbackId);
            $this->callbackId = null;
        }
    }

    public function send(string $message): void
    {
        fwrite($this->stdin, $message);
        fflush($this->stdin);
    }

    public function onReceive(Closure $handler): void
    {
        $this->handler = $handler;
    }

    /**
     * Read content from the stream using Content-Length header protocol.
     *
     * Note: fread() should always read a multiple of 8192 bytes to work correctly
     * with loop backends other than stream_select (e.g., ext-uv, ext-ev).
     *
     * @see https://revolt.run/streams
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
            $chunk = fread($this->stdout, 8192);

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
}
