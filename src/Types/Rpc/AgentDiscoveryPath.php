<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AgentDiscoveryPathScope;

/**
 * Schema for the AgentDiscoveryPath type.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentDiscoveryPath implements Arrayable
{
    /**
     * @param  string  $path  Absolute path of the search/create directory (may not exist on disk yet)
     * @param  AgentDiscoveryPathScope  $scope  Which tier this directory belongs to
     * @param  bool  $preferredForCreation  Whether this is the canonical directory to create a new agent in its tier
     * @param  ?string  $projectPath  The input project path this directory was derived from (only for project scope)
     */
    public function __construct(
        public string $path,
        public AgentDiscoveryPathScope $scope,
        public bool $preferredForCreation,
        public ?string $projectPath = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: $data['path'] ?? '',
            scope: AgentDiscoveryPathScope::from($data['scope'] ?? 'user'),
            preferredForCreation: (bool) ($data['preferredForCreation'] ?? false),
            projectPath: $data['projectPath'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'path' => $this->path,
            'scope' => $this->scope->value,
            'preferredForCreation' => $this->preferredForCreation,
            'projectPath' => $this->projectPath,
        ], fn ($v) => $v !== null);
    }
}
