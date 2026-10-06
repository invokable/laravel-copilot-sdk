<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\AgentInfoSource;
use Revolution\Copilot\Enums\AgentModelPolicy;

/**
 * Information about a custom or built-in agent.
 */
readonly class AgentInfo implements Arrayable
{
    /**
     * @param  string  $name  Unique identifier of the custom agent
     * @param  string  $displayName  Human-readable display name
     * @param  string  $description  Description of the agent's purpose
     * @param  string|null  $path  Absolute local file path of the agent definition. Only set for file-based agents loaded from disk; remote agents do not have a path.
     * @param  string|null  $prompt  Custom agent system prompt, when available.
     * @param  ?bool  $userInvocable  Whether the agent can be selected directly by the user. Agents marked false are subagent-only.
     * @param  ?bool  $disableModelInvocation  Whether model-driven invocation is disabled for this agent.
     * @param  ?string  $id  Stable identifier for selection; defaults to the agent name when omitted by older runtimes.
     * @param  ?string  $model  Authored preferred model ID.
     * @param  ?AgentModelPolicy|string  $modelPolicy  Whether authored models are preferences or required constraints.
     * @param  array<string, mixed>  $mcpServers  MCP server configurations attached to the agent.
     * @param  string[]  $models  Authored preferred model IDs, in priority order.
     * @param  ?string  $reasoningEffort  Authored reasoning effort preference.
     * @param  string[]  $skills  Skill names preloaded into the agent context.
     * @param  AgentInfoSource|string|null  $source  Where the agent definition was loaded from.
     * @param  string[]  $tools  Allowed tool names for the agent.
     */
    public function __construct(
        public string $name,
        public string $displayName,
        public string $description,
        public ?string $path = null,
        public ?string $prompt = null,
        public ?bool $userInvocable = null,
        public ?bool $disableModelInvocation = null,
        public ?string $id = null,
        public ?string $model = null,
        public AgentModelPolicy|string|null $modelPolicy = null,
        public array $mcpServers = [],
        public array $models = [],
        public ?string $reasoningEffort = null,
        public array $skills = [],
        public AgentInfoSource|string|null $source = null,
        public array $tools = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            displayName: Arr::string($data, 'displayName'),
            description: Arr::string($data, 'description'),
            path: $data['path'] ?? null,
            prompt: $data['prompt'] ?? null,
            userInvocable: $data['userInvocable'] ?? null,
            disableModelInvocation: $data['disableModelInvocation'] ?? null,
            id: $data['id'] ?? Arr::string($data, 'name'),
            model: $data['model'] ?? null,
            modelPolicy: isset($data['modelPolicy']) && is_string($data['modelPolicy'])
                ? (AgentModelPolicy::tryFrom($data['modelPolicy']) ?? $data['modelPolicy'])
                : null,
            mcpServers: $data['mcpServers'] ?? [],
            models: $data['models'] ?? [],
            reasoningEffort: $data['reasoningEffort'] ?? null,
            skills: $data['skills'] ?? [],
            source: isset($data['source']) && is_string($data['source'])
                ? (AgentInfoSource::tryFrom($data['source']) ?? $data['source'])
                : null,
            tools: $data['tools'] ?? [],
        );
    }

    public function toArray(): array
    {
        $result = [
            'name' => $this->name,
            'displayName' => $this->displayName,
            'description' => $this->description,
        ];

        if ($this->id !== null) {
            $result['id'] = $this->id;
        }

        if ($this->path !== null) {
            $result['path'] = $this->path;
        }

        if ($this->prompt !== null) {
            $result['prompt'] = $this->prompt;
        }

        if ($this->userInvocable !== null) {
            $result['userInvocable'] = $this->userInvocable;
        }

        if ($this->disableModelInvocation !== null) {
            $result['disableModelInvocation'] = $this->disableModelInvocation;
        }

        if ($this->mcpServers !== []) {
            $result['mcpServers'] = $this->mcpServers;
        }

        if ($this->model !== null) {
            $result['model'] = $this->model;
        }

        if ($this->modelPolicy !== null) {
            $result['modelPolicy'] = $this->modelPolicy instanceof AgentModelPolicy
                ? $this->modelPolicy->value
                : $this->modelPolicy;
        }

        if ($this->models !== []) {
            $result['models'] = $this->models;
        }

        if ($this->reasoningEffort !== null) {
            $result['reasoningEffort'] = $this->reasoningEffort;
        }

        if ($this->skills !== []) {
            $result['skills'] = $this->skills;
        }

        if ($this->source !== null) {
            $result['source'] = $this->source instanceof AgentInfoSource
                ? $this->source->value
                : $this->source;
        }

        if ($this->tools !== []) {
            $result['tools'] = $this->tools;
        }

        return $result;
    }
}
