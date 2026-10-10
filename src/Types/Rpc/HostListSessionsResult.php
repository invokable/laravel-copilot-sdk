<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Complete advertised host session catalog. */
readonly class HostListSessionsResult implements Arrayable
{
    /** @param array<HostSessionSummary> $sessions */
    public function __construct(
        public array $sessions = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessions: array_map(
                static fn (HostSessionSummary|array $session) => $session instanceof HostSessionSummary
                    ? $session
                    : HostSessionSummary::fromArray($session),
                $data['sessions'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'sessions' => array_map(
                static fn (HostSessionSummary $session) => $session->toArray(),
                $this->sessions,
            ),
        ];
    }
}
