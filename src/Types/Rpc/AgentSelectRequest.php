<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for selecting an agent.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentSelectRequest implements Arrayable
{
    /**
     * @param  string  $name  Name of the custom agent to select
     */
    public function __construct(
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
