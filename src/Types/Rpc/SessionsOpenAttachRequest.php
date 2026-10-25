<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Support\Arr;

/**
 * Request to attach to an active local session by ID.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsOpenAttachRequest extends SessionsOpenRequest
{
    public function __construct(
        public string $sessionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId'),
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => 'attach',
            'sessionId' => $this->sessionId,
        ];
    }
}
