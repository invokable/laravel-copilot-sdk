<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Remote session connection result.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RemoteSessionConnectionResult implements Arrayable
{
    /**
     * @param  ConnectedRemoteSessionMetadata  $metadata  Metadata for a connected remote session.
     * @param  string  $sessionId  SDK session ID for the connected remote session.
     */
    public function __construct(
        public ConnectedRemoteSessionMetadata $metadata,
        public string $sessionId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            metadata: ConnectedRemoteSessionMetadata::fromArray($data['metadata'] ?? []),
            sessionId: Arr::string($data, 'sessionId', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'metadata' => $this->metadata->toArray(),
            'sessionId' => $this->sessionId,
        ];
    }
}
