<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogAgentPluginMediaType;

/**
 * Where and when an Agent Plugin catalog reference was observed.
 *
 * @experimental
 */
readonly class CatalogAgentPluginCandidateProvenance implements Arrayable
{
    public function __construct(
        public string $authority,
        public string $observedAt,
        public CatalogAgentPluginMediaType $mediaType,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            authority: $data['authority'],
            observedAt: $data['observedAt'],
            mediaType: CatalogAgentPluginMediaType::from($data['mediaType']),
        );
    }

    public function toArray(): array
    {
        return [
            'authority' => $this->authority,
            'observedAt' => $this->observedAt,
            'mediaType' => $this->mediaType->value,
        ];
    }
}
