<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\TaskExecutionMode;
use Revolution\Copilot\Enums\TaskShellAttachmentMode;
use Revolution\Copilot\Enums\TaskStatus;

/**
 * Information about a shell task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TaskShellInfo implements Arrayable
{
    public function __construct(
        public string $id,
        public string $description,
        public TaskStatus $status,
        public string $startedAt,
        public string $command,
        public TaskShellAttachmentMode $attachmentMode,
        public ?string $completedAt = null,
        public ?TaskExecutionMode $executionMode = null,
        public ?bool $canPromoteToBackground = null,
        public ?string $logPath = null,
        public ?int $pid = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            description: $data['description'] ?? '',
            status: TaskStatus::from($data['status'] ?? 'running'),
            startedAt: $data['startedAt'] ?? '',
            command: $data['command'] ?? '',
            attachmentMode: TaskShellAttachmentMode::from($data['attachmentMode'] ?? 'attached'),
            completedAt: $data['completedAt'] ?? null,
            executionMode: isset($data['executionMode']) ? TaskExecutionMode::from($data['executionMode']) : null,
            canPromoteToBackground: $data['canPromoteToBackground'] ?? null,
            logPath: $data['logPath'] ?? null,
            pid: isset($data['pid']) ? (int) $data['pid'] : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'type' => 'shell',
            'id' => $this->id,
            'description' => $this->description,
            'status' => $this->status->value,
            'startedAt' => $this->startedAt,
            'command' => $this->command,
            'attachmentMode' => $this->attachmentMode->value,
        ];

        if ($this->completedAt !== null) {
            $result['completedAt'] = $this->completedAt;
        }
        if ($this->executionMode !== null) {
            $result['executionMode'] = $this->executionMode->value;
        }
        if ($this->canPromoteToBackground !== null) {
            $result['canPromoteToBackground'] = $this->canPromoteToBackground;
        }
        if ($this->logPath !== null) {
            $result['logPath'] = $this->logPath;
        }
        if ($this->pid !== null) {
            $result['pid'] = $this->pid;
        }

        return $result;
    }
}
