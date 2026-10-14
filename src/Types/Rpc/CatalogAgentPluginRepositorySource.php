<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A GitHub repository source for an Agent Plugin install plan.
 *
 * @experimental
 */
readonly class CatalogAgentPluginRepositorySource implements Arrayable
{
    public string $kind;

    public function __construct(
        public string $repository,
        public ?string $path = null,
    ) {
        $this->kind = 'repository';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            repository: $data['repository'],
            path: $data['path'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'kind' => $this->kind,
            'repository' => $this->repository,
        ];

        if ($this->path !== null) {
            $arr['path'] = $this->path;
        }

        return $arr;
    }
}
