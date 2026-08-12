<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of discovering device-managed settings before a session exists.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ManagedSettingsReadResult implements Arrayable
{
    public function __construct(
        public mixed $settingsJson = null,
        public ?string $errorMessage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            settingsJson: $data['settingsJson'] ?? null,
            errorMessage: $data['errorMessage'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'settingsJson' => $this->settingsJson,
            'errorMessage' => $this->errorMessage,
        ], fn ($value) => $value !== null);
    }
}
