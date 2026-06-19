<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Agents discovered across user, project, plugin, and remote sources.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ServerAgentList implements Arrayable
{
    /**
     * @param  AgentInfo[]  $agents  All discovered agents across all sources
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
            'agents' => array_map(fn (AgentInfo $a) => $a->toArray(), $this->agents),
        ];
    }
}
