<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Per-source attribution breakdown for the session's current context window, or null if uninitialized.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class MetadataContextAttributionResult implements Arrayable
{
    /**
     * @param  SessionContextAttribution|null  $contextAttribution  Attribution data, or null if the session has not yet initialized.
     */
    public function __construct(
        public ?SessionContextAttribution $contextAttribution = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contextAttribution: isset($data['contextAttribution']) && is_array($data['contextAttribution'])
                ? SessionContextAttribution::fromArray($data['contextAttribution'])
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'contextAttribution' => $this->contextAttribution?->toArray(),
        ], fn ($value): bool => $value !== null);
    }
}
