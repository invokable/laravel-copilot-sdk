<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for a ping.
 */
readonly class PingRequest implements Arrayable
{
    /**
     * @param  ?string  $message  Optional message to echo back
     */
    public function __construct(
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
