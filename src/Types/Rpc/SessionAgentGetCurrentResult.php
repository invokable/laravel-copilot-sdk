<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of getting current agent.
 */
readonly class SessionAgentGetCurrentResult implements Arrayable
{
    public function __construct(
        /** Currently selected custom agent, or null if using the default agent */
        public ?AgentInfo $agent = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            agent: isset($data['agent']) ? AgentInfo::fromArray($data['agent']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'agent' => $this->agent?->toArray(),
        ];
    }
}
