<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request to start a background agent task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksStartAgentRequest implements Arrayable
{
    /**
     * @param  string  $agentType  Type of agent to start (e.g., 'explore', 'task', 'general-purpose')
     * @param  string  $prompt  Task prompt for the agent
     * @param  string  $name  Short name for the agent, used to generate a human-readable ID
     * @param  string|null  $description  Short description of the task
     * @param  string|null  $model  Optional model override
     */
    public function __construct(
        public string $agentType,
        public string $prompt,
        public string $name,
        public ?string $description = null,
        public ?string $model = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            agentType: $data['agentType'] ?? '',
            prompt: $data['prompt'] ?? '',
            name: $data['name'] ?? '',
            description: $data['description'] ?? null,
            model: $data['model'] ?? null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'agentType' => $this->agentType,
            'prompt' => $this->prompt,
            'name' => $this->name,
        ];

        if ($this->description !== null) {
            $result['description'] = $this->description;
        }
        if ($this->model !== null) {
            $result['model'] = $this->model;
        }

        return $result;
    }
}
