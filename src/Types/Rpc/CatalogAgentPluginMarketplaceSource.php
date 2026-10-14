<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A marketplace source for an Agent Plugin install plan.
 *
 * @experimental
 */
readonly class CatalogAgentPluginMarketplaceSource implements Arrayable
{
    public string $kind;

    public function __construct(
        public string $name,
        public string $repository,
    ) {
        $this->kind = 'marketplace';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            repository: $data['repository'],
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'name' => $this->name,
            'repository' => $this->repository,
        ];
    }
}
