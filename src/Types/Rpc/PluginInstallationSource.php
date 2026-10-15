<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Exact verified Agent Plugin source identity.
 *
 * @experimental
 */
readonly class PluginInstallationSource implements Arrayable
{
    public function __construct(
        public string $bundleDigest,
        public string $catalogRevisionId,
        public string $descriptorDigest,
        public string $repository,
        public string $repositoryId,
        public string $resourceId,
        public string $revision,
        public string $root,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            bundleDigest: $data['bundleDigest'],
            catalogRevisionId: $data['catalogRevisionId'],
            descriptorDigest: $data['descriptorDigest'],
            repository: $data['repository'],
            repositoryId: $data['repositoryId'],
            resourceId: $data['resourceId'],
            revision: $data['revision'],
            root: $data['root'],
        );
    }

    public function toArray(): array
    {
        return [
            'bundleDigest' => $this->bundleDigest,
            'catalogRevisionId' => $this->catalogRevisionId,
            'descriptorDigest' => $this->descriptorDigest,
            'repository' => $this->repository,
            'repositoryId' => $this->repositoryId,
            'resourceId' => $this->resourceId,
            'revision' => $this->revision,
            'root' => $this->root,
        ];
    }
}
