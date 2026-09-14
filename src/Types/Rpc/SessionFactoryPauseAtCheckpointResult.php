<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FactoryPauseCheckpointAction;

/**
 * Result of pausing at a durable factory checkpoint.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionFactoryPauseAtCheckpointResult implements Arrayable
{
    /**
     * @param  FactoryPauseCheckpointAction  $action  Whether this execution attempt must pause or may continue.
     */
    public function __construct(
        public FactoryPauseCheckpointAction $action,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            action: FactoryPauseCheckpointAction::from(Arr::string($data, 'action')),
        );
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action->value,
        ];
    }
}
