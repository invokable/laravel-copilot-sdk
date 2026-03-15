<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Information about a custom agent.
 */
readonly class AgentInfo implements Arrayable
{
    /**
     * @param  string  $name  Unique identifier of the custom agent
     * @param  string  $displayName  Human-readable display name
     * @param  string  $description  Description of the agent's purpose
     */
    public function __construct(
        public string $name,
        public string $displayName,
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
