<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class PermissionsExtensionApprovalsGrantResult implements Arrayable
{
    public function __construct(
        public bool $granted,
        public ?PermissionsExtensionApproval $approval = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            granted: Arr::boolean($data, 'granted'),
            approval: isset($data['approval']) ? PermissionsExtensionApproval::fromArray($data['approval']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'granted' => $this->granted,
            'approval' => $this->approval?->toArray(),
        ], fn ($value): bool => $value !== null);
    }
}
