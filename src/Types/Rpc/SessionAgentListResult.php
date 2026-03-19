<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of listing agents.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionAgentListResult implements Arrayable
{
    /**
     * @param  array<AgentInfo>  $agents  Available custom agents
     */
    public function __construct(
        public array $agents,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            agents: array_map(
                fn (array $agent) => AgentInfo::fromArray($agent),
                $data['agents'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'agents' => array_map(fn (AgentInfo $agent) => $agent->toArray(), $this->agents),
        ];
    }
}
