<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Shipped agents available under the requested runtime context. */
readonly class AgentsGetAvailableBuiltinsResult implements Arrayable
{
    /** @param BuiltinAgentSummary[] $agents */
    public function __construct(public array $agents = []) {}

    public static function fromArray(array $data): self
    {
        return new self(agents: array_map(
            fn (array $agent) => BuiltinAgentSummary::fromArray($agent),
            $data['agents'] ?? [],
        ));
    }

    public function toArray(): array
    {
        return ['agents' => array_map(fn (BuiltinAgentSummary $agent) => $agent->toArray(), $this->agents)];
    }
}
