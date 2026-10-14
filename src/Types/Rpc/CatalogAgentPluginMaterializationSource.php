<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * An Agent Finder materialization source for an Agent Plugin install plan.
 *
 * @experimental
 */
readonly class CatalogAgentPluginMaterializationSource implements Arrayable
{
    public string $kind;

    public function __construct()
    {
        $this->kind = 'materialization';
    }

    public static function fromArray(array $data): static
    {
        return new static;
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
        ];
    }
}
