<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of selecting an agent.
 */
readonly class SessionAgentSelectResult implements Arrayable
{
    public function __construct(
        /** The newly selected custom agent */
        public AgentInfo $agent,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            agent: AgentInfo::fromArray($data['agent']),
        );
    }

    public function toArray(): array
    {
        return [
            'agent' => $this->agent->toArray(),
        ];
    }
}
