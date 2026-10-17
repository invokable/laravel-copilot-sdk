<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Enriched local metadata records returned by the server. */
readonly class SessionsEnrichMetadataResult implements Arrayable
{
    /**
     * @param  array<int, LocalSessionMetadataValue>  $sessions
     */
    public function __construct(
        public array $sessions = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessions: array_map(
                static fn (LocalSessionMetadataValue|array $session): LocalSessionMetadataValue => $session instanceof LocalSessionMetadataValue
                    ? $session
                    : LocalSessionMetadataValue::fromArray($session),
                $data['sessions'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'sessions' => array_map(
                static fn (LocalSessionMetadataValue $session): array => $session->toArray(),
                $this->sessions,
            ),
        ];
    }
}
