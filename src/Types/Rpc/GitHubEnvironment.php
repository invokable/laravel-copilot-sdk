<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\EnvironmentKind;

/**
 * Safe discovery information. Host-side relay bootstrap credentials are never included.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class GitHubEnvironment implements Arrayable
{
    /**
     * @param  array<string, string>|null  $labels
     */
    public function __construct(
        public string $id,
        public EnvironmentKind $kind,
        public string $name,
        public string $status,
        public ?EnvironmentCapabilities $capabilities = null,
        public ?array $labels = null,
        public ?string $lastHeartbeatAt = null,
        public ?string $orgId = null,
        public ?string $ownerId = null,
        public ?string $ownerType = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id', ''),
            kind: EnvironmentKind::from(Arr::string($data, 'kind', '')),
            name: Arr::string($data, 'name', ''),
            status: Arr::string($data, 'status', ''),
            capabilities: isset($data['capabilities']) ? EnvironmentCapabilities::fromArray($data['capabilities']) : null,
            labels: isset($data['labels']) ? Arr::array($data, 'labels') : null,
            lastHeartbeatAt: isset($data['lastHeartbeatAt']) ? (string) $data['lastHeartbeatAt'] : null,
            orgId: isset($data['orgId']) ? (string) $data['orgId'] : null,
            ownerId: isset($data['ownerId']) ? (string) $data['ownerId'] : null,
            ownerType: isset($data['ownerType']) ? (string) $data['ownerType'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'kind' => $this->kind->value,
            'name' => $this->name,
            'status' => $this->status,
            'capabilities' => $this->capabilities?->toArray(),
            'labels' => $this->labels,
            'lastHeartbeatAt' => $this->lastHeartbeatAt,
            'orgId' => $this->orgId,
            'ownerId' => $this->ownerId,
            'ownerType' => $this->ownerType,
        ], fn ($v) => $v !== null);
    }
}
