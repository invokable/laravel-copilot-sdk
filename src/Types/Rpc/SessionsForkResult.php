<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of forking a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsForkResult implements Arrayable
{
    /**
     * @param  string  $sessionId  The new forked session's ID
     */
    public function __construct(
        public string $sessionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: $data['sessionId'],
        );
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
        ];
    }
}
