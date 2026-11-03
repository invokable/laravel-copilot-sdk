<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Acceptance is not a completed turn or a reply.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionManagementMessageResult implements Arrayable
{
    public function __construct(
        public bool $accepted,
        public ?string $targetSessionName = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            accepted: $data['accepted'],
            targetSessionName: $data['targetSessionName'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'accepted' => $this->accepted,
            'targetSessionName' => $this->targetSessionName,
        ], fn ($value): bool => $value !== null);
    }
}
