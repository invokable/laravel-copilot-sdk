<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Metadata about a custom agent from the session.custom_agents_updated event.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CustomAgentEventData implements Arrayable
{
    /**
     * @param  string  $id  Unique identifier for the agent
     * @param  string  $name  Internal name of the agent
     * @param  string  $displayName  Human-readable display name
     * @param  string  $description  Description of what the agent does
     * @param  string  $source  Source location: user, project, inherited, remote, or plugin
     * @param  array<string>  $tools  List of tool names available to this agent
     * @param  bool  $userInvocable  Whether the agent can be selected by the user
     * @param  ?string  $model  Model override for this agent, if set
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $displayName,
        public string $description,
        public string $source,
        public array $tools,
        public bool $userInvocable,
        public ?string $model = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            displayName: $data['displayName'] ?? '',
            description: $data['description'] ?? '',
            source: $data['source'] ?? '',
            tools: $data['tools'] ?? [],
            userInvocable: $data['userInvocable'] ?? false,
            model: $data['model'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'displayName' => $this->displayName,
            'description' => $this->description,
            'source' => $this->source,
            'tools' => $this->tools,
            'userInvocable' => $this->userInvocable,
            'model' => $this->model,
        ], fn ($v) => $v !== null);
    }
}
