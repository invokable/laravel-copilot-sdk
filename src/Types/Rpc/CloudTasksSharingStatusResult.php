<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The outcome and resulting sharing status of a cloud coding-agent task.
 *
 * @experimental
 */
readonly class CloudTasksSharingStatusResult implements Arrayable
{
    public function __construct(
        public bool $credentialResolved,
        public bool $ok,
        public ?string $sharingStatus = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            credentialResolved: $data['credentialResolved'],
            ok: $data['ok'],
            sharingStatus: $data['sharingStatus'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'credentialResolved' => $this->credentialResolved,
            'ok' => $this->ok,
            'sharingStatus' => $this->sharingStatus,
        ], fn ($value): bool => $value !== null);
    }
}
