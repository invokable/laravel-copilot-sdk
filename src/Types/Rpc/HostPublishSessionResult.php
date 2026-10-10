<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Published session identity and host resource. */
readonly class HostPublishSessionResult implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public ?string $sessionUri = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: $data['sessionId'] ?? '',
            sessionUri: $data['sessionUri'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'sessionId' => $this->sessionId,
            'sessionUri' => $this->sessionUri,
        ], fn ($value) => $value !== null);
    }
}
