<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Managed-settings authoring schema recognized by this runtime. */
readonly class ManagedSettingsSchemaResult implements Arrayable
{
    public function __construct(
        public mixed $schema = null,
        public string $runtimeVersion = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            schema: $data['schema'] ?? null,
            runtimeVersion: $data['runtimeVersion'] ?? '',
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'schema' => $this->schema,
            'runtimeVersion' => $this->runtimeVersion,
        ], fn ($value) => $value !== null);
    }
}
