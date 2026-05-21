<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result handle for an event-interest registration.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RegisterEventInterestResult implements Arrayable
{
    public function __construct(
        public string $handle,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            handle: $data['handle'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'handle' => $this->handle,
        ];
    }
}
