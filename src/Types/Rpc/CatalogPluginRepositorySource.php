<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A repository source claim declared by Agent Plugin catalog metadata.
 *
 * @experimental
 */
readonly class CatalogPluginRepositorySource implements Arrayable
{
    public function __construct(
        public string $repository,
        public string $path,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            repository: $data['repository'],
            path: $data['path'],
        );
    }

    public function toArray(): array
    {
        return [
            'repository' => $this->repository,
            'path' => $this->path,
        ];
    }
}
