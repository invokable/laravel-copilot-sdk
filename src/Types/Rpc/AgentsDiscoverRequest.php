<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional project paths to include in agent discovery.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentsDiscoverRequest implements Arrayable
{
    /**
     * @param  ?bool  $excludeHostAgents  When true, omit the host's agents (user-level and plugin agents).
     * @param  ?array<string>  $projectPaths  Optional list of project directory paths to scan.
     */
    public function __construct(
        public ?bool $excludeHostAgents = null,
        public ?array $projectPaths = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            excludeHostAgents: $data['excludeHostAgents'] ?? null,
            projectPaths: $data['projectPaths'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'excludeHostAgents' => $this->excludeHostAgents,
            'projectPaths' => $this->projectPaths,
        ], fn ($v) => $v !== null);
    }
}
