<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Where and when an AI skill catalog reference was observed.
 *
 * @experimental
 */
readonly class CatalogAiSkillCandidateProvenance implements Arrayable
{
    /** @var string */
    public string $mediaType;

    public function __construct(
        public string $authority,
        public string $observedAt,
    ) {
        $this->mediaType = 'application/ai-skill';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            authority: $data['authority'],
            observedAt: $data['observedAt'],
        );
    }

    public function toArray(): array
    {
        return [
            'authority' => $this->authority,
            'observedAt' => $this->observedAt,
            'mediaType' => $this->mediaType,
        ];
    }
}
