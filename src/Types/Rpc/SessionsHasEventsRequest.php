<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to check for a local session's persisted event log.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsHasEventsRequest implements Arrayable
{
    public function __construct(
        public string $sessionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(sessionId: Arr::string($data, 'sessionId'));
    }

    public function toArray(): array
    {
        return ['sessionId' => $this->sessionId];
    }
}
