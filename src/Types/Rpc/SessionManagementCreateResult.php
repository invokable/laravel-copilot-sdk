<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Identifies a session owned and supervised by the creating host.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionManagementCreateResult implements Arrayable
{
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
