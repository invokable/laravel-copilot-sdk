<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for a ping request.
 */
readonly class PingParams implements Arrayable
{
    public function __construct(
        /** Optional message to echo back */
        public ?string $message = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            message: $data['message'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'message' => $this->message,
        ], fn ($v) => $v !== null);
    }
}
