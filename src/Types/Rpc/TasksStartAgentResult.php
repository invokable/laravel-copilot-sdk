<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of starting a background agent task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksStartAgentResult implements Arrayable
{
    /**
     * @param  string  $agentId  Generated agent ID for the background task
     */
    public function __construct(
        public string $agentId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            agentId: Arr::string($data, 'agentId', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'agentId' => $this->agentId,
        ];
    }
}
