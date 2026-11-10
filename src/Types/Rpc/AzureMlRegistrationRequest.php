<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AzureMlRegistrationOperation;
use Revolution\Copilot\Enums\AzureMlRegistry;

/**
 * Managed Azure ML registration changes under a connection-owned family lease.
 *
 * @experimental
 */
readonly class AzureMlRegistrationRequest implements Arrayable
{
    public function __construct(
        public AzureMlRegistry|string $registry,
        public string $name,
        public string $leaseId,
        public AzureMlRegistrationOperation|string $operation,
        public ?ModelProviderSaveConfigurationRequest $entries = null,
        public mixed $before = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $registry = $data['registry'] ?? '';
        $operation = $data['operation'] ?? '';
        $entries = $data['entries'] ?? null;

        return new self(
            registry: is_string($registry) ? (AzureMlRegistry::tryFrom($registry) ?? $registry) : '',
            name: $data['name'] ?? '',
            leaseId: $data['leaseId'] ?? '',
            operation: is_string($operation)
                ? (AzureMlRegistrationOperation::tryFrom($operation) ?? $operation)
                : '',
            entries: $entries instanceof ModelProviderSaveConfigurationRequest
                ? $entries
                : (is_array($entries) ? ModelProviderSaveConfigurationRequest::fromArray($entries) : null),
            before: $data['before'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'registry' => $this->registry instanceof AzureMlRegistry ? $this->registry->value : $this->registry,
            'name' => $this->name,
            'leaseId' => $this->leaseId,
            'operation' => $this->operation instanceof AzureMlRegistrationOperation
                ? $this->operation->value
                : $this->operation,
            'entries' => $this->entries?->toArray(),
            'before' => $this->before,
        ], static fn ($value) => $value !== null);
    }
}
