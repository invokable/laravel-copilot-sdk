<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Candidate managed-settings documents to merge without applying them. */
readonly class ManagedSettingsComposeRequest implements Arrayable
{
    /** @param array<ManagedSettingsComposeLayer|array{source: string, settings?: mixed}> $layers */
    public function __construct(public array $layers) {}

    public static function fromArray(array $data): self
    {
        return new self(array_map(
            static fn ($layer) => $layer instanceof ManagedSettingsComposeLayer ? $layer : ManagedSettingsComposeLayer::fromArray($layer),
            $data['layers'] ?? [],
        ));
    }

    public function toArray(): array
    {
        return ['layers' => array_map(
            static fn ($layer) => $layer instanceof ManagedSettingsComposeLayer ? $layer->toArray() : $layer,
            $this->layers,
        )];
    }
}
