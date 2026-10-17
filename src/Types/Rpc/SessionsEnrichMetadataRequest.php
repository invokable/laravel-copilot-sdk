<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Request to enrich local metadata records with available summary and context. */
readonly class SessionsEnrichMetadataRequest implements Arrayable
{
    /**
     * @param  array<int, LocalSessionMetadataValue|array<string, mixed>>  $sessions
     */
    public function __construct(
        public array $sessions,
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
                static fn (LocalSessionMetadataValue|array $session): array => $session instanceof LocalSessionMetadataValue
                    ? $session->toArray()
                    : LocalSessionMetadataValue::fromArray($session)->toArray(),
                $this->sessions,
            ),
        ];
    }
}
