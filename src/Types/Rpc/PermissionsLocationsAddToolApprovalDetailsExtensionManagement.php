<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Extension management approval stored for a permissions location.
 */
readonly class PermissionsLocationsAddToolApprovalDetailsExtensionManagement implements Arrayable
{
    public string $kind;

    public function __construct(
        public ?string $operation = null,
    ) {
        $this->kind = 'extension-management';
    }

    public static function fromArray(array $data): self
    {
        return new self(
            operation: isset($data['operation']) ? Arr::string($data, 'operation') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind,
            'operation' => $this->operation,
        ], fn ($value): bool => $value !== null);
    }
}
