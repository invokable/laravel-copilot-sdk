<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\TaskClientStatus;

/**
 * Generic progress snapshot for a client-owned task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TaskClientProgress implements Arrayable
{
    /**
     * @param  array<TaskProgressLine>  $recentActivity
     */
    public function __construct(
        public TaskClientStatus $status,
        public int $sequence,
        public string $updatedAt,
        public array $recentActivity = [],
        public ?string $phase = null,
        public int|float|null $percentage = null,
        public ?string $lastMessage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: TaskClientStatus::from($data['status'] ?? 'running'),
            sequence: Arr::integer($data, 'sequence', 0),
            updatedAt: Arr::string($data, 'updatedAt', ''),
            recentActivity: array_map(
                static fn (array $line) => TaskProgressLine::fromArray($line),
                $data['recentActivity'] ?? [],
            ),
            phase: $data['phase'] ?? null,
            percentage: $data['percentage'] ?? null,
            lastMessage: $data['lastMessage'] ?? null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'type' => 'client',
            'status' => $this->status->value,
            'sequence' => $this->sequence,
            'updatedAt' => $this->updatedAt,
        ];

        foreach ([
            'phase' => $this->phase,
            'percentage' => $this->percentage,
            'lastMessage' => $this->lastMessage,
        ] as $key => $value) {
            if ($value !== null) {
                $result[$key] = $value;
            }
        }

        $result['recentActivity'] = array_map(
            static fn (TaskProgressLine $line) => $line->toArray(),
            $this->recentActivity,
        );

        return $result;
    }
}
