<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Whether the local session's event log path exists.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsHasEventsResult implements Arrayable
{
    public function __construct(
        public bool $exists,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(exists: Arr::boolean($data, 'exists', false));
    }

    public function toArray(): array
    {
        return ['exists' => $this->exists];
    }
}
