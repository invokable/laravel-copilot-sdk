<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Remote session connection parameters.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ConnectRemoteSessionParams implements Arrayable
{
    /**
     * @param  string  $sessionId  Session ID to connect to.
     */
    public function __construct(
        public string $sessionId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            sessionId: $data['sessionId'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
        ];
    }
}
