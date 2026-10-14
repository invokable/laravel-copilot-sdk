<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogAgentPluginCompatibilityTag;
use Revolution\Copilot\Enums\CatalogAgentPluginInstallability;
use Revolution\Copilot\Enums\CatalogAgentPluginMediaType;

/**
 * An inert Agent Plugin catalog result.
 *
 * @experimental
 */
readonly class CatalogAgentPluginCandidate implements Arrayable
{
    public string $kind;

    /**
     * @param  CatalogAgentPluginCompatibilityTag[]  $compatibilityTags
     */
    public function __construct(
        public CatalogAgentPluginMediaType $mediaType,
        public string $identity,
        public string $displayName,
        public CatalogPluginRepositorySource $source,
        public array $compatibilityTags,
        public CatalogAgentPluginCandidateProvenance $provenance,
        public ?string $version = null,
        public ?string $description = null,
        public ?string $publisher = null,
        public ?CatalogAgentPluginInstallability $installability = null,
        public ?CatalogAgentPluginInstallPlan $installPlan = null,
        public CatalogTrustSnapshotCurrent|CatalogTrustSnapshotAbsent|CatalogTrustSnapshotStale|CatalogTrustSnapshotDowngraded|CatalogTrustSnapshotRevoked|CatalogTrustSnapshotUnsupported|CatalogTrustSnapshotMalformed|null $trust = null,
    ) {
        $this->kind = 'plugin';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            mediaType: CatalogAgentPluginMediaType::from($data['mediaType']),
            identity: $data['identity'],
            displayName: $data['displayName'],
            source: CatalogPluginRepositorySource::fromArray($data['source']),
            compatibilityTags: array_map(
                fn (string $tag): CatalogAgentPluginCompatibilityTag => CatalogAgentPluginCompatibilityTag::from($tag),
                $data['compatibilityTags'] ?? [],
            ),
            provenance: CatalogAgentPluginCandidateProvenance::fromArray($data['provenance']),
            version: $data['version'] ?? null,
            description: $data['description'] ?? null,
            publisher: $data['publisher'] ?? null,
            installability: isset($data['installability'])
                ? CatalogAgentPluginInstallability::from($data['installability'])
                : null,
            installPlan: isset($data['installPlan'])
                ? CatalogAgentPluginInstallPlan::fromArray($data['installPlan'])
                : null,
            trust: isset($data['trust']) ? CatalogTrustSnapshot::fromArray($data['trust']) : null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'kind' => $this->kind,
            'mediaType' => $this->mediaType->value,
            'identity' => $this->identity,
            'displayName' => $this->displayName,
            'source' => $this->source->toArray(),
            'compatibilityTags' => array_map(
                fn (CatalogAgentPluginCompatibilityTag $tag): string => $tag->value,
                $this->compatibilityTags,
            ),
            'provenance' => $this->provenance->toArray(),
        ];

        if ($this->version !== null) {
            $arr['version'] = $this->version;
        }
        if ($this->description !== null) {
            $arr['description'] = $this->description;
        }
        if ($this->publisher !== null) {
            $arr['publisher'] = $this->publisher;
        }
        if ($this->installability !== null) {
            $arr['installability'] = $this->installability->value;
        }
        if ($this->installPlan !== null) {
            $arr['installPlan'] = $this->installPlan->toArray();
        }
        if ($this->trust !== null) {
            $arr['trust'] = $this->trust->toArray();
        }

        return $arr;
    }
}
