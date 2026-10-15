<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Catalogue identity retained at installation planning time.
 *
 * @experimental
 */
readonly class InstallationCatalogueIdentity implements Arrayable
{
    /**
     * @param  array<string, mixed>|null  $trustAtInstall
     */
    public function __construct(
        public string $displayName,
        public string $source,
        public ?string $description = null,
        public ?string $itemUrl = null,
        public ?string $publisher = null,
        public ?string $resourceId = null,
        public ?array $trustAtInstall = null,
        public ?string $version = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            displayName: $data['displayName'],
            source: $data['source'],
            description: $data['description'] ?? null,
            itemUrl: $data['itemUrl'] ?? null,
            publisher: $data['publisher'] ?? null,
            resourceId: $data['resourceId'] ?? null,
            trustAtInstall: $data['trustAtInstall'] ?? null,
            version: $data['version'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'displayName' => $this->displayName,
            'source' => $this->source,
        ];

        foreach ([
            'description' => $this->description,
            'itemUrl' => $this->itemUrl,
            'publisher' => $this->publisher,
            'resourceId' => $this->resourceId,
            'trustAtInstall' => $this->trustAtInstall,
            'version' => $this->version,
        ] as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
