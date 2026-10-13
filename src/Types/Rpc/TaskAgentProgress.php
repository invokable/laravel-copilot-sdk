<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Progress snapshot for an agent task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TaskAgentProgress implements Arrayable
{
    /**
     * @param  array<TaskProgressLine>  $recentActivity
     */
    public function __construct(
        public array $recentActivity = [],
        public ?string $latestIntent = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            recentActivity: array_map(
                static fn (array $line) => TaskProgressLine::fromArray($line),
                $data['recentActivity'] ?? [],
            ),
            latestIntent: $data['latestIntent'] ?? null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'type' => 'agent',
            'recentActivity' => array_map(
                static fn (TaskProgressLine $line) => $line->toArray(),
                $this->recentActivity,
            ),
        ];

        if ($this->latestIntent !== null) {
            $result['latestIntent'] = $this->latestIntent;
        }

        return $result;
    }
}
