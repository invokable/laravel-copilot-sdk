<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\SessionMetadata;

/**
 * Sessions matching a list request.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionList implements Arrayable
{
    /**
     * @param  array<SessionMetadata>  $sessions
     */
    public function __construct(
        public array $sessions,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessions: array_map(
                fn (array $session): SessionMetadata => SessionMetadata::fromArray($session),
                $data['sessions'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'sessions' => array_map(
                fn (SessionMetadata $session): array => $session->toArray(),
                $this->sessions,
            ),
        ];
    }
}
