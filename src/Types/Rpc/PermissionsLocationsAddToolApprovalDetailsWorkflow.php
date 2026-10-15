<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Workflow approval stored for a permissions location.
 */
readonly class PermissionsLocationsAddToolApprovalDetailsWorkflow implements Arrayable
{
    public string $kind;

    public function __construct(
        public ?string $approvalKey = null,
    ) {
        $this->kind = 'workflow';
    }

    public static function fromArray(array $data): self
    {
        return new self(
            approvalKey: isset($data['approvalKey']) ? Arr::string($data, 'approvalKey') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind,
            'approvalKey' => $this->approvalKey,
        ], fn ($value): bool => $value !== null);
    }
}
