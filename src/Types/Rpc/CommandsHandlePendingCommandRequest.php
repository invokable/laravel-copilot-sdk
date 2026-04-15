<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for handling a pending command.
 */
readonly class CommandsHandlePendingCommandRequest implements Arrayable
{
    /**
     * @param  string  $requestId  Request ID from the command invocation event
     * @param  ?string  $error  Error message if the command handler failed
     */
    public function __construct(
        public string $requestId,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: $data['requestId'],
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'error' => $this->error,
        ], fn ($v) => $v !== null);
    }
}
