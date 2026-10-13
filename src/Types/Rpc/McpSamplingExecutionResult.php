<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Outcome of an MCP sampling execution.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpSamplingExecutionResult implements Arrayable
{
    /**
     * @param  array<string, mixed>|null  $result  MCP CreateMessageResult when the action is success.
     */
    public function __construct(
        public string $action,
        public ?array $result = null,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            action: Arr::string($data, 'action'),
            result: $data['result'] ?? null,
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'action' => $this->action,
            'result' => $this->result,
            'error' => $this->error,
        ], fn ($value) => $value !== null);
    }
}
