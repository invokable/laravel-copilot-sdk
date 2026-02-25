<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Information about a custom agent.
 */
readonly class AgentInfo implements Arrayable
{
    public function __construct(
        /** Unique identifier of the custom agent */
        public string $name,
        /** Human-readable display name */
        public string $displayName,
        /** Description of the agent's purpose */
        public string $description,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            displayName: $data['displayName'],
            description: $data['description'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'displayName' => $this->displayName,
            'description' => $this->description,
        ];
    }
}
