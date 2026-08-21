<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * An inert AI skill catalog result. Discovery-only, cannot be installed.
 *
 * @experimental
 */
readonly class CatalogAiSkillCandidate implements Arrayable
{
    /** @var string */
    public string $kind;

    /** @var string */
    public string $mediaType;

    /** @var string */
    public string $installability;

    public function __construct(
        public string $handle,
        public string $handleExpiresAt,
        public string $displayName,
        public ?string $description,
        public ?string $publisher,
        public CatalogCandidateSourceUrl|CatalogCandidateSourceEmbedded $source,
        public CatalogAiSkillCandidateProvenance $provenance,
    ) {
        $this->kind = 'ai-skill';
        $this->mediaType = 'application/ai-skill';
        $this->installability = 'not-installable-kind';
    }

    public static function fromArray(array $data): static
    {
        $sourceData = $data['source'] ?? [];
        $source = ($sourceData['kind'] ?? '') === 'url'
            ? CatalogCandidateSourceUrl::fromArray($sourceData)
            : CatalogCandidateSourceEmbedded::fromArray($sourceData);

        return new static(
            handle: $data['handle'],
            handleExpiresAt: $data['handleExpiresAt'],
            displayName: $data['displayName'],
            description: $data['description'] ?? null,
            publisher: $data['publisher'] ?? null,
            source: $source,
            provenance: CatalogAiSkillCandidateProvenance::fromArray($data['provenance']),
        );
    }

    public function toArray(): array
    {
        $arr = [
            'handle' => $this->handle,
            'handleExpiresAt' => $this->handleExpiresAt,
            'kind' => $this->kind,
            'mediaType' => $this->mediaType,
            'installability' => $this->installability,
            'displayName' => $this->displayName,
            'source' => $this->source->toArray(),
            'provenance' => $this->provenance->toArray(),
        ];
        if ($this->description !== null) {
            $arr['description'] = $this->description;
        }
        if ($this->publisher !== null) {
            $arr['publisher'] = $this->publisher;
        }

        return $arr;
    }
}
