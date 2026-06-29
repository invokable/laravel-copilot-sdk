<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Whether the named MCP server is running.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpIsServerRunningResult implements Arrayable
{
    /**
     * @param  bool  $running  True if the server has an active client and transport.
     */
    public function __construct(
        public bool $running,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            running: Arr::boolean($data, 'running', false),
        );
    }

    public function toArray(): array
    {
        return [
            'running' => $this->running,
        ];
    }
}
