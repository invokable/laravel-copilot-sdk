<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ConnectedRemoteSessionMetadataKind;

/**
 * Metadata for a connected remote session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ConnectedRemoteSessionMetadata implements Arrayable
{
    /**
     * @param  ConnectedRemoteSessionMetadataKind  $kind  Neutral SDK discriminator for the connected remote session kind.
     * @param  string  $modifiedTime  Last session update time as an ISO 8601 string.
     * @param  ConnectedRemoteSessionMetadataRepository  $repository  Repository associated with the connected remote session.
     * @param  string  $sessionId  SDK session ID for the connected remote session.
     * @param  string  $startTime  Session start time as an ISO 8601 string.
     * @param  ?string  $name  Optional friendly session name.
     * @param  ?int  $pullRequestNumber  Pull request number associated with the session.
     * @param  ?string  $resourceId  Original remote resource identifier.
     * @param  ?string  $staleAt  Remote session staleness deadline as an ISO 8601 string.
     * @param  ?string  $state  Remote session state returned by the backing service.
     * @param  ?string  $summary  Optional session summary.
     */
    public function __construct(
        public ConnectedRemoteSessionMetadataKind $kind,
        public string $modifiedTime,
        public ConnectedRemoteSessionMetadataRepository $repository,
        public string $sessionId,
        public string $startTime,
        public ?string $name = null,
        public ?int $pullRequestNumber = null,
        public ?string $resourceId = null,
        public ?string $staleAt = null,
        public ?string $state = null,
        public ?string $summary = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: ConnectedRemoteSessionMetadataKind::from($data['kind'] ?? ''),
            modifiedTime: $data['modifiedTime'] ?? '',
            repository: ConnectedRemoteSessionMetadataRepository::fromArray($data['repository'] ?? []),
            sessionId: $data['sessionId'] ?? '',
            startTime: $data['startTime'] ?? '',
            name: $data['name'] ?? null,
            pullRequestNumber: $data['pullRequestNumber'] ?? null,
            resourceId: $data['resourceId'] ?? null,
            staleAt: $data['staleAt'] ?? null,
            state: $data['state'] ?? null,
            summary: $data['summary'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind->value,
            'modifiedTime' => $this->modifiedTime,
            'repository' => $this->repository->toArray(),
            'sessionId' => $this->sessionId,
            'startTime' => $this->startTime,
            'name' => $this->name,
            'pullRequestNumber' => $this->pullRequestNumber,
            'resourceId' => $this->resourceId,
            'staleAt' => $this->staleAt,
            'state' => $this->state,
            'summary' => $this->summary,
        ], fn ($value) => $value !== null);
    }
}
