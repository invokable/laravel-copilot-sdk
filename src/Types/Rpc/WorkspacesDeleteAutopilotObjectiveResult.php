<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of deleting the autopilot objective file.
 *
 * @experimental
 */
readonly class WorkspacesDeleteAutopilotObjectiveResult implements Arrayable
{
    public function __construct(
        public bool $deleted,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(deleted: Arr::boolean($data, 'deleted', false));
    }

    public function toArray(): array
    {
        return ['deleted' => $this->deleted];
    }
}
