<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of reading the current Autopilot objective state.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AutopilotObjectiveGetStateResult implements Arrayable
{
    public function __construct(
        public ?AutopilotObjectiveState $state,
    ) {}

    public static function fromArray(array $data): self
    {
        $state = $data['state'] ?? null;

        return new self(
            state: $state === null
                ? null
                : ($state instanceof AutopilotObjectiveState
                    ? $state
                    : AutopilotObjectiveState::fromArray($state)),
        );
    }

    public function toArray(): array
    {
        return [
            'state' => $this->state?->toArray(),
        ];
    }
}
