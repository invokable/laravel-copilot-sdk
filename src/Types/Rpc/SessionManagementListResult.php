<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Eligible live peers reported by their owning runtimes.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionManagementListResult implements Arrayable
{
    /**
     * @param  array<ManagedSessionInfo>  $sessions
     */
    public function __construct(
        public array $sessions,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessions: array_map(
                fn (array $session): ManagedSessionInfo => ManagedSessionInfo::fromArray($session),
                $data['sessions'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'sessions' => array_map(
                fn (ManagedSessionInfo $session): array => $session->toArray(),
                $this->sessions,
            ),
        ];
    }
}
