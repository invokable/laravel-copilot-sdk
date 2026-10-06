<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of writing the autopilot objective file.
 *
 * @experimental
 */
readonly class WorkspacesWriteAutopilotObjectiveResult implements Arrayable
{
    public function __construct(
        public string $operation,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(operation: Arr::string($data, 'operation'));
    }

    public function toArray(): array
    {
        return ['operation' => $this->operation];
    }
}
