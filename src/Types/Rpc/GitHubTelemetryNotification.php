<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Payload for a `gitHubTelemetry.event` notification: a single GitHub telemetry event
 * the runtime forwards to a host connection that opted into telemetry forwarding for the session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class GitHubTelemetryNotification implements Arrayable
{
    /**
     * @param  string  $sessionId  Session the telemetry event belongs to.
     * @param  bool  $restricted  Whether this is a restricted telemetry event. Hosts must route restricted events to first-party Microsoft stores only.
     * @param  GitHubTelemetryEvent  $event  The telemetry event.
     */
    public function __construct(
        public string $sessionId,
        public bool $restricted,
        public GitHubTelemetryEvent $event,
    ) {}

    public static function fromArray(array $data): self
    {
        $event = $data['event'] instanceof GitHubTelemetryEvent
            ? $data['event']
            : GitHubTelemetryEvent::fromArray($data['event'] ?? []);

        return new self(
            sessionId: Arr::string($data, 'sessionId', ''),
            restricted: Arr::boolean($data, 'restricted', false),
            event: $event,
        );
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'restricted' => $this->restricted,
            'event' => $this->event->toArray(),
        ];
    }
}
