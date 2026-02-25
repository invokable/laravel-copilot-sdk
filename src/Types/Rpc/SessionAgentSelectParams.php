<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for selecting an agent.
 */
readonly class SessionAgentSelectParams implements Arrayable
{
    public function __construct(
        /** Name of the custom agent to select */
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
