<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of selecting an agent.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionAgentSelectResult implements Arrayable
{
    /**
     * @param  AgentInfo  $agent  The newly selected custom agent
     */
    public function __construct(
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
