<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of synchronously executing a slash command.
 */
readonly class ExecuteCommandResult implements Arrayable
{
    public function __construct(
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'error' => $this->error,
        ], fn ($value) => $value !== null);
    }
}
