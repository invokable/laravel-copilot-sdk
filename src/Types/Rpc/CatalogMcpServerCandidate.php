<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogMcpServerInstallability;
use Revolution\Copilot\Enums\McpServerCardMediaType;

/**
 * An inert MCP server catalog result.
 *
 * @experimental
 */
readonly class CatalogMcpServerCandidate implements Arrayable
{
    public string $kind;

    public function __construct(
        public string $handle,
        public string $handleExpiresAt,
        public McpServerCardMediaType $mediaType,
        public CatalogMcpServerInstallability $installability,
        public string $displayName,
        public ?string $description,
        public ?string $publisher,
        public CatalogCandidateSourceUrl|CatalogCandidateSourceEmbedded $source,
        public CatalogMcpServerCandidateProvenance $provenance,
    ) {
        $this->kind = 'mcp-server';
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
            mediaType: McpServerCardMediaType::from($data['mediaType']),
            installability: CatalogMcpServerInstallability::from($data['installability']),
            displayName: $data['displayName'],
            description: $data['description'] ?? null,
            publisher: $data['publisher'] ?? null,
            source: $source,
            provenance: CatalogMcpServerCandidateProvenance::fromArray($data['provenance']),
        );
    }

    public function toArray(): array
    {
        $arr = [
            'handle' => $this->handle,
            'handleExpiresAt' => $this->handleExpiresAt,
            'kind' => $this->kind,
            'mediaType' => $this->mediaType->value,
            'installability' => $this->installability->value,
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
