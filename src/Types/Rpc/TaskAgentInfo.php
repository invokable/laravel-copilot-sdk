<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\TaskExecutionMode;
use Revolution\Copilot\Enums\TaskStatus;

/**
 * Information about an agent task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TaskAgentInfo implements Arrayable
{
    public function __construct(
        public string $id,
        public string $toolCallId,
        public string $description,
        public TaskStatus $status,
        public string $startedAt,
        public string $agentType,
        public string $prompt,
        public ?string $completedAt = null,
        public ?int $activeTimeMs = null,
        public ?string $activeStartedAt = null,
        public ?string $error = null,
        public ?string $result = null,
        public ?string $model = null,
        public ?TaskExecutionMode $executionMode = null,
        public ?bool $canPromoteToBackground = null,
        public ?string $latestResponse = null,
        public ?string $idleSince = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            toolCallId: $data['toolCallId'] ?? '',
            description: $data['description'] ?? '',
            status: TaskStatus::from($data['status'] ?? 'running'),
            startedAt: $data['startedAt'] ?? '',
            agentType: $data['agentType'] ?? '',
            prompt: $data['prompt'] ?? '',
            completedAt: $data['completedAt'] ?? null,
            activeTimeMs: isset($data['activeTimeMs']) ? (int) $data['activeTimeMs'] : null,
            activeStartedAt: $data['activeStartedAt'] ?? null,
            error: $data['error'] ?? null,
            result: $data['result'] ?? null,
            model: $data['model'] ?? null,
            executionMode: isset($data['executionMode']) ? TaskExecutionMode::from($data['executionMode']) : null,
            canPromoteToBackground: $data['canPromoteToBackground'] ?? null,
            latestResponse: $data['latestResponse'] ?? null,
            idleSince: $data['idleSince'] ?? null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'type' => 'agent',
            'id' => $this->id,
            'toolCallId' => $this->toolCallId,
            'description' => $this->description,
            'status' => $this->status->value,
            'startedAt' => $this->startedAt,
            'agentType' => $this->agentType,
            'prompt' => $this->prompt,
        ];

        if ($this->completedAt !== null) {
            $result['completedAt'] = $this->completedAt;
        }
        if ($this->activeTimeMs !== null) {
            $result['activeTimeMs'] = $this->activeTimeMs;
        }
        if ($this->activeStartedAt !== null) {
            $result['activeStartedAt'] = $this->activeStartedAt;
        }
        if ($this->error !== null) {
            $result['error'] = $this->error;
        }
        if ($this->result !== null) {
            $result['result'] = $this->result;
        }
        if ($this->model !== null) {
            $result['model'] = $this->model;
        }
        if ($this->executionMode !== null) {
            $result['executionMode'] = $this->executionMode->value;
        }
        if ($this->canPromoteToBackground !== null) {
            $result['canPromoteToBackground'] = $this->canPromoteToBackground;
        }
        if ($this->latestResponse !== null) {
            $result['latestResponse'] = $this->latestResponse;
        }
        if ($this->idleSince !== null) {
            $result['idleSince'] = $this->idleSince;
        }

        return $result;
    }
}
