<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of one factory-scoped subagent call.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryAgentResult implements Arrayable
{
    /**
     * @param  mixed  $result  Agent result, omitted when the agent produced no result.
     */
    public function __construct(
        public mixed $result = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            result: $data['result'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'result' => $this->result,
        ], fn ($v) => $v !== null);
    }
}
