<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

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
     * @param  string|null  $prompt  Custom agent system prompt, when available.
     * @param  ?bool  $userInvocable  Whether the agent can be selected directly by the user. Agents marked false are subagent-only.
     * @param  ?bool  $disableModelInvocation  Whether model-driven invocation is disabled for this agent.
     */
    public function __construct(
        public string $name,
        public string $displayName,
        public string $description,
        public ?string $path = null,
        public ?string $prompt = null,
        public ?bool $userInvocable = null,
        public ?bool $disableModelInvocation = null,
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

        if ($this->prompt !== null) {
            $result['prompt'] = $this->prompt;
        }

        if ($this->userInvocable !== null) {
            $result['userInvocable'] = $this->userInvocable;
        }

        if ($this->disableModelInvocation !== null) {
            $result['disableModelInvocation'] = $this->disableModelInvocation;
        }

        return $result;
    }
}
