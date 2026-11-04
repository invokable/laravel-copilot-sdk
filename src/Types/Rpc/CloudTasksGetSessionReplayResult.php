<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The recorded events for replaying a cloud coding-agent session.
 *
 * @experimental
 */
readonly class CloudTasksGetSessionReplayResult implements Arrayable
{
    public function __construct(
        public string $replayEventsJson,
        public string $observedEventsJson,
        public bool $preStart,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            replayEventsJson: $data['replayEventsJson'],
            observedEventsJson: $data['observedEventsJson'],
            preStart: $data['preStart'],
        );
    }

    public function toArray(): array
    {
        return [
            'replayEventsJson' => $this->replayEventsJson,
            'observedEventsJson' => $this->observedEventsJson,
            'preStart' => $this->preStart,
        ];
    }
}
