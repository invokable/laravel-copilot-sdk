<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class ModelProviderStatus implements Arrayable
{
    public function __construct(
        public string $status,
        public ?string $version = null,
        public ?ModelProviderInstance $instance = null,
        public ?ModelProviderOperationOutcome $outcome = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? '',
            version: $data['version'] ?? null,
            instance: isset($data['instance']) ? ModelProviderInstance::fromArray($data['instance']) : null,
            outcome: isset($data['outcome']) ? ModelProviderOperationOutcome::fromArray($data['outcome']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'version' => $this->version,
            'instance' => $this->instance?->toArray(),
            'outcome' => $this->outcome?->toArray(),
        ], static fn ($value) => $value !== null);
    }
}
