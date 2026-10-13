<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\TaskClientStatus;
use Revolution\Copilot\Enums\TaskExecutionMode;

/**
 * Information about a client-owned task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TaskClientInfo implements Arrayable
{
    public function __construct(
        public string $id,
        public string $clientTaskId,
        public string $description,
        public TaskClientStatus $status,
        public TaskClientOwner $owner,
        public string $startedAt,
        public string $updatedAt,
        public int $activeTimeMs,
        public TaskExecutionMode $executionMode,
        public bool $canCancel,
        public int $sequence,
        public ?string $displayName = null,
        public ?string $completedAt = null,
        public ?string $activeStartedAt = null,
        public ?string $idleSince = null,
        public ?string $orphanedAt = null,
        public ?string $reclaimedAt = null,
        public mixed $result = null,
        public ?string $error = null,
        public ?string $errorCode = null,
        public ?string $cancellationReason = null,
        private bool $resultProvided = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id', ''),
            clientTaskId: Arr::string($data, 'clientTaskId', ''),
            description: Arr::string($data, 'description', ''),
            status: TaskClientStatus::from($data['status'] ?? 'running'),
            owner: TaskClientOwner::fromArray($data['owner'] ?? []),
            startedAt: Arr::string($data, 'startedAt', ''),
            updatedAt: Arr::string($data, 'updatedAt', ''),
            activeTimeMs: Arr::integer($data, 'activeTimeMs', 0),
            executionMode: TaskExecutionMode::from($data['executionMode'] ?? 'background'),
            canCancel: Arr::boolean($data, 'canCancel', false),
            sequence: Arr::integer($data, 'sequence', 0),
            displayName: $data['displayName'] ?? null,
            completedAt: $data['completedAt'] ?? null,
            activeStartedAt: $data['activeStartedAt'] ?? null,
            idleSince: $data['idleSince'] ?? null,
            orphanedAt: $data['orphanedAt'] ?? null,
            reclaimedAt: $data['reclaimedAt'] ?? null,
            result: $data['result'] ?? null,
            error: $data['error'] ?? null,
            errorCode: $data['errorCode'] ?? null,
            cancellationReason: $data['cancellationReason'] ?? null,
            resultProvided: array_key_exists('result', $data),
        );
    }

    public function toArray(): array
    {
        $result = [
            'type' => 'client',
            'id' => $this->id,
            'clientTaskId' => $this->clientTaskId,
        ];

        if ($this->displayName !== null) {
            $result['displayName'] = $this->displayName;
        }

        $result['description'] = $this->description;
        $result['status'] = $this->status->value;
        $result['owner'] = $this->owner->toArray();
        $result['startedAt'] = $this->startedAt;
        $result['updatedAt'] = $this->updatedAt;

        if ($this->completedAt !== null) {
            $result['completedAt'] = $this->completedAt;
        }

        $result['activeTimeMs'] = $this->activeTimeMs;

        foreach ([
            'activeStartedAt' => $this->activeStartedAt,
            'idleSince' => $this->idleSince,
            'orphanedAt' => $this->orphanedAt,
            'reclaimedAt' => $this->reclaimedAt,
        ] as $key => $value) {
            if ($value !== null) {
                $result[$key] = $value;
            }
        }

        $result['executionMode'] = $this->executionMode->value;
        $result['canCancel'] = $this->canCancel;
        $result['sequence'] = $this->sequence;

        if ($this->result !== null || $this->resultProvided) {
            $result['result'] = $this->result;
        }

        foreach ([
            'error' => $this->error,
            'errorCode' => $this->errorCode,
            'cancellationReason' => $this->cancellationReason,
        ] as $key => $value) {
            if ($value !== null) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
