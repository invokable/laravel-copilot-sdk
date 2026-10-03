<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** A managed-settings channel and the document it delivered. */
readonly class ManagedSettingsLayer implements Arrayable
{
    public function __construct(
        public string $source,
        public mixed $settings = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            source: $data['source'] ?? '',
            settings: $data['settings'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'source' => $this->source,
            'settings' => $this->settings,
        ], static fn ($value) => $value !== null);
    }
}
