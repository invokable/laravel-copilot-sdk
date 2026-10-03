<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ManagedSettingsChannel;

/** A candidate managed-settings document for one composition channel. */
readonly class ManagedSettingsComposeLayer implements Arrayable
{
    public function __construct(
        public ManagedSettingsChannel|string $source,
        public mixed $settings = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $source = $data['source'] ?? '';

        return new self(
            source: is_string($source) ? (ManagedSettingsChannel::tryFrom($source) ?? $source) : $source,
            settings: $data['settings'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'source' => $this->source instanceof ManagedSettingsChannel ? $this->source->value : $this->source,
            'settings' => $this->settings,
        ], static fn ($value) => $value !== null);
    }
}
