<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\McpServerStatus;

/**
 * Observational state for a matching materialized MCP server.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpConfiguredServerState implements Arrayable
{
    /**
     * @param  ?string  $error  Observed connection error
     * @param  ?bool  $toolsLoaded  Whether the current connection has loaded its tool list, when reported
     */
    public function __construct(
        public McpServerStatus $status,
        public ?string $error = null,
        public ?bool $toolsLoaded = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: McpServerStatus::from($data['status']),
            error: $data['error'] ?? null,
            toolsLoaded: isset($data['toolsLoaded']) ? Arr::boolean($data, 'toolsLoaded') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status->value,
            'error' => $this->error,
            'toolsLoaded' => $this->toolsLoaded,
        ], fn ($value) => $value !== null);
    }
}
