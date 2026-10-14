<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Pins an exact Agent Finder materialisation revision and its expected digests.
 *
 * @experimental
 */
readonly class CatalogMaterializationReference implements Arrayable
{
    public function __construct(
        public string $resourceId,
        public string $revisionId,
        public string $schemaVersion,
        public string $descriptorDigest,
        public string $bundleDigest,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            resourceId: $data['resourceId'],
            revisionId: $data['revisionId'],
            schemaVersion: $data['schemaVersion'],
            descriptorDigest: $data['descriptorDigest'],
            bundleDigest: $data['bundleDigest'],
        );
    }

    public function toArray(): array
    {
        return [
            'resourceId' => $this->resourceId,
            'revisionId' => $this->revisionId,
            'schemaVersion' => $this->schemaVersion,
            'descriptorDigest' => $this->descriptorDigest,
            'bundleDigest' => $this->bundleDigest,
        ];
    }
}
