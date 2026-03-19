<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of getting current agent.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionAgentGetCurrentResult implements Arrayable
{
    /**
     * @param  ?AgentInfo  $agent  Currently selected custom agent, or null if using the default agent
     */
    public function __construct(
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
