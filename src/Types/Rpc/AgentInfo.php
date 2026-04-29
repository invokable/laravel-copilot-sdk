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
     * @param  string|null  $path  Absolute local file path of the agent definition. Only set for file-based agents loaded from disk; remote agents do not have a path.
     */
    public function __construct(
        public string $name,
        public string $displayName,
        public string $description,
        public ?string $path = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            displayName: $data['displayName'],
            description: $data['description'],
            path: $data['path'] ?? null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'name' => $this->name,
            'displayName' => $this->displayName,
            'description' => $this->description,
        ];

        if ($this->path !== null) {
            $result['path'] = $this->path;
        }

        return $result;
    }
}
