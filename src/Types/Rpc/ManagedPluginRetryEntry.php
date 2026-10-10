<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ManagedPluginRetryStatus;

/** Retry outcome for one organization-required plugin. */
readonly class ManagedPluginRetryEntry implements Arrayable
{
    public function __construct(
        public string $spec,
        public ManagedPluginRetryStatus|string $status,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $status = $data['status'] ?? 'failed';

        return new self(
            spec: $data['spec'] ?? '',
            status: ManagedPluginRetryStatus::tryFrom($status) ?? $status,
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'spec' => $this->spec,
            'status' => $this->status instanceof ManagedPluginRetryStatus
                ? $this->status->value
                : $this->status,
            'error' => $this->error,
        ], fn ($value) => $value !== null);
    }
}
